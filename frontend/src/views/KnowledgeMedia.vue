<script setup>
import HeaderBar from '@/components/HeaderBar.vue'
import SidebarNav from '@/components/SidebarNav.vue'

const theme = {
  icon: '📚',
  title: 'คลังความรู้สู้ภัยพิบัติด้านพืช',
  subtitle: 'Knowledge & Media',
  color: 'var(--pdm-orange)',
}

// Mockup data (ตัวอย่างข้อมูลจำลอง)
const flood = [
  { type: 'ข่าวประชาสัมพันธ์', title: 'แถลงสถานการณ์ภัยน้ำท่วม ประจำสัปดาห์', date: '22 ก.ย. 2569', views: '1.2k', icon: '📰' },
  { type: 'ข่าวประชาสัมพันธ์', title: 'มาตรการช่วยเหลือเกษตรกรผู้ประสบภัย', date: '20 ก.ย. 2569', views: '3.4k', icon: '📰' },
  { type: 'ข่าวประชาสัมพันธ์', title: 'เปิดจุดจ่ายน้ำเคลื่อนที่ จ.สุรินทร์', date: '18 ก.ย. 2569', views: '860', icon: '📰' },
]

const drought = [
  { title: 'แถลงสถานการณ์ภัยแล้ง ประจำสัปดาห์', tag: 'Infographic', icon: '🎞' },
  { title: 'VDO : ขั้นตอนการขอรับเงินชดเชย', tag: 'VDO', icon: '📹' },
  { title: 'สื่อความรู้การจัดการน้ำในแปลงนา', tag: 'สื่อความรู้', icon: '📖' },
]

const alternativeCrops = [
  { crop: 'ข้าวโพดเลี้ยงสัตว์', water: 'ต่ำ', period: '90 วัน', income: '12,000 บาท/ไร่', suitable: 'สูง', levelClass: 'ok' },
  { crop: 'ถั่วเขียว', water: 'ต่ำ', period: '70 วัน', income: '8,500 บาท/ไร่', suitable: 'สูง', levelClass: 'ok' },
  { crop: 'งา', water: 'ต่ำมาก', period: '80 วัน', income: '9,000 บาท/ไร่', suitable: 'ปานกลาง', levelClass: 'watch' },
  { crop: 'ข้าวโพดหวาน', water: 'ปานกลาง', period: '75 วัน', income: '15,000 บาท/ไร่', suitable: 'ปานกลาง', levelClass: 'watch' },
]

const waterSaving = [
  { method: 'การปลูกหลังนาเขตชลประทาน', saving: '30–40%', note: 'ใช้น้ำร่วมกับแปลงนาเดิม' },
  { method: 'ระบบน้ำหยดในแปลงผัก', saving: '50%', note: 'ร่วมกับแปลงผักสวนครัว' },
  { method: 'การคลุมดินด้วยฟาง / วัสดุเหลือใช้', saving: '20–25%', note: 'ลดการระเหยของน้ำผิวดิน' },
  { method: 'การปรับพื้นที่แบบแลนด์เลเวลลิ่ง', saving: '35%', note: 'กระจายน้ำให้ทั่วแปลง' },
]

const guides = [
  { title: 'คู่มือการบริหารจัดการน้ำในภาวะขาดแคลน', format: 'PDF • 32 หน้า' },
  { title: 'เอกสารวิชาการการใช้น้ำอย่างประหยัด', format: 'PDF • 18 หน้า' },
  { title: 'คู่มือการกักเก็บน้ำในพื้นที่เกษตร', format: 'PDF • 24 หน้า' },
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
            <div class="card-title" :style="{ color: theme.color }">📰 คลังความรู้สู้ภัยน้ำท่วม</div>
            <!-- <ul class="media-list">
              <li v-for="m in flood" :key="m.title">
                <span class="media-icon">{{ m.icon }}</span>
                <span class="media-body">
                  <strong>{{ m.title }}</strong>
                  <small>{{ m.type }} · {{ m.date }} · {{ m.views }} ครั้ง</small>
                </span>
              </li>
            </ul> -->
          </section>

          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">🎞 คลังความรู้สู้ภัยแล้ง</div>
            <!-- <ul class="media-list">
              <li v-for="g in drought" :key="g.title">
                <span class="media-icon">{{ g.icon }}</span>
                <span class="media-body">
                  <strong>{{ g.title }}</strong>
                  <small>{{ g.tag }}</small>
                </span>
              </li>
            </ul> -->
          </section>
        </div>

        <div class="grid-2">
          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">🌱 เมนูพืชทางเลือก</div>
            <!-- <table class="mock-table">
            <thead>
              <tr>
                <th>พืช</th>
                <th>การใช้น้ำ</th>
                <th>ระยะเวลา</th>
                <th>รายได้โดยประมาณ</th>
                <th>ความเหมาะสม</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in alternativeCrops" :key="c.crop">
                <td>{{ c.crop }}</td>
                <td>{{ c.water }}</td>
                <td>{{ c.period }}</td>
                <td>{{ c.income }}</td>
                <td><span class="tag" :class="'bg-' + c.levelClass">{{ c.suitable }}</span></td>
              </tr>
            </tbody>
          </table> -->
          </section>
          <!-- 
        <div class="grid-2">
          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">💧 ทางเลือกการปลูกพืชใช้น้ำน้อย</div>
            <table class="mock-table">
              <thead>
                <tr>
                  <th>วิธีการ</th>
                  <th>ประหยัดน้ำ</th>
                  <th>รายละเอียด</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="w in waterSaving" :key="w.method">
                  <td>{{ w.method }}</td>
                  <td>{{ w.saving }}</td>
                  <td>{{ w.note }}</td>
                </tr>
              </tbody>
            </table>
          </section>

          <section class="card block">
            <div class="card-title" :style="{ color: theme.color }">📕 คำแนะนำวิชาการ / คู่มือ</div>
            <ul class="media-list">
              <li v-for="d in guides" :key="d.title">
                <span class="media-icon">📑</span>
                <span class="media-body">
                  <strong>{{ d.title }}</strong>
                  <small>{{ d.format }}</small>
                </span>
              </li>
            </ul>
          </section> 
    </div> -->
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

.tag {
  color: #fff;
  border-radius: 999px;
  padding: 2px 10px;
  font-size: 11.5px;
  white-space: nowrap;
}

.bg-ok {
  background: var(--pdm-green-bright);
}

.bg-watch {
  background: var(--pdm-yellow);
}

.media-list {
  list-style: none;
  margin: 0;
  padding: 4px 16px 14px;
  display: grid;
  gap: 10px;
}

.media-list li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.media-icon {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 17px;
  background: #f4f6f5;
  border-radius: 8px;
}

.media-body {
  display: flex;
  flex-direction: column;
  font-size: 13px;
}

.media-body strong {
  line-height: 1.35;
}

.media-body small {
  color: var(--text-muted);
  font-size: 11.5px;
  margin-top: 2px;
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