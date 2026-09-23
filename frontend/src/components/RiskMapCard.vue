<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// --- Props and Emits ---
const props = defineProps({
  provinces: Array,
  selectedProvince: Object,
  weatherData: Object,
  rainfallData: Object,
  damWaterData: Array,
  mapView: String, // 'risk', 'weather', or 'dam'
  rainfallPeriod: String, // 'today', 'yesterday', 'last_3_days', 'last_7_days'
});

const emit = defineEmits(['select-province', 'set-map-view', 'set-rainfall-period']);

// --- Leaflet Map Setup ---
const mapContainer = ref(null);
let map = null;
let markersLayer = new L.LayerGroup();

const riskLevelColors = {
  critical: '#dc3545',
  high: '#fd7e14',
  medium: '#ffc107',
  low: '#198754',
};

const rainfallLevels = [
  { min: 250, label: 'ฝนตกหนักมาก (> 250)', color: '#800080' }, // Purple
  { min: 125, label: 'ฝนตกหนัก (125-250)', color: '#dc3545' }, // Red
  { min: 65, label: 'ฝนตกค่อนข้างหนัก (65-125)', color: '#fd7e14' }, // Orange
  { min: 35, label: 'ฝนตกปานกลาง (35-65)', color: '#ffc107' }, // Yellow
  { min: 10, label: 'ฝนตกเล็กน้อย (10-35)', color: '#0dcaf0' }, // Cyan
  { min: 0.1, label: 'ฝนโปรยปราย (0.1-10)', color: '#198754' }, // Green
  { min: 0, label: 'ไม่มีฝน', color: '#6c757d' }, // Grey
];

const rainfallPeriods = [
  { key: 'today', label: 'ฝนสะสมวันนี้' },
  { key: 'yesterday', label: 'ฝนสะสมเมื่อวาน' },
  { key: 'last_3_days', label: 'ฝนสะสม 3 วัน' },
  { key: 'last_7_days', label: 'ฝนสะสม 7 วัน' },
];

const damWaterLevels = [
  { max: 30, label: 'น้อยวิกฤต (<= 30%)', color: '#0d6efd' },
  { max: 50, label: 'น้อย (> 30-50%)', color: '#198754' },
  { max: 80, label: 'ปานกลาง (> 50-80%)', color: '#ffc107' },
  { max: 100, label: 'มาก (> 80-100%)', color: '#fd7e14' },
  { max: Infinity, label: 'ล้นเขื่อน (> 100%)', color: '#dc3545' },
];

const getRainfallColor = (value) => {
  if (value === null || value === undefined || value <= 0) return rainfallLevels.find(l => l.min === 0).color;
  for (const level of rainfallLevels) {
    if (value > level.min) {
      return level.color;
    }
  }
  return rainfallLevels.find(l => l.min === 0).color;
};

onMounted(() => {
  // Fix Leaflet's default icon path issue with bundlers
  delete L.Icon.Default.prototype._getIconUrl;
  L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  });

  map = L.map(mapContainer.value);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  const thailandBounds = [[5.6, 97.3], [20.5, 105.7]];
  map.fitBounds(thailandBounds);

  markersLayer.addTo(map);

  updateMap();
});

onBeforeUnmount(() => {
  if (map) {
    map.remove();
  }
});

watch(() => [props.mapView, props.provinces, props.weatherData, props.rainfallData, props.damWaterData, props.rainfallPeriod], () => {
  updateMap();
}, { deep: true });

watch(() => props.selectedProvince, (newVal) => {
  // No-op: Keep the map zoomed out to the whole country.
});

const updateMap = () => {
  if (!map) return;
  markersLayer.clearLayers();

  if (props.mapView === 'temperature') {
    drawProvinceRiskMarkers();
  } else if (props.mapView === 'weather') {
    drawWeatherStationMarkers();
  } else if (props.mapView === 'dam') {
    drawDamWaterMarkers();
  }
};

const getDamWaterLevel = (value) => {
  const numericValue = Number(value);
  if (!Number.isFinite(numericValue)) return null;
  return damWaterLevels.find(level => numericValue <= level.max) || damWaterLevels[0];
};

const drawDamWaterMarkers = () => {
  if (!props.damWaterData) return;

  props.damWaterData.forEach(damRecord => {
    const dam = damRecord.dam;
    const level = getDamWaterLevel(damRecord.dam_storage_percent);
    if (!dam || !level || dam.dam_lat == null || dam.dam_long == null) return;

    const marker = L.circleMarker([dam.dam_lat, dam.dam_long], {
      radius: 7,
      fillColor: level.color,
      color: '#fff',
      weight: 2,
      opacity: 1,
      fillOpacity: 0.9
    }).addTo(markersLayer);

    const damName = dam.dam_name?.th || dam.dam_name?.en || 'ไม่ระบุชื่อเขื่อน';
    const storage = damRecord.dam_storage ?? 'N/A';
    const capacity = dam.normal_storage ?? dam.max_storage ?? 'N/A';
    marker.bindPopup(`<b>เขื่อน${damName}</b><br>
      ระดับ: ${level.label}<br>
      ปริมาณน้ำ: ${storage} ล้าน ลบ.ม.<br>
      ความจุปกติ: ${capacity} ล้าน ลบ.ม.<br>
      วันที่ข้อมูล: ${damRecord.dam_date || 'N/A'}`);
  });
};

const drawProvinceRiskMarkers = () => {
  if (!props.provinces) return;
  props.provinces.forEach(p => {
    const lon = p.lng ?? p.lon;
    if (p.lat == null || lon == null) return;

    const color = riskLevelColors[p.risk_level] || '#6c757d';
    const marker = L.circleMarker([p.lat, lon], {
      radius: 8,
      fillColor: color,
      color: '#fff',
      weight: 2,
      opacity: 1,
      fillOpacity: 0.8
    }).addTo(markersLayer);

    marker.bindPopup(`<b>${p.name_th}</b><br>ระดับความเสี่ยง: ${p.risk_level}`);
    marker.on('click', () => emit('select-province', p));
  });
};

const drawWeatherStationMarkers = () => {
  const dataSet = props.rainfallPeriod == 'today' ? props.weatherData?.data : props.rainfallData?.data;
  if (!dataSet) return;

  dataSet.forEach(station => {
    const lat = station.station_lat;
    const lon = station.station_lon;
    if (lat == null || lon == null) return;

    let rainfallValue = null;
    let periodLabel = '';
    var popupContent = '';
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Bangkok' };


    switch (props.rainfallPeriod) {
      case 'today':
        rainfallValue = station.precip_today;
        periodLabel = 'วันนี้';
        break;
      case 'yesterday':
      case 'last_3_days':
      case 'last_7_days':
        const history = props.rainfallData?.data.find(s => s.station_id === station.station_id);
        if (history) {
          const periodKeyMap = { yesterday: 'precip_yesterday', last_3_days: 'precip_3days', last_7_days: 'precip_7days' };
          const key = periodKeyMap[props.rainfallPeriod];
          rainfallValue = history[key];
        }
        periodLabel = rainfallPeriods.find(p => p.key === props.rainfallPeriod)?.label || '';
        break;
    }

    const color = getRainfallColor(rainfallValue);
    const marker = L.circleMarker([lat, lon], {
      radius: 6,
      fillColor: color,
      color: '#000',
      weight: 1,
      opacity: 1,
      fillOpacity: 0.9
    }).addTo(markersLayer);

    if (props.rainfallPeriod === 'today') {
      const tempDate = new Date(station.datetime_utc7);
      const displayDate = tempDate ? `วันที่ปรับปรุง: ${tempDate.toLocaleString('th-TH', options)} น.` : ''
      const temp = station.temperature;
      popupContent = `${displayDate}<br>
      <b>สถานี: ${station.station_name_th}</b><br>
      จังหวัด: ${station.province_name_th}<br>
      <hr class="my-1">
      <b>${periodLabel}: ${rainfallValue ?? 'N/A'} มม.</b><br>
     อุณหภูมิวันนี้: ${temp ?? 'N/A'} °C`;
    } else {
      const tempDate = new Date(station.date);
      const displayDate = tempDate ? `วันที่ปรับปรุง: ${tempDate.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' })}` : ''
      popupContent = `${displayDate}<br>
      <b>สถานี: ${station.station_name_th}</b><br>
      จังหวัด: ${station.province_name_th}<br>
      <hr class="my-1">
      <b>${periodLabel}: ${rainfallValue ?? 'N/A'} มม.</b><br>
      `;
    }

    marker.bindPopup(popupContent);
  });
};
</script>

<template>
  <div class="card map-card">
    <div class="card-header" style="background: var(--pdm-green-deep)">
      รู้ข้อมูลก่อนเกิดภัย
    </div>

    <div class="card-body p-0" style="position: relative;">
      <div class="view-switcher">
        <!-- <button :class="{ active: mapView === 'risk' }"
          @click="$emit('setMapView', 'risk')">ความเสี่ยงภัยพิบัติ</button> -->
        <button :class="{ active: mapView === 'weather' }"
          @click="$emit('setMapView', 'weather')">ปริมาณน้ำฝนจากกรมอุตุนิยมวิทยา</button>
        <button :class="{ active: mapView === 'dam' }" @click="$emit('setMapView', 'dam')">ปริมาณน้ำในเขื่อน</button>
        <button :class="{ active: mapView === 'temperature' }"
          @click="$emit('setMapView', 'temperature')">อุณหภูมิ</button>
      </div>

      <div id="map-container" ref="mapContainer"></div>

      <div v-if="mapView === 'weather'" class="weather-controls">
        <div class="rainfall-legend">
          <h6>ปริมาณน้ำฝน (มม.)</h6>
          <ul>
            <li v-for="level in rainfallLevels" :key="level.label">
              <span class="legend-color" :style="{ backgroundColor: level.color }"></span>
              {{ level.label }}
            </li>
          </ul>
        </div>
        <div class="period-selector">
          <h6>เลือกช่วงเวลา</h6>
          <div class="btn-group-vertical w-100">
            <button v-for="period in rainfallPeriods" :key="period.key" type="button" class="btn btn-sm text-start"
              :class="{ active: rainfallPeriod === period.key }" @click="$emit('setRainfallPeriod', period.key)">
              {{ period.label }}
            </button>
          </div>
        </div>
      </div>
      <div v-if="mapView === 'dam'" class="weather-controls">
        <div class="rainfall-legend">
          <h6>ปริมาณน้ำในเขื่อน (%)</h6>
          <ul>
            <li v-for="level in damWaterLevels" :key="level.label">
              <span class="legend-color" :style="{ backgroundColor: level.color }"></span>
              {{ level.label }}
            </li>
          </ul>
        </div>
      </div>
      <!-- <div v-if="mapView === 'temperature'" class="weather-controls">
        <div class="rainfall-legend">
          <h6>อุณหภูมิ</h6>
          <ul>
            <li v-for="level in damWaterLevels" :key="level.label">
              <span class="legend-color" :style="{ backgroundColor: level.color }"></span>
              {{ level.label }}
            </li>
          </ul>
        </div>
      </div> -->
    </div>
  </div>
</template>

<style scoped>
#map-container {
  width: 100%;
  height: 100%;
  min-height: clamp(360px, 55vw, 600px);
}

.map-card {
  min-width: 0;
  overflow: hidden;
}

.weather-controls {
  position: absolute;
  top: 50px;
  right: 10px;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.rainfall-legend,
.period-selector {
  background: rgba(255, 255, 255, 0.9);
  padding: 10px;
  border-radius: 5px;
  box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
  width: 220px;
}

.rainfall-legend h6,
.period-selector h6 {
  margin-top: 0;
  margin-bottom: 8px;
  font-size: 0.9rem;
  font-weight: bold;
  border-bottom: 1px solid #eee;
  padding-bottom: 5px;
}

.rainfall-legend ul {
  list-style: none;
  padding: 0;
  margin: 0;
  font-size: 0.8rem;
}

.rainfall-legend li {
  display: flex;
  align-items: center;
  margin-bottom: 4px;
}

.legend-color {
  width: 18px;
  height: 18px;
  margin-right: 8px;
  border: 1px solid #ccc;
}

.btn-group-vertical .btn {
  border-radius: 0;
}

/* .btn-group-vertical .btn:first-child {
  border-top-left-radius: .25rem;
  border-top-right-radius: .25rem;
}

.btn-group-vertical .btn:last-child {
  border-top-right-radius: .25rem;
  border-bottom-left-radius: .25rem;
  border-bottom-right-radius: .25rem;
} */

.period-selector .btn {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  color: #495057;
  transition: background-color 0.2s, color 0.2s;
}

.period-selector .btn.active {
  background-color: var(--pdm-green);
  border-color: var(--pdm-green-deep);
  color: #fff;
  font-weight: 600;
}

.view-switcher {
  display: flex;
  flex-wrap: wrap;
  /* background-color: var(--bg-page); */
  /* border-radius: var(--radius-md); */
  padding: 4px;
  /* border: 1px solid var(--border-soft); */
}

.view-switcher button {
  border: none;
  background-color: #fff;
  padding: 6px 16px;
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  /* cursor: pointer; */
  transition: background-color 0.2s, color 0.2s;
  white-space: nowrap;
}

.view-switcher button.active {
  background: var(--bg-page);
  color: var(--pdm-green-deep);
  font-weight: 600;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

@media (max-width: 640px) {
  #map-container {
    height: 58vw;
    min-height: 280px;
    max-height: 420px;
  }

  .weather-controls {
    position: relative;
    top: auto;
    right: auto;
    padding: 10px;
    background: var(--bg-page);
  }

  .rainfall-legend,
  .period-selector {
    width: 100%;
  }

  .view-switcher {
    gap: 4px;
    overflow-x: auto;
    flex-wrap: nowrap;
    -webkit-overflow-scrolling: touch;
  }

  .view-switcher button {
    flex: 0 0 auto;
    padding-left: 10px;
    padding-right: 10px;
    font-size: 12px;
  }

  .card-header {
    padding: 12px 14px;
    font-size: 14px;
  }
}
</style>
