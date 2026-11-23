#!/usr/bin/env bash
set -euo pipefail

# Idempotent deploy script for Hostinger shared hosting (staging).
# Usage: bash scripts/deploy-staging.sh [branch]

BRANCH="${1:-feat/staging-hostinger}"
PHP_BIN="${PHP_BIN:-/opt/alt/php83/usr/bin/php}"
COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer || true)}"
BUILD_ASSETS="${BUILD_ASSETS:-0}"

if [ ! -f ".env" ]; then
    echo "Error: .env not found. Copy .env.staging.example to .env and configure it first." >&2
    exit 1
fi

if [ ! -x "$PHP_BIN" ]; then
    echo "Error: PHP 8.3 binary not executable at $PHP_BIN" >&2
    exit 1
fi

if [ -z "$COMPOSER_BIN" ]; then
    echo "Error: composer binary not found in PATH" >&2
    exit 1
fi

if [ -d ".git" ]; then
    echo "==> Pulling latest commits from $BRANCH"
    git pull --ff-only origin "$BRANCH"
else
    echo "==> Skipping git pull (non-git rsync deploy)"
fi

echo "==> Installing production Composer dependencies"
"$PHP_BIN" "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction

if [ "$BUILD_ASSETS" = "1" ] && command -v npm >/dev/null 2>&1; then
    echo "==> Building frontend assets"
    npm ci --no-audit --no-fund
    npm run build
fi

echo "==> Running database migrations"
"$PHP_BIN" artisan migrate --force

if [ ! -L "public/storage" ]; then
    echo "==> Creating storage symlink"
    "$PHP_BIN" artisan storage:link
fi

echo "==> Refreshing Laravel and Filament caches"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan filament:optimize

echo "==> Staging deploy completed"
