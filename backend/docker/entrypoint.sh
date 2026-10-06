#!/usr/bin/env bash
set -e

# Handle APP_KEY safely (supports read-only .env)
if [ -z "${APP_KEY:-}" ]; then
  if grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    export APP_KEY="$(grep "^APP_KEY=" .env | head -n1 | cut -d= -f2- | tr -d '\r')"
  else
    echo "[entrypoint] APP_KEY is missing, generating runtime key..."
    export APP_KEY="$(php artisan key:generate --show)"
  fi
fi

# Wait for database connection with retry limit (15 retries * 2s = 30s)
echo "[entrypoint] Waiting for database connection..."
MAX_RETRIES=15
COUNT=0
until php artisan db:show > /dev/null 2>&1; do
  COUNT=$((COUNT + 1))
  if [ $COUNT -ge $MAX_RETRIES ]; then
    echo "[entrypoint] [WARNING] Database connection check timed out after ${MAX_RETRIES} attempts."
    php artisan db:show || true
    echo "[entrypoint] Proceeding anyway..."
    break
  fi
  echo "[entrypoint] Database not ready yet, retrying in 2s ($COUNT/$MAX_RETRIES)..."
  sleep 2
done

# Run migrations safely (do not crash container if tables already exist)
echo "[entrypoint] Running migrations..."
php artisan migrate --force || echo "[entrypoint] [WARNING] Migration had errors (tables may already exist), continuing..."

# Seed demo data only in non-production
if [ "${APP_ENV:-local}" != "production" ]; then
  echo "[entrypoint] Seeding demo data (non-production)..."
  php artisan db:seed --class=DashboardSeeder --force || true
fi

# Cache config & routes
echo "[entrypoint] Optimizing Laravel cache..."
php artisan config:cache || true
php artisan route:cache || true

echo "[entrypoint] Starting: $@"
exec "$@"
