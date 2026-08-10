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
├── docker-compose.yml          # LOCAL: orchestrate ทั้ง 3 service (oracle-db, backend, frontend-node)
├── docker-compose.prod.yml     # PRODUCTION: build static assets + nginx (ไม่ bind-mount source)
├── .env                        # ค่า env สำหรับ docker-compose local (port, db credential)
├── .env.production             # ค่า env สำหรับ docker-compose.prod.yml
├── docker/nginx/default.conf   # nginx reverse-proxy หน้า Laravel (PHP-FPM)
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

### 2. คัดลอกไฟล์ env ตัวอย่าง

```bash
cp .env.example .env
cp backend/.env.example backend/.env
```

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

---

## วิธีรัน (Docker) — Production

### 1. เตรียมไฟล์ env สำหรับ production

```bash
cp .env.production .env.production.local   # แก้ไขค่าตามจริงของ production server
cp backend/.env.production backend/.env.production.local
```

แก้ไขค่าที่สำคัญใน `backend/.env.production.local`:

| ตัวแปร | คำอธิบาย |
| ------ | -------- |
| `APP_URL` | URL จริงของ backend (เช่น `https://api.example.com`) |
| `APP_KEY` | ควรใส่ค่า `APP_KEY` ที่คงที่ (รัน `php artisan key:generate --show` ใน container แล้วนำมาใส่) เพื่อให้ key ไม่เปลี่ยนทุกครั้งที่ recreate container |
| `DB_PASSWORD` | รหัสผ่าน Oracle DB จริง |
| `SANCTUM_STATEFUL_DOMAINS` | domain ของ frontend (เช่น `example.com`) |
| `FRONTEND_URL` | URL จริงของ frontend (เช่น `https://example.com`) |

และแก้ไขใน `.env.production.local`:

| ตัวแปร | คำอธิบาย |
| ------ | -------- |
| `VITE_API_BASE_URL` | URL ของ backend API ที่ frontend จะเรียก (เช่น `https://api.example.com/api`) |
| `ORACLE_ARCH` | `arm64` หรือ `amd64` ตาม CPU ของ production server |

### 2. Build และรัน (production)

```bash
docker compose --env-file .env.production.local -f docker-compose.prod.yml up -d --build
```

### 3. เปิดใช้งาน (production):

- Frontend: <http://localhost:8080> (หรือ port ตาม `WEB_PORT` ใน `.env.production.local`)
- Backend API: <http://localhost:8000/api/dashboard/summary> (หรือ port ตาม `API_PORT`)

> **หมายเหตุ production**:
> - `docker-compose.prod.yml` **ไม่ bind-mount** source code ของ backend — โค้ดถูก
>   `COPY` เข้า image ตอน build ดังนั้นการแก้ไขโค้ดต้อง rebuild image ใหม่
> - Frontend ถูก build เป็น static assets แล้ว serve ด้วย nginx (ไม่ใช่ Vite dev server)
> - `VITE_API_BASE_URL` ถูกฝัง (bake) เข้า image ตอน build — ถ้าเปลี่ยนต้อง rebuild
> - ข้อมูล Oracle ถูกเก็บใน named volume `oracle-data` (ไม่หายเมื่อ container ถูกลบ)

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

> **หมายเหตุ**: ในโหมด local `backend/` ถูก bind-mount เข้า container (`./backend:/var/www`)
> ดังนั้นการแก้ไขโค้ดหรือ composer dependencies บน host จะมีผลทันที
> โดยไม่ต้อง rebuild image ใหม่ (ยกเว้นแก้ Dockerfile เอง)
> แต่ในโหมด production ต้อง rebuild image ใหม่ทุกครั้งที่แก้โค้ด

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

## หมายเหตุเรื่อง Oracle Database

- ใช้ไดรเวอร์ [`yajra/laravel-oci8`](https://github.com/yajra/laravel-oci8) ซึ่งครอบ PHP OCI8 extension
- ต้องมี **Oracle Instant Client** ในอิมเมจ (Dockerfile ดาวน์โหลดให้อัตโนมัติ
  จาก `download.oracle.com` — หากเครือข่ายของคุณบล็อกโดเมนนี้ ให้ดาวน์โหลด
  Instant Client RPM ด้วยตนเองแล้วปรับ Dockerfile ให้ `COPY` จากไฟล์ในเครื่องแทน)
- หากต้องต่อกับ Oracle instance ที่มีอยู่แล้ว (ไม่ใช้ container `oracle-db`)
  ให้ลบ service `oracle-db` ออกจาก `docker-compose.yml` / `docker-compose.prod.yml`
  และแก้ `DB_HOST`, `DB_PORT`, `DB_SERVICE_NAME` ใน `backend/.env` (local)
  หรือ `backend/.env.production` (prod) ให้ชี้ไปที่ฐานข้อมูลจริง

## สิ่งที่ควรทำต่อ (ยังไม่รวมในสโคปนี้)

- ระบบยืนยันตัวตน/สิทธิ์ผู้ใช้ (Laravel Sanctum + login สำหรับเจ้าหน้าที่)
- แผนที่แบบ choropleth จริง (แนะนำ Leaflet + GeoJSON เขตจังหวัดไทย) แทน marker
  แบบง่ายที่ใช้ demo ใน `RiskMapCard.vue`
- Endpoint สำหรับข้อมูลพยากรณ์อากาศ/เรดาร์ฝนจริง (ปัจจุบัน `WeatherCard.vue`
  รับค่าผ่าน props แบบ static)
- Import ข้อมูลจริงจากหน่วยงาน (GISTDA, กรมอุตุฯ, กรมชลประทาน) แทน `DashboardSeeder`
- CI/CD, automated tests, queue worker (สำหรับแจ้งเตือน SMS/LINE เป็นต้น)