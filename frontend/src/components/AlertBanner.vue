<script setup>
defineProps({
  alert: { type: Object, default: null },
})
</script>

<template>
  <div v-if="alert" class="banner">
    <div class="col main">
      <span class="icon">⚠</span>
      <div>
        <div class="title">{{ alert.title }}</div>
        <div class="msg">{{ alert.message }}</div>
      </div>
    </div>
    <div class="col watch">
      <div class="label">จังหวัดเฝ้าระวัง</div>
      <div class="chips">
        <span v-for="p in alert.watch_provinces" :key="p" class="chip">{{ p }}</span>
      </div>
    </div>
    <div class="col level">
      <div class="label">ระดับความเสี่ยง</div>
      <div class="badge">{{ (alert.risk_level || '').toUpperCase() }}</div>
    </div>
  </div>
</template>

<style scoped>
.banner {
  display: flex;
  align-items: center;
  gap: 24px;
  background: linear-gradient(90deg, #c22a20, var(--pdm-red));
  color: #fff;
  padding: 14px 22px;
  flex-wrap: wrap;
}
.col { display: flex; align-items: center; gap: 10px; }
.main { flex: 2; min-width: 260px; }
.icon { font-size: 26px; }
.title { font-weight: 700; font-size: 15px; }
.msg { font-size: 13px; opacity: 0.9; }
.watch { flex: 2; min-width: 260px; flex-direction: column; align-items: flex-start; gap: 4px; }
.label { font-size: 11px; opacity: 0.85; }
.chips { display: flex; gap: 6px; flex-wrap: wrap; }
.chip {
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.4);
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 12px;
}
.level { flex-direction: column; align-items: flex-end; gap: 4px; }
.badge {
  background: #fff;
  color: var(--pdm-red);
  font-weight: 800;
  padding: 4px 14px;
  border-radius: 6px;
  font-size: 15px;
}
</style>
