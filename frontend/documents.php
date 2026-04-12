<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dokumen Tersimpan — Pro LMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg-900:#0a0a0f;--bg-800:#12121a;--bg-700:#1a1a26;--bg-600:#22223a;
      --surface:rgba(255,255,255,0.04);--border:rgba(255,255,255,0.08);
      --primary:#6366f1;--primary-light:#818cf8;--primary-glow:rgba(99,102,241,0.25);
      --accent:#22d3ee;--success:#10b981;--danger:#f43f5e;
      --text-100:#f1f5f9;--text-200:#cbd5e1;--text-400:#64748b;
      --font-sans:'Inter',sans-serif;
      --radius-sm:8px;--radius-md:12px;--radius-lg:16px;
      --transition:all 0.2s cubic-bezier(0.4,0,0.2,1);
    }
    html { font-size:16px; }
    body { font-family:var(--font-sans);background:var(--bg-900);color:var(--text-100);min-height:100vh; }
    body::before {
      content:'';position:fixed;inset:0;
      background:radial-gradient(ellipse 80% 50% at 20% 10%,rgba(99,102,241,.1) 0%,transparent 60%);
      pointer-events:none;z-index:0;
    }

    .topbar {
      position:sticky;top:0;z-index:100;
      display:flex;align-items:center;gap:16px;padding:0 24px;height:64px;
      background:rgba(10,10,15,.8);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);
    }
    .back-btn {
      display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:var(--radius-sm);
      background:var(--surface);border:1px solid var(--border);color:var(--text-200);
      font-size:.82rem;font-family:var(--font-sans);cursor:pointer;text-decoration:none;
      transition:var(--transition);
    }
    .back-btn:hover { background:rgba(255,255,255,.08);color:var(--text-100); }
    .page-title { font-size:1rem;font-weight:600; }

    .page-content { max-width:900px;margin:0 auto;padding:40px 24px;position:relative;z-index:1; }
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:28px; }
    .page-header h1 { font-size:1.4rem;font-weight:700; }
    .page-header p { font-size:.85rem;color:var(--text-400);margin-top:4px; }

    .stats-bar {
      display:flex;gap:16px;margin-bottom:24px;
    }
    .stat-card {
      flex:1;background:var(--bg-800);border:1px solid var(--border);border-radius:var(--radius-md);
      padding:16px;display:flex;align-items:center;gap:12px;
    }
    .stat-icon { font-size:1.5rem; }
    .stat-value { font-size:1.3rem;font-weight:700;color:var(--primary-light); }
    .stat-label { font-size:.72rem;color:var(--text-400); }

    .search-bar {
      background:var(--bg-800);border:1px solid var(--border);border-radius:var(--radius-md);
      padding:10px 16px;display:flex;align-items:center;gap:10px;margin-bottom:20px;
      transition:var(--transition);
    }
    .search-bar:focus-within { border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-glow); }
    .search-bar input {
      flex:1;background:transparent;border:none;outline:none;color:var(--text-100);
      font-size:.875rem;font-family:var(--font-sans);
    }
    .search-bar input::placeholder { color:var(--text-400); }

    /* Document grid */
    .docs-grid {
      display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;
    }
    .doc-card {
      background:var(--bg-800);border:1px solid var(--border);border-radius:var(--radius-md);
      padding:20px;transition:var(--transition);position:relative;overflow:hidden;
    }
    .doc-card::before {
      content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,var(--primary),var(--accent));
      border-radius:var(--radius-md) var(--radius-md) 0 0;
    }
    .doc-card:hover { border-color:rgba(99,102,241,.4);transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,0,0,.3); }
    .doc-icon { font-size:2rem;margin-bottom:12px;display:block; }
    .doc-name { font-size:.875rem;font-weight:600;margin-bottom:6px;word-break:break-all; }
    .doc-cat {
      display:inline-flex;align-items:center;gap:4px;
      background:var(--primary-glow);border:1px solid rgba(99,102,241,.3);
      border-radius:99px;padding:3px 10px;font-size:.72rem;color:var(--primary-light);
      margin-bottom:14px;
    }
    .doc-actions { display:flex;gap:8px; }
    .action-btn {
      flex:1;padding:7px;border-radius:var(--radius-sm);border:1px solid var(--border);
      background:var(--surface);color:var(--text-200);font-size:.75rem;
      font-family:var(--font-sans);cursor:pointer;transition:var(--transition);text-align:center;
      text-decoration:none;display:flex;align-items:center;justify-content:center;gap:4px;
    }
    .action-btn:hover { background:rgba(255,255,255,.08); }
    .action-btn.danger:hover { background:rgba(244,63,94,.15);border-color:var(--danger);color:#fda4af; }

    /* Empty state */
    .empty-state {
      text-align:center;padding:64px 24px;color:var(--text-400);
    }
    .empty-icon { font-size:3rem;margin-bottom:16px;display:block;opacity:.5; }
    .empty-title { font-size:1.1rem;font-weight:600;margin-bottom:8px; }
    .empty-text { font-size:.85rem; }
    .btn-go-upload {
      display:inline-flex;align-items:center;gap:8px;margin-top:20px;
      padding:10px 24px;background:var(--primary);border:none;border-radius:var(--radius-sm);
      color:#fff;font-size:.875rem;font-weight:600;font-family:var(--font-sans);
      cursor:pointer;text-decoration:none;transition:var(--transition);
    }
    .btn-go-upload:hover { background:var(--primary-light);transform:translateY(-2px); }

    /* Loading skeleton */
    .skeleton {
      display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;
    }
    .skel-card {
      background:var(--bg-800);border:1px solid var(--border);border-radius:var(--radius-md);
      padding:20px;
    }
    .skel-line {
      height:14px;background:linear-gradient(90deg,var(--bg-600) 25%,var(--bg-700) 50%,var(--bg-600) 75%);
      background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:99px;margin-bottom:10px;
    }
    .skel-line.short { width:60%; }
    .skel-line.shorter { width:40%; }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

    /* Toast */
    #toast {
      position:fixed;bottom:24px;right:24px;z-index:9999;
      padding:12px 20px;border-radius:var(--radius-sm);font-size:.82rem;
      display:none;animation:slideIn .3s ease;
    }
    #toast.success { display:block;background:#064e3b;border:1px solid var(--success);color:#6ee7b7; }
    #toast.error   { display:block;background:#4c0519;border:1px solid var(--danger);color:#fda4af; }
    @keyframes slideIn { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }

    /* Confirm modal */
    .modal-overlay {
      position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);
      z-index:200;display:none;align-items:center;justify-content:center;
    }
    .modal-overlay.open { display:flex; }
    .modal {
      background:var(--bg-800);border:1px solid var(--border);border-radius:var(--radius-lg);
      padding:28px;max-width:380px;width:90%;text-align:center;
    }
    .modal-title { font-size:1rem;font-weight:700;margin-bottom:8px; }
    .modal-text  { font-size:.85rem;color:var(--text-400);margin-bottom:24px; }
    .modal-actions { display:flex;gap:10px;justify-content:center; }
    .modal-btn {
      padding:9px 24px;border-radius:var(--radius-sm);font-family:var(--font-sans);
      font-size:.85rem;font-weight:600;cursor:pointer;border:none;transition:var(--transition);
    }
    .modal-btn.cancel  { background:var(--surface);border:1px solid var(--border);color:var(--text-200); }
    .modal-btn.confirm { background:var(--danger);color:#fff; }
    .modal-btn.cancel:hover  { background:rgba(255,255,255,.08); }
    .modal-btn.confirm:hover { background:#e11d48; }
  </style>
</head>
<body>

<?php require_once 'config.php'; ?>

<header class="topbar">
  <a class="back-btn" href="index.php">← Kembali</a>
  <span class="page-title">📚 Dokumen Tersimpan</span>
</header>

<div class="page-content">
  <div class="page-header">
    <div>
      <h1>Knowledge Base</h1>
      <p>Dokumen PDF yang sudah diindeks ke dalam AI Knowledge Base</p>
    </div>
    <a href="upload.php" style="display:flex;align-items:center;gap:6px;padding:9px 18px;background:var(--primary);border-radius:var(--radius-sm);color:#fff;font-size:.85rem;font-weight:600;text-decoration:none;transition:var(--transition);" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background='var(--primary)'">
      + Upload Baru
    </a>
  </div>

  <div class="stats-bar">
    <div class="stat-card">
      <span class="stat-icon">📄</span>
      <div>
        <div class="stat-value" id="total-docs">—</div>
        <div class="stat-label">Total Dokumen</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon">🏷️</span>
      <div>
        <div class="stat-value" id="total-cats">—</div>
        <div class="stat-label">Kategori</div>
      </div>
    </div>
    <div class="stat-card">
      <span class="stat-icon">✅</span>
      <div>
        <div class="stat-value" style="color:var(--success)">Aktif</div>
        <div class="stat-label">Status Knowledge Base</div>
      </div>
    </div>
  </div>

  <div class="search-bar">
    <span>🔍</span>
    <input type="text" id="search-input" placeholder="Cari dokumen atau kategori..." oninput="filterDocs()" />
  </div>

  <div id="skeleton" class="skeleton">
    <?php for($i=0;$i<6;$i++): ?>
    <div class="skel-card">
      <div class="skel-line" style="width:40%;height:10px;margin-bottom:14px"></div>
      <div class="skel-line" style="height:16px"></div>
      <div class="skel-line short"></div>
      <div class="skel-line shorter" style="height:28px;border-radius:var(--radius-sm)"></div>
    </div>
    <?php endfor; ?>
  </div>

  <div id="docs-grid" class="docs-grid" style="display:none;"></div>
  <div id="empty-state" class="empty-state" style="display:none">
    <span class="empty-icon">📭</span>
    <div class="empty-title">Belum ada dokumen</div>
    <p class="empty-text">Upload dokumen PDF pertama kamu untuk mulai membangun Knowledge Base</p>
    <a href="upload.php" class="btn-go-upload">📤 Upload Sekarang</a>
  </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirm-modal">
  <div class="modal">
    <div class="modal-title">⚠️ Hapus Dokumen?</div>
    <p class="modal-text" id="confirm-text">Dokumen ini akan dihapus secara permanen dari Knowledge Base.</p>
    <div class="modal-actions">
      <button class="modal-btn cancel" onclick="closeModal()">Batal</button>
      <button class="modal-btn confirm" id="confirm-btn">Hapus</button>
    </div>
  </div>
</div>

<div id="toast"></div>

<script>
  const API_DOCS = '<?= API_DOCUMENTS ?>';
  const API_BASE = '<?= BASE_API_URL ?>';
  let allDocuments = [];
  let pendingDelete = null;

  async function loadDocuments() {
    try {
      const res  = await fetch(API_DOCS);
      const data = await res.json();

      document.getElementById('skeleton').style.display = 'none';

      if (!res.ok || !data.documents?.length) {
        document.getElementById('empty-state').style.display = 'block';
        return;
      }

      allDocuments = data.documents;
      document.getElementById('total-docs').textContent = data.total_documents;
      const cats = [...new Set(data.documents.map(d => d.category))];
      document.getElementById('total-cats').textContent = cats.length;

      document.getElementById('docs-grid').style.display = 'grid';
      renderDocs(allDocuments);
    } catch {
      document.getElementById('skeleton').style.display = 'none';
      document.getElementById('empty-state').style.display = 'block';
    }
  }

  function renderDocs(docs) {
    const grid = document.getElementById('docs-grid');
    if (!docs.length) {
      grid.innerHTML = '<div class="empty-state"><span class="empty-icon">🔍</span><div class="empty-title">Tidak ditemukan</div><p class="empty-text">Coba kata kunci lain</p></div>';
      return;
    }
    grid.innerHTML = docs.map(doc => `
      <div class="doc-card">
        <span class="doc-icon">📄</span>
        <div class="doc-name">${escHtml(doc.source)}</div>
        <div class="doc-cat">🏷️ ${escHtml(doc.category)}</div>
        <div class="doc-actions">
          <a class="action-btn" href="index.php?cat=${encodeURIComponent(doc.category)}" title="Tanya tentang dokumen ini">💬 Tanya</a>
          <button class="action-btn danger" onclick="confirmDelete('${escHtml(doc.source)}')">🗑️ Hapus</button>
        </div>
      </div>
    `).join('');
  }

  function filterDocs() {
    const q = document.getElementById('search-input').value.toLowerCase();
    renderDocs(allDocuments.filter(d =>
      d.source.toLowerCase().includes(q) || d.category.toLowerCase().includes(q)
    ));
  }

  function confirmDelete(source) {
    pendingDelete = source;
    document.getElementById('confirm-text').textContent = `"${source}" akan dihapus secara permanen dari Knowledge Base.`;
    document.getElementById('confirm-modal').classList.add('open');
    document.getElementById('confirm-btn').onclick = () => deleteDoc(source);
  }
  function closeModal() {
    document.getElementById('confirm-modal').classList.remove('open');
    pendingDelete = null;
  }

  async function deleteDoc(source) {
    closeModal();
    try {
      const res = await fetch(`${API_BASE}/api/documents/${encodeURIComponent(source)}`, { method: 'DELETE' });
      if (res.ok) {
        showToast(`✅ "${source}" berhasil dihapus.`, 'success');
        loadDocuments();
      } else {
        const d = await res.json();
        showToast(`❌ ${d.detail}`, 'error');
      }
    } catch {
      showToast('❌ Gagal menghubungi server.', 'error');
    }
  }

  function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = type;
    setTimeout(() => { t.className = ''; }, 4000);
  }
  function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  loadDocuments();
</script>
</body>
</html>
