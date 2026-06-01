---
name: CMS table architecture
description: Ankur Infotech CMS — services vs products table separation and public page routing
---

The CMS has two separate content types that must not be confused:

**`services` table** (managed by `admin/services.php`):
- IT infrastructure services: Cloud Hosting, Domain & Hosting, Bulk SMS, Security Audit, Website Dev, IT Consultancy
- Displayed on public `services.php` — queries: `SELECT title AS name, slug, tagline, ... FROM services WHERE active=1`
- Columns: `id, title, slug, tagline, summary, description, icon, lucide_icon, icon_color, badge, price_from, highlights, features, screenshot_url, active, position, created_at, updated_at`

**`products` table** (managed by `admin/products.php`):
- Software products: Core Banking System, Mobile Banking App, DMS, HR & Payroll, Cooperative Website
- Displayed on public `products.php` — queries: `SELECT slug, name, tagline, ... FROM products WHERE active=1`
- Columns include extra: `show_on_home, home_position, home_card_wide, home_card_dark, home_bg_css, demo_screenshot_url, tab_label`

**Why:** services = IT/infrastructure services sold standalone; products = proprietary software platforms licensed to clients.

**How to apply:** When the public `services.php` page shows wrong data, check it queries FROM services (not products). When the public `products.php` page shows fallback defaults, check the products table has `lucide_icon` and `icon_color` columns (may need migration).

**Historical bug fixed:** `services.php` was previously querying `FROM products WHERE active=1` — this caused the admin-managed services to never appear on the public page. Fixed by correcting the query and column aliases.
