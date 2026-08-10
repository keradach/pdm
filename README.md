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
├── docker-compose.yml          # orchestrate ทั้ง 3 service (oracle-db, backend, frontend)
├── .env.example                 # ค่า env สำหรับ docker-compose (port, db credential)
├── docker/nginx/default.conf    # nginx reverse-proxy หน้า Laravel (PHP-FPM)
│
├── backend/                     # Laravel 13 API (skeleton + โค้ดโปรเจกต์)
│   ├── Dockerfile               # build PHP-FPM + oci8 (Oracle) — ไม่มี composer steps
│   ├── .env.example              # ค่า env ของ Laravel (DB_CONNECTION=oracle, ...)
│   ├── docker/entrypoint.sh     # รอ DB พร้อม -> migrate -> seed -> start php-fpm
│   ├── composer.json / composer.lock  # จัดการ dependencies บน host
│   ├── vendor/                  # dependencies ที่ติดตั้งบน host (bind-mount เข้า container)
│   ├── app/                     # โค้ด Laravel (controllers, models, ...)
│   ├── config/                  # config ของ Laravel
│   ├── database/                # migrations + seeders
│   ├── routes/                  # routes
│   └── ...
│
└── frontend/                    # Vue 3 SPA
    ├── Dockerfile                # npm build -> serve ด้วย nginx
    ├── nginx.conf
    ├── src/
    │   ├── components/           # HeaderBar, AlertBanner, StatCards, RiskMapCard,
    │   │                         # DisasterGauges, TrendLineChart, DisasterPieChart,
    │   │                         # TopProvincesBarChart, InfoPanel, PartnerLogos, ...
    │   ├── views/Dashboard.vue   # ประกอบทุก component เป็นหน้าแดชบอร์ดตาม mockup
    │   ├── stores/dashboard.js   # Pinia store เรียก API ทั้งหมด
    │   └── services/api.js       # axios client
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

## วิธีรัน (Docker)

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

### 3. Build และรันทุก service

```bash
docker compose up -d --build
```

ครั้งแรกจะใช้เวลานานพอสมควร เพราะต้องดาวน์โหลด Oracle Instant Client
และ `npm install` ทั้งหมดภายใน build

### 4. รอจน Oracle DB พร้อม (healthcheck) แล้ว backend container จะรัน

migration + seeder อัตโนมัติผ่าน `entrypoint.sh`

### 5. เปิดใช้งาน:

- Frontend: <http://localhost:8080>
- Backend API: <http://localhost:8000/api/dashboard/summary>
- Oracle DB: `localhost:1521` (service name `FREEPDB1`, user/pass ตามที่ตั้งใน `.env`)

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

> **หมายเหตุ**: `backend/` ถูก bind-mount เข้า container (`./backend:/var/www`)
> ดังนั้นการแก้ไขโค้ดหรือ composer dependencies บน host จะมีผลทันที
> โดยไม่ต้อง rebuild image ใหม่ (ยกเว้นแก้ Dockerfile เอง)

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
  ให้ลบ service `oracle-db` ออกจาก `docker-compose.yml` และแก้ `DB_HOST`,
  `DB_PORT`, `DB_SERVICE_NAME` ใน `backend/.env` ให้ชี้ไปที่ฐานข้อมูลจริง

## สิ่งที่ควรทำต่อ (ยังไม่รวมในสโคปนี้)

- ระบบยืนยันตัวตน/สิทธิ์ผู้ใช้ (Laravel Sanctum + login สำหรับเจ้าหน้าที่)
- แผนที่แบบ choropleth จริง (แนะนำ Leaflet + GeoJSON เขตจังหวัดไทย) แทน marker
  แบบง่ายที่ใช้ demo ใน `RiskMapCard.vue`
- Endpoint สำหรับข้อมูลพยากรณ์อากาศ/เรดาร์ฝนจริง (ปัจจุบัน `WeatherCard.vue`
  รับค่าผ่าน props แบบ static)
- Import ข้อมูลจริงจากหน่วยงาน (GISTDA, กรมอุตุฯ, กรมชลประทาน) แทน `DashboardSeeder`
- CI/CD, automated tests, queue worker (สำหรับแจ้งเตือน SMS/LINE เป็นต้น)
