#!/usr/bin/env bash
set -euo pipefail
OUT=skyblue-local-snapshot.txt
{
  echo "=== GIT ==="
  git rev-parse --short HEAD 2>/dev/null || echo "no git"

  echo
  echo "=== PHP ==="
  php -v

  echo
  echo "=== PHP modules ==="
  php -m | sort

  echo
  echo "=== Laravel about ==="
  php artisan about || true

  echo
  echo "=== Node & npm ==="
  node -v 2>/dev/null || echo "node: not installed"
  npm -v 2>/dev/null || echo "npm: not installed"

  echo
  echo "=== Build tool ==="
  [ -f vite.config.js ] && echo "vite.config.js present"
  [ -f vite.config.ts ] && echo "vite.config.ts present"
  [ -f webpack.mix.js ] && echo "webpack.mix.js present"
  [ -f public/build/manifest.json ] && echo "public/build/manifest.json present"
  [ -f public/mix-manifest.json ] && echo "public/mix-manifest.json present"

  echo
  echo "=== .env (sanitized) ==="
  grep -E '^(APP_ENV|APP_URL|DB_CONNECTION|DB_DATABASE|CACHE_DRIVER|SESSION_DRIVER|QUEUE_CONNECTION|ASSET_URL|SESSION_DOMAIN|SANCTUM_STATEFUL_DOMAINS)=' .env 2>/dev/null || echo ".env not found"

  echo
  echo "=== composer.json (require) ==="
  awk '/"require"\s*:/,/^\s*},?$/ {print}' composer.json || cat composer.json || true

  echo
  echo "=== package.json (name & scripts) ==="
  awk '/"name"\s*:| "scripts"\s*:/,/^\s*},?$/ {print}' package.json || cat package.json || true
} > "$OUT"
echo "Wrote $OUT"
