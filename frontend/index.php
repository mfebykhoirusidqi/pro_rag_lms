<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Pro LMS — Platform belajar cerdas berbasis AI dengan RAG (Retrieval-Augmented Generation). Tanya apa saja, AI menjawab berdasarkan materi kuliah kamu." />
  <title>Pro LMS — AI Knowledge Base</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />

  <!-- Marked.js untuk render Markdown dari AI -->
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

  <style>
    /* ─── CSS Reset & Root Variables ─────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg-900: #0a0a0f;
      --bg-800: #12121a;
      --bg-700: #1a1a26;
      --bg-600: #22223a;
      --surface: rgba(255,255,255,0.04);
      --surface-hover: rgba(255,255,255,0.07);
      --border: rgba(255,255,255,0.08);
      --border-glow: rgba(99,102,241,0.4);

      --primary: #6366f1;
      --primary-light: #818cf8;
      --primary-glow: rgba(99,102,241,0.25);
      --accent: #22d3ee;
      --accent-glow: rgba(34,211,238,0.2);
      --success: #10b981;
      --warning: #f59e0b;
      --danger: #f43f5e;

      --text-100: #f1f5f9;
      --text-200: #cbd5e1;
      --text-400: #64748b;
      --text-600: #334155;

      --font-sans: 'Inter', sans-serif;
      --font-mono: 'JetBrains Mono', monospace;

      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-xl: 24px;

      --shadow-glow: 0 0 40px rgba(99,102,241,0.15);
      --transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
    }

    html { font-size: 16px; scroll-behavior: smooth; }
    body {
      font-family: var(--font-sans);
      background-color: var(--bg-900);
      color: var(--text-100);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    /* ─── Animated Background ─────────────────────────────────────────────── */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 80% 50% at 20% 10%, rgba(99,102,241,0.1) 0%, transparent 60%),
        radial-gradient(ellipse 60% 40% at 80% 90%, rgba(34,211,238,0.06) 0%, transparent 60%);
      pointer-events: none;
      z-index: 0;
    }

    /* ─── Layout ──────────────────────────────────────────────────────────── */
    .app-shell {
      display: grid;
      grid-template-columns: 280px 1fr;
      grid-template-rows: 64px 1fr;
      height: 100vh;
      position: relative;
      z-index: 1;
    }

    /* ─── Topbar ──────────────────────────────────────────────────────────── */
    .topbar {
      grid-column: 1 / -1;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      background: rgba(10,10,15,0.8);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }
    .brand-icon {
      width: 36px; height: 36px;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .brand-name {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--text-100);
      letter-spacing: -0.02em;
    }
    .brand-badge {
      font-size: 0.6rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--primary-light);
      background: var(--primary-glow);
      padding: 2px 7px;
      border-radius: 99px;
      border: 1px solid rgba(99,102,241,0.3);
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .status-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: var(--success);
      box-shadow: 0 0 8px var(--success);
      animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.4; }
    }
    .status-label { font-size: 0.8rem; color: var(--text-400); }

    /* ─── Sidebar ─────────────────────────────────────────────────────────── */
    .sidebar {
      background: var(--bg-800);
      border-right: 1px solid var(--border);
      padding: 20px 16px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .sidebar-section-label {
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: var(--text-400);
      padding: 8px 8px 4px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: var(--radius-sm);
      cursor: pointer;
      transition: var(--transition);
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--text-200);
      border: 1px solid transparent;
      text-decoration: none;
    }
    .nav-item:hover {
      background: var(--surface-hover);
      color: var(--text-100);
    }
    .nav-item.active {
      background: var(--primary-glow);
      color: var(--primary-light);
      border-color: rgba(99,102,241,0.3);
    }
    .nav-item .icon { font-size: 1rem; width: 20px; text-align: center; }

    .sidebar-divider {
      height: 1px;
      background: var(--border);
      margin: 8px 0;
    }

    /* Category filter */
    .category-list { display: flex; flex-direction: column; gap: 4px; }
    .cat-btn {
      padding: 8px 12px;
      border-radius: var(--radius-sm);
      background: transparent;
      border: 1px solid transparent;
      color: var(--text-200);
      font-size: 0.8rem;
      font-family: var(--font-sans);
      cursor: pointer;
      transition: var(--transition);
      text-align: left;
    }
    .cat-btn:hover { background: var(--surface-hover); }
    .cat-btn.active {
      background: var(--primary-glow);
      border-color: rgba(99,102,241,0.3);
      color: var(--primary-light);
    }

    /* Upload area */
    .upload-area {
      border: 2px dashed var(--border);
      border-radius: var(--radius-md);
      padding: 16px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      margin-top: 4px;
    }
    .upload-area:hover { border-color: var(--primary); background: var(--primary-glow); }
    .upload-area .upload-icon { font-size: 1.5rem; margin-bottom: 6px; }
    .upload-area p { font-size: 0.75rem; color: var(--text-400); }

    .form-group { display: flex; flex-direction: column; gap: 6px; margin-top: 12px; }
    .form-label { font-size: 0.75rem; font-weight: 500; color: var(--text-200); }
    .form-input {
      background: var(--bg-700);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 8px 12px;
      color: var(--text-100);
      font-size: 0.8rem;
      font-family: var(--font-sans);
      outline: none;
      transition: var(--transition);
    }
    .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 10px 16px;
      border-radius: var(--radius-sm);
      border: none;
      font-family: var(--font-sans);
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
    }
    .btn-primary {
      background: var(--primary);
      color: #fff;
    }
    .btn-primary:hover { background: var(--primary-light); transform: translateY(-1px); box-shadow: 0 4px 20px var(--primary-glow); }
    .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    .btn-sm { padding: 7px 12px; font-size: 0.78rem; }
    .btn-full { width: 100%; }

    /* ─── Main Chat Area ──────────────────────────────────────────────────── */
    .main-area {
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* Chat header */
    .chat-header {
      padding: 16px 24px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(10,10,15,0.5);
    }
    .chat-title { font-size: 1rem; font-weight: 600; }
    .chat-subtitle { font-size: 0.75rem; color: var(--text-400); margin-top: 2px; }

    /* Messages */
    .messages-container {
      flex: 1;
      overflow-y: auto;
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      scrollbar-width: thin;
      scrollbar-color: var(--bg-600) transparent;
    }

    .message {
      display: flex;
      gap: 12px;
      animation: fadeInUp 0.3s ease;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .message.user { flex-direction: row-reverse; }

    .avatar {
      width: 36px; height: 36px; min-width: 36px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.9rem;
      font-weight: 700;
    }
    .avatar-ai {
      background: linear-gradient(135deg, var(--primary), var(--accent));
    }
    .avatar-user {
      background: linear-gradient(135deg, #334155, #475569);
    }

    .bubble-wrap { display: flex; flex-direction: column; max-width: 75%; }
    .message.user .bubble-wrap { align-items: flex-end; }

    .bubble {
      padding: 14px 18px;
      border-radius: var(--radius-lg);
      font-size: 0.9rem;
      line-height: 1.65;
    }
    .bubble-ai {
      background: var(--bg-700);
      border: 1px solid var(--border);
      border-top-left-radius: 4px;
    }
    .bubble-user {
      background: var(--primary);
      color: #fff;
      border-bottom-right-radius: 4px;
    }

    /* Markdown styles inside bubble */
    .bubble-ai h1,.bubble-ai h2,.bubble-ai h3 { margin: 12px 0 6px; font-weight: 600; }
    .bubble-ai p { margin: 6px 0; }
    .bubble-ai ul, .bubble-ai ol { margin: 6px 0 6px 20px; }
    .bubble-ai li { margin: 4px 0; }
    .bubble-ai code {
      background: var(--bg-900);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 1px 6px;
      font-family: var(--font-mono);
      font-size: 0.82em;
    }
    .bubble-ai pre {
      background: var(--bg-900);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 12px;
      overflow-x: auto;
      margin: 8px 0;
    }
    .bubble-ai pre code { background: none; border: none; padding: 0; }
    .bubble-ai strong { color: var(--primary-light); }
    .bubble-ai a { color: var(--accent); text-decoration: none; }
    .bubble-ai a:hover { text-decoration: underline; }
    .bubble-ai blockquote {
      border-left: 3px solid var(--primary);
      padding-left: 12px;
      margin: 8px 0;
      color: var(--text-200);
    }

    .sources-block {
      margin-top: 8px;
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }
    .source-tag {
      font-size: 0.72rem;
      padding: 3px 10px;
      border-radius: 99px;
      background: var(--bg-600);
      border: 1px solid var(--border);
      color: var(--text-400);
    }
    .ref-links {
      margin-top: 10px;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .ref-link {
      font-size: 0.75rem;
      color: var(--accent);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .ref-link:hover { text-decoration: underline; }

    .timestamp {
      font-size: 0.68rem;
      color: var(--text-400);
      margin-top: 4px;
    }

    /* Typing indicator */
    .typing-indicator {
      display: flex;
      align-items: center;
      gap: 4px;
      padding: 14px 18px;
      background: var(--bg-700);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      border-top-left-radius: 4px;
      width: fit-content;
    }
    .typing-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: var(--text-400);
      animation: bounce 1.2s infinite;
    }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes bounce {
      0%,60%,100% { transform: translateY(0); }
      30% { transform: translateY(-6px); }
    }

    /* Welcome screen */
    .welcome-screen {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      text-align: center;
      gap: 16px;
      padding: 40px;
    }
    .welcome-icon {
      font-size: 3rem;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .welcome-title { font-size: 1.5rem; font-weight: 700; }
    .welcome-subtitle { font-size: 0.9rem; color: var(--text-400); max-width: 380px; }
    .welcome-chips { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 8px; }
    .chip {
      padding: 8px 16px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 99px;
      font-size: 0.8rem;
      color: var(--text-200);
      cursor: pointer;
      transition: var(--transition);
    }
    .chip:hover {
      background: var(--primary-glow);
      border-color: rgba(99,102,241,0.4);
      color: var(--primary-light);
    }

    /* ─── Input Row ───────────────────────────────────────────────────────── */
    .input-row {
      padding: 16px 24px;
      border-top: 1px solid var(--border);
      background: rgba(10,10,15,0.7);
      backdrop-filter: blur(20px);
    }
    .input-wrap {
      display: flex;
      gap: 10px;
      align-items: flex-end;
      background: var(--bg-700);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 8px 8px 8px 16px;
      transition: var(--transition);
    }
    .input-wrap:focus-within {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px var(--primary-glow);
    }
    #chat-input {
      flex: 1;
      background: transparent;
      border: none;
      outline: none;
      color: var(--text-100);
      font-size: 0.9rem;
      font-family: var(--font-sans);
      resize: none;
      max-height: 120px;
      line-height: 1.5;
      padding: 4px 0;
    }
    #chat-input::placeholder { color: var(--text-400); }
    #send-btn {
      width: 40px; height: 40px; min-width: 40px;
      border-radius: 12px;
      background: var(--primary);
      border: none;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem;
      transition: var(--transition);
      color: #fff;
    }
    #send-btn:hover { background: var(--primary-light); transform: scale(1.05); }
    #send-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
    .input-hint { font-size: 0.7rem; color: var(--text-400); margin-top: 6px; text-align: right; }

    /* ─── Toast Notification ──────────────────────────────────────────────── */
    #toast-container {
      position: fixed;
      bottom: 24px; right: 24px;
      display: flex; flex-direction: column; gap: 8px;
      z-index: 9999;
    }
    .toast {
      padding: 12px 20px;
      border-radius: var(--radius-sm);
      font-size: 0.82rem;
      font-weight: 500;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4);
      animation: slideIn 0.3s ease;
      max-width: 320px;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(20px); }
      to   { opacity: 1; transform: translateX(0); }
    }
    .toast-success { background: #064e3b; border: 1px solid var(--success); color: #6ee7b7; }
    .toast-error   { background: #4c0519; border: 1px solid var(--danger); color: #fda4af; }
    .toast-info    { background: #1e1b4b; border: 1px solid var(--primary); color: var(--primary-light); }

    /* ─── Scrollbar ───────────────────────────────────────────────────────── */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--bg-600); border-radius: 99px; }

    /* ─── Responsive ──────────────────────────────────────────────────────── */
    @media (max-width: 768px) {
      .app-shell { grid-template-columns: 1fr; grid-template-rows: 64px auto 1fr; }
      .sidebar { display: none; }
      .main-area { grid-column: 1; }
    }
  </style>
</head>
<body>

<div class="app-shell">

  <!-- ── Topbar ─────────────────────────────────────────────────────────── -->
  <header class="topbar">
    <a class="brand" href="#">
      <div class="brand-icon">🎓</div>
      <span class="brand-name">Pro LMS</span>
      <span class="brand-badge">AI Powered</span>
    </a>
    <div class="topbar-right">
      <div class="status-dot" id="api-status-dot"></div>
      <span class="status-label" id="api-status-label">Checking API...</span>
    </div>
  </header>

  <!-- ── Sidebar ────────────────────────────────────────────────────────── -->
  <aside class="sidebar">
    <span class="sidebar-section-label">Navigation</span>
    <a class="nav-item active" href="index.php" id="nav-chat">
      <span class="icon">💬</span> AI Chat
    </a>
    <a class="nav-item" href="upload.php" id="nav-upload">
      <span class="icon">📤</span> Upload Materi
    </a>
    <a class="nav-item" href="documents.php" id="nav-docs">
      <span class="icon">📚</span> Dokumen Tersimpan
    </a>

    <div class="sidebar-divider"></div>
    <span class="sidebar-section-label">Filter Mata Kuliah</span>
    <div class="category-list" id="category-list">
      <button class="cat-btn active" data-cat="" onclick="setCategory(this, '')">🌐 Semua Kategori</button>
      <button class="cat-btn" data-cat="Fisika" onclick="setCategory(this, 'Fisika')">⚛️ Fisika</button>
      <button class="cat-btn" data-cat="Matematika" onclick="setCategory(this, 'Matematika')">📐 Matematika</button>
      <button class="cat-btn" data-cat="Kimia" onclick="setCategory(this, 'Kimia')">🧪 Kimia</button>
      <button class="cat-btn" data-cat="Biologi" onclick="setCategory(this, 'Biologi')">🧬 Biologi</button>
      <button class="cat-btn" data-cat="Sejarah" onclick="setCategory(this, 'Sejarah')">🏛️ Sejarah</button>
      <button class="cat-btn" data-cat="Ekonomi" onclick="setCategory(this, 'Ekonomi')">📊 Ekonomi</button>
    </div>

    <div class="sidebar-divider"></div>
    <span class="sidebar-section-label">Upload Cepat</span>
    <div id="quick-upload">
      <div class="upload-area" onclick="document.getElementById('quick-file').click()">
        <div class="upload-icon">📎</div>
        <p>Klik untuk upload PDF</p>
      </div>
      <input type="file" id="quick-file" accept=".pdf" style="display:none" onchange="handleQuickUpload(this)" />
      <div class="form-group">
        <label class="form-label">Kategori</label>
        <input type="text" id="quick-category" class="form-input" placeholder="Contoh: Fisika" />
      </div>
      <button class="btn btn-primary btn-full btn-sm" style="margin-top:8px" id="quick-upload-btn" onclick="submitQuickUpload()" disabled>
        Upload
      </button>
    </div>
  </aside>

  <!-- ── Main Chat Area ─────────────────────────────────────────────────── -->
  <main class="main-area">
    <div class="chat-header">
      <div>
        <div class="chat-title" id="chat-title">AI Assistant</div>
        <div class="chat-subtitle" id="chat-subtitle">Tanya apa saja tentang materi kuliah kamu</div>
      </div>
      <button class="btn btn-sm" onclick="clearChat()" style="background:var(--surface);border:1px solid var(--border);color:var(--text-200)">
        🗑️ Clear
      </button>
    </div>

    <div class="messages-container" id="messages">
      <!-- Welcome screen -->
      <div class="welcome-screen" id="welcome-screen">
        <div class="welcome-icon">🤖</div>
        <h1 class="welcome-title">Halo! Saya AI Akademik kamu</h1>
        <p class="welcome-subtitle">
          Powered by Google Gemini + RAG Technology. Saya bisa menjawab pertanyaan berdasarkan materi kuliah yang sudah diupload.
        </p>
        <div class="welcome-chips">
          <div class="chip" onclick="sendChip(this)">Jelaskan hukum Newton</div>
          <div class="chip" onclick="sendChip(this)">Apa itu integral?</div>
          <div class="chip" onclick="sendChip(this)">Rangkum materi terakhir</div>
          <div class="chip" onclick="sendChip(this)">Contoh soal Fisika</div>
        </div>
      </div>
    </div>

    <div class="input-row">
      <div class="input-wrap">
        <textarea
          id="chat-input"
          rows="1"
          placeholder="Tanya sesuatu tentang materi kuliahmu..."
          onkeydown="handleKey(event)"
          oninput="autoResize(this)"
        ></textarea>
        <button id="send-btn" onclick="sendMessage()" title="Kirim pesan">➤</button>
      </div>
      <div class="input-hint">Enter untuk kirim · Shift+Enter untuk baris baru</div>
    </div>
  </main>
</div>

<!-- Toast Container -->
<div id="toast-container"></div>

<script>
  // ─── Config ────────────────────────────────────────────────────────────────
  const API_BASE    = '<?= defined("BASE_API_URL") ? BASE_API_URL : "http://localhost:8001" ?>';
  const API_CHAT    = API_BASE + '/api/chat';
  const API_INGEST  = API_BASE + '/api/ingest';
  const API_DOCS    = API_BASE + '/api/documents';

  let selectedCategory = '';
  let isLoading = false;
  let messageHistory = [];

  // ─── API Status Check ──────────────────────────────────────────────────────
  async function checkApiStatus() {
    try {
      const res = await fetch(API_BASE + '/', { signal: AbortSignal.timeout(5000) });
      if (res.ok) {
        document.getElementById('api-status-dot').style.background = 'var(--success)';
        document.getElementById('api-status-dot').style.boxShadow = '0 0 8px var(--success)';
        document.getElementById('api-status-label').textContent = 'API Online';
      } else { throw new Error(); }
    } catch {
      document.getElementById('api-status-dot').style.background = 'var(--danger)';
      document.getElementById('api-status-dot').style.boxShadow = '0 0 8px var(--danger)';
      document.getElementById('api-status-label').textContent = 'API Offline';
    }
  }
  checkApiStatus();
  setInterval(checkApiStatus, 30000);

  // ─── Category Filter ────────────────────────────────────────────────────────
  function setCategory(el, cat) {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    selectedCategory = cat;
    const title    = document.getElementById('chat-title');
    const subtitle = document.getElementById('chat-subtitle');
    title.textContent    = cat ? `AI — ${cat}` : 'AI Assistant';
    subtitle.textContent = cat
      ? `Filter aktif: hanya menjawab tentang ${cat}`
      : 'Tanya apa saja tentang materi kuliah kamu';
  }

  // ─── Chat ───────────────────────────────────────────────────────────────────
  function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  }
  function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
  }

  async function sendMessage() {
    const input = document.getElementById('chat-input');
    const query = input.value.trim();
    if (!query || isLoading) return;

    hideWelcome();
    appendMessage('user', query);
    input.value = '';
    input.style.height = 'auto';

    const typingId = showTyping();
    isLoading = true;
    document.getElementById('send-btn').disabled = true;

    try {
      const body = { query };
      if (selectedCategory) body.category_filter = selectedCategory;

      const res = await fetch(API_CHAT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      });

      removeTyping(typingId);

      if (!res.ok) {
        const err = await res.json();
        appendMessage('ai', `❌ Error: ${err.detail || 'Terjadi kesalahan pada server.'}`, [], []);
      } else {
        const data = await res.json();
        appendMessage('ai', data.answer, data.sources || [], data.reference_links || []);
      }
    } catch (err) {
      removeTyping(typingId);
      appendMessage('ai', '❌ Gagal menghubungi server. Pastikan backend FastAPI sudah berjalan.', [], []);
    } finally {
      isLoading = false;
      document.getElementById('send-btn').disabled = false;
    }
  }

  function sendChip(el) {
    document.getElementById('chat-input').value = el.textContent;
    sendMessage();
  }

  function hideWelcome() {
    const w = document.getElementById('welcome-screen');
    if (w) w.remove();
  }

  function appendMessage(role, text, sources = [], refLinks = []) {
    const container = document.getElementById('messages');
    const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const isAI = role === 'ai';

    // Sources HTML
    let sourcesHTML = '';
    if (isAI && sources.length > 0) {
      sourcesHTML = '<div class="sources-block">' +
        sources.map(s => `<span class="source-tag">📄 ${s.source}${s.category ? ' · ' + s.category : ''}</span>`).join('') +
        '</div>';
    }

    // Ref links HTML
    let refHTML = '';
    if (isAI && refLinks.length > 0) {
      refHTML = '<div class="ref-links">' +
        refLinks.map(r => `<a class="ref-link" href="${r.url}" target="_blank" rel="noopener">🔗 ${r.title}</a>`).join('') +
        '</div>';
    }

    const el = document.createElement('div');
    el.className = `message ${role}`;
    el.innerHTML = `
      <div class="avatar ${isAI ? 'avatar-ai' : 'avatar-user'}">${isAI ? '🤖' : '👤'}</div>
      <div class="bubble-wrap">
        <div class="bubble ${isAI ? 'bubble-ai' : 'bubble-user'}">
          ${isAI ? marked.parse(text) : escapeHtml(text)}
        </div>
        ${sourcesHTML}
        ${refHTML}
        <span class="timestamp">${time}</span>
      </div>
    `;

    container.appendChild(el);
    container.scrollTop = container.scrollHeight;
  }

  function showTyping() {
    const id = 'typing-' + Date.now();
    const container = document.getElementById('messages');
    const el = document.createElement('div');
    el.className = 'message ai';
    el.id = id;
    el.innerHTML = `
      <div class="avatar avatar-ai">🤖</div>
      <div class="bubble-wrap">
        <div class="typing-indicator">
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
        </div>
      </div>
    `;
    container.appendChild(el);
    container.scrollTop = container.scrollHeight;
    return id;
  }
  function removeTyping(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
  }

  function clearChat() {
    const container = document.getElementById('messages');
    container.innerHTML = '';
    const welcome = document.createElement('div');
    welcome.className = 'welcome-screen';
    welcome.id = 'welcome-screen';
    welcome.innerHTML = `
      <div class="welcome-icon">🤖</div>
      <h1 class="welcome-title">Chat baru dimulai!</h1>
      <p class="welcome-subtitle">Silakan ajukan pertanyaan tentang materi kuliah kamu.</p>
    `;
    container.appendChild(welcome);
  }

  function escapeHtml(text) {
    return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  // ─── Quick Upload ───────────────────────────────────────────────────────────
  function handleQuickUpload(input) {
    document.getElementById('quick-upload-btn').disabled = !input.files.length;
  }

  async function submitQuickUpload() {
    const fileInput    = document.getElementById('quick-file');
    const categoryInput = document.getElementById('quick-category');
    const btn          = document.getElementById('quick-upload-btn');

    if (!fileInput.files.length) return showToast('Pilih file PDF terlebih dahulu.', 'error');
    if (!categoryInput.value.trim()) return showToast('Isi kategori mata kuliah.', 'error');

    btn.disabled = true;
    btn.textContent = 'Mengupload...';

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('category', categoryInput.value.trim());

    try {
      const res = await fetch(API_INGEST, { method: 'POST', body: formData });
      const data = await res.json();
      if (res.ok) {
        showToast(`✅ ${data.message}`, 'success');
        fileInput.value = '';
        categoryInput.value = '';
        btn.disabled = true;
      } else {
        showToast(`❌ ${data.detail}`, 'error');
      }
    } catch {
      showToast('❌ Gagal menghubungi server.', 'error');
    } finally {
      btn.textContent = 'Upload';
    }
  }

  // ─── Toast ──────────────────────────────────────────────────────────────────
  function showToast(msg, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = msg;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
  }
</script>

</body>
</html>
