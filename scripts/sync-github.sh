#!/usr/bin/env bash
# sync-github.sh — Push Replit commits to GitHub.
#
# Called automatically by scripts/post-merge.sh after every Replit task merge
# (configured via [postMerge] in .replit).
# Run this script manually to push outside of the normal merge cycle.
#
# REQUIREMENTS
# ─────────────
# GITHUB_SYNC_TOKEN must be set as a Replit Secret (Tools → Secrets).
# It is a GitHub Personal Access Token (classic) with the 'repo' scope.
# Legacy: GITHUB_PAT is also accepted as a fallback.
#
# USAGE
# ─────
#   bash scripts/sync-github.sh

set -euo pipefail

GITHUB_USER="adakash26434"
GITHUB_REPO="tmssoftware"
PUBLIC_URL="https://github.com/${GITHUB_USER}/${GITHUB_REPO}.git"

# Accept GITHUB_SYNC_TOKEN (primary) or GITHUB_PAT (legacy fallback)
TOKEN="${GITHUB_SYNC_TOKEN:-${GITHUB_PAT:-}}"

if [[ -z "${TOKEN}" ]]; then
  echo ""
  echo "ERROR: GITHUB_SYNC_TOKEN is not set."
  echo ""
  echo "Add it via Replit → Tools → Secrets:"
  echo "  Key:   GITHUB_SYNC_TOKEN"
  echo "  Value: a GitHub Personal Access Token (classic) with the 'repo' scope"
  echo ""
  exit 1
fi

echo "Syncing to GitHub (${PUBLIC_URL})..."

AUTHED_URL="https://${GITHUB_USER}:${TOKEN}@github.com/${GITHUB_USER}/${GITHUB_REPO}.git"

# Add or update the github remote (never touches 'origin' which may not exist)
if git remote get-url github 2>/dev/null; then
  git remote set-url github "$AUTHED_URL"
else
  git remote add github "$AUTHED_URL"
fi

# Always reset the remote to the public URL on exit so the token never persists
cleanup() {
  git remote set-url github "$PUBLIC_URL" 2>/dev/null || true
}
trap cleanup EXIT

git push github HEAD:main --force

echo ""
echo "Done. GitHub is now up to date."
echo "Latest commit: $(git log -1 --oneline)"
