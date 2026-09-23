<script setup>
import HeaderBar from '@/components/HeaderBar.vue'
import SidebarNav from '@/components/SidebarNav.vue'

const theme = {
  icon: '📊',
  title: 'แดชบอร์ดติดตามสถานการณ์และการช่วยเหลือ',
  subtitle: 'Monitoring & Assistance',
  color: 'var(--pdm-green)',
}

// Mockup data (ตัวอย่างข้อมูลจำลอง)
const assistance = [
  { province: 'นครราชสีมา', requested: 1280, serviced: 1120, rate: 88, status: 'ดำเนินการ', statusClass: 'ok' },
  { province: 'สุรินทร์', requested: 954, serviced: 760, rate: 80, status: 'ดำเนินการ', statusClass: 'ok' },
  { province: 'บุรีรัมย์', requested: 810, serviced: 540, rate: 67, status: 'ระหว่างช่วยเหลือ', statusClass: 'watch' },
  { province: 'อุบลราชธานี', requested: 1503, serviced: 620, rate: 41, status: 'รอลงพื้นที่', statusClass: 'watch' },
  { province: 'ศรีสะเกษ', requested: 605, serviced: 190, rate: 31, status: 'เร่งดำเนินการ', statusClass: 'critical' },
]

const progress = [
  { activity: 'แจกจ่ายเมล็ดพันธุ์', target: 1200, done: 965, rate: 80 },
  { activity: 'แจกจ่ายปัจจัยการผลิต', target: 850, done: 620, rate: 73 },
  { activity: 'ฟื้นฟูพื้นที่เพาะปลูก', target: 2000, done: 1450, rate: 73 },
  { activity: 'ช่วยเหลือเงินชดเชย', target: 500, done: 310, rate: 62 },
]

const farmers = { total: 38420, registered: 29105, pending: 9315, areas: 6 }

const villageAssist = [
  { village: 'ต.หนองบัว', district: 'อ.ปักธงชัย', incident: 'อุทกภัย', households: 180, status: 'ช่วยเหลือแล้ว', statusClass: 'ok' },
  { village: 'ต.คูเมือง', district: 'อ.ศีขรภูมิ', incident: 'พายุฝน', households: 240, status: 'รอลงพื้นที่', statusClass: 'watch' },
  { village: 'ต.ไพรขลา', district: 'อ.เฉลิมพระเกียรติ', incident: 'น้ำท่วมฉับพลัน', households: 96, status: 'ขอรับการสนับสนุน', statusClass: 'critical' },
]
</script>
<template>
  <div class="page">
    <HeaderBar />

    <div class="page-body">
      <aside class="col-nav">
        <SidebarNav />
      </aside>

      <main class="col-content">
        <section class="hero" :style="{ background: theme.color }">
          <span class="hero-icon">{{ theme.icon }}</span>
          <div>
            <h2>{{ theme.title }}</h2>
            <p>{{ theme.subtitle }}</p>
          </div>
        </section>

        <div class="stat-row">
          <div class="stat-card">
            <span class="stat-num">{{ farmers.total.toLocaleString() }}</span>
            <span class="stat-label">เกษตรกรทั้งหมด (ทะเบียน)</span>
          </div>
          <div class="stat-card">
            <span class="stat-num">{{ farmers.registered.toLocaleString() }}</span>
            <span class="stat-label">ขึ้นทะเบียนแล้ว</span>
          </div>
          <div class="stat-card">
            <span class="stat-num">{{ farmers.pending.toLocaleString() }}</span>
            <span class="stat-label">รอขึ้นทะเบียน</span>
          </div>
          <div class="stat-card">
            <span class="stat-num">{{ farmers.areas }}</span>
            <span class="stat-label">จังหวัดเป้าหมาย</span>
          </div>
        </div>

        <section class="card block">
          <div class="card-title" :style="{ color: theme.color }">ติดตามการช่วยเหลือรายจังหวัด</div>
          <table class="mock-table">
            <thead><tr><th>จังหวัด</th><th>คำขอ (ราย)</th><th>ช่วยเหลือแล้ว</th><th>ความคืบหน้า</th><th>สถานะ</th></tr></thead>
            <tbody>
              <tr v-for="a in assistance" :key="a.province">
                <td>{{ a.province }}</td>
                <td>{{ a.requested.toLocaleString() }}</td>
                <td>{{ a.serviced.toLocaleString() }}</td>
                <td>
                  <div class="progress"><span class="bar" :class="'bg-' + a.statusClass" :style="{ width: a.rate + '%' }"></span><em>{{ a.rate }}%</em></div>
                </td>
                <td><span class="tag" :class="'bg-' + a.statusClass">{{ a.status }}</span></td>
              </tr>
            </tbody>
          </table>
        </section>

        <section class="card block">
          <div class="card-title" :style="{ color: theme.color }">ความคืบหน้าการช่วยเหลือ (รายกิจกรรม)</div>
          <table class="mock-table">
            <thead><tr><th>กิจกรรม</th><th>เป้าหมาย</th><th>ดำเนินการแล้ว</th><th>ความคืบหน้า</th></tr></thead>
            <tbody>
              <tr v-for="p in progress" :key="p.activity">
                <td>{{ p.activity }}</td>
                <td>{{ p.target.toLocaleString() }}</td>
                <td>{{ p.done.toLocaleString() }}</td>
                <td>
                  <div class="progress"><span class="bar bg-ok" :style="{ width: p.rate + '%' }"></span><em>{{ p.rate }}%</em></div>
                </td>
              </tr>
            </tbody>
          </table>
        </section>

        <section class="card block">
          <div class="card-title" :style="{ color: theme.color }">รายงานเหตุเบื้องต้นและการลงพื้นที่</div>
          <table class="mock-table">
            <thead><tr><th>ตำบล</th><th>อำเภอ</th><th>เหตุการณ์</th><th>ครัวเรือน</th><th>สถานะ</th></tr></thead>
            <tbody>
              <tr v-for="v in villageAssist" :key="v.village">
                <td>{{ v.village }}</td>
                <td>{{ v.district }}</td>
                <td>{{ v.incident }}</td>
                <td>{{ v.households }}</td>
                <td><span class="tag" :class="'bg-' + v.statusClass">{{ v.status }}</span></td>
              </tr>
            </tbody>
          </table>
        </section>
      </main>
    </div>
  </div>
</template>

<style scoped>
.page { min-height: 100%; padding-bottom: 20px; }
.page-body {
  display: grid;
  grid-template-columns: minmax(180px, 200px) minmax(0, 1fr);
  gap: 16px;
  padding: 16px 22px;
  align-items: start;
}
.col-nav { position: sticky; top: 16px; }
.col-content { display: flex; flex-direction: column; gap: 14px; min-width: 0; }

.hero {
  display: flex;
  align-items: center;
  gap: 14px;
  color: #fff;
  border-radius: var(--radius-lg);
  padding: 18px 20px;
  box-shadow: var(--shadow-card);
}
.hero-icon { font-size: 30px; }
.hero h2 { font-size: 20px; }
.hero p { margin: 2px 0 0; opacity: 0.85; font-size: 13px; }

.stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-card);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.stat-num { font-family: var(--font-display); font-size: 26px; font-weight: 700; color: var(--pdm-green); }
.stat-label { font-size: 12px; color: var(--text-muted); }

.block { padding-bottom: 6px; }
.card-title { font-weight: 700; font-size: 14px; padding: 14px 16px 8px; }

.mock-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.mock-table th, .mock-table td {
  text-align: left;
  padding: 9px 12px;
  border-bottom: 1px solid var(--border-soft);
}
.mock-table th { background: #f6f8f7; font-weight: 600; font-size: 12px; color: var(--text-muted); }

.progress { display: flex; align-items: center; gap: 8px; min-width: 120px; }
.progress .bar { height: 8px; border-radius: 999px; }
.progress em { font-style: normal; font-size: 11.5px; color: var(--text-muted); }

.bg-ok { background: var(--pdm-green-bright); }
.bg-watch { background: var(--pdm-yellow); }
.bg-critical { background: var(--pdm-red); }

.tag { color: #fff; border-radius: 999px; padding: 2px 10px; font-size: 11.5px; white-space: nowrap; }

@media (max-width: 900px) {
  .page-body { grid-template-columns: 1fr; padding: 12px 14px; gap: 12px; }
  .col-nav { position: static; }
  .stat-row { grid-template-columns: 1fr 1fr; }
  .mock-table { display: block; overflow-x: auto; }
}
</style>