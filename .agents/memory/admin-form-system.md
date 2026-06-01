---
name: Admin form system
description: CSS classes, JS, and conventions for the CMS admin form layout system
---

**Layout system** lives in `artifacts/cms/assets/css/admin-forms.css`, loaded for admin+portal contexts via `head.php`.

**Split-panel pattern** (standard for all list+edit pages):
- `.af-split` — CSS grid (1fr 380px), list left, form panel right
- `.af-panel` — sticky right column wrapper

**In-panel form tabs** (for complex forms with 10+ fields):
- `.af-tab-nav` + `.af-tab-btn[data-tab="x"]` — tab buttons
- `.af-tab-pane[data-tab-pane="x"]` — tab content panes
- Tab-switching JS is in `includes/admin-layout-close.php` (shared, runs on all admin pages)
- Panes use `display:flex; flex-direction:column; gap:0.875rem` when active

**Page-level navigation tabs** (e.g. Jobs / Applications):
- `.af-page-tabs` + `.af-page-tab` + `.af-page-tab.active`
- `.af-page-tab .af-badge` — pill badge on tab (e.g. pending count)

**Applied to:** products.php (Basic/Content/Homepage tabs), news.php (Content/Publish tabs), staff.php (converted from modal to split-panel), careers.php (fixed broken markup, CSS page tabs).

**Why tabs are recommended:** forms with 3+ logical groups of fields benefit from tabs — it reduces visible form height and makes the 380px panel scannable. Simple forms (banners, faqs, partners) stay as single-column `.col-1`/`.col-1-tight` stacks.

**Form footer:** use `.af-form-footer` div for submit + cancel buttons — provides consistent border-top spacing.
