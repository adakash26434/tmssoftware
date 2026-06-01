#!/bin/bash
set -e
pnpm install --frozen-lockfile
pnpm --filter db push

# Auto-sync to GitHub after every merge
if [ -n "$GITHUB_PAT" ]; then
  echo "Syncing to GitHub..."
  git remote set-url origin "https://adakash26434:${GITHUB_PAT}@github.com/adakash26434/tmssoftware.git"
  git push origin main --force
  git remote set-url origin "https://github.com/adakash26434/tmssoftware.git"
  echo "GitHub sync complete."
else
  echo "GITHUB_PAT not set — skipping GitHub sync."
fi
