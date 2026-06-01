#!/bin/bash
set -e

pnpm install --frozen-lockfile
pnpm --filter @workspace/db run push 2>/dev/null || true

# ── Auto-sync to GitHub after every Replit merge ──────────────────────────────
# GITHUB_PAT must be stored as a Replit Secret (Tools → Secrets).
# Target: https://github.com/adakash26434/tmssoftware.git

GITHUB_USER="adakash26434"
GITHUB_REPO="tmssoftware"
PUBLIC_URL="https://github.com/${GITHUB_USER}/${GITHUB_REPO}.git"

if [ -z "${GITHUB_PAT:-}" ]; then
  echo "GITHUB_PAT not set — skipping GitHub sync."
  echo "Add it via Replit → Tools → Secrets to enable automatic mirroring."
  exit 0
fi

echo "Syncing to GitHub (${PUBLIC_URL})..."

# Store the current origin so we can restore it after pushing.
ORIGINAL_ORIGIN=$(git remote get-url origin 2>/dev/null || echo "")

# Ensure we always restore the clean (non-authenticated) remote URL,
# even if the push fails, so the token is never left in .git/config.
cleanup() {
  if [ -n "$ORIGINAL_ORIGIN" ]; then
    git remote set-url origin "$ORIGINAL_ORIGIN" 2>/dev/null || true
  else
    git remote set-url origin "$PUBLIC_URL" 2>/dev/null || true
  fi
}
trap cleanup EXIT

AUTHED_URL="https://${GITHUB_USER}:${GITHUB_PAT}@github.com/${GITHUB_USER}/${GITHUB_REPO}.git"
git remote set-url origin "$AUTHED_URL"
git push origin HEAD:main --force-with-lease

echo "GitHub sync complete."
echo "Latest commit: $(git log -1 --oneline)"
