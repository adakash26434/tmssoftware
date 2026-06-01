<?php
// ── Replit Dev Environment — SQLite config ────────────────────
// This file is auto-loaded by config.php when REPL_ID is set.
// Uses SQLite so no MySQL setup is needed in Replit.

define('DB_DRIVER',      'sqlite');
define('DB_SQLITE_PATH', __DIR__ . '/../.cache/dev.sqlite');

// Override SITE_URL to the Replit dev domain
$replSlug = getenv('REPL_SLUG') ?: 'workspace';
$replOwner = getenv('REPL_OWNER') ?: 'user';
$devDomain = getenv('REPLIT_DEV_DOMAIN') ?: 'localhost:8000';
define('SITE_URL', 'https://' . $devDomain);
define('UPLOAD_URL', 'https://' . $devDomain . '/uploads/');
