<script setup>
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS, LineElement, PointElement, LinearScale,
  CategoryScale, Tooltip, Filler,
} from 'chart.js'

ChartJS.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler)

const props = defineProps({
  trend: { type: Array, default: () => [] }, // [{report_month, total_rai}]
})

const monthLabels = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.']

const chartData = computed(() => {
  let cumulative = 0
  const values = props.trend.map((r) => {
    cumulative += Number(r.total_rai) / 1_000_000
    return Number(cumulative.toFixed(2))
  })
  const labels = props.trend.map((r) => {
    const d = new Date(r.report_month)
    return `${monthLabels[d.getMonth()]}${String(d.getFullYear() + 543).slice(-2)}`
  })
  return {
    labels,
    datasets: [{
      label: 'พื้นที่เสียหายสะสม (ล้านไร่)',
      data: values,
      borderColor: '#1f5c3f',
      backgroundColor: 'rgba(31,92,63,0.12)',
      tension: 0.35,
      fill: true,
      pointRadius: 3,
      pointBackgroundColor: '#1f5c3f',
    }],
  }
})

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, ticks: { font: { size: 10 } } },
    x: { ticks: { font: { size: 10 } } },
  },
}
</script>

<template>
  <div class="card chart-card">
    <div class="chart-title">แนวโน้มพื้นที่เสียหาย (ล้านไร่)</div>
    <div class="chart-wrap">
      <Line :data="chartData" :options="options" />
    </div>
  </div>
</template>

<style scoped>
.chart-card { padding: 14px; }
.chart-title { font-size: 13px; font-weight: 600; margin-bottom: 8px; }
.chart-wrap { height: 200px; }
</style>
