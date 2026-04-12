<div align="center">

<img src="https://img.shields.io/badge/Pro%20LMS-AI%20Knowledge%20Base-6366f1?style=for-the-badge&logo=bookstack&logoColor=white" alt="Pro LMS" />

# 🎓 Pro LMS — AI-Powered Knowledge Base

**Retrieval-Augmented Generation (RAG) platform for academic use,  
built with Claude AI · Voyage AI · Pinecone · FastAPI · PHP**

[![FastAPI](https://img.shields.io/badge/FastAPI-0.115-009688?style=flat-square&logo=fastapi&logoColor=white)](https://fastapi.tiangolo.com/)
[![Claude](https://img.shields.io/badge/Claude-3.5%20Haiku-D97706?style=flat-square&logo=anthropic&logoColor=white)](https://www.anthropic.com/)
[![Pinecone](https://img.shields.io/badge/Pinecone-Vector%20DB-00BFA5?style=flat-square&logo=pinecone&logoColor=white)](https://www.pinecone.io/)
[![Voyage AI](https://img.shields.io/badge/Voyage%20AI-Embeddings-6366f1?style=flat-square)](https://www.voyageai.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-22c55e?style=flat-square)](LICENSE)

<br/>

> **Pro LMS** is a production-ready, full-stack AI academic assistant that lets students query their course materials using natural language. Documents are ingested, semantically embedded, and retrieved at inference time to ground Claude's responses — a classic **Deep RAG** pipeline.

</div>

---

## 📌 Table of Contents

- [Overview](#-overview)
- [Architecture](#-architecture)
- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
  - [Backend Setup](#1-backend-fastapi)
  - [Frontend Setup](#2-frontend-php)
- [API Reference](#-api-reference)
- [RAG Pipeline Deep Dive](#-rag-pipeline-deep-dive)
- [Screenshots](#-screenshots)
- [Roadmap](#-roadmap)
- [Author](#-author)

---

## 🔍 Overview

Pro LMS solves a core problem in academic environments: **students can't efficiently find answers buried inside hundreds of pages of lecture materials**. Instead of keyword search, Pro LMS uses a **Deep RAG pipeline** to semantically understand questions and retrieve the most relevant document chunks before generating a grounded, cited response.

```
Student asks a question
        ↓
Voyage AI encodes the query into a 512-dim vector
        ↓
Pinecone finds the top-5 most semantically similar chunks
        ↓
Claude 3.5 Haiku synthesizes a structured, cited answer
        ↓
PHP frontend renders the response with source attribution
```

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        PRO LMS SYSTEM                           │
│                                                                 │
│  ┌──────────────────┐          ┌──────────────────────────────┐ │
│  │   FRONTEND (PHP) │◄────────►│     BACKEND (FastAPI)        │ │
│  │                  │  REST    │                              │ │
│  │  index.php       │  API     │  POST /api/chat              │ │
│  │  upload.php      │          │  POST /api/ingest            │ │
│  │  documents.php   │          │  GET  /api/documents         │ │
│  └──────────────────┘          │  DEL  /api/documents/{src}   │ │
│                                └──────────┬───────────────────┘ │
│                                           │                     │
│                          ┌────────────────▼──────────────────┐  │
│                          │         RAG PIPELINE              │  │
│                          │                                   │  │
│                          │  PDF → Chunker → Voyage AI        │  │
│                          │  Embeddings (512-dim)             │  │
│                          │         ↓                         │  │
│                          │  Pinecone Vector Store            │  │
│                          │  (cosine similarity search)       │  │
│                          │         ↓                         │  │
│                          │  Claude 3.5 Haiku (Anthropic)     │  │
│                          │  Context-grounded generation      │  │
│                          └───────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## ✨ Key Features

| Feature | Description |
|---|---|
| 🤖 **Deep RAG Q&A** | Ask questions in natural language; Claude answers using your course PDFs as context |
| 📤 **PDF Ingestion** | Upload lecture materials per subject — auto-chunked, embedded, and indexed into Pinecone |
| 🏷️ **Category Filtering** | Filter AI responses by subject (Physics, Math, Chemistry, etc.) for precision answers |
| 📚 **Knowledge Base Manager** | View, search, and delete indexed documents via the UI |
| 🔗 **External References** | If context is insufficient, Claude cites reputable external sources (Wikipedia, Khan Academy, etc.) |
| ⚡ **Fast Semantic Search** | Pinecone serverless delivers sub-100ms vector retrieval at scale |
| 🌐 **REST API** | Fully documented FastAPI backend with interactive Swagger UI at `/docs` |
| 📱 **Responsive UI** | Dark-themed PHP frontend, responsive across desktop and mobile |

---

## 🛠️ Tech Stack

### Backend
| Layer | Technology | Purpose |
|---|---|---|
| **API Framework** | [FastAPI](https://fastapi.tiangolo.com/) 0.115 | High-performance async REST API |
| **LLM** | [Claude 3.5 Haiku](https://www.anthropic.com/) (Anthropic) | Natural language generation |
| **Embeddings** | [Voyage AI](https://www.voyageai.com/) `voyage-3-lite` | 512-dim semantic text encoding |
| **Vector DB** | [Pinecone](https://www.pinecone.io/) Serverless | Approximate nearest-neighbor search |
| **Orchestration** | [LangChain](https://www.langchain.com/) 0.3 | LLM chaining & document pipeline |
| **PDF Parser** | PyPDF 5 | Text extraction from lecture PDFs |
| **Server** | Uvicorn (ASGI) | Production-ready async server |

### Frontend
| Layer | Technology | Purpose |
|---|---|---|
| **Language** | PHP 8.2 | Server-side rendering |
| **Styling** | Vanilla CSS (dark theme, glassmorphism) | Premium UI without frameworks |
| **Markdown** | [Marked.js](https://marked.js.org/) | Render Claude's markdown responses |
| **HTTP** | Fetch API | Async communication with FastAPI |

---

## 📁 Project Structure

```
pro_rag_lms/
│
├── backend/                    # FastAPI Deep RAG API
│   ├── main.py                 # Core application — all endpoints & RAG pipeline
│   ├── requirements.txt        # Python dependencies
│   ├── .env.example            # Environment variable template
│   └── .gitignore
│
├── frontend/                   # PHP Frontend
│   ├── config.php              # Central config — API base URL & constants
│   ├── index.php               # AI Chat interface (main page)
│   ├── upload.php              # PDF upload with drag & drop
│   └── documents.php          # Knowledge base manager (list, search, delete)
│
└── README.md
```

---

## 🚀 Getting Started

### Prerequisites

- Python 3.11+
- PHP 8.2+ (or XAMPP / Laragon)
- API Keys for: [Anthropic](https://console.anthropic.com/), [Voyage AI](https://dash.voyageai.com/), [Pinecone](https://app.pinecone.io/)

---

### 1. Backend (FastAPI)

```bash
# Clone repository
git clone https://github.com/your-username/pro_rag_lms.git
cd pro_rag_lms/backend

# Create virtual environment
python -m venv venv
venv\Scripts\activate        # Windows
# source venv/bin/activate   # macOS/Linux

# Install dependencies
pip install -r requirements.txt

# Configure environment
cp .env.example .env
```

Edit `.env` with your API keys:

```env
ANTHROPIC_API_KEY=sk-ant-...         # console.anthropic.com
VOYAGE_API_KEY=pa-...                # dash.voyageai.com (free: 50M tokens/month)
PINECONE_API_KEY=pcsk_...            # app.pinecone.io  (free: 1 index, 100k vectors)
PINECONE_INDEX_NAME=prolms-knowledge-base
```

```bash
# Start the API server
python main.py
# → API running at http://localhost:8001
# → Swagger UI at http://localhost:8001/docs
```

> **Note:** On first run, Pinecone will automatically create the index `prolms-knowledge-base` (512-dim, cosine similarity, AWS us-east-1 serverless).

---

### 2. Frontend (PHP)

```bash
cd ../frontend

# Using PHP built-in server
php -S localhost:8080

# OR place in XAMPP/Laragon htdocs folder and access via browser
```

Open **http://localhost:8080/index.php** in your browser.

> If your backend runs on a different port/host, edit `BASE_API_URL` in `frontend/config.php`.

---

## 📡 API Reference

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/` | Health check — returns service info & stack details |
| `POST` | `/api/ingest` | Upload a PDF with a category label for indexing |
| `POST` | `/api/chat` | Send a question, get a Claude-generated answer |
| `GET` | `/api/documents` | List all indexed documents from Pinecone |
| `DELETE` | `/api/documents/{source}` | Remove a document from the knowledge base |

### Chat Request Example

```bash
curl -X POST http://localhost:8001/api/chat \
  -H "Content-Type: application/json" \
  -d '{
    "query": "Jelaskan hukum Newton ke-2",
    "category_filter": "Fisika"
  }'
```

```json
{
  "answer": "Hukum Newton ke-2 menyatakan bahwa...",
  "sources": [
    { "source": "fisika_dasar.pdf", "category": "Fisika", "page": 14 }
  ],
  "reference_links": []
}
```

### Ingest Request Example

```bash
curl -X POST http://localhost:8001/api/ingest \
  -F "file=@fisika_dasar.pdf" \
  -F "category=Fisika"
```

```json
{
  "status": "success",
  "message": "File 'fisika_dasar.pdf' berhasil diproses dan disimpan ke Pinecone.",
  "chunks_added": 142
}
```

> Full interactive documentation available at **http://localhost:8001/docs** (Swagger UI)

---

## 🧠 RAG Pipeline Deep Dive

### Ingestion Phase
```
PDF File
  │
  ▼
PyPDFLoader           ← Extract raw text page-by-page
  │
  ▼
RecursiveCharacterTextSplitter
  chunk_size=1000
  chunk_overlap=200   ← Preserve context across chunk boundaries
  │
  ▼
Metadata Attachment   ← { "source": "file.pdf", "category": "Fisika" }
  │
  ▼
Voyage AI voyage-3-lite  ← Encode each chunk → 512-dim dense vector
  │
  ▼
Pinecone Upsert       ← Store vectors + metadata in serverless index
```

### Retrieval & Generation Phase
```
User Query (natural language)
  │
  ▼
Voyage AI voyage-3-lite  ← Encode query → 512-dim query vector
  │
  ▼
Pinecone similarity_search(k=5, filter={"category": "Fisika"})
  │                     ← ANN search using cosine similarity
  ▼
Top-5 Document Chunks  ← Most semantically relevant passages
  │
  ▼
Prompt Assembly        ← System prompt + context + user question
  │
  ▼
Claude 3.5 Haiku       ← Generate grounded, structured response
  │
  ▼
Reference Parser       ← Extract [REFERENSI] block if present
  │
  ▼
JSON Response          ← { answer, sources, reference_links }
```

### Why This Stack?

| Decision | Rationale |
|---|---|
| **Claude over GPT** | Superior instruction-following, safer outputs, better structured writing |
| **Voyage AI for embeddings** | Officially recommended by Anthropic; strong multilingual performance; generous free tier |
| **Pinecone over ChromaDB** | Cloud-native, zero-ops, scales to billions of vectors; no local SQLite issues on shared hosting |
| **FastAPI over Flask** | Native async support, automatic OpenAPI docs, Pydantic validation |

---

## 📸 Screenshots

### AI Chat Interface
<img src="docs/screenshot-chat.png" alt="AI Chat" width="100%">

### PDF Upload with Drag & Drop
<img src="docs/screenshot-upload.png" alt="Upload" width="100%">

### Knowledge Base Manager
<img src="docs/screenshot-documents.png" alt="Documents" width="100%">

---

## 🗺️ Roadmap

- [x] PDF ingestion pipeline (chunk → embed → store)
- [x] Category-filtered RAG chat
- [x] Knowledge base management UI
- [x] External reference citation system
- [ ] User authentication & multi-tenant knowledge bases  
- [ ] Streaming responses (SSE / WebSocket)
- [ ] Re-ranking with Cohere Rerank for improved retrieval precision
- [ ] LangGraph-based multi-step agent for complex queries
- [ ] Analytics dashboard (query logs, popular topics)
- [ ] Docker Compose deployment

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

## 👤 Author

**M Feby khoiru sidqi**
*AI Engineer · LLM Integration Specialist*

[![LinkedIn](https://img.shields.io/badge/LinkedIn-Connect-0A66C2?style=flat-square&logo=linkedin)](https://linkedin.com/in/mfebykhoirusidqi)
[![GitHub](https://img.shields.io/badge/GitHub-Follow-181717?style=flat-square&logo=github)](https://github.com/mfebykhoirusidqi)

---

<div align="center">

**Built with 🤖 Claude · 🔍 Voyage AI · 🌲 Pinecone · ⚡ FastAPI**

*If this project helped you, consider giving it a ⭐*

</div>