<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS, BarElement, LinearScale, CategoryScale, Tooltip,
} from 'chart.js'

ChartJS.register(BarElement, LinearScale, CategoryScale, Tooltip)

const props = defineProps({
  provinces: { type: Array, default: () => [] }, // [{name_th, damaged_area_rai}]
})

const chartData = computed(() => ({
  labels: props.provinces.map((p) => p.name_th),
  datasets: [{
    label: 'พื้นที่เสียหาย (พันไร่)',
    data: props.provinces.map((p) => Number((Number(p.damaged_area_rai) / 1000).toFixed(2))),
    backgroundColor: '#e8524a',
    borderRadius: 4,
    barThickness: 14,
  }],
}))

const options = {
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: { title: { display: true, text: 'พันไร่', font: { size: 10 } }, ticks: { font: { size: 10 } } },
    y: { ticks: { font: { size: 11 } } },
  },
}
</script>

<template>
  <div class="card chart-card">
    <div class="chart-title">10 จังหวัด พื้นที่เสียหายสูงสุด</div>
    <div class="chart-wrap">
      <Bar :data="chartData" :options="options" />
    </div>
  </div>
</template>

<style scoped>
.chart-card { padding: 14px; }
.chart-title { font-size: 13px; font-weight: 600; margin-bottom: 8px; }
.chart-wrap { height: 260px; }
</style>
