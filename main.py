"""
RAG API Backend — Global Knowledge Base for Pro LMS
====================================================
FastAPI application that ingests PDF documents by category into ChromaDB
and answers questions with optional category filtering using Google Gemini.
"""

import os
import re
import shutil
import tempfile
from typing import Optional

from dotenv import load_dotenv
from fastapi import FastAPI, File, Form, HTTPException, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

# ─── Load environment ────────────────────────────────────────────────────────
try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:
    pass

GOOGLE_API_KEY = os.getenv("GOOGLE_API_KEY")
if not GOOGLE_API_KEY:
    raise EnvironmentError(
        "GOOGLE_API_KEY belum diset. "
        "Buat file .env di root project dan isi: GOOGLE_API_KEY=your-key-here"
    )

# ─── cPanel SQLite Patch (Mencegah Error ChromaDB di cPanel) ─────────────────
try:
    __import__('pysqlite3')
    import sys
    sys.modules['sqlite3'] = sys.modules.pop('pysqlite3')
except ImportError:
    pass

# ─── LangChain imports ───────────────────────────────────────────────────────
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_chroma import Chroma
from langchain_community.document_loaders import PyPDFLoader
from langchain_core.documents import Document
from langchain_core.prompts import ChatPromptTemplate
from langchain_google_genai import ChatGoogleGenerativeAI, GoogleGenerativeAIEmbeddings

# ─── Initialise models & vector store ────────────────────────────────────────
CHROMA_DIR = "./chroma_db"
COLLECTION_NAME = "prolms_knowledge_base"

embedding_model = GoogleGenerativeAIEmbeddings(
    model="models/gemini-embedding-001",
    google_api_key=GOOGLE_API_KEY,
)

llm = ChatGoogleGenerativeAI(
    model="gemini-2.5-flash",
    google_api_key=GOOGLE_API_KEY,
    temperature=0.3,
)

vectorstore = Chroma(
    collection_name=COLLECTION_NAME,
    embedding_function=embedding_model,
    persist_directory=CHROMA_DIR,
)

text_splitter = RecursiveCharacterTextSplitter(
    chunk_size=1000,
    chunk_overlap=200,
)

# ─── Prompt template ─────────────────────────────────────────────────────────
SYSTEM_PROMPT = """\
Anda adalah asisten akademik cerdas untuk platform Pro LMS.
Jawab pertanyaan pengguna berdasarkan **konteks** yang diberikan di bawah ini.

Aturan:
1. Jawab dalam bahasa yang sama dengan pertanyaan pengguna.
2. Jika ada informasi dalam konteks, utamakan menjawab berdasarkan konteks tersebut.
3. Jika informasi TIDAK ditemukan dalam konteks, Anda BOLEH menggunakan pengetahuan umum Anda untuk menjawab, namun berikan catatan bahwa informasi tersebut tidak berasal dari dokumen resmi mata kuliah.
4. Sebutkan sumber dokumen dan kategori dari jawaban Anda di akhir respons.
5. Berikan jawaban yang jelas, terstruktur, dan mudah dipahami oleh mahasiswa.

## ATURAN REFERENSI EKSTERNAL (WAJIB DIIKUTI)
Setiap kali Anda menjawab menggunakan pengetahuan umum (BUKAN dari konteks dokumen yang diberikan), Anda **HARUS** menambahkan blok referensi di akhir jawaban Anda. Blok ini WAJIB mengikuti format persis di bawah ini:

[REFERENSI]
- Judul Halaman Wikipedia | https://id.wikipedia.org/wiki/Nama_Artikel
- Judul Sumber Lain | https://url-lengkap

KETENTUAN:
- Gunakan sumber terpercaya: Wikipedia (id/en), Khan Academy, Britannica, atau situs edukasi resmi.
- Berikan minimal 2-3 referensi yang relevan dengan jawaban.
- Pastikan setiap baris referensi menggunakan format: Judul | URL (dipisahkan dengan karakter pipa |).
- URL HARUS valid dan nyata, JANGAN mengarang URL.
- Blok [REFERENSI] HARUS selalu menjadi bagian PALING AKHIR dari jawaban Anda.
- Jika jawaban 100% berasal dari konteks dokumen yang diberikan, TIDAK perlu menambahkan blok [REFERENSI].

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
        "kepada AI dengan konteks yang difilter berdasarkan kategori."
    ),
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# ─── Pydantic schemas ────────────────────────────────────────────────────────
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
    reference_links: list[dict] = []  # [{"title": "...", "url": "..."}, ...]


# ─── Endpoints ────────────────────────────────────────────────────────────────


@app.get("/", tags=["Health"])
async def root():
    """Health check endpoint."""
    return {
        "status": "ok",
        "service": "Pro LMS RAG Knowledge Base API",
        "docs": "/docs",
    }


@app.post("/api/ingest", response_model=IngestResponse, tags=["Ingestion"])
async def ingest_document(
    file: UploadFile = File(..., description="File PDF yang akan diunggah"),
    category: str = Form(..., description="Kategori mata kuliah, contoh: Fisika, Sejarah"),
):
    """
    Menerima file PDF dan kategori mata kuliah.
    File akan dipecah menjadi chunk, di-embed, lalu disimpan ke ChromaDB
    dengan metadata `category` dan `source`.
    """

    # ── Validate file type ────────────────────────────────────────────────
    if not file.filename or not file.filename.lower().endswith(".pdf"):
        raise HTTPException(
            status_code=400,
            detail="Hanya file PDF yang diperbolehkan. Pastikan file berekstensi .pdf",
        )

    # ── Save temp file ────────────────────────────────────────────────────
    tmp_dir = tempfile.mkdtemp()
    tmp_path = os.path.join(tmp_dir, file.filename)

    try:
        with open(tmp_path, "wb") as f:
            shutil.copyfileobj(file.file, f)

        # ── Load & split PDF ─────────────────────────────────────────────
        loader = PyPDFLoader(tmp_path)
        raw_docs: list[Document] = loader.load()

        if not raw_docs:
            raise HTTPException(
                status_code=400,
                detail="PDF tidak mengandung teks yang bisa diekstrak.",
            )

        chunks: list[Document] = text_splitter.split_documents(raw_docs)

        # ── Attach metadata ──────────────────────────────────────────────
        for chunk in chunks:
            chunk.metadata["category"] = category
            chunk.metadata["source"] = file.filename

        # ── Store in ChromaDB ─────────────────────────────────────────────
        vectorstore.add_documents(chunks)

        return IngestResponse(
            status="success",
            message=f"File '{file.filename}' berhasil diproses dan disimpan ke Vector DB.",
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
        # ── Cleanup temp file ─────────────────────────────────────────────
        shutil.rmtree(tmp_dir, ignore_errors=True)


@app.post("/api/chat", response_model=ChatResponse, tags=["Chat"])
async def chat(request: ChatRequest):
    """
    Menerima pertanyaan dan opsional filter kategori.
    Melakukan similarity search di ChromaDB, lalu mengirim konteks
    ke Gemini LLM untuk menghasilkan jawaban.
    """

    try:
        # ── Build search kwargs ───────────────────────────────────────────
        search_kwargs: dict = {"k": 5}
        if request.category_filter:
            search_kwargs["filter"] = {"category": request.category_filter}

        # ── Similarity search ─────────────────────────────────────────────
        relevant_docs: list[Document] = vectorstore.similarity_search(
            query=request.query,
            **search_kwargs,
        )

        sources = []
        if not relevant_docs:
            context = "Tidak ada dokumen relevan ditemukan dalam knowledge base untuk pertanyaan ini."
            sources = [
                {
                    "source": "AI General Knowledge",
                    "category": "General",
                    "page": None,
                }
            ]
        else:
            # ── Build context string ──────────────────────────────────────────
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
                    sources.append(
                        {
                            "source": doc.metadata.get("source", "unknown"),
                            "category": doc.metadata.get("category", "unknown"),
                            "page": doc.metadata.get("page", None),
                        }
                    )
            context = "\n\n---\n\n".join(context_parts)

        # ── Call LLM ─────────────────────────────────────────────────────
        chain = prompt_template | llm
        response = chain.invoke({"context": context, "question": request.query})

        # ── Parse reference links from response ─────────────────────────
        answer_text = response.content
        reference_links: list[dict] = []

        ref_match = re.search(
            r"\[REFERENSI\]\s*\n(.*)",
            answer_text,
            re.DOTALL | re.IGNORECASE,
        )
        if ref_match:
            # Remove the [REFERENSI] block from the displayed answer
            answer_text = answer_text[: ref_match.start()].rstrip()
            ref_block = ref_match.group(1).strip()
            for line in ref_block.splitlines():
                line = line.strip().lstrip("- ").strip()
                if "|" in line:
                    parts = line.split("|", 1)
                    title = parts[0].strip()
                    url = parts[1].strip()
                    if url.startswith("http"):
                        reference_links.append({"title": title, "url": url})
                elif line.startswith("http"):
                    reference_links.append({"title": line, "url": line})

        if reference_links:
            # Jika LLM menggunakan pengetahuan umum dan menghasilkan link eksternal,
            # hapus referensi PDF (sources) karena jawaban tidak berasal dari sana.
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


# ─── Run with Uvicorn ─────────────────────────────────────────────────────────
if __name__ == "__main__":
    import uvicorn

    print("--- Starting Pro LMS RAG API ---")
    print("API Docs: http://localhost:8001/docs")
    print("ReDoc:    http://localhost:8001/redoc")
    uvicorn.run("main:app", host="0.0.0.0", port=8001, reload=True)
