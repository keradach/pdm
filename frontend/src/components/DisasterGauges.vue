<script setup>
import { computed } from 'vue'
import GaugeChart from './GaugeChart.vue'

const props = defineProps({
  gauges: { type: Array, default: () => [] },
})

const ICONS = { drought: '☀', flood: '🌊', pest: '🐛', cold: '❄' }
const COLORS = {
  normal: '#3fa66a',
  watch: '#f0b429',
  high: '#e8770f',
  critical: '#d1352b',
}
const LABELS = { normal: 'ปกติ', watch: 'เฝ้าระวัง', high: 'เสี่ยงสูง', critical: 'วิกฤต' }

const items = computed(() =>
  props.gauges.map((g) => ({
    ...g,
    icon: ICONS[g.code] || '⚠',
    color: COLORS[g.severity_label] || '#2e7d4f',
    statusLabel: LABELS[g.severity_label] || '',
  }))
)
</script>

<template>
  <div class="card overflow-hidden">
    <div class="card-header bg-pdm-green-deep">
      สถานการณ์ภัยพิบัติด้านพืช
    </div>
    <div class="grid grid-cols-2 gap-[10px] p-4">
      <div v-for="g in items" :key="g.code" class="text-center p-[10px] rounded-[10px] bg-[#fafcfa]">
        <div class="text-xl mb-0.5">{{ g.icon }}</div>
        <GaugeChart :percent="g.severity_percent" :color="g.color" :label="g.statusLabel" />
        <div class="text-xs text-muted mt-0.5">{{ g.name_th }}</div>
      </div>
    </div>
  </div>
</template>
