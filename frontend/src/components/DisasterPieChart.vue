<script setup>
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  breakdown: { type: Array, default: () => [] }, // [{label, total_rai}]
})

const PALETTE = ['#e8770f', '#2f6fb0', '#6f4fa3', '#2e7d4f', '#d1352b', '#f2c80f', '#b0b0b0', '#f2c80f', '#b0b0b0', '#f2c80f', '#b0b0b0']

const chartData = computed(() => ({
  labels: props.breakdown.map((b) => b.label),
  datasets: [{
    data: props.breakdown.map((b) => Number(b.total_rai)),
    backgroundColor: props.breakdown.map((_, i) => PALETTE[i % PALETTE.length]),
    borderWidth: 2,
    borderColor: '#fff',
  }],
}))

const options = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '62%',
  plugins: {
    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
  },
}
</script>

<template>
  <div class="card p-[14px]">
    <div class="text-[13px] font-semibold mb-2">ข้อความแจ้งเตือนจากแหล่งอ้างอิงได้ (พี่อุ้ม)</div>
    <div class="h-[210px]">
      <!-- <Doughnut :data="chartData" :options="options" /> -->
    </div>
  </div>
</template>
