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

# Run migrations ONLY when explicitly enabled (RUN_MIGRATIONS=true).
# Production ควรตั้ง RUN_MIGRATIONS=false แล้วรัน manual ตามช่วง release:
#   docker compose exec backend php artisan migrate --force
# เพื่อควบคุมการเปลี่ยน schema ตอน deploy / rollback ไม่ให้เกิดอัตโนมัติทุกครั้งที่ container เริ่มใหม่
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "[entrypoint] Running migrations..."
  php artisan migrate --force
else
  echo "[entrypoint] Skipping migrations (RUN_MIGRATIONS != true)."
  echo "[entrypoint]   รัน manual ได้ด้วย: docker compose exec backend php artisan migrate --force"
fi

# Seed only when explicitly enabled (RUN_SEEDER=true). Production ควรเป็น false.
if [ "${RUN_SEEDER:-false}" = "true" ]; then
  echo "[entrypoint] Seeding demo data (safe to re-run)..."
  php artisan db:seed --class=DashboardSeeder --force || true
else
  echo "[entrypoint] Skipping seeder (RUN_SEEDER != true)."
  echo "[entrypoint]   รัน manual ได้ด้วย: docker compose exec backend php artisan db:seed --class=DashboardSeeder --force"
fi

echo "[entrypoint] Caching config/routes..."
php artisan config:cache
php artisan route:cache

echo "[entrypoint] Starting: $@"
exec "$@"
