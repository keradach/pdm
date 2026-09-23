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
      <div class="contact-list">
        <div class="contact-row">📞 0 2579 3926</div>
        <div class="contact-row">✉ pdmrc@doae.go.th</div>
      </div>
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

.footer-row>* {
  min-width: 0;
}

.partners,
.contact {
  padding: 14px 18px;
}

.section-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--pdm-green-deep);
  margin-bottom: 8px;
}

.section-title.light {
  color: #fff;
}

.partner-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 18px;
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 10px;
}

.feature-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 18px;
  font-size: 12px;
}

.contact {
  background: linear-gradient(135deg, var(--pdm-green-deep), var(--pdm-green));
  color: #fff;
}

.contact-row {
  flex-shrink: 0;
  white-space: nowrap;
}

.contact-list {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 12px;
  margin-top: 2px;
  min-width: 0;
}

.contact-list .contact-row {
  display: inline-flex;
  align-items: center;
  font-size: 13px;
  min-width: 0;
  overflow-wrap: anywhere;
}

@media (max-width: 900px) {
  .footer-row {
    grid-template-columns: 1fr;
    padding: 0 14px 14px;
  }

  .contact-list .contact-row {
    font-size: 13px;
  }
}

@media (max-width: 640px) {
  .footer-row {
    padding: 0 10px 12px;
  }

  .contact-list .contact-row {
    font-size: 12.5px;
  }
}
</style>
