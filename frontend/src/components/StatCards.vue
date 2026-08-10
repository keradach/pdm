<script setup>
import { computed } from 'vue'

const props = defineProps({
  summary: { type: Object, default: null },
})

const cards = computed(() => {
  const s = props.summary || {}
  return [
    { icon: '📍', label: 'จังหวัดเสี่ยงภัย', value: s.provinces_at_risk ?? '-', unit: 'จังหวัด', color: 'var(--pdm-red)' },
    { icon: '🏘', label: 'อำเภอได้รับผลกระทบ', value: s.amphoe_affected ?? '-', unit: 'อำเภอ', color: 'var(--pdm-orange)' },
    { icon: '🌾', label: 'เกษตรกรได้รับผลกระทบ', value: (s.farmers_affected ?? 0).toLocaleString(), unit: 'ราย', color: 'var(--pdm-green)' },
    { icon: '📐', label: 'พื้นที่เสียหาย', value: s.damaged_area_million_rai ?? '-', unit: 'ล้านไร่', color: 'var(--pdm-blue)' },
    { icon: '💰', label: 'จ่ายเงินช่วยเหลือแล้ว', value: (s.assistance_progress_percent ?? 0) + '%', unit: 'ความคืบหน้า', color: 'var(--pdm-purple)' },
    { icon: '👷', label: 'เจ้าหน้าที่ลงพื้นที่', value: (s.staff_deployed ?? 0).toLocaleString(), unit: 'ทีม', color: 'var(--pdm-green-deep)' },
    { icon: '🕗', label: 'รายงานล่าสุด', value: s.report_time ?? '-', unit: 'น.', color: 'var(--text-muted)' },
  ]
})
</script>

<template>
  <div class="stat-row">
    <div v-for="c in cards" :key="c.label" class="stat-card card">
      <div class="icon" :style="{ background: c.color + '1a', color: c.color }">{{ c.icon }}</div>
      <div class="body">
        <div class="value">{{ c.value }}<span class="unit">{{ c.unit }}</span></div>
        <div class="label">{{ c.label }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.stat-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
  padding: 16px 22px 0;
}
.stat-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
}
.icon {
  width: 42px; height: 42px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.value { font-size: 20px; font-weight: 800; font-family: var(--font-display); }
.unit { font-size: 11px; font-weight: 500; color: var(--text-muted); margin-left: 4px; }
.label { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
</style>
