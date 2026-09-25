<script setup>
defineProps({
  title: { type: String, required: true },
  headerIcon: { type: String, default: '📌' },
  color: { type: String, default: '#2f6fb0' },
  items: { type: Array, default: () => [] }, // [{icon, label}]
})
</script>

<template>
  <div class="card overflow-hidden">
    <div class="card-header" :style="{ background: color }">
      <span>{{ headerIcon }}</span> {{ title }}
    </div>
    <ul class="list-none m-0 p-[10px_16px_14px] grid gap-y-2 gap-x-3.5 grid-cols-1 min-w-0">
      <li v-for="item in items" :key="item.label">
        <span class="shrink-0">{{ item.icon }}</span>
        <span>{{ item.label }}</span>
      </li>
    </ul>
  </div>
</template>
