#!/usr/bin/env bash
set -e

echo "[entrypoint] Waiting for database connection..."
until php artisan db:show > /dev/null 2>&1; do
  echo "[entrypoint] Database not ready yet, retrying in 3s..."
  sleep 3
done

# Generate an APP_KEY and persist it into .env if neither the environment
# nor the .env file provides one. The exported value is what Laravel uses.
if [ -z "${APP_KEY:-}" ]; then
  echo "[entrypoint] Generating app key (if missing)..."
  if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    php artisan key:generate --force
  fi
  export APP_KEY="$(grep "^APP_KEY=" .env | cut -d= -f2-)"
fi

echo "[entrypoint] Running migrations..."
php artisan migrate --force

echo "[entrypoint] Seeding demo data (safe to re-run)..."
php artisan db:seed --class=DashboardSeeder --force || true

echo "[entrypoint] Caching config/routes..."
php artisan config:cache
php artisan route:cache

echo "[entrypoint] Starting: $@"
exec "$@"
