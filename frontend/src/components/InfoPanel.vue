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
.panel {
  /* allow the list below to react to the panel's own width (container query),
     which works correctly at any viewport size (incl. 1080) */
  container-type: inline-size;
  overflow: hidden;
}
.list {
  list-style: none;
  margin: 0;
  padding: 10px 16px 14px;
  display: grid;
  row-gap: 8px;
  column-gap: 14px;
  grid-template-columns: 1fr;
  min-width: 0;
}
.list li { display: flex; align-items: center; gap: 8px; font-size: 12.5px; min-width: 0; }
.list li > span:last-child { min-width: 0; }
.ic { flex-shrink: 0; }

/* Only show two columns when the panel itself is wide enough for the long
   Thai/English labels; otherwise fall back to a single column. */
@container (min-width: 460px) {
  .list { grid-template-columns: 1fr 1fr; }
}
</style>
