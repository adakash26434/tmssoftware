---
name: SQLite compat shim
description: sqliteCompat() in db.php converts MySQL SQL syntax to SQLite equivalents automatically
---

All CMS admin PHP files use MySQL SQL syntax. Dev runs SQLite. The shim transparently converts SQL at the PDO prepare() layer.

**Location:** `artifacts/cms/includes/db.php` — functions `sqliteCompat()`, `_sqliteConvertIf()`, `_sqliteConvertField()`, `_sqliteParseArgs()`

**Conversions handled:**
- `NOW()` → `datetime('now')`
- `=!column` (toggle pattern) → `=(1-column)`
- `IF(cond, a, b)` → `CASE WHEN cond THEN a ELSE b END` (nested parens handled by character-level parser)
- `FIELD(col, v1, v2, ...)` → `CASE col WHEN v1 THEN 1 WHEN v2 THEN 2 ... END`
- `DATE_SUB(expr, INTERVAL n UNIT)` → `datetime(expr, '-n UNITs')`
- `DATE_ADD(expr, INTERVAL n UNIT)` → `datetime(expr, '+n UNITs')`

**Why:** The CMS targets cPanel/MySQL in production but uses SQLite locally. Fixing 30+ files is impractical; the shim fixes all at once. Only applies when `DB_DRIVER === 'sqlite'` (no-op on MySQL).

**How to apply:** Any new PHP admin files that use MySQL SQL syntax will automatically be covered. Do NOT call PDO prepare() directly — always use `execute()`, `query()`, `queryOne()` helpers which invoke the shim.
