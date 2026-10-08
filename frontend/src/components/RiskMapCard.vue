<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import provinceBoundaries from '@/assets/th_adm1.json';

// --- Props and Emits ---
const props = defineProps({
  provinces: Array,
  selectedProvince: Object,
  rainfallData: Object,
  damWaterData: Array,
  temperatureData: Array,
  rainAverageData: { type: Array, default: () => [] }, // ปริมาณน้ำฝนเฉลี่ย 24 ชม. (riskmap)
  noneProduceData: { type: Array, default: () => [] },
  mapView: String, // 'risk', 'weather', 'dam', or 'rain_avg'
  rainfallPeriod: String, // 'today', 'yesterday', 'last_3_days', 'last_7_days'
});


const emit = defineEmits(['select-province', 'set-map-view', 'setMapView', 'set-rainfall-period', 'setRainfallPeriod']);

// --- Leaflet Map Setup ---
const mapContainer = ref(null);
let map = null;
let markersLayer = new L.LayerGroup();
let provincePolygonsLayer = null;
// Per-view caches (keyed by 'lat,lng') so Leaflet markers get reused across
// renders instead of being destroyed & recreated on every tab/period change.
const markerCache = {
  rain: new Map(),
  dam: new Map(),
  temperature: new Map(),
};


const riskLevelColors = {
  critical: '#dc3545',
  high: '#fd7e14',
  medium: '#ffc107',
  low: '#198754',
};

const rainfallLevels = [
  { min: 90, label: 'ฝนตกหนักมาก (> 90)', color: '#dc3545' }, // red
  { min: 70, label: 'ฝนตกหนัก (70-90)', color: '#CD7F32' }, // brown
  { min: 50, label: 'ฝนตกหนัก (50-70)', color: '#ffc107' }, // orange
  { min: 35, label: 'ฝนตกหนัก (35-50)', color: '#fd7e14' }, // yellow
  { min: 20, label: 'ฝนตกปานกลาง (20-35)', color: '#198754' }, // green
  { min: 10, label: 'ฝนตกปานกลาง (10-20)', color: '#90EE90' }, // green-light
  { min: 0, label: 'ฝนตกเล็กน้อย (0-10)', color: '#0dcaf0' }, // Blue
];

const rainfallPeriods = [
  { key: 'today', label: 'ฝนสะสมวันนี้' },
  { key: 'yesterday', label: 'ฝนสะสมเมื่อวาน' },
  { key: 'last_3_days', label: 'ฝนสะสม 3 วัน' },
  { key: 'last_7_days', label: 'ฝนสะสม 7 วัน' },
];

const periodLabelMap = Object.freeze({ today: 'ฝนสะสมวันนี้', yesterday: 'ฝนสะสมเมื่อวาน', 'last_3_days': 'ฝนสะสม 3 วัน', 'last_7_days': 'ฝนสะสม 7 วัน' });

const damWaterLevels = [
  { max: 30, label: 'น้อยวิกฤต (<= 30%)', color: '#0d6efd' },
  { max: 50, label: 'น้อย (> 30-50%)', color: '#198754' },
  { max: 80, label: 'ปานกลาง (> 50-80%)', color: '#ffc107' },
  { max: 100, label: 'มาก (> 80-100%)', color: '#fd7e14' },
  { max: Infinity, label: 'ล้นเขื่อน (> 100%)', color: '#dc3545' },
];

const temperatureLevels = [
  { max: 24, label: 'อากาศเย็น (< 24°C)', color: '#3b82f6' },
  { max: 28, label: 'ปกติ/สบาย (24-28°C)', color: '#10b981' },
  { max: 32, label: 'ค่อนข้างร้อน (28-32°C)', color: '#eab308' },
  { max: 35, label: 'อากาศร้อน (32-35°C)', color: '#f97316' },
  { max: Infinity, label: 'ร้อนจัด (> 35°C)', color: '#ef4444' },
];

const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Bangkok' };

const getTemperatureLevel = (val) => {
  const num = Number(val);
  if (!Number.isFinite(num)) return null;
  return temperatureLevels.find(l => num <= l.max) || temperatureLevels[temperatureLevels.length - 1];
};

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
  provincePolygonsLayer = L.geoJSON(undefined, {
    style: getProvincePolygonStyle,
    onEachFeature: bindProvinceTooltip,
  }).addTo(map);

  updateMap();
});

onBeforeUnmount(() => {
  if (map) {
    map.remove();
    map = null;
  }
  markerCache.rain.clear();
  markerCache.dam.clear();
  markerCache.temperature.clear();
  provincePolygonsLayer = null;
});


// Coalesce rapid successive changes into one render on the next animation
// frame so the UI thread stays responsive while switching tabs / periods.
let updateFrame = 0;
const scheduleUpdate = () => {
  if (updateFrame) return;
  updateFrame = requestAnimationFrame(() => {
    updateFrame = 0;
    updateMap();
  });
};

const updateMap = () => {
  if (!map) return;
  markersLayer.clearLayers();
  provincePolygonsLayer?.clearLayers();

  if (props.mapView === 'temperature') {
    renderReusableMarkers('temperature', buildTemperatureItems());
  } else if (props.mapView === 'rain') {
    renderReusableMarkers('rain', buildRainItems());
  } else if (props.mapView === 'dam') {
    renderReusableMarkers('dam', buildDamItems());
  } else if (props.mapView === 'rain_avg') {
    provincePolygonsLayer?.addData(provinceBoundaries);
  }
};


// Reuses existing Leaflet circle markers keyed by lat,lng so switching rainfall
// periods (or refreshing a view) only updates styles/popups instead of tearing
// down and rebuilding every marker layer on the map.
const renderReusableMarkers = (viewKey, items) => {
  const cache = markerCache[viewKey];
  const seen = new Set();

  items.forEach(item => {
    const key = `${item.lat},${item.lng}`;
    seen.add(key);

    let marker = cache.get(key);
    if (!marker) {
      marker = L.circleMarker([item.lat, item.lng], { radius: item.style.radius || 1 });
      marker.bindPopup(item.popup, { closeButton: false, autoPan: false });
      marker.on('mouseover', () => marker.openPopup());
      marker.on('mouseout', () => marker.closePopup());
      cache.set(key, marker);
    } else {
      if (marker.getPopup()) {
        marker.setPopupContent(item.popup);
      } else {
        marker.bindPopup(item.popup, { closeButton: false, autoPan: false });
      }
    }

    // `updateMap()` calls `markersLayer.clearLayers()` first, so EVERY marker
    // — whether newly created or reused from the cache — must be (re-)added to
    // the layer group, otherwise reused markers silently drop off the map
    // (leaving the count short and their popups unbound).
    marker.addTo(markersLayer);
    marker.setStyle({
      radius: item.style.radius,
      weight: item.style.weight,
      opacity: item.style.opacity ?? 1,
      color: item.style.color ?? '#fff',
      fillColor: item.style.fillColor,
      fillOpacity: item.style.fillOpacity ?? 1,
    });
  });

  // Remove cached markers that are no longer part of the current dataset.
  for (const [key, marker] of cache) {
    if (!seen.has(key)) {
      markersLayer.removeLayer(marker);
      cache.delete(key);
    }
  }
};

// Watch the view / period triggers and the data sources separately. Avoid a
// single `deep: true` watcher over the combined array, which made Vue deep-walk
// the entire nested props on every reactive tick and blocked the main thread
// when clicking a tab or period button.
watch(() => props.mapView, scheduleUpdate);
watch(() => props.rainfallPeriod, scheduleUpdate);
watch(
  () => [
    props.provinces,
    props.rainfallData,
    props.damWaterData,
    props.temperatureData,
    props.rainAverageData,
    props.noneProduceData,
  ],
  scheduleUpdate
);


watch(() => props.selectedProvince, (_newVal) => {
  // No-op: Keep the map zoomed out to the whole country.
});

const buildTemperatureItems = () => {
  const items = [];
  if (!props.temperatureData || !props.temperatureData.length) return items;

  props.temperatureData.forEach(station => {
    const lat = station.station_lat;
    const lon = station.station_lon;
    if (lat == null || lon == null) return;

    const temp = station.temperature;
    const level = getTemperatureLevel(temp);
    const color = level ? level.color : '#6c757d';

    const stationName = station.station_name_th || station.station_name_en || 'ไม่ระบุชื่อสถานี';
    const province = station.province_name_th || 'ไม่ระบุจังหวัด';
    const region = station.region_name_th || '';
    const minTemp = station.temperature_min_today != null ? `${station.temperature_min_today}°C` : '-';
    const maxTemp = station.temperature_max_today != null ? `${station.temperature_max_today}°C` : '-';
    const humidity = station.humidity != null ? `${station.humidity}%` : '-';
    const displayDate = station.datetime_utc7 ? new Date(station.datetime_utc7).toLocaleString('th-TH', options) : '-';

    const popup = `<b>${stationName}</b><br>
      จังหวัด: ${province} ${region ? `(${region})` : ''}<br>
      <hr class="my-1">
      <b>อุณหภูมิปัจจุบัน: <span style="color: ${color}; font-size: 1.1em; font-weight: bold;">${temp != null ? `${temp}°C` : 'N/A'}</span></b><br>
      อุณหภูมิต่ำสุด/สูงสุดวันนี้: ${minTemp} / ${maxTemp}<br>
      ความชื้นสัมพัทธ์: ${humidity}<br>
      เวลาตรวจวัด: ${displayDate}`;

    items.push({ lat, lng: lon, style: { radius: 6, weight: 1.5, color: '#fff', fillColor: color, fillOpacity: 0.9 }, popup });
  });
  return items;
};

const getDamWaterLevel = (value) => {
  const numericValue = Number(value);
  if (!Number.isFinite(numericValue)) return null;
  return damWaterLevels.find(level => numericValue <= level.max) || damWaterLevels[0];
};

const buildDamItems = () => {
  const items = [];
  if (!props.damWaterData) return items;

  props.damWaterData.forEach(damRecord => {
    const dam = damRecord.dam;
    const level = getDamWaterLevel(damRecord.dam_storage_percent);
    if (!dam || !level || dam.dam_lat == null || dam.dam_long == null) return;

    const damName = dam.dam_name?.th || dam.dam_name?.en || 'ไม่ระบุชื่อเขื่อน';
    const storage = damRecord.dam_storage ?? 'N/A';
    const capacity = dam.normal_storage ?? dam.max_storage ?? 'N/A';
    const displayDate = damRecord.dam_date ? new Date(damRecord.dam_date).toLocaleString('th-TH', options) : 'N/A';
    const popup = `<b>เขื่อน${damName}</b><br>
      ระดับ: ${level.label}<br>
      ปริมาณน้ำ: ${storage} ล้าน ลบ.ม.<br>
      ความจุปกติ: ${capacity} ล้าน ลบ.ม.<br>
      วันที่ข้อมูล: ${displayDate}`;

    items.push({ lat: dam.dam_lat, lng: dam.dam_long, style: { radius: 7, weight: 2, color: '#fff', fillColor: level.color, fillOpacity: 0.9 }, popup });
  });
  return items;
};

// const drawProvinceRiskMarkers = () => {
//   if (!props.provinces) return;
//   props.provinces.forEach(p => {
//     const lon = p.lng ?? p.lon;
//     if (p.lat == null || lon == null) return;

//     const color = riskLevelColors[p.risk_level] || '#6c757d';
//     const marker = L.circleMarker([p.lat, lon], {
//       radius: 8,
//       fillColor: color,
//       color: '#fff',
//       weight: 2,
//       opacity: 1,
//       fillOpacity: 0.8
//     }).addTo(markersLayer);

//     marker.bindPopup(`<b>${p.name_th}</b><br>ระดับความเสี่ยง: ${p.risk_level}`);
//     marker.on('click', () => emit('select-province', p));
//   });
// };

const getRainfallDataSet = () => {
  const d = props.rainfallData?.data;
  switch (props.rainfallPeriod) {
    case 'yesterday': return d?.yesterday || [];
    case 'last_3_days': return d?.['3d'] || [];
    case 'last_7_days': return d?.['7d'] || [];
    case 'today':
    default: return d?.today || [];
  }
};

const buildRainItems = () => {
  const items = [];
  const periodLabel = periodLabelMap[props.rainfallPeriod] || '';
  const dataSet = getRainfallDataSet();

  dataSet.forEach(value => {
    const lat = value.station.tele_station_lat;
    const lon = value.station.tele_station_long;
    const station = value.station.tele_station_name.th;
    if (lat == null || lon == null) return;

    let rainfallValue = null;
    let provinceName = '';
    let displayDate = '';

    const tempDate = new Date(value.rainfall_datetime);
    switch (props.rainfallPeriod) {
      case 'today':
      case 'yesterday':
        rainfallValue = value.rainfall_value;
        displayDate = tempDate ? `วันที่ปรับปรุง: ${tempDate.toLocaleString('th-TH', options)} น.` : '';
        provinceName = value.geocode.province_name.th;
        break;
      case 'last_3_days':
        rainfallValue = value.rain_3d;
        displayDate = tempDate ? `วันที่ปรับปรุง: ${tempDate.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' })}` : '';
        provinceName = value.geocode.province_name.th;
        break;
      case 'last_7_days':
        displayDate = tempDate ? `วันที่ปรับปรุง: ${tempDate.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' })}` : '';
        rainfallValue = value.rain_7d;
        provinceName = value.geocode.province_name.th;
        break;
    }

    const color = getRainfallColor(rainfallValue);
    const popup = `${displayDate}<br>
      <b>สถานี: ${station}</b><br>
      จังหวัด: ${provinceName}<br>
      <hr class="my-1">
      <b>${periodLabel}: ${rainfallValue ?? 'N/A'} มม.</b><br>`;

    items.push({ lat, lng: lon, style: { radius: 3, weight: 0.6, color, fillColor: color, fillOpacity: 1 }, popup });
  });
  return items;
};

// ---- rain_avg (riskmap.doae.go.th) ----
const rainAvgLevels = [
  { min: 35, color: '#dc3545' },
  { min: 20, color: '#fd7e14' },
  { min: 10, color: '#ffc107' },
  { min: 0.1, color: '#198754' },
  { min: 0, color: '#0dcaf0' },
];

const rainAveragePeriods = [
  { key: 'avg_rain_24h', label: '24 ชั่วโมง' },
  { key: 'avg_yesterday', label: 'เมื่อวาน' },
  { key: 'avg_3d', label: 'สะสม 3 วัน' },
  { key: 'avg_7d', label: 'สะสม 7 วัน' },
];
const selectedRainAveragePeriod = ref('avg_rain_24h');
watch(selectedRainAveragePeriod, scheduleUpdate);

const selectedRainAveragePeriodInfo = () =>
  rainAveragePeriods.find((period) => period.key === selectedRainAveragePeriod.value) || rainAveragePeriods[0];

const getRainAvgColor = (val) => {
  for (const l of rainAvgLevels) {
    if (val >= l.min) return l.color;
  }
  return '#0dcaf0';
};

const normalizeAdminCode = (code) => String(code ?? '').replace(/^TH/i, '').padStart(2, '0');

const rainAverageByCode = () => new Map(
  props.rainAverageData.map((record) => [normalizeAdminCode(record.admin_code), record])
);

const noneProduceByCode = () => new Map(
  props.noneProduceData.map((record) => [normalizeAdminCode(record.province_code), record])
);

const getProvincePolygonStyle = (feature) => {
  const code = normalizeAdminCode(feature.properties.ADM1_PCODE);
  const rain = rainAverageByCode().get(code)?.[selectedRainAveragePeriod.value];

  return {
    color: '#fff',
    weight: 1,
    fillColor: rain == null ? '#adb5bd' : getRainAvgColor(Number(rain)),
    fillOpacity: rain == null ? 0.35 : 0.75,
  };
};

const appendTooltipRow = (container, label, value) => {
  const row = document.createElement('div');
  const title = document.createElement('strong');
  title.textContent = `${label}: `;
  row.append(title, document.createTextNode(value));
  container.append(row);
};

const bindProvinceTooltip = (feature, layer) => {
  const code = normalizeAdminCode(feature.properties.ADM1_PCODE);
  const rain = rainAverageByCode().get(code);
  const noneProduce = noneProduceByCode().get(code);
  const tooltip = document.createElement('div');
  const name = document.createElement('strong');
  name.textContent = noneProduce?.province_name || rain?.admin_name || feature.properties.ADM1_TH;
  tooltip.append(name);
  const periodInfo = selectedRainAveragePeriodInfo();

  appendTooltipRow(tooltip, 'เกษตรกร', noneProduce
    ? `${Number(noneProduce.total_farmers || 0).toLocaleString('th-TH')} ราย`
    : 'ไม่มีข้อมูล');
  appendTooltipRow(tooltip, 'พื้นที่ยังไม่เก็บเกี่ยว', noneProduce
    ? `${Number(noneProduce.total_plant || 0).toLocaleString('th-TH')} ไร่`
    : 'ไม่มีข้อมูล');
  appendTooltipRow(tooltip, `ฝนเฉลี่ยสะสม (${periodInfo.label})`, rain
    ? `${Number(rain[periodInfo.key] || 0).toLocaleString('th-TH', { maximumFractionDigits: 2 })} มม.`
    : 'ไม่มีข้อมูล');
  appendTooltipRow(tooltip, 'จำนวนสถานี', rain ? `${rain.station_count ?? '-'} สถานี` : 'ไม่มีข้อมูล');

  layer.bindTooltip(tooltip, { sticky: true, direction: 'auto', className: 'province-data-tooltip' });
  layer.on({
    mouseover: (event) => event.target.setStyle({ weight: 2, fillOpacity: 0.95 }),
    mouseout: (event) => provincePolygonsLayer?.resetStyle(event.target),
  });
};
</script>


<template>
  <div class="card overflow-hidden min-w-0">
    <div class="card-header bg-pdm-green-deep">
      รู้ข้อมูลก่อนเกิดภัย
    </div>

    <div class="p-0 relative">
      <div class="flex flex-wrap p-1 gap-1 max-[640px]:overflow-x-auto max-[640px]:flex-nowrap">
        <!-- <button :class="{ active: mapView === 'risk' }"
          @click="$emit('setMapView', 'risk')">ความเสี่ยงภัยพิบัติ</button> -->
        <button
          :class="['px-4 py-1.5 rounded-[4px] text-[13px] font-medium whitespace-nowrap', mapView === 'rain' ? 'bg-page text-pdm-green-deep font-semibold shadow-[0_1px_3px_rgba(0,0,0,0.1)]' : 'bg-white text-muted']"
          @click="$emit('setMapView', 'rain')">ปริมาณน้ำฝนจากthaiwater</button>
        <button
          :class="['px-4 py-1.5 rounded-[4px] text-[13px] font-medium whitespace-nowrap', mapView === 'rain_avg' ? 'bg-page text-pdm-green-deep font-semibold shadow-[0_1px_3px_rgba(0,0,0,0.1)]' : 'bg-white text-muted']"
          @click="$emit('setMapView', 'rain_avg')">ฝนเฉลี่ย 24 ชม. (riskmap)</button>
        <button
          :class="['px-4 py-1.5 rounded-[4px] text-[13px] font-medium whitespace-nowrap', mapView === 'dam' ? 'bg-page text-pdm-green-deep font-semibold shadow-[0_1px_3px_rgba(0,0,0,0.1)]' : 'bg-white text-muted']"
          @click="$emit('setMapView', 'dam')">ปริมาณน้ำในเขื่อน</button>
        <button
          :class="['px-4 py-1.5 rounded-[4px] text-[13px] font-medium whitespace-nowrap', mapView === 'temperature' ? 'bg-page text-pdm-green-deep font-semibold shadow-[0_1px_3px_rgba(0,0,0,0.1)]' : 'bg-white text-muted']"
          @click="$emit('setMapView', 'temperature')">อุณหภูมิ</button>
      </div>

      <div id="map-container" class="w-full h-full min-h-[500px] max-[640px]:min-h-[420px] max-[640px]:max-h-[420px]"
        ref="mapContainer"></div>

      <div v-if="mapView === 'rain'"
        class="absolute top-[50px] right-[10px] z-[1000] flex flex-col gap-[10px] max-[640px]:relative max-[640px]:top-auto max-[640px]:right-auto max-[640px]:p-[10px] max-[640px]:bg-page">
        <div class="bg-white/90 p-[10px] rounded-[5px] shadow-[0_1px_5px_rgba(0,0,0,0.2)] w-[220px] max-[640px]:w-full">
          <h6 class="text-[0.9rem] font-bold border-b border-[#eee] pb-[5px] mb-2 m-0">ปริมาณน้ำฝน (มม.)</h6>
          <ul class="list-none p-0 m-0 text-[0.8rem]">
            <li class="flex items-center mb-1" v-for="level in rainfallLevels" :key="level.label">
              <span class="w-[18px] h-[18px] mr-2 border border-[#ccc]"
                :style="{ backgroundColor: level.color }"></span>
              {{ level.label }}
            </li>
          </ul>
        </div>
        <div class="bg-white/90 p-[10px] rounded-[5px] shadow-[0_1px_5px_rgba(0,0,0,0.2)] w-[220px] max-[640px]:w-full">
          <h6 class="text-[0.9rem] font-bold border-b border-[#eee] pb-[5px] mb-2 m-0">เลือกช่วงเวลา</h6>
          <div class="flex flex-col gap-0.5 w-full">
            <button v-for="period in rainfallPeriods" :key="period.key" type="button"
              class="w-full text-left text-[13px] bg-[#f8f9fa] border border-[#dee2e6] text-[#495057] py-1.5 px-2 rounded"
              :class="rainfallPeriod === period.key ? 'bg-pdm-green border-pdm-green-deep text-white font-semibold' : ''"
              @click="$emit('setRainfallPeriod', period.key)">
              {{ period.label }}
            </button>
          </div>
        </div>
      </div>
      <div v-if="mapView === 'rain_avg'"
        class="absolute top-[50px] right-[10px] z-[1000] flex flex-col gap-[10px] max-[640px]:relative max-[640px]:top-auto max-[640px]:right-auto max-[640px]:p-[10px] max-[640px]:bg-page">
        <div class="bg-white/90 p-[10px] rounded-[5px] shadow-[0_1px_5px_rgba(0,0,0,0.2)] w-[200px] max-[640px]:w-full">
          <h6 class="text-[0.9rem] font-bold border-b border-[#eee] pb-[5px] mb-2 m-0">
            ฝนเฉลี่ยสะสม: {{ selectedRainAveragePeriodInfo().label }}
          </h6>
          <div class="grid grid-cols-2 gap-1 mb-2">
            <button
              v-for="period in rainAveragePeriods"
              :key="period.key"
              type="button"
              class="rounded border border-[#dee2e6] px-2 py-1 text-[11px] text-[#495057]"
              :class="selectedRainAveragePeriod === period.key ? 'bg-pdm-green-deep text-white font-semibold' : 'bg-[#f8f9fa]'"
              :aria-pressed="selectedRainAveragePeriod === period.key"
              @click="selectedRainAveragePeriod = period.key"
            >
              {{ period.label }}
            </button>
          </div>
          <ul class="list-none p-0 m-0 text-[0.8rem]">
            <li class="flex items-center mb-1" v-for="level in rainAvgLevels" :key="level.min">
              <span class="w-[18px] h-[18px] mr-2 border border-[#ccc]"
                :style="{ backgroundColor: level.color }"></span>
              <span v-if="level.min >= 35">≥ 35 มม.</span>
              <span v-else-if="level.min >= 20">20-35 มม.</span>
              <span v-else-if="level.min >= 10">10-20 มม.</span>
              <span v-else-if="level.min >= 0.1">0.1-10 มม.</span>
              <span v-else>0 มม.</span>
            </li>
          </ul>
          <p class="mt-2 text-[0.75rem] text-muted leading-tight">
            สีพื้นที่ = ปริมาณฝนเฉลี่ยสะสม (มม.)<br>
            แหล่งข้อมูล: riskmap.doae.go.th<br>
            ขอบเขต: <a href="https://github.com/piyayut-ch/mapthai" target="_blank" rel="noreferrer" class="hover:underline">mapthai / UNOCHA</a>
          </p>
        </div>
      </div>
      <div v-if="mapView === 'dam'"
        class="absolute top-[50px] right-[10px] z-[1000] flex flex-col gap-[10px] max-[640px]:relative max-[640px]:top-auto max-[640px]:right-auto max-[640px]:p-[10px] max-[640px]:bg-page">
        <div class="bg-white/90 p-[10px] rounded-[5px] shadow-[0_1px_5px_rgba(0,0,0,0.2)] w-[220px] max-[640px]:w-full">
          <h6 class="text-[0.9rem] font-bold border-b border-[#eee] pb-[5px] mb-2 m-0">ปริมาณน้ำในเขื่อน (%)</h6>
          <ul class="list-none p-0 m-0 text-[0.8rem]">
            <li class="flex items-center mb-1" v-for="level in damWaterLevels" :key="level.label">
              <span class="w-[18px] h-[18px] mr-2 border border-[#ccc]"
                :style="{ backgroundColor: level.color }"></span>
              {{ level.label }}
            </li>
          </ul>
        </div>
      </div>
      <div v-if="mapView === 'temperature'"
        class="absolute top-[50px] right-[10px] z-[1000] flex flex-col gap-[10px] max-[640px]:relative max-[640px]:top-auto max-[640px]:right-auto max-[640px]:p-[10px] max-[640px]:bg-page">
        <div class="bg-white/90 p-[10px] rounded-[5px] shadow-[0_1px_5px_rgba(0,0,0,0.2)] w-[220px] max-[640px]:w-full">
          <h6 class="text-[0.9rem] font-bold border-b border-[#eee] pb-[5px] mb-2 m-0">อุณหภูมิ (°C)</h6>
          <ul class="list-none p-0 m-0 text-[0.8rem]">
            <li class="flex items-center mb-1" v-for="level in temperatureLevels" :key="level.label">
              <span class="w-[18px] h-[18px] mr-2 border border-[#ccc]"
                :style="{ backgroundColor: level.color }"></span>
              {{ level.label }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
