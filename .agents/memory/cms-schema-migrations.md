---
name: CMS schema migrations
description: Pattern for adding new columns to SQLite tables in the Ankur Infotech CMS
---

The CMS uses a dual-path schema strategy for SQLite (dev) — new columns must be added in TWO places:

**1. `CREATE TABLE IF NOT EXISTS` block in `sqliteInit()`** — handles fresh databases:
- Located in `artifacts/cms/includes/sqlite-init.php` inside the big `$pdo->exec("...")` block
- Add the column directly to the CREATE TABLE statement

**2. `sqliteMigrate()` function in same file** — handles existing databases:
- Add `ALTER TABLE <table> ADD COLUMN ...` statements wrapped in per-statement try/catch
- Pattern: `foreach ([...ALTER statements...] as $__col) { try { $pdo->exec($__col); } catch (\Throwable $ignored) {} }`
- This is idempotent — fails silently if column already exists (SQLite has no IF NOT EXISTS for ALTER TABLE)

**Why:** `sqliteInit()` runs only once on a fresh DB; `sqliteMigrate()` runs on every boot so it catches existing DBs that were created before the column was added.

**How to apply:** When `admin/*.php` inserts/updates a column that causes "no such column" errors, add it to both places. After adding migrations, delete `artifacts/cms/.cache/dev.sqlite` AND restart the PHP workflow (PHP has a static PDO singleton that persists across requests in the same process).

**Columns added for services table:** `tagline`, `badge`, `price_from`, `lucide_icon`, `highlights`, `screenshot_url`
**Columns added for products table:** `lucide_icon`, `icon_color`, `show_on_home`, `home_position`, `home_card_wide`, `home_card_dark`, `home_bg_css`, `demo_screenshot_url`, `tab_label`

**Dev DB reset note:** Deleting dev.sqlite while PHP is running doesn't force a schema refresh — the static PDO holds an old file descriptor. Always restart the PHP CMS workflow after resetting the DB.
