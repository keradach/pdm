<script setup>
defineProps({
  title: { type: String, required: true },
  headerIcon: { type: String, default: '📌' },
  color: { type: String, default: 'var(--pdm-blue)' },
  items: { type: Array, default: () => [] }, // [{icon, label}]
})
</script>

<template>
  <div class="card panel">
    <div class="card-header" :style="{ background: color }">
      <span>{{ headerIcon }}</span> {{ title }}
    </div>
    <ul class="list">
      <li v-for="item in items" :key="item.label">
        <span class="ic">{{ item.icon }}</span>
        <span>{{ item.label }}</span>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.panel { overflow: hidden; }
.list { list-style: none; margin: 0; padding: 10px 16px 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px 14px; }
.list li { display: flex; align-items: center; gap: 8px; font-size: 12.5px; }
.ic { flex-shrink: 0; }
</style>
