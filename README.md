# PDM - ศูนย์ปฏิบัติการติดตามและแก้ไขปัญหาภัยพิบัติด้านพืช

Web application scaffold ที่จำลองแดชบอร์ด PDM (Plant Disaster Monitoring Platform)
ตามภาพต้นแบบที่ให้มา โดยใช้:

- **Backend**: Laravel 13 (REST API) + Oracle Database (ผ่าน `yajra/laravel-oci8`)
- **Frontend**: Vue 3 + Vite + Pinia + Chart.js
- **Infra**: Docker / docker-compose (Laravel + nginx, Vue build + nginx, Oracle DB)

> ⚠️ **สิ่งที่ควรทราบก่อนใช้งาน**: โค้ด Laravel เต็มรูปแบบ (skeleton) ถูกสร้างไว้ใน
> `backend/` แล้ว และ **Composer dependencies ถูกจัดการบนเครื่อง host** (ไม่ใช่ภายใน Docker)
> ดังนั้นก่อน build ต้องรัน `composer install` ใน `backend/` ก่อน
> ส่วน Oracle Instant Client ยังคงถูกดาวน์โหลดภายใน Dockerfile (จำเป็นสำหรับ oci8 extension)

---

## โครงสร้างโปรเจกต์

```
pdm-project/
├── docker-compose.yml          # SINGLE compose file for BOTH local & production
│                               #   mode เลือกผ่าน COMPOSE_PROFILES ใน .env
├── .env                        # ค่า env สำหรับ docker-compose (local)
├── .env.production             # template สำหรับ production (คัดลอกไป .env บนเซิร์ฟเวอร์ prod)
├── docker/nginx/default.conf      # nginx reverse-proxy หน้า Laravel (LOCAL)
├── docker/nginx/default.prod.conf # nginx reverse-proxy หน้า Laravel (PRODUCTION)
│
├── backend/                    # Laravel 13 API (skeleton + โค้ดโปรเจกต์)
│   ├── Dockerfile              # build PHP-FPM + oci8 (Oracle) — ไม่มี composer steps
│   ├── .env                    # ค่า env ของ Laravel (local)
│   ├── .env.production         # ค่า env ของ Laravel (production)
│   ├── docker/entrypoint.sh    # รอ DB พร้อม -> (migrate/seed ถ้า env เปิด) -> start php-fpm
│   ├── composer.json / composer.lock  # จัดการ dependencies บน host
│   ├── vendor/                 # dependencies ที่ติดตั้งบน host (bind-mount เข้า container)
│   ├── app/                    # โค้ด Laravel (controllers, models, ...)
│   ├── config/                 # config ของ Laravel
│   ├── database/               # migrations + seeders
│   ├── routes/                 # routes
│   └── ...
│
└── frontend/                   # Vue 3 SPA
    ├── Dockerfile              # multi-stage: npm build -> serve ด้วย nginx (prod)
    ├── nginx.conf
    ├── src/
    │   ├── components/         # HeaderBar, AlertBanner, StatCards, RiskMapCard,
    │   │                       # DisasterGauges, TrendLineChart, DisasterPieChart,
    │   │                       # TopProvincesBarChart, InfoPanel, PartnerLogos, ...
    │   ├── views/Dashboard.vue # ประกอบทุก component เป็นหน้าแดชบอร์ดตาม mockup
    │   ├── stores/dashboard.js # Pinia store เรียก API ทั้งหมด
    │   └── services/api.js     # axios client
    └── ...
```

---

## โหมดการทำงาน (Local / Production) ผ่าน docker-compose เดียวกัน

ใช้ `docker-compose.yml` **ไฟล์เดียว** ทั้ง local และ production โดยเลือกโหมดจากตัวแปร `COMPOSE_PROFILES` ใน `.env`:

| `COMPOSE_PROFILES` | โหมด        | ลักษณะการทำงาน                                                                               |
| ------------------ | ----------- | -------------------------------------------------------------------------------------------- |
| `local` (default)  | Development | Vite dev server (hot-reload) + **bind-mount** source code → แก้โค้ดแล้วเห็นผลทันที           |
| `production`       | Deployment  | Build static assets → nginx serve + **baked** code ใน image → แก้โค้ดต้อง rebuild image ใหม่ |

### คำสั่งรันเหมือนกันทั้งสองโหมด

```bash
docker compose up -d --build
```

- ถ้า `.env` มี `COMPOSE_PROFILES=local` → รันเป็น local
- ถ้า `.env` มี `COMPOSE_PROFILES=production` → รันเป็น production

> บน production server ให้คัดลอก `.env.production` ไปเป็น `.env` แล้วแก้ค่าต่าง ๆ ตามจริง

---

## API endpoints (Laravel)

| Method | Endpoint                     | คำอธิบาย                                                   |
| ------ | ---------------------------- | ---------------------------------------------------------- |
| GET    | `/api/dashboard/summary`     | การ์ดสรุปด้านบน (จังหวัดเสี่ยงภัย, อำเภอ, เกษตรกร, ฯลฯ)    |
| GET    | `/api/dashboard/gauges`      | 4 เกจ: ภัยแล้ง / น้ำท่วม / ศัตรูพืช / ภัยหนาว              |
| GET    | `/api/provinces`             | รายชื่อจังหวัด + พิกัด + ระดับความเสี่ยง (ใช้วาด Risk Map) |
| GET    | `/api/provinces/{id}`        | รายละเอียดจังหวัด + รายงานภัยพิบัติ                        |
| GET    | `/api/provinces/top-damaged` | 10 อันดับจังหวัดพื้นที่เสียหายสูงสุด                       |
| GET    | `/api/reports/trend`         | แนวโน้มพื้นที่เสียหายรายเดือน                              |
| GET    | `/api/reports/breakdown`     | สัดส่วนประเภทภัยพิบัติ                                     |
| GET    | `/api/alerts`                | ประกาศแจ้งเตือนที่ active อยู่ (แบนเนอร์สีแดง)             |
| POST   | `/api/disaster/subsidy`      | สรุปการช่วยเหลือผู้ประสบภัยด้านพืช แยกตามช่วงเวลา/พื้นที่ |

#### Third-party API endpoints (responses cached into the DB)

Every third-party API response (TMD, Thai Water, eFarmer, Open-Meteo, Nominatim)
is persisted into the `api_caches` table **before** it is served. The Laravel
backend proxies each upstream call, stores the payload in the DB, and the
frontend reads from the DB only (it never calls upstream directly). If an
upstream call fails or is slow, the last cached copy from the DB is returned.

| Method | Endpoint                                        | Source            | TTL |
| ------ | ----------------------------------------------- | ----------------- | --- |
| GET    | `/api/external/disaster/breakdown`              | eFarmer           | 15m |
| GET    | `/api/external/rain/today`                      | Thai Water        | 5m  |
| GET    | `/api/external/rain/yesterday`                  | Thai Water        | 30m |
| GET    | `/api/external/rain/24h`                        | Thai Water        | 5m  |
| GET    | `/api/external/rain/3d`                         | Thai Water        | 30m |
| GET    | `/api/external/rain/7d`                         | Thai Water        | 60m |
| GET    | `/api/external/dam-water`                       | Thai Water (dam)  | 30m |
| GET    | `/api/external/temperature-stations`            | TMD (awsnow)      | 15m |
| GET    | `/api/external/rainfall`                        | TMD (awsrainfall) | 5m  |
| GET    | `/api/external/weather/forecast?lat&lng`        | Open-Meteo        | 60m |
| GET    | `/api/external/weather/reverse-geocode?lat&lng` | Nominatim         | 24h |

ตัวอย่างเรียกข้อมูลการช่วยเหลือผู้ประสบภัยด้านพืช โดย filter พื้นที่เป็น optional:

```json
{
  "date_from": "2026-01",
  "date_to": "2026-12",
  "danger_type_id": null,
  "danger_type_name": null,
  "province_code": null,
  "amphur_code": null,
  "tambon_code": null
}
```

API นี้ proxy ไปยัง eFarmer และ cache แยกตามชุด filter เป็นเวลา 4 ชั่วโมง
ส่วน Dashboard จะแสดงตัวเลขสรุปและรายละเอียดตามกลุ่มพืชใต้แผนที่

To fetch every source immediately (e.g. via cron) and persist into the DB:

```bash
php artisan api:cache          # respects each endpoint TTL
php artisan api:cache --force  # force re-fetch all endpoints
```

---

## วิธีรัน (Docker) — Local Development / Testing

### 1. ติดตั้ง Composer dependencies บน host

```bash
cd backend
composer install --ignore-platform-req=ext-oci8
cd ..
```

> `--ignore-platform-req=ext-oci8` จำเป็นเพราะเครื่อง host ไม่ได้ติดตั้ง
> Oracle Instant Client / oci8 extension (ติดตั้งเฉพาะใน Docker image เท่านั้น)

#### ทำไมต้องใช้ `--ignore-platform-req=ext-oci8` ทั้งที่ระบบต้องใช้ oci8 จริง?

อธิบายง่าย ๆ: **`composer install` รันบนเครื่อง host (เครื่อง dev) ไม่ได้รันใน Docker**

1. `yajra/laravel-oci8` ประกาศใน `composer.json` ว่าต้องการ extension `ext-oci8`
2. Composer จะตรวจ "platform" — เวอร์ชัน PHP + extensions ที่ติดตั้งบนเครื่อง **ที่รันคำสั่ง composer อยู่**
3. เครื่อง host (เช่น Mac ของนักพัฒนา) **ไม่ได้ติดตั้ง** PHP oci8 extension → Composer จะ error ว่า
   `requires ext-oci8 it is missing` และไม่ยอมติดตั้ง package
4. `--ignore-platform-req=ext-oci8` = สั่งให้ Composer **ข้ามการตรวจเช็คตัวนั้นตัวเดียว** เฉพาะบน host

**ระบบยังจำเป็นต้องใช้ oci8 จริง** แต่อยู่ที่ **รันไทม์ภายใน Docker container**:

- Dockerfile ของ backend ติดตั้ง Oracle Instant Client + `oci8` extension ไว้ใน image แล้ว
  (`pecl install oci8-3.2.1`)
- พอ container เริ่มทำงาน Laravel จะโหลด `ext-oci8` จาก container ได้จริง
- `--ignore-platform-req` ไม่ได้แปลว่า "ไม่ใช้ oci8" — แปลว่า "ข้ามการตรวจบน host
  เพราะ extension มีอยู่ใน container แทน"

จะเห็นว่าถ้ารัน composer **ภายใน container ที่มี oci8 อยู่แล้ว** ก็ไม่ต้องใช้ flag นี้:

```bash
docker compose exec backend composer install
```

แต่โปรเจกต์นี้เลือกจัดการ composer บน host เพราะโหมด local ใช้ bind-mount
(`./backend:/var/www`) เพื่อให้แก้ dependencies ได้ไวโดยไม่ต้อง build image ใหม่ทุกครั้ง
ส่วนในโหมด production `vendor/` จะถูก `COPY` เข้า image ไปพร้อมกับโค้ด
และ oci8 ก็ถูกติดตั้งใน image แล้วตอน build

### 2. คัดลอกไฟล์ env ตัวอย่าง

```bash
cp .env.example .env
cp backend/.env.example backend/.env
```

> `COMPOSE_PROFILES=local` ใน `.env` เป็นค่า default อยู่แล้ว

### 3. Build และรันทุก service (local)

```bash
docker compose up -d --build
```

ครั้งแรกจะใช้เวลานานพอสมควร เพราะต้องดาวน์โหลด Oracle Instant Client
และ `npm install` ทั้งหมดภายใน build

### 4. รอจน Oracle DB พร้อม (healthcheck) แล้ว backend container จะรัน

migration และ seeder จะรันอัตโนมัติผ่าน `entrypoint.sh` **เฉพาะเมื่อ** ตั้ง `RUN_MIGRATIONS=true` / `RUN_SEEDER=true` (local dev ตั้งไว้แล้ว ดู `.env`) — production ควรเป็น `false` แล้วรัน manual ตามรอบ release

### 5. เปิดใช้งาน (local):

- Frontend (Vite dev server + HMR): <http://localhost:5173>
- Backend API: <http://localhost:8000/api/dashboard/summary>
- Oracle DB: `localhost:1521` (service name `FREEPDB1`, user/pass ตามที่ตั้งใน `.env`)

> **ข้อดีของโหมด local**: `./backend` และ `./frontend` ถูก **bind-mount** เข้า container
> ดังนั้นแก้โค้ดบน host แล้วเห็นผลทันที (backend reload ผ่าน PHP-FPM, frontend ผ่าน Vite HMR)
> โดยไม่ต้อง rebuild image ใหม่ (ยกเว้นแก้ Dockerfile เอง)

---

## วิธีรัน (Docker) — Production

### 1. เตรียมไฟล์ env สำหรับ production

```bash
cp .env.production .env          # ใช้ .env.production เป็น template
cp backend/.env.production backend/.env.production.local
```

แก้ไขค่าที่สำคัญใน `backend/.env` (หรือ `backend/.env.production.local` แล้วลิงก์):

| ตัวแปร                     | คำอธิบาย                                                                                                                                            |
| -------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| `APP_URL`                  | URL จริงของ backend (เช่น `https://api.example.com`)                                                                                                |
| `APP_KEY`                  | ควรใส่ค่า `APP_KEY` ที่คงที่ (รัน `php artisan key:generate --show` ใน container แล้วนำมาใส่) เพื่อให้ key ไม่เปลี่ยนทุกครั้งที่ recreate container |
| `DB_PASSWORD`              | รหัสผ่าน Oracle DB จริง                                                                                                                             |
| `SANCTUM_STATEFUL_DOMAINS` | domain ของ frontend (เช่น `example.com`)                                                                                                            |
| `FRONTEND_URL`             | URL จริงของ frontend (เช่น `https://example.com`)                                                                                                   |

และแก้ไขใน `.env` (ซึ่งมาจาก `.env.production`):

| ตัวแปร              | คำอธิบาย                                                                                |
| ------------------- | --------------------------------------------------------------------------------------- |
| `COMPOSE_PROFILES`  | ต้องเป็น `production` (ตั้งไว้แล้วใน `.env.production`)                                 |
| `VITE_API_BASE_URL` | URL ของ backend API ที่ frontend จะเรียก (เช่น `https://api.example.com/api`)           |
| `VITE_API_ENABLED`  | เปิด/ปิดการเรียก API ทั้งหมดของ frontend (`true` เปิด, `false` ปิด; ค่าเริ่มต้น `true`) |
| `ORACLE_ARCH`       | `arm64` หรือ `amd64` ตาม CPU ของ production server                                      |

### 2. Build และรัน (production) — คำสั่งเดียวกันกับ local

```bash
docker compose up -d --build
```

### 3. เปิดใช้งาน (production):

- Frontend: <http://localhost:80> (หรือ port ตาม `WEB_PORT` ใน `.env`)
- Backend API: <http://localhost:80/api/dashboard/summary> (ผ่าน frontend nginx proxy)

> **หมายเหตุ production**:
>
> - `docker-compose.yml` ในโหมด production **ไม่ bind-mount** source code —
>   โค้ดถูก `COPY` เข้า image ตอน build ดังนั้นการแก้ไขโค้ดต้อง **rebuild image ใหม่**
> - Frontend ถูก build เป็น static assets แล้ว serve ด้วย nginx (ไม่ใช่ Vite dev server)
> - `VITE_API_BASE_URL` และ `VITE_API_ENABLED` ถูกฝัง (bake) เข้า image ตอน build — ถ้าเปลี่ยนต้อง rebuild
> - ข้อมูล Oracle ถูกเก็บใน named volume `oracle-data` (ไม่หายเมื่อ container ถูกลบ)

---

## 3.x การ deploy แบบ versioned images (แนะนำสำหรับ production)

> ปกติ `docker compose up -d --build` จะติดตั้งเฉพาะ `:latest` ซึ่ง rollback ไม่ได้
> ส่วนนี้เพิ่มการ tag เวอร์ชันให้ image เพื่อให้ deploy reproducible และ rollback ได้จริง

### ตัวแปรใหม่ (ใน `.env.production.example`)

| ตัวแปร           | คำอธิบาย                                                                                                               |
| ---------------- | ---------------------------------------------------------------------------------------------------------------------- |
| `IMAGE_TAG`      | เวอร์ชันของ image เช่น `v1.2.3`, `sha-abc1234` (default: `latest`)                                                     |
| `IMAGE_REGISTRY` | prefix ของ registry เช่น `ghcr.io/keradach/` หรือ `10.2.34.10:5000/` (ต้องลงท้าย `/`, ปล่อยว่าง = ใช้ image ในเครื่อง) |

### ขั้นตอน deploy เมื่อมีการปรับโค้ด

ตัวอย่างนี้ใช้ `v1.0.4` เป็น release ใหม่ (เปลี่ยนเป็น version ถัดไปจริงทุกครั้ง)
และใช้ GitHub Actions สร้าง/push image ไป GHCR อัตโนมัติเมื่อ push Git tag `v*`.
อย่านำคำสั่ง shell เช่น `IMAGE_TAG=v1.0.4 ./scripts/deploy-prod.sh` ไปใส่ใน `.env`;
ใน `.env` ให้ใส่เฉพาะค่า เช่น `IMAGE_TAG=v1.0.4`.

#### 1. ฝั่ง developer — commit โค้ดและสร้าง release tag

รันจาก root ของ repository หลังแก้โค้ดและทดสอบแล้ว:

```bash
git status
git add <ไฟล์ที่แก้>
git commit -m "Describe the production change"
git push origin <branch>

# สร้าง tag บน commit ที่ push แล้ว เพื่อเริ่ม workflow build/push images
git tag -a v1.0.4 -m "Release v1.0.4"
git push origin v1.0.4
```

รอให้ GitHub Actions **Build & Push Images** ของ tag `v1.0.4` สำเร็จ ก่อน deploy.
อย่าใช้ tag ที่เคยปล่อยไปแล้วซ้ำ; ใช้ version ใหม่สำหรับทุก release.

#### 2. ฝั่ง production — pull image และเริ่มบริการ

ใน `.env` บน production server ให้ตั้ง registry prefix (ต้องมี `/` ท้ายค่า):

```env
IMAGE_REGISTRY=ghcr.io/keradach/
```

หาก GHCR package เป็น private ให้ login ก่อน (ใช้ GitHub token/PAT ที่มี `read:packages`;
Docker จะถาม token แบบ interactive อย่าใส่ token ลงใน command หรือ commit ลง `.env`):

```bash
docker login ghcr.io -u <github-username>
```

จาก root ของ repository บน server ให้ดึงไฟล์ deploy/config ล่าสุดตาม branch ที่ใช้งาน
(ถ้า checkout ไว้แล้วและ branch ตั้ง upstream ถูกต้อง):

```bash
git pull --ff-only
```

จากนั้น deploy tag ที่ build สำเร็จ:

```bash
IMAGE_TAG=v1.0.4 ./scripts/deploy-prod.sh
```

สคริปต์นี้รัน `docker compose pull` แล้วตามด้วย `docker compose up -d --no-build --remove-orphans`.
ตรวจสอบสถานะและ log:

```bash
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs --tail=100 backend backend-nginx frontend scheduler
```

หาก release นี้มี database migration ให้รันตามแผน release หลัง deploy:

```bash
docker compose -f docker-compose.prod.yml exec backend php artisan migrate --force
```

#### 3. Rollback

หากต้องย้อนกลับ ให้เลือก tag ก่อนหน้าที่ยังมี image อยู่ใน GHCR แล้วสั่งบน production server:

```bash
IMAGE_TAG=v1.0.3 ./scripts/deploy-prod.sh
```

> **ข้อควรรู้**:
>
> - `IMAGE_TAG` ที่ใส่หน้าคำสั่งเป็น environment variable สำหรับคำสั่งนั้นเท่านั้น
>   ไม่ใช่ข้อความที่ต้องนำไปรวมต่อท้ายค่า `IMAGE_TAG` ใน `.env`.
> - `docker compose pull` อย่างเดียวเพียงดาวน์โหลด image; ต้องรัน `up -d --no-build`
>   ต่อเพื่อให้ container ใช้ image ใหม่ สคริปต์ `deploy-prod.sh` ทำทั้งสองขั้นตอนให้แล้ว.
> - `docker-compose.prod.yml` ใช้ `${IMAGE_TAG:-latest}` เมื่อไม่ได้ระบุ tag.
> - `scheduler` ใช้ image backend tag เดียวกันเสมอ.
> - `VITE_API_BASE_URL` และ `VITE_API_ENABLED` ถูกฝังใน frontend image ตอน build;
>   หากเปลี่ยนค่าเหล่านี้ ต้อง build release image ใหม่.

### 3.x.1 GitHub Actions และการตั้งค่า GHCR

Workflow `Build & Push Images` เริ่มทำงานเมื่อ push Git tag ที่ขึ้นต้น `v*`
ตามขั้นตอนด้านบน และ push images ไปที่ `ghcr.io/<owner>/pdm-backend:<tag>` กับ
`ghcr.io/<owner>/pdm-frontend:<tag>` (พร้อม `:latest` ตามค่าเริ่มต้น).

**เปิดใช้ครั้งแรก** ตรวจสอบว่า workflow มีสิทธิ์เขียน packages:
ไปที่ **Settings → Actions → General → Workflow permissions** แล้วเลือก
_Read and write permissions_ หากจำเป็น. หาก GHCR packages เป็น private ให้ production server
login ด้วย GitHub token/PAT ที่มี `read:packages` ก่อน deploy.

นอกจากนี้ยังสั่ง build แบบ manual ได้จาก **Actions → "Build & Push Images" → Run workflow**.
กรอก `image_tag` เพื่อกำหนด tag ที่จะ push; หากเว้นว่าง workflow จะใช้ `sha-<commit>`.
นำ tag ที่ workflow แสดงว่า build สำเร็จไป deploy ด้วยคำสั่งเดียวกับขั้นตอนด้านบน.

- `image_tag` ว่าง = tag เป็น `sha-<commit>` โดยอัตโนมัติ
- `update_latest` ตั้งเป็น `false` ได้ ถ้าไม่อยากให้แตะ tag `:latest`

> **งานภายในของ workflow** (อยู่ใน `.github/workflows/build-images.yml`):
>
> - อ่านชื่อ image / build args จาก `docker-compose.prod.yml` โดยตรง (ผ่าน `docker buildx bake`)
>   จึงไม่ต้อง duplicate ค่าต่าง ๆ — แก้ compose ที่เดียวจบ
> - สร้าง `.env` ว่างบน runner เมื่อไม่มีไฟล์ เพื่อให้ Bake โหลด `env_file` ใน Compose ได้
> - อัปเดต `:latest` ใน GHCR ด้วย `docker buildx imagetools` เพราะ image ที่ Bake push ไม่ได้อยู่ใน local Docker daemon
> - บังคับ `ORACLE_ARCH=x86_64` ตรงกันกับ GitHub-hosted runner
> - ใช้ GHA cache (type=gha) ลดเวลา rebuild ใน tag ถัดไป
> - ข้อควรระวัง: build บน runner เป็น amd64 (x86_64) — ถ้าต้อง arm64 image ให้เพิ่ม `platforms` + self-hosted runner

---

## ตัวอย่างการสลับโหมดอย่างรวดเร็วบนเครื่องเดียวกัน

```bash
# --- โหมด LOCAL ---
echo "COMPOSE_PROFILES=local" >> .env
docker compose up -d --build       # Vite dev + bind mount

# --- สลับเป็น PRODUCTION (บนเครื่องเดียวกัน) ---
docker compose down                # หยุด services เดิม
export COMPOSE_PROFILES=production
docker compose up -d --build       # built static assets + baked code

# --- กลับเป็น LOCAL ---
docker compose down
export COMPOSE_PROFILES=local
docker compose up -d --build
```

> วิธีนี้เหมาะทดสอบ production behaviour บนเครื่อง dev ได้ โดย
>
> - ใช้ `COMPOSE_PROFILES` environment variable override ค่าใน `.env` ชั่วคราว
> - แต่อย่าลืมว่า `oracle-data` volume ถูกแชร์ร่วมกันทั้งสองโหมด (ข้อมูลจะอยู่ด้วยกัน)

---

## การจัดการ Composer (บน host)

เนื่องจาก Composer ถูกแยกออกจาก Docker แล้ว คุณสามารถจัดการ dependencies ได้โดยตรง:

```bash
cd backend

# ติดตั้ง dependencies ตาม composer.lock
composer install --ignore-platform-req=ext-oci8

# เพิ่ม package ใหม่
composer require some/package --ignore-platform-req=ext-oci8

# อัปเดต dependencies
composer update --ignore-platform-req=ext-oci8

# ตรวจสอบ outdated packages
composer outdated
```

> **หมายเหตุ**:
>
> - ในโหมด **local** `backend/` ถูก bind-mount เข้า container (`./backend:/var/www`)
>   ดังนั้นการแก้ไขโค้ดหรือ composer dependencies บน host จะมีผลทันที
>   โดยไม่ต้อง rebuild image ใหม่ (ยกเว้นแก้ Dockerfile เอง)
> - ในโหมด **production** ต้อง rebuild image ใหม่ทุกครั้งที่แก้โค้ด
>   (เพราะโค้ดถูก baked เข้า image แล้ว)

---

## วิธีตรวจสอบว่า compose config ถูกต้อง

```bash
# ดู services ที่จะรันในโหมด local
COMPOSE_PROFILES=local docker compose config --services
# -> oracle-db backend backend-nginx frontend-node

# ดู services ที่จะรันในโหมด production
COMPOSE_PROFILES=production docker compose config --services
# -> oracle-db backend-prod backend-nginx-prod frontend
```

---

## รันแบบ Local (ไม่ใช้ Docker) เพื่อพัฒนา

```bash
# Backend
cd backend
composer install --ignore-platform-req=ext-oci8
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve   # http://localhost:8000

# Frontend
cd frontend
npm install
npm run dev          # http://localhost:5173
```

---

## หมายเหตุเรื่อง Oracle Database

- ใช้ไดรเวอร์ [`yajra/laravel-oci8`](https://github.com/yajra/laravel-oci8) ซึ่งครอบ PHP OCI8 extension
- ต้องมี **Oracle Instant Client** ในอิมเมจ (Dockerfile ดาวน์โหลดให้อัตโนมัติ
  จาก `download.oracle.com` — หากเครือข่ายของคุณบล็อกโดเมนนี้ ให้ดาวน์โหลด
  Instant Client RPM ด้วยตนเองแล้วปรับ Dockerfile ให้ `COPY` จากไฟล์ในเครื่องแทน)
- หากต้องต่อกับ Oracle instance ที่มีอยู่แล้ว (ไม่ใช้ container `oracle-db`)
  ให้ลบ service `oracle-db` ออกจาก `docker-compose.yml`
  และแก้ `DB_HOST`, `DB_PORT`, `DB_SERVICE_NAME` ใน `backend/.env`
  ให้ชี้ไปที่ฐานข้อมูลจริง

---

## ข้อแตกต่างระหว่างโหมด local กับ production (สรุป)

| ประเด็น            | Local                             | Production                                    |
| ------------------ | --------------------------------- | --------------------------------------------- |
| Frontend           | Vite dev server (HMR)             | Build static → nginx serve                    |
| Backend source     | Bind-mount (`./backend:/var/www`) | Baked ใน image (`COPY`)                       |
| แก้โค้ดแล้วเห็นผล  | ทันที (HMR / PHP reload)          | ต้อง rebuild image                            |
| `COMPOSE_PROFILES` | `local`                           | `production`                                  |
| Port web           | `5173` (Vite)                     | `80` (nginx)                                  |
| `APP_ENV`          | `local`                           | `production`                                  |
| `APP_DEBUG`        | `true`                            | `false`                                       |
| env_file backend   | `./backend/.env`                  | `./backend/.env` (override APP_ENV/APP_DEBUG) |

---

## สิ่งที่ควรทำต่อ (ยังไม่รวมในสโคปนี้)

- ระบบยืนยันตัวตน/สิทธิ์ผู้ใช้ (Laravel Sanctum + login สำหรับเจ้าหน้าที่)
- แผนที่แบบ choropleth จริง (แนะนำ Leaflet + GeoJSON เขตจังหวัดไทย) แทน marker
  แบบง่ายที่ใช้ demo ใน `RiskMapCard.vue`
- Endpoint สำหรับข้อมูลพยากรณ์อากาศ/เรดาร์ฝนจริง (ปัจจุบัน `WeatherCard.vue`
  รับค่าผ่าน props แบบ static)
- Import ข้อมูลจริงจากหน่วยงาน (GISTDA, กรมอุตุฯ, กรมชลประทาน) แทน `DashboardSeeder`
- CI/CD, automated tests, queue worker (สำหรับแจ้งเตือน SMS/LINE เป็นต้น)
