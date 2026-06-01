---
name: PHP router chdir fix
description: router.php must chdir() before require or relative paths in admin PHP files fail
---

All admin PHP files use relative paths like `require_once '../includes/admin-layout.php'`. PHP resolves these relative to the CWD, not the file's own directory. The CWD is `artifacts/cms` (set by the workflow command), so `../includes/` resolves to `artifacts/includes/` — wrong.

**Fix:** Add `chdir(dirname($phpFile))` before every `require $phpFile` call in `router.php`.

**Why:** PHP built-in server's router script keeps its own CWD throughout all nested requires. Only `chdir()` or `__DIR__`-based paths work correctly.

**How to apply:** Any time a new `require` branch is added to `router.php`, always pair it with `chdir(dirname($file))` immediately before the `require`.
