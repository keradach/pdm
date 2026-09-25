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
  <div class="grid grid-cols-[3fr_1fr] gap-3 px-[22px] pb-[22px] max-[900px]:grid-cols-1 max-[900px]:px-[14px] max-[900px]:pb-[14px] max-[640px]:px-[10px] max-[640px]:pb-3">
    <div class="card p-[14px_18px]">
      <div class="text-[13px] font-bold text-pdm-green-deep mb-2">บูรณาการข้อมูลจากหน่วยงาน</div>
      <div class="flex flex-wrap gap-x-[18px] gap-y-2 text-xs text-muted mb-[10px]">
        <span v-for="p in partners" :key="p" class="partner">{{ p }}</span>
      </div>
      <div class="flex flex-wrap gap-x-[18px] gap-y-2 text-xs">
        <span v-for="f in features" :key="f.label" class="feature">{{ f.icon }} {{ f.label }}</span>
      </div>
    </div>

    <div class="card contact p-[14px_18px] bg-gradient-to-br from-pdm-green-deep to-pdm-green text-white">
      <div class="text-[13px] font-bold text-white mb-2">DOAE Contact Center</div>
      <div class="contact-list flex flex-wrap items-center gap-x-3 gap-y-1.5 mt-0.5 min-w-0">
        <div class="contact-row inline-flex items-center text-[13px] min-w-0 break-words whitespace-nowrap shrink-0">📞 0 2579 3926</div>
        <div class="contact-row inline-flex items-center text-[13px] min-w-0 break-words whitespace-nowrap shrink-0">✉ pdmrc@doae.go.th</div>
      </div>
    </div>
  </div>
</template>
