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

AUTHED_URL="https://${GITHUB_USER}:${GITHUB_PAT}@github.com/${GITHUB_USER}/${GITHUB_REPO}.git"

# Add or update the github remote (never touches 'origin' which may not exist)
if git remote get-url github 2>/dev/null; then
  git remote set-url github "$AUTHED_URL"
else
  git remote add github "$AUTHED_URL"
fi

# Always remove the authenticated remote after push so the token never persists
cleanup() {
  git remote set-url github "$PUBLIC_URL" 2>/dev/null || true
}
trap cleanup EXIT

git push github HEAD:main --force-with-lease

echo "GitHub sync complete."
echo "Latest commit: $(git log -1 --oneline)"
