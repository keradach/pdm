<script setup>
import { computed } from 'vue'

const props = defineProps({
  percent: { type: Number, required: true },
  color: { type: String, default: '#2e7d4f' },
  label: { type: String, default: '' },
})

const RADIUS = 42
const CIRC = Math.PI * RADIUS // half circle length

const dashOffset = computed(() => CIRC - (Math.min(Math.max(props.percent, 0), 100) / 100) * CIRC)
</script>

<template>
  <div class="gauge">
    <svg viewBox="0 0 100 58" class="gauge-svg">
      <path
        d="M 8 50 A 42 42 0 0 1 92 50"
        fill="none"
        stroke="#eef0ee"
        stroke-width="9"
        stroke-linecap="round"
      />
      <path
        d="M 8 50 A 42 42 0 0 1 92 50"
        fill="none"
        :stroke="color"
        stroke-width="9"
        stroke-linecap="round"
        :stroke-dasharray="CIRC"
        :stroke-dashoffset="dashOffset"
      />
    </svg>
    <div class="value" :style="{ color }">{{ percent }}%</div>
    <div v-if="label" class="label" :style="{ color }">{{ label }}</div>
  </div>
</template>

<style scoped>
.gauge { text-align: center; }
.gauge-svg { width: 100%; max-width: 130px; }
.value { font-size: 20px; font-weight: 800; font-family: var(--font-display); margin-top: -6px; }
.label { font-size: 12px; margin-top: 2px; }
</style>
