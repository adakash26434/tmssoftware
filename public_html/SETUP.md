# Ankur Infotech Pvt. Ltd. — Setup

PHP 8 + MySQL + Tailwind (compiled) + DaisyUI + Alpine + Lucide.
Public site, admin panel, client portal, REST API — single codebase.

## Quick start (cPanel / shared host)

1. Upload everything to `public_html/` (or sub-folder).
2. Create a MySQL database, import `fresh_database.sql`.
3. Edit `includes/config.php` — set DB credentials, `SITE_URL`, `SITE_NAME`, mail SMTP.
4. Ensure `uploads/` is writable (chmod 775).
5. Visit `/admin/login.php` — default admin credentials are in `fresh_database.sql` (change immediately).

## Local dev

```bash
php -S localhost:8000 router.php
```

## Rebuilding Tailwind

After adding new utility classes anywhere in `*.php`, recompile:

```bash
cd tw-build   # only if you keep the build folder separately
bunx tailwindcss -c tailwind.config.js -i input.css \
  -o ../assets/css/tailwind.min.css --minify
```

The current `assets/css/tailwind.min.css` (~31 KB) is already production-ready.

## Asset layout

```
assets/
  theme.css            ← design tokens + global components
  css/
    tailwind.min.css   ← compiled production Tailwind
    fonts.css          ← self-hosted @font-face declarations
    daisyui.min.css    ← admin/portal only
    pages.css          ← shared public-page overrides
    home.css           ← homepage-only layout + animations
    st-bs-datepicker.css
  fonts/               ← 13 woff2 files (Sora, DM Sans, Noto Devanagari, JetBrains Mono)
  vendor/
    alpine.min.js      ← Alpine 3.14.1 pinned
    lucide.min.js      ← Lucide UMD pinned
  js/
    st-bs-datepicker.js
```

## Cron jobs (optional)

```
*/15 * * * *  php /path/to/cron/email-to-ticket.php
0 9 * * *     php /path/to/cron/renewal-reminders.php
*/5 * * * *   php /path/to/cron/sla-check.php
```

## Automated deployment (GitHub Actions)

Every push to `main` that passes the PHP syntax check is automatically deployed to `public_html/` on the cPanel server via FTP. No manual upload needed.

**Required GitHub Actions secrets** (set in your repo → Settings → Secrets → Actions):

| Secret | Description |
|---|---|
| `FTP_SERVER` | Your cPanel FTP hostname, e.g. `ftp.ankurinfotech.com.np` or the server IP |
| `FTP_USERNAME` | FTP username (usually your cPanel username or `user@domain`) |
| `FTP_PASSWORD` | FTP password for the account above |

The deploy job uploads everything under `artifacts/cms/` into `public_html/`, skipping `.git`, `.env`, `node_modules`, and `dev-config.php` so local-only files never reach production.

## Security checklist before going live

- [ ] Change default admin password
- [ ] Set `SITE_URL` to your https domain
- [ ] Enable HTTPS redirect in `.htaccess`
- [ ] Update SMTP credentials in `includes/config.php`
- [ ] Set strong `SESSION_SALT` in `includes/config.php`
- [ ] Confirm `uploads/` is outside web root or has `.htaccess` deny-execute
