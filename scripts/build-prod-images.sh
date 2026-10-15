#!/usr/bin/env bash
# =============================================================================
# PDM - Build & Push production images with versioned tags
#
# ใช้บนเครื่อง dev / CI เพื่อ build image เวอร์ชันเป็น tag แล้ว push ขึ้น registry
#
# การเลือก tag (เรียงตามลำดับความสำคัญ):
#   1. argument ตัวแรก         ./scripts/build-prod-images.sh v1.2.3
#   2. environment VERSION     VERSION=v1.2.3 ./scripts/build-prod-images.sh
#   3. git tag ที่ match v*    (เช่น v1.2.3) ถ้ามี tag สุดท้าย
#   4. fallback                sha-<short-commit> เช่น sha-abc1234
#
# Regression: IMAGE_REGISTRY=ghcr.io/keradach/ ./scripts/build-prod-images.sh
#            IMAGE_REGISTRY=ghcr.io/keradach/ PUSH=false ./scripts/build-prod-images.sh  # build อย่างเดียว (local test)
#            TAG_LATEST=false ./scripts/build-prod-images.sh v1.2.3                     # ไม่ re-tag :latest
# =============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

# --- Resolve image tag ---------------------------------------------------------
IMAGE_TAG=""
if [[ $# -ge 1 && -n "$1" ]]; then
  IMAGE_TAG="$1"
elif [[ -n "${VERSION:-}" ]]; then
  IMAGE_TAG="$VERSION"
elif GIT_TAG="$(git describe --tags --abbrev=0 --match 'v*' 2>/dev/null)" && [[ -n "$GIT_TAG" ]]; then
  IMAGE_TAG="$GIT_TAG"
else
  IMAGE_TAG="sha-$(git rev-parse --short HEAD)"
fi

IMAGE_REGISTRY="${IMAGE_REGISTRY:-}"
TAG_LATEST="${TAG_LATEST:-true}"    # ระบุ PUSH=false เพื่อ build อย่างเดียว (ไม่ push)
PUSH="${PUSH:-true}"                # ระบุ TAG_LATEST=false เพื่อไม่ re-tag/push :latest

BACKEND_IMAGE="${IMAGE_REGISTRY}pdm-backend"
FRONTEND_IMAGE="${IMAGE_REGISTRY}pdm-frontend"

echo "=== PDM image build ==="
echo "  IMAGE_TAG      : ${IMAGE_TAG}"
echo "  IMAGE_REGISTRY : ${IMAGE_REGISTRY:-<local only>}"
echo "  TAG_LATEST     : ${TAG_LATEST}"
echo "  PUSH           : ${PUSH}"
echo ""

# --- Build (compose จะ tag image ตามชื่อด้านบนอัตโนมัติ) -----------------------
IMAGE_TAG="$IMAGE_TAG" IMAGE_REGISTRY="$IMAGE_REGISTRY" \
  docker compose -f docker-compose.prod.yml build

# --- Tag :latest (optional) -----------------------------------------------------
if [[ "$TAG_LATEST" == "true" ]]; then
  echo "=== Re-tagging to :latest ==="
  docker tag "${BACKEND_IMAGE}:${IMAGE_TAG}"   "${BACKEND_IMAGE}:latest"
  docker tag "${FRONTEND_IMAGE}:${IMAGE_TAG}"  "${FRONTEND_IMAGE}:latest"
fi

# --- Push (optional) --------------------------------------------------------------
if [[ "$PUSH" == "true" ]]; then
  if [[ -n "$IMAGE_REGISTRY" ]]; then
    echo "=== Pushing ${IMAGE_TAG} (+ :latest) to ${IMAGE_REGISTRY} ==="
    docker push "${BACKEND_IMAGE}:${IMAGE_TAG}"
    docker push "${FRONTEND_IMAGE}:${IMAGE_TAG}"
    if [[ "$TAG_LATEST" == "true" ]]; then
      docker push "${BACKEND_IMAGE}:latest"
      docker push "${FRONTEND_IMAGE}:latest"
    fi
  else
    echo "!!! IMAGE_REGISTRY ไม่ได้ตั้งค่า — ข้ามการ push"
    echo "    ตั้ง IMAGE_REGISTRY เช่น IMAGE_REGISTRY=ghcr.io/keradach/ แล้วลองใหม่"
  fi
fi

echo ""
echo "=== Done. Images: ${BACKEND_IMAGE}:${IMAGE_TAG} / ${FRONTEND_IMAGE}:${IMAGE_TAG} ==="