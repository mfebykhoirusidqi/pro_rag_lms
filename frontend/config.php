<?php
/**
 * Pro LMS — Konfigurasi Global Frontend
 * ======================================
 * Ubah BASE_API_URL sesuai dengan alamat backend FastAPI kamu.
 */

define('APP_NAME', 'Pro LMS');
define('APP_VERSION', '2.0.0');

// URL backend FastAPI — sesuaikan saat deploy
define('BASE_API_URL', 'http://localhost:8001');

// Endpoint API
define('API_CHAT',      BASE_API_URL . '/api/chat');
define('API_INGEST',    BASE_API_URL . '/api/ingest');
define('API_DOCUMENTS', BASE_API_URL . '/api/documents');

// Session timeout (detik)
define('SESSION_TIMEOUT', 3600);

session_start();
