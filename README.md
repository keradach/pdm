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
│   ├── docker/entrypoint.sh    # รอ DB พร้อม -> migrate -> seed -> start php-fpm
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

| `COMPOSE_PROFILES` | โหมด | ลักษณะการทำงาน |
| ------------------ | ---- | --------------- |
| `local` (default)  | Development | Vite dev server (hot-reload) + **bind-mount** source code → แก้โค้ดแล้วเห็นผลทันที |
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

migration + seeder อัตโนมัติผ่าน `entrypoint.sh`

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

| ตัวแปร | คำอธิบาย |
| ------ | -------- |
| `APP_URL` | URL จริงของ backend (เช่น `https://api.example.com`) |
| `APP_KEY` | ควรใส่ค่า `APP_KEY` ที่คงที่ (รัน `php artisan key:generate --show` ใน container แล้วนำมาใส่) เพื่อให้ key ไม่เปลี่ยนทุกครั้งที่ recreate container |
| `DB_PASSWORD` | รหัสผ่าน Oracle DB จริง |
| `SANCTUM_STATEFUL_DOMAINS` | domain ของ frontend (เช่น `example.com`) |
| `FRONTEND_URL` | URL จริงของ frontend (เช่น `https://example.com`) |

และแก้ไขใน `.env` (ซึ่งมาจาก `.env.production`):

| ตัวแปร | คำอธิบาย |
| ------ | -------- |
| `COMPOSE_PROFILES` | ต้องเป็น `production` (ตั้งไว้แล้วใน `.env.production`) |
| `VITE_API_BASE_URL` | URL ของ backend API ที่ frontend จะเรียก (เช่น `https://api.example.com/api`) |
| `ORACLE_ARCH` | `arm64` หรือ `amd64` ตาม CPU ของ production server |

### 2. Build และรัน (production) — คำสั่งเดียวกันกับ local

```bash
docker compose up -d --build
```

### 3. เปิดใช้งาน (production):

- Frontend: <http://localhost:80> (หรือ port ตาม `WEB_PORT` ใน `.env`)
- Backend API: <http://localhost:80/api/dashboard/summary> (ผ่าน frontend nginx proxy)

> **หมายเหตุ production**:
> - `docker-compose.yml` ในโหมด production **ไม่ bind-mount** source code —
>   โค้ดถูก `COPY` เข้า image ตอน build ดังนั้นการแก้ไขโค้ดต้อง **rebuild image ใหม่**
> - Frontend ถูก build เป็น static assets แล้ว serve ด้วย nginx (ไม่ใช่ Vite dev server)
> - `VITE_API_BASE_URL` ถูกฝัง (bake) เข้า image ตอน build — ถ้าเปลี่ยนต้อง rebuild
> - ข้อมูล Oracle ถูกเก็บใน named volume `oracle-data` (ไม่หายเมื่อ container ถูกลบ)

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

| ประเด็น | Local | Production |
| ------- | ----- | ---------- |
| Frontend | Vite dev server (HMR) | Build static → nginx serve |
| Backend source | Bind-mount (`./backend:/var/www`) | Baked ใน image (`COPY`) |
| แก้โค้ดแล้วเห็นผล | ทันที (HMR / PHP reload) | ต้อง rebuild image |
| `COMPOSE_PROFILES` | `local` | `production` |
| Port web | `5173` (Vite) | `80` (nginx) |
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| env_file backend | `./backend/.env` | `./backend/.env` (override APP_ENV/APP_DEBUG) |

---

## สิ่งที่ควรทำต่อ (ยังไม่รวมในสโคปนี้)

- ระบบยืนยันตัวตน/สิทธิ์ผู้ใช้ (Laravel Sanctum + login สำหรับเจ้าหน้าที่)
- แผนที่แบบ choropleth จริง (แนะนำ Leaflet + GeoJSON เขตจังหวัดไทย) แทน marker
  แบบง่ายที่ใช้ demo ใน `RiskMapCard.vue`
- Endpoint สำหรับข้อมูลพยากรณ์อากาศ/เรดาร์ฝนจริง (ปัจจุบัน `WeatherCard.vue`
  รับค่าผ่าน props แบบ static)
- Import ข้อมูลจริงจากหน่วยงาน (GISTDA, กรมอุตุฯ, กรมชลประทาน) แทน `DashboardSeeder`
- CI/CD, automated tests, queue worker (สำหรับแจ้งเตือน SMS/LINE เป็นต้น)