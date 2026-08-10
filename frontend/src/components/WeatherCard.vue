<script setup>
defineProps({
  rainfallMm: { type: Number, default: 58.6 },
  hotspotProvince: { type: String, default: 'อ.เวียงป่าเป้า จ.เชียงราย' },
  updatedAt: { type: String, default: '08:30 น.' },
})
</script>

<template>
  <div class="card weather-card">
    <div class="row">
      <div>
        <div class="title">☁ สภาพอากาศและปริมาณฝน</div>
        <div class="rain">{{ rainfallMm }} <span>มม.</span></div>
        <div class="caption">ฝนสะสม 24 ชม. (มม.)</div>
        <div class="hotspot">มากที่สุด {{ hotspotProvince }}</div>
      </div>
      <div class="radar" aria-hidden="true">
        <div class="radar-ring r1"></div>
        <div class="radar-ring r2"></div>
        <div class="radar-ring r3"></div>
      </div>
    </div>
    <div class="footer">ข้อมูลล่าสุด {{ updatedAt }}</div>
  </div>
</template>

<style scoped>
.weather-card { padding: 14px 18px; min-width: 260px; }
.row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.title { font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
.rain { font-size: 30px; font-weight: 800; font-family: var(--font-display); color: var(--pdm-blue); }
.rain span { font-size: 13px; font-weight: 500; color: var(--text-muted); }
.caption { font-size: 11px; color: var(--text-muted); }
.hotspot { font-size: 12px; margin-top: 6px; }
.footer { font-size: 11px; color: var(--text-muted); margin-top: 10px; }
.radar {
  position: relative;
  width: 56px; height: 56px;
  flex-shrink: 0;
}
.radar-ring {
  position: absolute; inset: 0;
  border-radius: 50%;
  border: 2px solid var(--pdm-blue);
  opacity: 0.5;
}
.r1 { inset: 16px; background: var(--pdm-blue); opacity: 0.8; }
.r2 { inset: 8px; }
.r3 { inset: 0; opacity: 0.25; }
</style>
