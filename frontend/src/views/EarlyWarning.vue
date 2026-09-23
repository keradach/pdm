<script setup>
import HeaderBar from '@/components/HeaderBar.vue'
import SidebarNav from '@/components/SidebarNav.vue'

const theme = {
  icon: '🔔',
  title: 'ระบบแจ้งเตือนภัยและชี้เป้าหมาย',
  subtitle: 'Early Warning & Risk Maps',
  color: 'var(--pdm-blue)',
}

// Mockup data (ตัวอย่างข้อมูลจำลอง)
const provinceRisks = [
  { province: 'นครราชสีมา', district: 'อ.ปักธงชัย', level: 'critical', levelLabel: 'วิกฤต', rainfall: '178 มม.', impact: 'ข้าวนาปรัง 850 ไร่', note: 'น้ำท่วมขังพื้นที่ลุ่ม' },
  { province: 'สุรินทร์', district: 'อ.ศีขรภูมิ', level: 'high', levelLabel: 'สูง', rainfall: '142 มม.', impact: 'มันสำปะหลัง 320 ไร่', note: 'อุปสรรคการระบายน้ำ' },
  { province: 'บุรีรัมย์', district: 'อ.ประโคนชัย', level: 'high', levelLabel: 'สูง', rainfall: '135 มม.', impact: 'ข้าวโพด 210 ไร่', note: 'เฝ้าระวังต่อเนื่อง' },
  { province: 'อุบลราชธานี', district: 'อ.วารินชำราบ', level: 'watch', levelLabel: 'เฝ้าระวัง', rainfall: '96 มม.', impact: 'พืชไร่ 120 ไร่', note: 'ติดตามระดับน้ำ' },
  { province: 'ยโสธร', district: 'อ.เมือง', level: 'normal', levelLabel: 'ปกติ', rainfall: '58 มม.', impact: '-', note: 'สถานการณ์โดยรวมดี' },
]

const forecasts = [
  { day: 'วันนี้', date: '23 ก.ย. 69', rain: '80%', temp: '29°C', risk: 'high', riskLabel: 'สูง' },
  { day: 'พรุ่งนี้', date: '24 ก.ย. 69', rain: '60%', temp: '30°C', risk: 'watch', riskLabel: 'เฝ้าระวัง' },
  { day: '3 วันถัดไป', date: '25–27 ก.ย. 69', rain: '45%', temp: '31°C', risk: 'watch', riskLabel: 'เฝ้าระวัง' },
  { day: '7 วันถัดไป', date: '28 ก.ย.–4 ต.ค. 69', rain: '30%', temp: '32°C', risk: 'normal', riskLabel: 'ปกติ' },
  { day: '15 วันถัดไป', date: '5–8 ต.ค. 69', rain: '20%', temp: '33°C', risk: 'normal', riskLabel: 'ปกติ' },
]

const waterBalance = [
  { basin: 'ลุ่มน้ำมูล', inflow: '48 ลบ.ม./วินาที', storage: '72%', status: 'เพียงพอ', statusClass: 'ok' },
  { basin: 'ลุ่มน้ำชี', inflow: '36 ลบ.ม./วินาที', storage: '58%', status: 'เฝ้าระวัง', statusClass: 'watch' },
  { basin: 'ลุ่มน้ำลำตะคอง', inflow: '22 ลบ.ม./วินาที', storage: '41%', status: 'เฝ้าระวัง', statusClass: 'watch' },
  { basin: 'ลุ่มน้ำมูลตอนล่าง', inflow: '61 ลบ.ม./วินาที', storage: '85%', status: 'เพียงพอ', statusClass: 'ok' },
]

const satelliteSources = [
  { src: 'Sentinel-1 (SAR)', freq: 'ทุก 6 วัน', usage: 'ตรวจจับพื้นที่น้ำท่วมขังความละเอียด 10 ม.' },
  { src: 'Sentinel-2 (MSI)', freq: 'ทุก 5 วัน', usage: 'ประเมินความเสียหายพื้นที่เพาะปลูก' },
  { src: 'Himawari-9', freq: 'ทุก 10 นาที', usage: 'ตรวจระบบเมฆฝนแบบเรียลไทม์' },
  { src: 'เรดาร์ตรวจอากาศ (TMD)', freq: 'ทุก 5 นาที', usage: 'ประเมินฝน 2 ชม. ล่วงหน้า' },
]

const dri = [
  { province: 'สุรินทร์', index: 4.2, level: 'สูง', levelClass: 'high' },
  { province: 'ศรีสะเกษ', index: 3.8, level: 'สูง', levelClass: 'high' },
  { province: 'บุรีรัมย์', index: 3.1, level: 'ปานกลาง', levelClass: 'watch' },
  { province: 'นครราชสีมา', index: 2.5, level: 'ปานกลาง', levelClass: 'watch' },
  { province: 'อุบลราชธานี', index: 1.4, level: 'ต่ำ', levelClass: 'normal' },
]

const villageBooks = [
  { village: 'ต.หนองบัว', district: 'อ.ปักธงชัย', families: 320, waterDays: 4, note: 'ใช้น้ำครบ 14 วัน' },
  { village: 'ต.คูเมือง', district: 'อ.ศีขรภูมิ', families: 245, waterDays: 7, note: 'ล็อกเกวียน บ่อสาธารณะ' },
  { village: 'ต.ไพรขลา', district: 'อ.เฉลิมพระเกียรติ', families: 180, waterDays: 2, note: 'รอผันน้ำจากเขื่อน' },
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
        <div class="grid-2">
          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">🗺 โซนเสี่ยงภัยรายจังหวัด (Risk Map)</div>
            <!-- <table class="mock-table">
            <thead>
              <tr><th>จังหวัด</th><th>อำเภอ</th><th>ระดับความเสี่ยง</th><th>ปริมาณฝน</th><th>พื้นที่กระทบ</th><th>หมายเหตุ</th></tr>
            </thead>
            <tbody>
              <tr v-for="r in provinceRisks" :key="r.province + r.district">
                <td>{{ r.province }}</td>
                <td>{{ r.district }}</td>
                <td><span class="tag" :class="'bg-risk-' + r.level">{{ r.levelLabel }}</span></td>
                <td>{{ r.rainfall }}</td>
                <td>{{ r.impact }}</td>
                <td>{{ r.note }}</td>
              </tr>
            </tbody>
          </table> -->
          </section>

          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">📈 คาดการณ์พื้นที่เสี่ยงอุทกภัยเดือนมกราคม 2570 จาก
              สสน.</div>
            <!-- <table class="mock-table">
            <thead><tr><th>ช่วงเวลา</th><th>วันที่</th><th>โอกาสฝนตก</th><th>อุณหภูมิ</th><th>ระดับความเสี่ยง</th></tr></thead>
            <tbody>
              <tr v-for="f in forecasts" :key="f.day">
                <td>{{ f.day }}</td>
                <td>{{ f.date }}</td>
                <td>{{ f.rain }}</td>
                <td>{{ f.temp }}</td>
                <td><span class="tag" :class="'bg-risk-' + f.risk">{{ f.riskLabel }}</span></td>
              </tr>
            </tbody>
          </table> -->
          </section>
        </div>
        <div class="grid-2">
          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">💧 ทุ่งรับน้ำ 13 ทุ่ง</div>
            <!-- <table class="mock-table">
              <thead>
                <tr>
                  <th>ลุ่มน้ำ</th>
                  <th>น้ำเข้า</th>
                  <th>ความจุ</th>
                  <th>สถานะ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="w in waterBalance" :key="w.basin">
                  <td>{{ w.basin }}</td>
                  <td>{{ w.inflow }}</td>
                  <td>{{ w.storage }}</td>
                  <td><span class="tag" :class="w.statusClass === 'ok' ? 'bg-risk-normal' : 'bg-risk-watch'">{{ w.status
                  }}</span></td>
                </tr>
              </tbody>
            </table> -->
          </section>

          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">🛰 พื้นที่น้ำท่วมซ้ำซาก จากกรมพัฒนาที่ดิน</div>
            <!-- <ul class="plain-list">
              <li v-for="s in satelliteSources" :key="s.src">
                <span class="list-name">{{ s.src }} <em>{{ s.freq }}</em></span>
                <span class="list-desc">{{ s.usage }}</span>
              </li>
            </ul> -->
          </section>
        </div>

        <div class="grid-2">
          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">☀ จุดความร้อน hotspot</div>
            <!-- <table class="mock-table">
              <thead>
                <tr>
                  <th>จังหวัด</th>
                  <th>ดัชนี</th>
                  <th>ระดับ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="d in dri" :key="d.province">
                  <td>{{ d.province }}</td>
                  <td>{{ d.index }}</td>
                  <td><span class="tag" :class="'bg-risk-' + d.levelClass">{{ d.level }}</span></td>
                </tr>
              </tbody>
            </table> -->
          </section>

          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">📓 พื้นที่แล้งซ้ำซาก LDD</div>
            <!-- <table class="mock-table">
              <thead>
                <tr>
                  <th>ตำบล</th>
                  <th>ครัวเรือน</th>
                  <th>วันน้ำเพียงพอ</th>
                  <th>หมายเหตุ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="v in villageBooks" :key="v.village">
                  <td>{{ v.village }}</td>
                  <td>{{ v.families }}</td>
                  <td>{{ v.waterDays }}</td>
                  <td>{{ v.note }}</td>
                </tr>
              </tbody>
            </table> -->
          </section>
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
.page {
  min-height: 100%;
  padding-bottom: 20px;
}

.page-body {
  display: grid;
  grid-template-columns: minmax(180px, 200px) minmax(0, 1fr);
  gap: 16px;
  padding: 16px 22px;
  align-items: start;
}

.col-nav {
  position: sticky;
  top: 16px;
}

.col-content {
  display: flex;
  flex-direction: column;
  gap: 14px;
  min-width: 0;
}

.hero {
  display: flex;
  align-items: center;
  gap: 14px;
  color: #fff;
  border-radius: var(--radius-lg);
  padding: 18px 20px;
  box-shadow: var(--shadow-card);
}

.hero-icon {
  font-size: 30px;
}

.hero h2 {
  font-size: 20px;
}

.hero p {
  margin: 2px 0 0;
  opacity: 0.85;
  font-size: 13px;
}

.block {
  padding-bottom: 6px;
}

.card-title {
  font-weight: 700;
  font-size: 14px;
  padding: 14px 16px 8px;
}

.mock-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.mock-table th,
.mock-table td {
  text-align: left;
  padding: 9px 12px;
  border-bottom: 1px solid var(--border-soft);
  vertical-align: top;
}

.mock-table th {
  background: #f6f8f7;
  font-weight: 600;
  font-size: 12px;
  color: var(--text-muted);
}

.mock-table tr:hover td {
  background: #fafcfb;
}

.tag {
  color: #fff;
  border-radius: 999px;
  padding: 2px 10px;
  font-size: 11.5px;
  white-space: nowrap;
}

.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.plain-list {
  list-style: none;
  margin: 0;
  padding: 4px 16px 14px;
  display: grid;
  gap: 10px;
}

.plain-list li {
  display: flex;
  flex-direction: column;
  gap: 2px;
  font-size: 13px;
}

.list-name {
  font-weight: 600;
}

.list-name em {
  font-style: normal;
  color: var(--text-muted);
  font-weight: 400;
}

.list-desc {
  color: var(--text-muted);
  font-size: 12px;
}

@media (max-width: 1280px) {
  .grid-2 {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 900px) {
  .page-body {
    grid-template-columns: 1fr;
    padding: 12px 14px;
    gap: 12px;
  }

  .col-nav {
    position: static;
  }

  .mock-table {
    display: block;
    overflow-x: auto;
  }
}
</style>