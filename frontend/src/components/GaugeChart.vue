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
  <div class="text-center">
    <svg viewBox="0 0 100 58" class="w-full max-w-[130px]">
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
    <div class="text-xl font-extrabold font-display -mt-1.5" :style="{ color }">{{ percent }}%</div>
    <div v-if="label" class="text-xs mt-0.5" :style="{ color }">{{ label }}</div>
  </div>
</template>
