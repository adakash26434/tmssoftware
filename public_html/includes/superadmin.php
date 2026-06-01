<?php
// ══════════════════════════════════════════════════════════════
// Ankur Infotech Pvt. Ltd. — Superadmin Credentials (FILE-BASED, NOT IN DB)
// ══════════════════════════════════════════════════════════════
//
// ⚠️  TEMPORARY: Plain-text password mode is ENABLED for development.
//                Switch back to a bcrypt hash BEFORE going to production.
//
// HOW TO USE PLAIN TEXT (current mode — quick & easy):
//   - Set SUPERADMIN_PASS_PLAIN to the password you want.
//   - Leave SUPERADMIN_PASS_HASH empty ('').
//   - Login at /login.php with SUPERADMIN_EMAIL + that plain password.
//
// HOW TO SWITCH TO SECURE BCRYPT (recommended for production):
//   1) Generate a hash from a shell:
//        php -r "echo password_hash('YourStrongPassword', PASSWORD_BCRYPT, ['cost'=>12]).PHP_EOL;"
//   2) Paste the resulting hash into SUPERADMIN_PASS_HASH.
//   3) Clear SUPERADMIN_PASS_PLAIN by setting it to ''.
// ──────────────────────────────────────────────────────────────

define('SUPERADMIN_EMAIL', 'infoankurinfotech.com.np');
define('SUPERADMIN_NAME',  'myadmin');

// ⚠️  PLAIN-TEXT password — active when non-empty. Remove for production.
define('SUPERADMIN_PASS_PLAIN', 'Akash55555');

// Bcrypt hash — used only when SUPERADMIN_PASS_PLAIN is empty.
define('SUPERADMIN_PASS_HASH', '');
