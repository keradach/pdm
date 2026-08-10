<script setup>
import { computed } from 'vue'
import GaugeChart from './GaugeChart.vue'

const props = defineProps({
  gauges: { type: Array, default: () => [] },
})

const ICONS = { drought: '☀', flood: '🌊', pest: '🐛', cold: '❄' }
const COLORS = {
  normal: 'var(--pdm-green-bright)',
  watch: 'var(--pdm-yellow)',
  high: 'var(--pdm-orange)',
  critical: 'var(--pdm-red)',
}
const LABELS = { normal: 'ปกติ', watch: 'เฝ้าระวัง', high: 'เสี่ยงสูง', critical: 'วิกฤต' }

const items = computed(() =>
  props.gauges.map((g) => ({
    ...g,
    icon: ICONS[g.code] || '⚠',
    color: COLORS[g.severity_label] || 'var(--pdm-green)',
    statusLabel: LABELS[g.severity_label] || '',
  }))
)
</script>

<template>
  <div class="card gauges-card">
    <div class="card-header" style="background: var(--pdm-green-deep)">
      สถานการณ์ภัยพิบัติด้านพืช
    </div>
    <div class="gauges-grid">
      <div v-for="g in items" :key="g.code" class="gauge-cell">
        <div class="icon">{{ g.icon }}</div>
        <GaugeChart :percent="g.severity_percent" :color="g.color" :label="g.statusLabel" />
        <div class="name">{{ g.name_th }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.gauges-card { overflow: hidden; }
.gauges-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  padding: 16px;
}
.gauge-cell { text-align: center; padding: 10px; border-radius: var(--radius-md); background: #fafcfa; }
.icon { font-size: 20px; margin-bottom: 2px; }
.name { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
</style>
