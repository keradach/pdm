<script setup>
import { computed } from 'vue'

const props = defineProps({
  summary: { type: Object, default: null },
})

const cards = computed(() => {
  const s = props.summary || {}
  return [
    { icon: '📍', label: 'จังหวัดเสี่ยงภัย', value: s.provinces_at_risk ?? '-', unit: 'จังหวัด', color: '#d1352b' },
    { icon: '🏘', label: 'อำเภอได้รับผลกระทบ', value: s.amphoe_affected ?? '-', unit: 'อำเภอ', color: '#e8770f' },
    { icon: '🌾', label: 'เกษตรกรได้รับผลกระทบ', value: (s.farmers_affected ?? 0).toLocaleString(), unit: 'ราย', color: '#2e7d4f' },
    { icon: '📐', label: 'พื้นที่เสียหาย', value: s.damaged_area_million_rai ?? '-', unit: 'ล้านไร่', color: '#2f6fb0' },
    { icon: '💰', label: 'จ่ายเงินช่วยเหลือแล้ว', value: (s.assistance_progress_percent ?? 0) + '%', unit: 'ความคืบหน้า', color: '#6f4fa3' },
    { icon: '👷', label: 'เจ้าหน้าที่ลงพื้นที่', value: (s.staff_deployed ?? 0).toLocaleString(), unit: 'ทีม', color: '#1f5c3f' },
    { icon: '🕗', label: 'รายงานล่าสุด', value: s.report_time ?? '-', unit: 'น.', color: '#6b7b72' },
  ]
})
</script>

<template>
  <div class="grid grid-cols-[repeat(auto-fit,minmax(160px,1fr))] gap-3 p-[16px_22px_0] max-[480px]:grid-cols-2 max-[480px]:gap-2 max-[480px]:p-[12px_14px_0]">
    <div v-for="c in cards" :key="c.label" class="card flex items-center gap-3 p-[14px_16px] max-[480px]:gap-2 max-[480px]:p-[10px] max-[480px]:min-w-0">
      <div class="w-[42px] h-[42px] rounded-[10px] flex items-center justify-center text-xl shrink-0 max-[480px]:w-[34px] max-[480px]:h-[34px] max-[480px]:text-base" :style="{ background: c.color + '1a', color: c.color }">{{ c.icon }}</div>
      <div class="flex flex-col min-w-0">
        <div class="text-xl font-extrabold font-display max-[480px]:text-[17px] max-[480px]:whitespace-nowrap">{{ c.value }}<span class="text-[11px] font-medium text-muted ml-1 max-[480px]:text-[10px]">{{ c.unit }}</span></div>
        <div class="text-xs text-muted mt-0.5 max-[480px]:text-[10px]">{{ c.label }}</div>
      </div>
    </div>
  </div>
</template>
