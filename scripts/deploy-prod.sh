#!/usr/bin/env bash
# =============================================================================
# PDM - Deploy / Rollback production stack using versioned images
#
# ใช้บน PRODUCTION SERVER (โค้ดโปรเจกต์ที่ clone ไว้บนเครื่อง server)
#
# ตัวอย่าง:
#   ./scripts/deploy-prod.sh                       # deploy ตาม IMAGE_TAG ใน .env
#   IMAGE_TAG=v1.2.3 ./scripts/deploy-prod.sh      # เปลี่ยน/rollback ไป v1.2.3
#   IMAGE_TAG=v1.1.0 ./scripts/deploy-prod.sh      # rollback กลับเวอร์ชันก่อนหน้า
#
# หมายเหตุ:
#   - IMAGE_TAG / IMAGE_REGISTRY อ่านจาก .env บน server โดยอัตโนมัติ
#     (compose ถูกตั้ง COMPOSE_FILE=docker-compose.prod.yml)
#   - ถ้าต้องการ push image ขึ้น registry ให้รัน build-prod-images.sh แล้ว
#     set IMAGE_REGISTRY ที่นี่ด้วย (จะ docker compose pull ก่อน up)
#   - ใช้ --no-build เสมอเพื่อให้ใช้ image ที่ระบุ tag เท่านั้น
# =============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
  echo "ERROR: ไม่พบ .env กรุณา cp .env.production.example .env ก่อน" >&2
  exit 1
fi

# PASS: ตัวแปรที่ override ผ่านคำสั่ง (เช่น IMAGE_TAG=v1.2.3) ต้องส่งต่อไป compose
# compose จะอ่าน IMAGE_TAG/IMAGE_REGISTRY จาก .env เองอยู่แล้ว
export IMAGE_TAG="${IMAGE_TAG:-}"

# ให้ script รู้จัก registry ที่ set ไว้ใน .env (เพื่อตัดสินใจ pull) โดยไม่อ่านทั้งไฟล์
if [[ -z "${IMAGE_REGISTRY:-}" ]]; then
  IMAGE_REGISTRY="$(grep -E '^IMAGE_REGISTRY=' .env | tail -1 | cut -d= -f2- | tr -d '"' || true)"
fi
export IMAGE_REGISTRY="${IMAGE_REGISTRY:-}"

echo "=== PDM deploy ==="
echo "  IMAGE_TAG      : ${IMAGE_TAG:-<จาก .env / latest>}"
echo "  IMAGE_REGISTRY : ${IMAGE_REGISTRY:-<local only>}"
echo ""

# --- Pull versioned images (ถ้ามี registry) -----------------------------------
if [[ -n "$IMAGE_REGISTRY" ]]; then
  echo "=== Pulling images from ${IMAGE_REGISTRY} ==="
  docker compose -f docker-compose.prod.yml pull
else
  echo "!!! IMAGE_REGISTRY ไม่ได้ตั้งค่า — ข้าม pull (ใช้ image ที่ build ไว้ในเครื่อง)"
fi

# --- Start stack with the pinned tag (no rebuild) ------------------------------
echo "=== Starting stack (image tag: ${IMAGE_TAG:-latest}) ==="
docker compose -f docker-compose.prod.yml up -d --no-build --remove-orphans

echo ""
echo "=== Done. รัน 'docker compose ps' เพื่อตรวจสอบสถานะ ==="