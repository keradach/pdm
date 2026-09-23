<script setup>
import HeaderBar from '@/components/HeaderBar.vue'
import SidebarNav from '@/components/SidebarNav.vue'

const theme = {
  icon: '🧑‍💼',
  title: 'ระบบสนับสนุนเจ้าหน้าที่',
  subtitle: 'Operation Support',
  color: 'var(--pdm-purple)',
}

// Mockup data (ตัวอย่างข้อมูลจำลอง)
const stock = [
  { item: 'ข้าวพันธุ์ กข.7', unit: 'กระสอบ', inStock: 320, target: 500, rate: 64 },
  { item: 'ข้าวโพดเลี้ยงสัตว์', unit: 'กระสอบ', inStock: 180, target: 300, rate: 60 },
  { item: 'ถั่วเขียว', unit: 'กระสอบ', inStock: 95, target: 150, rate: 63 },
  { item: 'ปุ๋ยชีวภาพ', unit: 'ถัง', inStock: 210, target: 250, rate: 84 },
]

const bioproducts = [
  { item: 'ไตรโคเดอร์มา', unit: 'ขวด', inStock: 1400, target: 2000, rate: 70 },
  { item: 'บิวเวอร์เรีย', unit: 'ขวด', inStock: 860, target: 1500, rate: 57 },
  { item: 'เมธาไรเซียม', unit: 'ขวด', inStock: 520, target: 1000, rate: 52 },
  { item: 'บีที (Bacillus)', unit: 'ขวด', inStock: 1100, target: 1600, rate: 69 },
]

const operations = [
  { activity: 'ลงพื้นที่สำรวจความเสียหาย', officer: 'นายสมชาย แก้วใส', date: '21 ก.ย. 2569', district: 'อ.ปักธงชัย', status: 'เสร็จสิ้น', statusClass: 'ok' },
  { activity: 'ติดตามการช่วยเหลือ', officer: 'น.ส.วิภาดา เรืองศรี', date: '22 ก.ย. 2569', district: 'อ.ศีขรภูมิ', status: 'ดำเนินการ', statusClass: 'watch' },
  { activity: 'แจกจ่ายปัจจัยการผลิต', officer: 'นายอนุชา ทองคำ', date: '23 ก.ย. 2569', district: 'อ.ประโคนชัย', status: 'รออนุมัติ', statusClass: 'critical' },
]

const allocations = [
  { item: 'ข้าวพันธุ์ กข.7', from: 'คลังกลาง จ.นครราชสีมา', to: 'จุดจ่าย อ.ศีขรภูมิ', qty: 120, status: 'ขนส่งแล้ว', statusClass: 'ok' },
  { item: 'ปุ๋ยชีวภาพ', from: 'คลังกลาง จ.นครราชสีมา', to: 'จุดจ่าย อ.ประโคนชัย', qty: 60, status: 'รอขนส่ง', statusClass: 'watch' },
  { item: 'ไตรโคเดอร์มา', from: 'คลัง จ.บุรีรัมย์', to: 'จุดจ่าย อ.ปักธงชัย', qty: 400, status: 'เตรียมเอกสาร', statusClass: 'watch' },
]

// helper for progress bar color
function barClass(rate) {
  if (rate >= 70) return 'bg-ok'
  if (rate >= 55) return 'bg-watch'
  return 'bg-critical'
}
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
            <div class="card-title" :style="{ color: theme.color }">🚚 ปัจจัยพันธุ์พืชสำรอง</div>
            <!-- <table class="mock-table">
              <thead>
                <tr>
                  <th>รายการ</th>
                  <th>คงคลัง</th>
                  <th>เป้าหมาย</th>
                  <th>ความพร้อม</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in stock" :key="s.item">
                  <td>{{ s.item }}</td>
                  <td>{{ s.inStock }} {{ s.unit }}</td>
                  <td>{{ s.target }} {{ s.unit }}</td>
                  <td>
                    <div class="progress"><span class="bar" :class="barClass(s.rate)"
                        :style="{ width: s.rate + '%' }"></span><em>{{ s.rate }}%</em></div>
                  </td>
                </tr>
              </tbody>
            </table> -->
          </section>

          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">🚚 สต็อกชีวภัณฑ์</div>
            <!-- <table class="mock-table">
              <thead>
                <tr>
                  <th>รายการ</th>
                  <th>คงคลัง</th>
                  <th>เป้าหมาย</th>
                  <th>ความพร้อม</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="b in bioproducts" :key="b.item">
                  <td>{{ b.item }}</td>
                  <td>{{ b.inStock.toLocaleString() }} {{ b.unit }}</td>
                  <td>{{ b.target.toLocaleString() }} {{ b.unit }}</td>
                  <td>
                    <div class="progress"><span class="bar" :class="barClass(b.rate)"
                        :style="{ width: b.rate + '%' }"></span><em>{{ b.rate }}%</em></div>
                  </td>
                </tr>
              </tbody>
            </table> -->
          </section>
        </div>

        <div class="grid-2">
          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">🧰 การรายงานเหตุเบื้องต้นจาก อกม.</div>
            <!-- <table class="mock-table">
              <thead>
                <tr>
                  <th>รายการ</th>
                  <th>จาก</th>
                  <th>ไปยัง</th>
                  <th>จำนวน</th>
                  <th>สถานะ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in allocations" :key="p.item + p.to">
                  <td>{{ p.item }}</td>
                  <td>{{ p.from }}</td>
                  <td>{{ p.to }}</td>
                  <td>{{ p.qty }}</td>
                  <td><span class="tag" :class="'bg-' + p.statusClass">{{ p.status }}</span></td>
                </tr>
              </tbody>
            </table> -->
          </section>

          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">📋 AI chatbot</div>
            <!-- <table class="mock-table">
              <thead>
                <tr>
                  <th>กิจกรรม</th>
                  <th>เจ้าหน้าที่</th>
                  <th>วันที่</th>
                  <th>อำเภอ</th>
                  <th>สถานะ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="o in operations" :key="o.activity + o.officer">
                  <td>{{ o.activity }}</td>
                  <td>{{ o.officer }}</td>
                  <td>{{ o.date }}</td>
                  <td>{{ o.district }}</td>
                  <td><span class="tag" :class="'bg-' + o.statusClass">{{ o.status }}</span></td>
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

.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
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
}

.mock-table th {
  background: #f6f8f7;
  font-weight: 600;
  font-size: 12px;
  color: var(--text-muted);
}

.progress {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 110px;
}

.progress .bar {
  height: 8px;
  border-radius: 999px;
}

.progress em {
  font-style: normal;
  font-size: 11.5px;
  color: var(--text-muted);
}

.bg-ok {
  background: var(--pdm-green-bright);
}

.bg-watch {
  background: var(--pdm-yellow);
}

.bg-critical {
  background: var(--pdm-red);
}

.tag {
  color: #fff;
  border-radius: 999px;
  padding: 2px 10px;
  font-size: 11.5px;
  white-space: nowrap;
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