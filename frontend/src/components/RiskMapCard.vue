<script setup>
import { computed } from 'vue'

const props = defineProps({
  provinces: { type: Array, default: () => [] },
  selected: { type: Object, default: null },
})
const emit = defineEmits(['select'])

// Rough bounding box of Thailand, used only to place demo markers.
// Swap this component for Leaflet + a real Thailand GeoJSON layer
// (province boundaries) for a production-grade choropleth map.
const BOUNDS = { latMin: 5.6, latMax: 20.5, lngMin: 97.3, lngMax: 105.7 }

function positionOf(p) {
  const x = ((p.lng - BOUNDS.lngMin) / (BOUNDS.lngMax - BOUNDS.lngMin)) * 100
  const y = 100 - ((p.lat - BOUNDS.latMin) / (BOUNDS.latMax - BOUNDS.latMin)) * 100
  return { left: `${x}%`, top: `${y}%` }
}

const legend = [
  { key: 'normal', label: 'ปกติ' },
  { key: 'watch', label: 'เฝ้าระวัง' },
  { key: 'high', label: 'เสี่ยงสูง' },
  { key: 'critical', label: 'เกิดภัยแล้ว' },
]

const markers = computed(() =>
  props.provinces.filter((p) => p.lat && p.lng).map((p) => ({ ...p, pos: positionOf(p) }))
)
</script>

<template>
  <div class="card map-card">
    <div class="card-header" style="background: var(--pdm-green-deep)">
      🗺 แผนที่ความเสี่ยงภัยด้านพืช (Risk Map)
    </div>

    <div class="map-body">
      <div class="legend">
        <div v-for="l in legend" :key="l.key" class="legend-item">
          <span class="dot" :class="`bg-risk-${l.key}`"></span>{{ l.label }}
        </div>
      </div>

      <div class="map-canvas">
        <button
          v-for="m in markers"
          :key="m.id"
          class="marker"
          :class="`bg-risk-${m.risk_level}`"
          :style="m.pos"
          :title="m.name_th"
          @click="emit('select', m)"
        ></button>
      </div>

      <div v-if="selected" class="detail-panel">
        <div class="detail-header">
          <span>📍</span> จังหวัด{{ selected.name_th }}
        </div>
        <ul class="detail-list">
          <li><span>อำเภอได้รับผลกระทบ</span><strong>{{ selected.amphoe_affected }} อำเภอ</strong></li>
          <li><span>ตำบลได้รับผลกระทบ</span><strong>{{ selected.tambon_affected }} ตำบล</strong></li>
          <li><span>เกษตรกรได้รับผลกระทบ</span><strong>{{ Number(selected.farmers_affected).toLocaleString() }} ราย</strong></li>
          <li><span>พื้นที่เสียหาย</span><strong>{{ Number(selected.damaged_area_rai).toLocaleString() }} ไร่</strong></li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>
.map-card { display: flex; flex-direction: column; overflow: hidden; }
.map-body { position: relative; padding: 14px; }
.legend {
  display: flex; gap: 14px; flex-wrap: wrap;
  font-size: 12px; margin-bottom: 10px; color: var(--text-muted);
}
.legend-item { display: flex; align-items: center; gap: 6px; }
.dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }

.map-canvas {
  position: relative;
  height: 420px;
  background:
    linear-gradient(160deg, #dceee1 0%, #cfe9e0 60%, #bfe0e6 100%);
  border-radius: var(--radius-md);
  overflow: hidden;
}
.marker {
  position: absolute;
  width: 14px; height: 14px;
  border-radius: 50% 50% 50% 0;
  transform: translate(-50%, -100%) rotate(-45deg);
  border: 2px solid #fff;
  cursor: pointer;
  box-shadow: 0 1px 4px rgba(0,0,0,0.3);
}
.marker:hover { transform: translate(-50%, -100%) rotate(-45deg) scale(1.3); }

.detail-panel {
  position: absolute;
  top: 60px; right: 24px;
  width: 220px;
  background: #fff;
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-card);
  overflow: hidden;
}
.detail-header {
  background: var(--pdm-red);
  color: #fff;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 700;
  display: flex; align-items: center; gap: 6px;
}
.detail-list { list-style: none; margin: 0; padding: 10px 12px; display: flex; flex-direction: column; gap: 8px; }
.detail-list li { display: flex; justify-content: space-between; font-size: 12px; gap: 8px; }
.detail-list span { color: var(--text-muted); }
</style>
