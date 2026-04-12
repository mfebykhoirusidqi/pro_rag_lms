<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Upload Materi — Pro LMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg-900:#0a0a0f;--bg-800:#12121a;--bg-700:#1a1a26;--bg-600:#22223a;
      --surface:rgba(255,255,255,0.04);--border:rgba(255,255,255,0.08);
      --primary:#6366f1;--primary-light:#818cf8;--primary-glow:rgba(99,102,241,0.25);
      --accent:#22d3ee;--success:#10b981;--danger:#f43f5e;--warning:#f59e0b;
      --text-100:#f1f5f9;--text-200:#cbd5e1;--text-400:#64748b;
      --font-sans:'Inter',sans-serif;
      --radius-sm:8px;--radius-md:12px;--radius-lg:16px;
      --transition:all 0.2s cubic-bezier(0.4,0,0.2,1);
    }
    html { font-size: 16px; }
    body {
      font-family:var(--font-sans);background:var(--bg-900);color:var(--text-100);
      min-height:100vh;display:flex;flex-direction:column;
    }
    body::before {
      content:'';position:fixed;inset:0;
      background:radial-gradient(ellipse 80% 50% at 20% 10%,rgba(99,102,241,.1) 0%,transparent 60%),
                 radial-gradient(ellipse 60% 40% at 80% 90%,rgba(34,211,238,.06) 0%,transparent 60%);
      pointer-events:none;z-index:0;
    }

    /* Topbar */
    .topbar {
      position:sticky;top:0;z-index:100;
      display:flex;align-items:center;gap:16px;padding:0 24px;height:64px;
      background:rgba(10,10,15,.8);backdrop-filter:blur(20px);
      border-bottom:1px solid var(--border);
    }
    .back-btn {
      display:flex;align-items:center;gap:6px;
      padding:8px 14px;border-radius:var(--radius-sm);
      background:var(--surface);border:1px solid var(--border);
      color:var(--text-200);font-size:.82rem;font-family:var(--font-sans);cursor:pointer;
      text-decoration:none;transition:var(--transition);
    }
    .back-btn:hover { background:rgba(255,255,255,.08);color:var(--text-100); }
    .page-title { font-size:1rem;font-weight:600; }

    /* Main */
    .page-content {
      flex:1;display:flex;align-items:center;justify-content:center;
      padding:40px 24px;position:relative;z-index:1;
    }
    .upload-card {
      width:100%;max-width:560px;
      background:var(--bg-800);border:1px solid var(--border);
      border-radius:var(--radius-lg);padding:36px;
      box-shadow:0 20px 60px rgba(0,0,0,.4);
    }
    .card-header { text-align:center;margin-bottom:28px; }
    .card-icon {
      font-size:2.5rem;
      background:linear-gradient(135deg,var(--primary),var(--accent));
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
      display:block;margin-bottom:12px;
    }
    .card-title { font-size:1.3rem;font-weight:700; }
    .card-subtitle { font-size:.85rem;color:var(--text-400);margin-top:6px; }

    /* Dropzone */
    .dropzone {
      border:2px dashed var(--border);border-radius:var(--radius-md);
      padding:36px 20px;text-align:center;cursor:pointer;
      transition:var(--transition);margin-bottom:20px;position:relative;
    }
    .dropzone.drag-over, .dropzone:hover { border-color:var(--primary);background:var(--primary-glow); }
    .dropzone input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer; }
    .dropzone-icon { font-size:2rem;margin-bottom:10px;display:block; }
    .dropzone-text { font-size:.85rem;color:var(--text-200);font-weight:500; }
    .dropzone-hint { font-size:.72rem;color:var(--text-400);margin-top:4px; }
    .file-preview {
      display:none;align-items:center;gap:10px;
      background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);
      border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:16px;
    }
    .file-preview.visible { display:flex; }
    .file-preview-name { font-size:.82rem;color:var(--primary-light);font-weight:500;flex:1; }
    .file-preview-remove {
      background:none;border:none;color:var(--danger);cursor:pointer;
      font-size:1.1rem;padding:2px;
    }

    /* Form */
    .form-group { margin-bottom:16px; }
    .form-label { display:block;font-size:.78rem;font-weight:600;color:var(--text-200);margin-bottom:6px; }
    .form-label span { color:var(--danger); }
    .form-input {
      width:100%;background:var(--bg-700);border:1px solid var(--border);
      border-radius:var(--radius-sm);padding:11px 14px;
      color:var(--text-100);font-size:.875rem;font-family:var(--font-sans);
      outline:none;transition:var(--transition);
    }
    .form-input:focus { border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-glow); }
    .form-hint { font-size:.72rem;color:var(--text-400);margin-top:4px; }

    /* Category chips */
    .cat-chips { display:flex;flex-wrap:wrap;gap:6px;margin-top:8px; }
    .cat-chip {
      padding:5px 12px;border-radius:99px;font-size:.75rem;
      background:var(--bg-600);border:1px solid var(--border);color:var(--text-200);
      cursor:pointer;transition:var(--transition);
    }
    .cat-chip:hover, .cat-chip.selected {
      background:var(--primary-glow);border-color:rgba(99,102,241,.4);color:var(--primary-light);
    }

    /* Progress */
    .progress-bar {
      display:none;height:4px;background:var(--bg-600);border-radius:99px;overflow:hidden;margin-bottom:16px;
    }
    .progress-bar.visible { display:block; }
    .progress-fill {
      height:100%;background:linear-gradient(90deg,var(--primary),var(--accent));
      border-radius:99px;width:0%;transition:width .3s ease;
      animation:shimmer 1.5s infinite;
    }
    @keyframes shimmer {
      0% { filter:brightness(1); }
      50% { filter:brightness(1.3); }
      100% { filter:brightness(1); }
    }

    /* Result */
    .result-box {
      display:none;border-radius:var(--radius-sm);padding:12px 16px;
      font-size:.82rem;margin-bottom:16px;
    }
    .result-box.visible { display:block; }
    .result-box.success { background:#064e3b;border:1px solid var(--success);color:#6ee7b7; }
    .result-box.error   { background:#4c0519;border:1px solid var(--danger);color:#fda4af; }

    /* Button */
    .btn-upload {
      width:100%;padding:13px;background:linear-gradient(135deg,var(--primary),#4f46e5);
      border:none;border-radius:var(--radius-md);color:#fff;font-size:.9rem;
      font-weight:700;font-family:var(--font-sans);cursor:pointer;
      transition:var(--transition);box-shadow:0 4px 20px var(--primary-glow);
    }
    .btn-upload:hover { transform:translateY(-2px);box-shadow:0 8px 30px var(--primary-glow); }
    .btn-upload:disabled { opacity:.5;cursor:not-allowed;transform:none; }
  </style>
</head>
<body>

<?php require_once 'config.php'; ?>

<header class="topbar">
  <a class="back-btn" href="index.php">← Kembali</a>
  <span class="page-title">📤 Upload Materi Kuliah</span>
</header>

<div class="page-content">
  <div class="upload-card">
    <div class="card-header">
      <span class="card-icon">📚</span>
      <h1 class="card-title">Upload Dokumen PDF</h1>
      <p class="card-subtitle">Dokumen akan diproses oleh AI dan diindeks ke Knowledge Base</p>
    </div>

    <!-- Dropzone -->
    <div class="dropzone" id="dropzone">
      <input type="file" id="pdf-file" accept=".pdf" onchange="handleFile(this)" />
      <span class="dropzone-icon">📄</span>
      <p class="dropzone-text">Drag & drop PDF kamu di sini</p>
      <p class="dropzone-hint">atau klik untuk memilih file · Maks. 50MB</p>
    </div>

    <div class="file-preview" id="file-preview">
      <span>📄</span>
      <span class="file-preview-name" id="file-name">—</span>
      <button class="file-preview-remove" onclick="removeFile()" title="Hapus">✕</button>
    </div>

    <!-- Category -->
    <div class="form-group">
      <label class="form-label">Kategori Mata Kuliah <span>*</span></label>
      <input type="text" id="category" class="form-input" placeholder="Contoh: Fisika Dasar" />
      <div class="cat-chips">
        <div class="cat-chip" onclick="pickCat(this,'Fisika')">⚛️ Fisika</div>
        <div class="cat-chip" onclick="pickCat(this,'Matematika')">📐 Matematika</div>
        <div class="cat-chip" onclick="pickCat(this,'Kimia')">🧪 Kimia</div>
        <div class="cat-chip" onclick="pickCat(this,'Biologi')">🧬 Biologi</div>
        <div class="cat-chip" onclick="pickCat(this,'Sejarah')">🏛️ Sejarah</div>
        <div class="cat-chip" onclick="pickCat(this,'Ekonomi')">📊 Ekonomi</div>
      </div>
      <p class="form-hint">Pilih dari chip di atas atau ketik manual</p>
    </div>

    <!-- Progress -->
    <div class="progress-bar" id="progress-bar">
      <div class="progress-fill" id="progress-fill"></div>
    </div>

    <!-- Result -->
    <div class="result-box" id="result-box"></div>

    <button class="btn-upload" id="upload-btn" onclick="submitUpload()" disabled>
      🚀 Upload & Proses ke Knowledge Base
    </button>
  </div>
</div>

<script>
  const API_INGEST = '<?= API_INGEST ?>';
  let selectedFile = null;

  // Drag & drop
  const dropzone = document.getElementById('dropzone');
  dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
  dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
  dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file && file.type === 'application/pdf') setFile(file);
    else showResult('Hanya file PDF yang diperbolehkan.', 'error');
  });

  function handleFile(input) {
    if (input.files[0]) setFile(input.files[0]);
  }
  function setFile(file) {
    selectedFile = file;
    document.getElementById('file-name').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(2) + ' MB)';
    document.getElementById('file-preview').classList.add('visible');
    document.getElementById('dropzone').style.display = 'none';
    updateBtn();
  }
  function removeFile() {
    selectedFile = null;
    document.getElementById('pdf-file').value = '';
    document.getElementById('file-preview').classList.remove('visible');
    document.getElementById('dropzone').style.display = 'block';
    updateBtn();
  }
  function pickCat(el, cat) {
    document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('category').value = cat;
    updateBtn();
  }
  function updateBtn() {
    document.getElementById('upload-btn').disabled = !(selectedFile && document.getElementById('category').value.trim());
  }
  document.getElementById('category').addEventListener('input', updateBtn);

  async function submitUpload() {
    const btn      = document.getElementById('upload-btn');
    const progress = document.getElementById('progress-bar');
    const fill     = document.getElementById('progress-fill');
    const category = document.getElementById('category').value.trim();

    if (!selectedFile || !category) return;

    btn.disabled = true;
    btn.textContent = '⏳ Sedang memproses...';
    progress.classList.add('visible');
    fill.style.width = '30%';
    hideResult();

    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('category', category);

    try {
      fill.style.width = '60%';
      const res = await fetch(API_INGEST, { method: 'POST', body: formData });
      fill.style.width = '100%';
      const data = await res.json();

      setTimeout(() => {
        progress.classList.remove('visible');
        fill.style.width = '0%';
      }, 600);

      if (res.ok) {
        showResult(`✅ ${data.message} (${data.chunks_added} chunk diindeks)`, 'success');
        removeFile();
        document.getElementById('category').value = '';
        document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('selected'));
      } else {
        showResult(`❌ ${data.detail}`, 'error');
      }
    } catch {
      showResult('❌ Gagal menghubungi server. Pastikan backend FastAPI sudah berjalan.', 'error');
      progress.classList.remove('visible');
    } finally {
      btn.disabled = false;
      btn.textContent = '🚀 Upload & Proses ke Knowledge Base';
      updateBtn();
    }
  }

  function showResult(msg, type) {
    const box = document.getElementById('result-box');
    box.textContent = msg;
    box.className = `result-box visible ${type}`;
  }
  function hideResult() {
    document.getElementById('result-box').className = 'result-box';
  }
</script>
</body>
</html>
