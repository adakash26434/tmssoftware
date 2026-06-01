# Ankur Infotech CMS

A bilingual (English / Nepali) content management and business-operations platform for **Ankur Infotech Pvt. Ltd.**, Butwal, Nepal.

The system combines a public-facing website, an administrative panel, a client portal, and a REST API in a single PHP codebase designed to run on any standard cPanel / shared-hosting environment.

---

## Features

| Area | Capabilities |
|------|-------------|
| **Public site** | Products, services, news, portfolio, team, FAQs |
| **Admin panel** | CRM (leads, client import), tickets, orders, audit logs, site settings |
| **Client portal** | Ticket management, invoices, service access, 2FA/TOTP, profile |
| **Business ops** | License management, SLA monitoring, career/application tracking |
| **Automation** | Cron jobs for email-to-ticket, renewal reminders, SLA checks |

---

## Tech Stack

- **Backend:** PHP 8, PDO
- **Database:** MySQL / MariaDB (production) · SQLite (local dev, auto-initialized)
- **Frontend:** Tailwind CSS, DaisyUI, Alpine.js, Lucide icons
- **Localization:** Session-based EN / NP language switching

---

## Project Layout

```
artifacts/cms/
├── includes/
│   ├── config.php        # DB credentials, site URL, secrets
│   ├── db.php            # Database singleton + SQLite compat layer
│   ├── helpers.php       # Sanitisation, flash messages, audit logging
│   └── lang.php          # EN/NP translation helpers
├── admin/                # Admin panel pages
├── portal/               # Client portal pages
├── cron/                 # Automated background jobs
├── assets/               # Compiled CSS, JS, images
├── uploads/              # User-uploaded files (must be writable)
├── fresh_database.sql    # Initial schema + seed data
└── router.php            # Entry point for PHP built-in server
```

---

## Local Development

**Requirements:** PHP 8.0+

```bash
# From the cms directory
cd artifacts/cms
php -S localhost:8000 router.php
```

The app auto-detects a non-production environment and initialises a local SQLite database — no MySQL setup needed.

> **Recompile Tailwind** (only if editing styles):
> ```bash
> bunx tailwindcss -c tailwind.config.js -i input.css -o ../assets/css/tailwind.min.css --minify
> ```

---

## Production Setup (cPanel / Shared Host)

1. **Upload** — copy all files from `artifacts/cms/` into `public_html/` (or a subfolder).
2. **Database** — create a MySQL database and import `fresh_database.sql`.
3. **Configure** — edit `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_db');
   define('DB_USER', 'your_user');
   define('DB_PASS', 'your_password');
   define('SITE_URL', 'https://yourdomain.com');
   ```
4. **Permissions** — make the uploads directory writable:
   ```bash
   chmod 775 uploads/
   ```
5. **Admin login** — visit `/admin/login.php`. Default credentials are in `fresh_database.sql`.

---

## Cron Jobs

Add these to your hosting control panel's cron scheduler:

```
*/15 * * * *   php /path/to/public_html/cron/email-to-ticket.php
0    9 * * *   php /path/to/public_html/cron/renewal-reminders.php
*/5  * * * *   php /path/to/public_html/cron/sla-check.php
```

---

## Environment Notes

- Change the default admin password immediately after first login.
- The `uploads/` directory should be excluded from version control (add to `.gitignore`) after initial setup.
- All database queries run through the `sqliteCompat()` helper in `includes/db.php`, keeping MySQL and SQLite syntax consistent.
# ankurinotech
