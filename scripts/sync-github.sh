#!/usr/bin/env bash
# sync-github.sh — Manually push Replit commits to GitHub.
#
# Automatic sync already happens via scripts/post-merge.sh after every
# Replit task merge (configured via [postMerge] in .replit).
# Run this script to push outside of the normal merge cycle.
#
# REQUIREMENTS
# ─────────────
# GITHUB_PAT must be set as a Replit Secret (Tools → Secrets).
# It is already configured in this project.
#
# USAGE
# ─────
#   bash scripts/sync-github.sh

set -euo pipefail

GITHUB_USER="adakash26434"
GITHUB_REPO="tmssoftware"
PUBLIC_URL="https://github.com/${GITHUB_USER}/${GITHUB_REPO}.git"

if [[ -z "${GITHUB_PAT:-}" ]]; then
  echo ""
  echo "ERROR: GITHUB_PAT is not set."
  echo ""
  echo "It should already be configured as a Replit Secret."
  echo "Check Tools → Secrets and add GITHUB_PAT with a GitHub Personal"
  echo "Access Token (classic) that has the 'repo' scope."
  echo ""
  exit 1
fi

# Store original remote so we can restore it (in all exit paths).
ORIGINAL_ORIGIN=$(git remote get-url origin 2>/dev/null || echo "$PUBLIC_URL")

cleanup() {
  git remote set-url origin "$ORIGINAL_ORIGIN" 2>/dev/null || true
}
trap cleanup EXIT

echo "Syncing to GitHub (${PUBLIC_URL})..."

AUTHED_URL="https://${GITHUB_USER}:${GITHUB_PAT}@github.com/${GITHUB_USER}/${GITHUB_REPO}.git"
git remote set-url origin "$AUTHED_URL"
git push origin HEAD:main --force-with-lease

echo ""
echo "Done. GitHub is now up to date."
echo "Latest commit: $(git log -1 --oneline)"
