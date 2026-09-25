<script setup>
defineProps({
  rainfallMm: { type: Number, default: 58.6 },
  hotspotProvince: { type: String, default: 'อ.เวียงป่าเป้า จ.เชียงราย' },
  updatedAt: { type: String, default: '08:30 น.' },
})
</script>

<template>
  <div class="card p-[14px_18px] min-w-[260px]">
    <div class="flex justify-between items-start gap-3">
      <div>
        <div class="text-[13px] font-semibold text-muted mb-1">☁ สภาพอากาศและปริมาณฝน</div>
        <div class="text-3xl font-extrabold font-display text-pdm-blue">{{ rainfallMm }} <span>มม.</span></div>
        <div class="text-[11px] text-muted">ฝนสะสม 24 ชม. (มม.)</div>
        <div class="text-xs mt-1.5">มากที่สุด {{ hotspotProvince }}</div>
      </div>
      <div class="relative w-[56px] h-[56px] shrink-0" aria-hidden="true">
        <div class="absolute inset-[16px] rounded-full bg-pdm-blue opacity-80"></div>
        <div class="absolute inset-2 rounded-full border-2 border-pdm-blue opacity-50"></div>
        <div class="absolute inset-0 rounded-full border-2 border-pdm-blue opacity-25"></div>
      </div>
    </div>
    <div class="text-[11px] text-muted mt-[10px]">ข้อมูลล่าสุด {{ updatedAt }}</div>
  </div>
</template>
