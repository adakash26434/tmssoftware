<?php
// ══════════════════════════════════════════════════════════════
// Ankur Infotech Pvt. Ltd. — Site Configuration for cPanel Hosting
// Butwal, Rupandehi, Nepal
// Phone: +977-071-438585, 071-437612
// Email: ankurinfotech8@gmail.com
// IMPORTANT: Edit DB_HOST / DB_NAME / DB_USER / DB_PASS / SITE_URL below.
// ══════════════════════════════════════════════════════════════

// ── Auto-load Replit dev config (SQLite, no MySQL needed) ─────
// When running in Replit (REPL_ID env is set) we use SQLite.
// This block is skipped on cPanel (no REPL_ID there).
if (getenv('REPL_ID') !== false && file_exists(__DIR__ . '/dev-config.php')) {
    require_once __DIR__ . '/dev-config.php';
}

// ── Database (fill in your cPanel MySQL details) ──────────────
if (!defined('DB_HOST'))    define('DB_HOST',    'localhost');
if (!defined('DB_NAME'))    define('DB_NAME',    'ankurinfotechcom_admin');
if (!defined('DB_USER'))    define('DB_USER',    'ankurinfotechcom_admin');
if (!defined('DB_PASS'))    define('DB_PASS',    'Akash55555.');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// ── Site URL (no trailing slash) ──────────────────────────────
// Root install:      define('SITE_URL', 'https://yourdomain.com');
// Subfolder install: define('SITE_URL', 'https://yourdomain.com/sahakari');
if (!defined('SITE_URL')) define('SITE_URL', 'https://ankurinfotech.com.np/');

// ── Site Identity ─────────────────────────────────────────────
define('SITE_NAME', 'Ankur Infotech Pvt. Ltd.');

// ── File Uploads ──────────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
if (!defined('UPLOAD_URL')) define('UPLOAD_URL', SITE_URL . '/uploads/');

// ── Session Security ──────────────────────────────────────────
// IMPORTANT: Generate a random 64-char string at https://generate-random.org/string-generator
if (!defined('SESSION_SECRET'))
    define('SESSION_SECRET', 'CHANGE_THIS_TO_A_RANDOM_64_CHAR_STRING_BEFORE_GOING_LIVE!!!!!!!!');

// ── PHP Settings ──────────────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
date_default_timezone_set('Asia/Kathmandu');

// Start session early (lang.php needs it)
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
             || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $isHttps,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Auto-load language helpers so isNepali(), __() and cms() work everywhere
// (must come after session_start so lang detection can read $_SESSION['lang'])
require_once __DIR__ . '/lang.php';
