#!/bin/bash
set -e

pnpm install --frozen-lockfile
pnpm --filter @workspace/db run push 2>/dev/null || true

# ── Auto-sync to GitHub after every Replit merge ──────────────────────────────
# Delegates to scripts/sync-github.sh.
# Requires GITHUB_SYNC_TOKEN secret (Tools → Secrets); GITHUB_PAT accepted as
# a legacy fallback. Skips silently if neither is set.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

TOKEN="${GITHUB_SYNC_TOKEN:-${GITHUB_PAT:-}}"

if [ -z "${TOKEN}" ]; then
  echo "GITHUB_SYNC_TOKEN not set — skipping GitHub sync."
  echo "Add it via Replit → Tools → Secrets to enable automatic mirroring."
else
  bash "${SCRIPT_DIR}/sync-github.sh"
fi
