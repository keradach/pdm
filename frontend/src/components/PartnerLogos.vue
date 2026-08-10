<script setup>
const partners = [
  'GISTDA (ข้อมูลดาวเทียม)',
  'กรมทรัพยากรน้ำ (ข้อมูลสถานการณ์น้ำ)',
  'กรมอุตุนิยมวิทยา (ข้อมูลสภาพอากาศ)',
  'กรมชลประทาน (ข้อมูลแหล่งน้ำ)',
]
const features = [
  { icon: '☁', label: 'เชื่อมโยงข้อมูลแบบบูรณาการ (Data Integration)' },
  { icon: '⟳', label: 'อัปเดตข้อมูลแบบเรียลไทม์ (Real-time Update)' },
  { icon: '📱', label: 'เข้าถึงได้ทุกที่ ทุกเวลา (Anytime Anywhere)' },
]
</script>

<template>
  <div class="footer-row">
    <div class="card partners">
      <div class="section-title">บูรณาการข้อมูลจากหน่วยงาน</div>
      <div class="partner-list">
        <span v-for="p in partners" :key="p" class="partner">{{ p }}</span>
      </div>
      <div class="feature-list">
        <span v-for="f in features" :key="f.label" class="feature">{{ f.icon }} {{ f.label }}</span>
      </div>
    </div>

    <div class="card contact">
      <div class="section-title light">DOAE Contact Center</div>
      <div class="contact-row">📞 0 2579 3926</div>
      <div class="contact-row">✉ disaster_pdm@doae.go.th</div>
    </div>
  </div>
</template>

<style scoped>
.footer-row {
  display: grid;
  grid-template-columns: 3fr 1fr;
  gap: 12px;
  padding: 0 22px 22px;
}
.partners, .contact { padding: 14px 18px; }
.section-title { font-size: 13px; font-weight: 700; color: var(--pdm-green-deep); margin-bottom: 8px; }
.section-title.light { color: #fff; }
.partner-list { display: flex; flex-wrap: wrap; gap: 8px 18px; font-size: 12px; color: var(--text-muted); margin-bottom: 10px; }
.feature-list { display: flex; flex-wrap: wrap; gap: 8px 18px; font-size: 12px; }
.contact {
  background: linear-gradient(135deg, var(--pdm-green-deep), var(--pdm-green));
  color: #fff;
}
.contact-row { font-size: 13px; margin-top: 6px; }
</style>
