"""
RAG API Backend — Pro LMS Knowledge Base
=========================================
Stack:
  LLM        : Claude (Anthropic) via langchain-anthropic
  Embeddings : Voyage AI (voyage-3-lite) — Anthropic's recommended partner
  Vector DB  : Pinecone (serverless)
  Framework  : FastAPI
"""

import os
import re
import shutil
import tempfile
from typing import Optional

# ─── Load environment ─────────────────────────────────────────────────────────
try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:
    pass

from fastapi import FastAPI, File, Form, HTTPException, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

# ─── Validate env vars ────────────────────────────────────────────────────────
ANTHROPIC_API_KEY = os.getenv("ANTHROPIC_API_KEY")
VOYAGE_API_KEY    = os.getenv("VOYAGE_API_KEY")
PINECONE_API_KEY  = os.getenv("PINECONE_API_KEY")
PINECONE_INDEX    = os.getenv("PINECONE_INDEX_NAME", "prolms-knowledge-base")

missing = [
    name for name, val in {
        "ANTHROPIC_API_KEY": ANTHROPIC_API_KEY,
        "VOYAGE_API_KEY":    VOYAGE_API_KEY,
        "PINECONE_API_KEY":  PINECONE_API_KEY,
    }.items() if not val
]
if missing:
    raise EnvironmentError(
        f"Environment variable(s) belum diset: {', '.join(missing)}\n"
        "Salin backend/.env.example ke backend/.env dan isi nilainya."
    )

# ─── LangChain imports ────────────────────────────────────────────────────────
from langchain_anthropic import ChatAnthropic
from langchain_voyageai import VoyageAIEmbeddings
from langchain_pinecone import PineconeVectorStore
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_community.document_loaders import PyPDFLoader
from langchain_core.documents import Document
from langchain_core.prompts import ChatPromptTemplate
from pinecone import Pinecone, ServerlessSpec

# ─── Initialise Pinecone ──────────────────────────────────────────────────────
pc = Pinecone(api_key=PINECONE_API_KEY)

# Buat index jika belum ada (dimension 512 = voyage-3-lite output size)
existing_indexes = [idx.name for idx in pc.list_indexes()]
if PINECONE_INDEX not in existing_indexes:
    pc.create_index(
        name=PINECONE_INDEX,
        dimension=512,
        metric="cosine",
        spec=ServerlessSpec(cloud="aws", region="us-east-1"),
    )
    print(f"[Pinecone] Index '{PINECONE_INDEX}' berhasil dibuat.")
else:
    print(f"[Pinecone] Menggunakan index yang sudah ada: '{PINECONE_INDEX}'")

pinecone_index = pc.Index(PINECONE_INDEX)

# ─── Initialise models ────────────────────────────────────────────────────────
embedding_model = VoyageAIEmbeddings(
    voyage_api_key=VOYAGE_API_KEY,
    model="voyage-3-lite",  # 512-dim, efisien & gratis 50M token/bulan
)

llm = ChatAnthropic(
    model="claude-3-5-haiku-20241022",  # Cepat & hemat; ganti ke claude-3-5-sonnet jika butuh lebih pintar
    api_key=ANTHROPIC_API_KEY,
    temperature=0.3,
    max_tokens=4096,
)

vectorstore = PineconeVectorStore(
    index=pinecone_index,
    embedding=embedding_model,
    text_key="text",
)

text_splitter = RecursiveCharacterTextSplitter(
    chunk_size=1000,
    chunk_overlap=200,
)

# ─── System Prompt ────────────────────────────────────────────────────────────
SYSTEM_PROMPT = """\
Anda adalah asisten akademik cerdas untuk platform Pro LMS.
Jawab pertanyaan mahasiswa berdasarkan **konteks dokumen** yang diberikan di bawah ini.

Aturan:
1. Jawab dalam bahasa yang sama dengan pertanyaan pengguna.
2. Jika konteks dokumen tersedia, utamakan menjawab berdasarkan konteks tersebut.
3. Jika informasi TIDAK ditemukan dalam konteks, gunakan pengetahuan umummu namun beri catatan bahwa informasi tersebut tidak berasal dari dokumen resmi mata kuliah.
4. Sebutkan sumber dokumen dan kategori dari jawaban kamu di akhir respons.
5. Berikan jawaban yang jelas, terstruktur, dan mudah dipahami oleh mahasiswa.
6. Gunakan format Markdown untuk membuat jawaban lebih mudah dibaca.

## ATURAN REFERENSI EKSTERNAL (WAJIB DIIKUTI)
Jika kamu menjawab menggunakan pengetahuan umum (BUKAN dari konteks dokumen), tambahkan blok referensi dengan format berikut di akhir jawaban:

[REFERENSI]
- Judul Sumber | https://url-lengkap

KETENTUAN:
- Gunakan sumber terpercaya: Wikipedia (id/en), Khan Academy, Britannica, atau situs edukasi resmi.
- Minimal 2-3 referensi yang relevan.
- Format: Judul | URL (dipisahkan pipa |).
- URL HARUS valid dan nyata.
- Blok [REFERENSI] HARUS menjadi bagian PALING AKHIR dari jawaban.
- Jika jawaban 100% berasal dari konteks dokumen, TIDAK perlu menambahkan [REFERENSI].

Konteks:
{context}
"""

prompt_template = ChatPromptTemplate.from_messages(
    [
        ("system", SYSTEM_PROMPT),
        ("human", "{question}"),
    ]
)

# ─── FastAPI app ──────────────────────────────────────────────────────────────
app = FastAPI(
    title="Pro LMS — RAG Knowledge Base API",
    description=(
        "API untuk mengunggah dokumen PDF per mata kuliah dan bertanya "
        "kepada Claude AI dengan konteks yang difilter berdasarkan kategori.\n\n"
        "**Stack**: Claude (Anthropic) · Voyage AI Embeddings · Pinecone Vector DB"
    ),
    version="3.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Ganti dengan domain frontend kamu saat production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# ─── Pydantic Schemas ─────────────────────────────────────────────────────────
class ChatRequest(BaseModel):
    query: str = Field(..., min_length=1, description="Pertanyaan pengguna")
    category_filter: Optional[str] = Field(
        None,
        description="Filter kategori mata kuliah (opsional). Contoh: 'Fisika', 'Sejarah'",
    )


class IngestResponse(BaseModel):
    status: str
    message: str
    chunks_added: int


class ChatResponse(BaseModel):
    answer: str
    sources: list[dict]
    reference_links: list[dict] = []


class DocumentListResponse(BaseModel):
    status: str
    total_documents: int
    documents: list[dict]


# ─── Endpoints ────────────────────────────────────────────────────────────────

@app.get("/", tags=["Health"])
async def root():
    """Health check — pastikan API berjalan."""
    return {
        "status": "ok",
        "service": "Pro LMS RAG Knowledge Base API",
        "version": "3.0.0",
        "stack": {
            "llm": "Claude 3.5 Haiku (Anthropic)",
            "embeddings": "Voyage AI voyage-3-lite",
            "vector_db": f"Pinecone ({PINECONE_INDEX})",
        },
        "docs": "/docs",
    }


@app.post("/api/ingest", response_model=IngestResponse, tags=["Ingestion"])
async def ingest_document(
    file: UploadFile = File(..., description="File PDF yang akan diunggah"),
    category: str = Form(..., description="Kategori mata kuliah, contoh: Fisika, Sejarah"),
):
    """
    Upload PDF → split jadi chunk → embed dengan Voyage AI → simpan ke Pinecone.
    Setiap chunk diberi metadata: `category` dan `source` (nama file).
    """
    if not file.filename or not file.filename.lower().endswith(".pdf"):
        raise HTTPException(
            status_code=400,
            detail="Hanya file PDF yang diperbolehkan. Pastikan file berekstensi .pdf",
        )

    tmp_dir = tempfile.mkdtemp()
    tmp_path = os.path.join(tmp_dir, file.filename)

    try:
        with open(tmp_path, "wb") as f:
            shutil.copyfileobj(file.file, f)

        loader = PyPDFLoader(tmp_path)
        raw_docs: list[Document] = loader.load()

        if not raw_docs:
            raise HTTPException(
                status_code=400,
                detail="PDF tidak mengandung teks yang bisa diekstrak.",
            )

        chunks: list[Document] = text_splitter.split_documents(raw_docs)

        for chunk in chunks:
            chunk.metadata["category"] = category
            chunk.metadata["source"]   = file.filename

        # add_documents ke Pinecone (auto-embed via Voyage AI)
        vectorstore.add_documents(chunks)

        return IngestResponse(
            status="success",
            message=f"File '{file.filename}' berhasil diproses dan disimpan ke Pinecone.",
            chunks_added=len(chunks),
        )

    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Terjadi kesalahan saat memproses file: {str(e)}",
        )
    finally:
        shutil.rmtree(tmp_dir, ignore_errors=True)


@app.post("/api/chat", response_model=ChatResponse, tags=["Chat"])
async def chat(request: ChatRequest):
    """
    Terima pertanyaan → similarity search di Pinecone → kirim konteks ke Claude → hasilkan jawaban.
    """
    try:
        # ── Build filter for Pinecone ─────────────────────────────────────
        search_kwargs: dict = {"k": 5}
        if request.category_filter:
            search_kwargs["filter"] = {"category": {"$eq": request.category_filter}}

        # ── Similarity search via Voyage AI embeddings ────────────────────
        relevant_docs: list[Document] = vectorstore.similarity_search(
            query=request.query,
            **search_kwargs,
        )

        sources = []
        if not relevant_docs:
            context = "Tidak ada dokumen relevan ditemukan dalam knowledge base untuk pertanyaan ini."
            sources = [{"source": "AI General Knowledge", "category": "General", "page": None}]
        else:
            context_parts: list[str] = []
            seen_sources: set = set()

            for doc in relevant_docs:
                context_parts.append(doc.page_content)
                source_key = (
                    doc.metadata.get("source", "unknown"),
                    doc.metadata.get("category", "unknown"),
                )
                if source_key not in seen_sources:
                    seen_sources.add(source_key)
                    sources.append({
                        "source":   doc.metadata.get("source", "unknown"),
                        "category": doc.metadata.get("category", "unknown"),
                        "page":     doc.metadata.get("page", None),
                    })
            context = "\n\n---\n\n".join(context_parts)

        # ── Call Claude ───────────────────────────────────────────────────
        chain = prompt_template | llm
        response = chain.invoke({"context": context, "question": request.query})

        # ── Parse reference links ─────────────────────────────────────────
        answer_text = response.content
        reference_links: list[dict] = []

        ref_match = re.search(
            r"\[REFERENSI\]\s*\n(.*)",
            answer_text,
            re.DOTALL | re.IGNORECASE,
        )
        if ref_match:
            answer_text = answer_text[: ref_match.start()].rstrip()
            ref_block   = ref_match.group(1).strip()
            for line in ref_block.splitlines():
                line = line.strip().lstrip("- ").strip()
                if "|" in line:
                    parts = line.split("|", 1)
                    title = parts[0].strip()
                    url   = parts[1].strip()
                    if url.startswith("http"):
                        reference_links.append({"title": title, "url": url})
                elif line.startswith("http"):
                    reference_links.append({"title": line, "url": line})

        if reference_links:
            sources = []

        return ChatResponse(
            answer=answer_text,
            sources=sources,
            reference_links=reference_links,
        )

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Terjadi kesalahan saat memproses pertanyaan: {str(e)}",
        )


@app.get("/api/documents", response_model=DocumentListResponse, tags=["Documents"])
async def list_documents():
    """
    Tampilkan daftar dokumen unik yang sudah diupload ke Pinecone.
    Mengambil metadata dari Pinecone index stats & query.
    """
    try:
        # Query Pinecone untuk mendapatkan metadata semua vector
        # Fetch sample vectors dan ekstrak metadata unik
        results = pinecone_index.query(
            vector=[0.0] * 512,
            top_k=10000,
            include_metadata=True,
        )

        seen = set()
        documents = []
        for match in results.get("matches", []):
            meta = match.get("metadata", {})
            key  = (meta.get("source", ""), meta.get("category", ""))
            if key not in seen and key[0]:
                seen.add(key)
                documents.append({
                    "source":   meta.get("source", "unknown"),
                    "category": meta.get("category", "unknown"),
                })

        return DocumentListResponse(
            status="success",
            total_documents=len(documents),
            documents=documents,
        )

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Gagal mengambil daftar dokumen: {str(e)}",
        )


@app.delete("/api/documents/{source}", tags=["Documents"])
async def delete_document(source: str):
    """
    Hapus semua chunk dari dokumen berdasarkan nama file (source) dari Pinecone.
    """
    try:
        # Pinecone delete by metadata filter
        pinecone_index.delete(filter={"source": {"$eq": source}})
        return {
            "status":  "success",
            "message": f"Dokumen '{source}' berhasil dihapus dari Pinecone knowledge base.",
        }
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Gagal menghapus dokumen: {str(e)}",
        )


# ─── Run with Uvicorn ─────────────────────────────────────────────────────────
if __name__ == "__main__":
    import uvicorn

    print("=" * 55)
    print("  Pro LMS RAG Knowledge Base API v3")
    print("  LLM       : Claude 3.5 Haiku (Anthropic)")
    print("  Embeddings: Voyage AI (voyage-3-lite)")
    print(f"  Vector DB : Pinecone ({PINECONE_INDEX})")
    print("=" * 55)
    print("  API Docs : http://localhost:8001/docs")
    print("  ReDoc    : http://localhost:8001/redoc")
    print("=" * 55)
    uvicorn.run("main:app", host="0.0.0.0", port=8001, reload=True)