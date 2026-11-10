<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { getNoneProduceDisplayRows } from '@/utils/noneProduce'

const props = defineProps({
  /** ข้อมูลจาก store.noneProduceData */
  data: { type: Array, default: () => [] },
  sumAll: { type: Object, default: null },
  parentAreaCode: { type: String, default: null },
  selectionPath: { type: Array, default: () => [] },
  /** วันที่เกิดภัย (YYYY-MM-DD) ที่ store ใช้อยู่ */
  date: { type: String, default: '' },
  level: { type: String, default: 'province' },
  rainAveragePeriod: { type: String, default: 'avg_rain_24h' },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
  /** ปริมาณน้ำฝนเฉลี่ย 24 ชม. (riskmap) — ใช้สำหรับ join กับ noneProduce */
  rainAverageData: { type: Array, default: () => [] },
  /** ข้อมูลคาดการณ์ความเสี่ยงภัยพิบัติล่วงหน้า */
  forecastData: { type: Object, default: null },
  forecastHorizon: { type: String, default: '3d' },
  /** มุมมองแผนที่ปัจจุบัน เพื่อซิงค์โหมดการแสดงผล */
  mapView: { type: String, default: 'forecast_risk' },
})

const emit = defineEmits(['update:date', 'set-map-view'])

// ฟังก์ชันจัดการและปุ่มลัดวันที่
const formatDateStr = (d) => {
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const getRelativeDate = (offsetDays = 0) => {
  const d = new Date()
  d.setDate(d.getDate() + offsetDays)
  return formatDateStr(d)
}

const quickDatePresets = [
  { label: 'วันนี้', days: 0 },
  { label: '+1 วัน', days: 1 },
  { label: '+3 วัน', days: 3 },
  { label: '+7 วัน', days: 7 },
]

const selectDatePreset = (days) => {
  emit('update:date', getRelativeDate(days))
}

// โหมดการแสดงผลข้อมูลฝน: 'actual' (ฝนตรวจวัดจริง) หรือ 'forecast' (ฝนคาดการณ์ล่วงหน้า)
const rainDisplayMode = ref('forecast')

// ซิงค์โหมดการแสดงผลอัตโนมัติตามมุมมองแผนที่ (mapView)
watch(
  () => props.mapView,
  (newView) => {
    if (newView === 'rain_avg' || newView === 'rain') {
      rainDisplayMode.value = 'actual'
    } else if (newView === 'forecast_risk') {
      rainDisplayMode.value = 'forecast'
    }
  },
  { immediate: true }
)

const setRainDisplayMode = (mode) => {
  rainDisplayMode.value = mode
  emit('set-map-view', mode === 'actual' ? 'rain_avg' : 'forecast_risk')
}

const levelLabels = {
  province: 'จังหวัด',
  district: 'อำเภอ/เขต',
  subdistrict: 'ตำบล/แขวง',
}
const rainfallPeriodLabels = {
  avg_rain_24h: '24 ชั่วโมง',
  avg_yesterday: 'เมื่อวาน',
  avg_3d: 'สะสม 3 วัน',
  avg_7d: 'สะสม 7 วัน',
}
const rainfallPeriodLabel = computed(() =>
  rainfallPeriodLabels[props.rainAveragePeriod] || rainfallPeriodLabels.avg_rain_24h
)
const tableScopeName = computed(() => {
  if (props.level === 'district') return props.selectionPath[0]?.name || 'ทั้งประเทศ'
  if (props.level === 'subdistrict') {
    return props.selectionPath[1]?.name || props.selectionPath[0]?.name || 'ทั้งประเทศ'
  }
  return 'ทั้งประเทศ'
})
const normalizeAdminCode = (code) => String(code ?? '').replace(/^TH/i, '')
  .padStart(2, '0')

// สร้าง Map จาก admin_code เพื่อ join ได้เร็ว
const rainMap = computed(() => {
  const m = new Map()
  props.rainAverageData.forEach(r => {
    if (r && r.admin_code != null) {
      m.set(normalizeAdminCode(r.admin_code), r)
    }
  })
  return m
})

// สร้าง Map ข้อมูลคาดการณ์ล่วงหน้า (รองรับทั้งระดับจังหวัด อำเภอ และตำบล)
const forecastMap = computed(() => {
  const m = new Map()
  const list = props.forecastData?.areas || props.forecastData?.provinces || []
  list.forEach(f => {
    if (f) {
      if (f.admin_code != null) m.set(normalizeAdminCode(f.admin_code), f)
      if (f.code != null) m.set(normalizeAdminCode(f.code), f)
      if (f.area_code != null) m.set(normalizeAdminCode(f.area_code), f)
    }
  })
  return m
})

// แปลงค่าน้ำฝนจากข้อมูลคาดการณ์ตามช่วงเวลาที่เลือก
const getPeriodRainFromForecast = (forecast, period) => {
  if (!forecast) return null
  if (period === 'avg_7d') return forecast.rain_7d ?? forecast.forecast_rain
  if (period === 'avg_3d') return forecast.rain_3d ?? forecast.forecast_rain
  return forecast.rain_1d ?? forecast.forecast_rain
}

// สถานะการเรียงลำดับ (เริ่มต้น: คาดการณ์ความเสี่ยง สูงไปต่ำ)
const sortState = reactive({
  key: 'risk',
  order: 'desc',
})

const toggleSort = (key) => {
  if (sortState.key === key) {
    sortState.order = sortState.order === 'desc' ? 'asc' : 'desc'
  } else {
    sortState.key = key
    sortState.order = key === 'name' ? 'asc' : 'desc'
  }
}

const riskWeight = {
  critical: 4,
  high: 3,
  watch: 2,
  normal: 1,
}

// รายการข้อมูลพืชยังไม่เก็บเกี่ยว พร้อมแนบข้อมูลฝนและความเสี่ยง
const mappedRows = computed(() =>
  getNoneProduceDisplayRows(props.data, props.level, props.parentAreaCode)
    .map(item => {
      const areaCode = normalizeAdminCode(item.area_code || item.district_code || item.subdistrict_code || item.province_code)
      const pCode = normalizeAdminCode(item.province_code)
      const forecastItem = forecastMap.value.get(areaCode) || (props.level === 'province' ? forecastMap.value.get(pCode) : null)

      // ดึงค่าน้ำฝนของพื้นที่:
      // 1. ถ้ามีข้อมูลฝนตรวจวัด riskmap ตรงตามรหัสพื้นที่ ให้ใช้ค่านั้น
      // 2. ถ้าเป็นระดับอำเภอ/ตำบล และมีข้อมูลพยากรณ์รายพื้นที่ (rain_1d, rain_3d, rain_7d) ให้ใช้ค่ารายพื้นที่นั้น
      // 3. Fallback ไปใช้ค่าเฉลี่ยระดับจังหวัด
      const riskmapRain = rainMap.value.get(areaCode)?.[props.rainAveragePeriod]
      const areaSpecificForecastRain = (props.level !== 'province' && forecastItem)
        ? getPeriodRainFromForecast(forecastItem, props.rainAveragePeriod)
        : null

      const averageRain = riskmapRain ?? areaSpecificForecastRain ?? rainMap.value.get(pCode)?.[props.rainAveragePeriod]
      const numericRain = averageRain == null ? null : Number(averageRain)

      return {
        ...item,
        avg_rain: Number.isFinite(numericRain) ? numericRain : null,
        forecast_rain: forecastItem ? forecastItem.forecast_rain : null,
        risk_level: forecastItem ? forecastItem.risk_level : null,
        risk_score: forecastItem?.risk_score != null ? Number(forecastItem.risk_score) : null,
      }
    })
)

// รายการ sort ตามเงื่อนไขที่เลือก (เริ่มต้น: เรียงจากความเสี่ยงสูงไปต่ำ)
const rows = computed(() => {
  const isDesc = sortState.order === 'desc'

  return [...mappedRows.value].sort((a, b) => {
    if (sortState.key === 'name') {
      const nameA = String(a.area_name || '')
      const nameB = String(b.area_name || '')
      const comp = nameA.localeCompare(nameB, 'th')
      return isDesc ? -comp : comp
    }

    if (sortState.key === 'farmers') {
      const fA = Number(a.total_farmers) || 0
      const fB = Number(b.total_farmers) || 0
      return isDesc ? fB - fA : fA - fB
    }

    if (sortState.key === 'plant') {
      const pA = Number(a.total_plant) || 0
      const pB = Number(b.total_plant) || 0
      return isDesc ? pB - pA : pA - pB
    }

    if (sortState.key === 'risk') {
      if (rainDisplayMode.value === 'actual') {
        const rA = a.avg_rain != null ? Number(a.avg_rain) : -1
        const rB = b.avg_rain != null ? Number(b.avg_rain) : -1
        return isDesc ? rB - rA : rA - rB
      }

      // คาดการณ์ความเสี่ยง (forecast mode): วิกฤต > เสี่ยงสูง > เฝ้าระวัง > ปกติ > ไม่มีข้อมูล
      const wA = riskWeight[a.risk_level] || 0
      const wB = riskWeight[b.risk_level] || 0
      if (wA !== wB) {
        return isDesc ? wB - wA : wA - wB
      }

      const sA = a.risk_score != null ? Number(a.risk_score) : -1
      const sB = b.risk_score != null ? Number(b.risk_score) : -1
      if (sA !== sB && (sA !== -1 || sB !== -1)) {
        return isDesc ? sB - sA : sA - sB
      }

      const rainA = Number(a.forecast_rain) || 0
      const rainB = Number(b.forecast_rain) || 0
      if (rainA !== rainB) {
        return isDesc ? rainB - rainA : rainA - rainB
      }

      const plantA = Number(a.total_plant) || 0
      const plantB = Number(b.total_plant) || 0
      return isDesc ? plantB - plantA : plantA - plantB
    }

    return 0
  })
})

const sumAllFarmers = computed(() => {
  const value = props.sumAll?.total_farmers ?? props.sumAll?.totalFarmers
  return value == null ? null : Number(value)
})
const sumAllPlant = computed(() => {
  const value = props.sumAll?.total_plant ?? props.sumAll?.totalPlant
  return value == null ? null : Number(value)
})

const totals = computed(() => ({
  farmers: Number.isFinite(sumAllFarmers.value)
    ? sumAllFarmers.value
    : rows.value.reduce((s, r) => s + Number(r.total_farmers || 0), 0),
  plant: Number.isFinite(sumAllPlant.value)
    ? sumAllPlant.value
    : rows.value.reduce((s, r) => s + Number(r.total_plant || 0), 0),
}))

function formatRai(val) {
  const n = Number(val)
  if (!Number.isFinite(n)) return '-'
  return n.toLocaleString('th-TH', { maximumFractionDigits: 2 })
}

function formatNumber(val) {
  const n = Number(val)
  if (!Number.isFinite(n)) return '-'
  return n.toLocaleString('th-TH')
}

// สีสัญลักษณ์ระดับฝน 24 ชม.
function rainBadgeStyle(rain) {
  if (rain === null || rain === undefined) return { bg: '#e9ecef', text: '#6c757d', label: 'N/A' }
  if (rain >= 35) return { bg: '#dc3545', text: '#fff', label: `${rain.toFixed(1)}` }
  if (rain >= 20) return { bg: '#fd7e14', text: '#fff', label: `${rain.toFixed(1)}` }
  if (rain >= 10) return { bg: '#ffc107', text: '#333', label: `${rain.toFixed(1)}` }
  if (rain > 0) return { bg: '#198754', text: '#fff', label: `${rain.toFixed(1)}` }
  return { bg: '#0dcaf0', text: '#333', label: '0.0' }
}

function riskBadgeStyle(level) {
  switch (level) {
    case 'critical': return { bg: '#dc3545', text: '#fff', label: 'วิกฤต' }
    case 'high': return { bg: '#fd7e14', text: '#fff', label: 'เสี่ยงสูง' }
    case 'watch': return { bg: '#ffc107', text: '#333', label: 'เฝ้าระวัง' }
    default: return { bg: '#198754', text: '#fff', label: 'ปกติ' }
  }
}
</script>

<template>
  <section class="card overflow-hidden relative flex min-h-0 min-w-0 flex-col p-3" aria-labelledby="noneproduce-heading"
    :aria-busy="loading">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
      <h2 id="noneproduce-heading" class="text-[14px] font-semibold text-ink flex items-center gap-1.5 shrink-0">
        <span>🌾</span>
        <span>พื้นที่เกษตรยังไม่เก็บเกี่ยว</span>
      </h2>

      <!-- Date filter with quick presets -->
      <div class="flex flex-wrap items-center gap-1.5 min-w-0 max-w-full">
        <label for="noneproduce-date"
          class="text-[11px] text-muted whitespace-nowrap shrink-0">วันที่คาดว่าเกิดภัย</label>

        <!-- Quick date presets -->
        <div class="inline-flex rounded border border-edge bg-page p-0.5 text-[11px]">
          <button
            v-for="preset in quickDatePresets"
            :key="preset.days"
            type="button"
            :class="[
              'px-1.5 py-0.5 rounded transition-all cursor-pointer text-[10px] font-medium leading-none',
              date === getRelativeDate(preset.days)
                ? 'bg-pdm-green-deep text-white font-semibold shadow-xs'
                : 'text-muted hover:text-ink hover:bg-white/60'
            ]"
            @click="selectDatePreset(preset.days)"
          >
            {{ preset.label }}
          </button>
        </div>

        <input id="noneproduce-date" :value="date" type="date"
          class="min-w-0 max-w-full border border-edge rounded px-2 py-0.5 text-[12px] text-ink bg-white focus:outline-none focus:ring-1 focus:ring-pdm-green-deep cursor-pointer"
          @change="emit('update:date', $event.target.value)" />
      </div>
    </div>

    <p v-if="error" class="mb-2 rounded border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
      {{ error }}
    </p>
    <!-- ข้อมูลไม่มี -->
    <p v-if="!data.length && !loading && !error" class="text-center text-xs text-muted py-6">
      ไม่พบข้อมูล — กรุณาตรวจสอบวันที่หรือการเชื่อมต่อ API
    </p>

    <template v-if="rows.length > 0 || sumAllFarmers !== null || sumAllPlant !== null">
      <!-- Summary chips & Display Mode Toggle -->
      <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
        <p class="inline-flex w-fit rounded bg-pdm-green-deep px-2.5 py-1 text-xs font-bold text-white">
          {{ tableScopeName }}
        </p>

        <!-- Mode Toggle: Actual Rain vs Forecast Rain -->
        <div class="flex items-center gap-1 bg-page p-0.5 rounded border border-edge text-[11px]">
          <button
            type="button"
            :class="[
              'px-2 py-0.5 rounded font-medium transition-all cursor-pointer',
              rainDisplayMode === 'forecast' ? 'bg-pdm-green-deep text-white font-semibold shadow-xs' : 'text-muted hover:text-ink'
            ]"
            @click="setRainDisplayMode('forecast')"
          >
            ⚡ คาดการณ์ความเสี่ยง
          </button>
          <button
            type="button"
            :class="[
              'px-2 py-0.5 rounded font-medium transition-all cursor-pointer',
              rainDisplayMode === 'actual' ? 'bg-pdm-green-deep text-white font-semibold shadow-xs' : 'text-muted hover:text-ink'
            ]"
            @click="setRainDisplayMode('actual')"
          >
            🌧️ ฝนสะสมจริง
          </button>
        </div>
      </div>

      <div class="flex flex-wrap gap-2 mb-3">
        <div class="flex items-center gap-1.5 rounded-lg border border-edge bg-page px-3 py-1.5">
          <span class="text-[20px]">👨‍🌾</span>
          <div>
            <p class="text-[10px] text-muted leading-none">เกษตรกรทั้งหมด</p>
            <p class="text-[15px] font-bold text-ink">{{ formatNumber(totals.farmers) }} ราย</p>
          </div>
        </div>
        <div class="flex items-center gap-1.5 rounded-lg border border-edge bg-page px-3 py-1.5">
          <span class="text-[20px]">🗺️</span>
          <div>
            <p class="text-[10px] text-muted leading-none">พื้นที่รวม</p>
            <p class="text-[15px] font-bold text-ink">{{ formatRai(totals.plant) }} ไร่</p>
          </div>
        </div>
      </div>

      <!-- Table -->
      <p v-if="!rows.length" class="py-4 text-center text-xs text-muted">
        ไม่พบข้อมูลใน polygon ที่เลือก
      </p>
      <div v-else class="min-h-0 flex-1 overflow-auto rounded-lg border border-edge">
        <table class="w-full text-[12px] text-left">
          <thead>
            <tr class="bg-pdm-green-deep text-white">
              <th class="sticky top-0 z-10 bg-pdm-green-deep px-3 py-2 font-semibold text-center w-10">#</th>
              
              <!-- ชื่อจังหวัด / อำเภอ / ตำบล -->
              <th
                class="sticky top-0 z-10 bg-pdm-green-deep px-3 py-2 font-semibold cursor-pointer select-none transition-colors hover:bg-[#184a32]"
                :title="`คลิกเพื่อเรียงตามชื่อ${levelLabels[level] || levelLabels.province}`"
                @click="toggleSort('name')"
              >
                <div class="flex items-center justify-between gap-1">
                  <span>{{ levelLabels[level] || levelLabels.province }}</span>
                  <span class="text-xs" :class="sortState.key === 'name' ? 'text-white' : 'text-white/40'">
                    {{ sortState.key === 'name' ? (sortState.order === 'asc' ? '▲' : '▼') : '↕' }}
                  </span>
                </div>
              </th>

              <!-- เกษตรกร (ราย) -->
              <th
                class="sticky top-0 z-10 bg-pdm-green-deep px-3 py-2 font-semibold text-right cursor-pointer select-none transition-colors hover:bg-[#184a32]"
                title="คลิกเพื่อเรียงตามจำนวนเกษตรกร"
                @click="toggleSort('farmers')"
              >
                <div class="flex items-center justify-end gap-1">
                  <span>เกษตรกร (ราย)</span>
                  <span class="text-xs" :class="sortState.key === 'farmers' ? 'text-white' : 'text-white/40'">
                    {{ sortState.key === 'farmers' ? (sortState.order === 'asc' ? '▲' : '▼') : '↕' }}
                  </span>
                </div>
              </th>

              <!-- พื้นที่ (ไร่) -->
              <th
                class="sticky top-0 z-10 bg-pdm-green-deep px-3 py-2 font-semibold text-right cursor-pointer select-none transition-colors hover:bg-[#184a32]"
                title="คลิกเพื่อเรียงตามพื้นที่"
                @click="toggleSort('plant')"
              >
                <div class="flex items-center justify-end gap-1">
                  <span>พื้นที่ (ไร่)</span>
                  <span class="text-xs" :class="sortState.key === 'plant' ? 'text-white' : 'text-white/40'">
                    {{ sortState.key === 'plant' ? (sortState.order === 'asc' ? '▲' : '▼') : '↕' }}
                  </span>
                </div>
              </th>

              <!-- คาดการณ์ความเสี่ยง / ฝนสะสม -->
              <th
                class="sticky top-0 z-10 bg-pdm-green-deep px-3 py-2 font-semibold text-center cursor-pointer select-none transition-colors hover:bg-[#184a32]"
                :title="rainDisplayMode === 'forecast' ? 'คลิกเพื่อเรียงตามระดับความเสี่ยง' : 'คลิกเพื่อเรียงตามปริมาณฝน'"
                @click="toggleSort('risk')"
              >
                <div class="flex items-center justify-center gap-1">
                  <span v-if="rainDisplayMode === 'forecast'">คาดการณ์ฝน & ความเสี่ยง</span>
                  <span v-else>ฝนเฉลี่ย {{ rainfallPeriodLabel }} (มม.)</span>
                  <span class="text-xs" :class="sortState.key === 'risk' ? 'text-white' : 'text-white/40'">
                    {{ sortState.key === 'risk' ? (sortState.order === 'asc' ? '▲' : '▼') : '↕' }}
                  </span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, idx) in rows"
              :key="[row.province_code, row.amphur_code, row.tambon_code, row.area_code].filter(Boolean).join('-') || idx"
              :class="idx % 2 === 0 ? 'bg-white' : 'bg-page/60'"
              class="border-t border-edge hover:bg-pdm-green-deep/5 transition-colors">
              <td class="px-3 py-2 text-muted font-mono">{{ idx + 1 }}</td>
              <td class="px-3 py-2 font-medium text-ink">{{ row.area_name }}</td>
              <td class="px-3 py-2 text-right text-ink">{{ formatNumber(row.total_farmers) }}</td>
              <td class="px-3 py-2 text-right text-ink">{{ formatRai(row.total_plant) }}</td>
              
              <!-- Forecast Mode Column -->
              <td v-if="rainDisplayMode === 'forecast'" class="px-3 py-2 text-center">
                <div v-if="row.risk_level" class="flex items-center justify-center gap-1.5">
                  <span class="text-[11px] font-medium text-ink">{{ row.forecast_rain }} มม.</span>
                  <span
                    class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold"
                    :style="{ backgroundColor: riskBadgeStyle(row.risk_level).bg, color: riskBadgeStyle(row.risk_level).text }"
                    :title="`ระดับความเสี่ยง: ${riskBadgeStyle(row.risk_level).label}`"
                  >
                    {{ riskBadgeStyle(row.risk_level).label }}
                  </span>
                </div>
                <span v-else class="text-muted">-</span>
              </td>

              <!-- Actual Rain Mode Column -->
              <td v-else class="px-3 py-2 text-center">
                <span v-if="row.avg_rain !== null" class="inline-block rounded px-1.5 py-0.5 text-[11px] font-semibold"
                  :style="{ backgroundColor: rainBadgeStyle(row.avg_rain).bg, color: rainBadgeStyle(row.avg_rain).text }">
                  {{ rainBadgeStyle(row.avg_rain).label }} มม.
                </span>
                <span v-else class="text-muted">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Legend ฝน / ความเสี่ยง -->
      <div v-if="rainDisplayMode === 'forecast'" class="mt-3 flex flex-wrap gap-2 text-[10px] text-muted border-t border-edge pt-2">
        <span class="font-medium text-ink">ระดับความเสี่ยงคาดการณ์:</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded inline-block" style="background:#dc3545"></span>วิกฤต</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded inline-block" style="background:#fd7e14"></span>เสี่ยงสูง</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded inline-block" style="background:#ffc107"></span>เฝ้าระวัง</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded inline-block" style="background:#198754"></span>ปกติ</span>
      </div>
      <div v-else class="mt-3 flex flex-wrap gap-2 text-[10px] text-muted border-t border-edge pt-2">
        <span class="font-medium text-ink">ระดับฝนเฉลี่ย {{ rainfallPeriodLabel }}:</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded inline-block"
            style="background:#0dcaf0"></span>0 มม.</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded inline-block"
            style="background:#198754"></span>&lt;10 มม.</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded inline-block"
            style="background:#ffc107"></span>10-20 มม.</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded inline-block"
            style="background:#fd7e14"></span>20-35 มม.</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded inline-block"
            style="background:#dc3545"></span>≥35 มม.</span>
      </div>

      <p class="mt-1 text-[10px] text-muted">
        แหล่งข้อมูล:
        <a href="https://efarmer.doae.go.th" target="_blank" rel="noreferrer"
          class="hover:underline">efarmer.doae.go.th</a>
        {{ `ข้อมูล ณ วันที่ ${new Date(Date.now() - 86400000).toLocaleDateString('th-TH')}` }}
        <!-- ·
        <a href="https://riskmap.doae.go.th" target="_blank" rel="noreferrer"
          class="hover:underline">riskmap.doae.go.th</a> -->
      </p>
    </template>

    <div v-if="loading"
      class="absolute inset-0 z-20 flex items-center justify-center rounded-[inherit] bg-white/75 backdrop-blur-[1px]"
      role="status" aria-live="polite">
      <div class="flex items-center gap-3 rounded-lg border border-edge bg-white px-4 py-3 text-sm text-ink shadow-md">
        <span class="h-5 w-5 animate-spin rounded-full border-2 border-pdm-green-deep border-t-transparent"
          aria-hidden="true"></span>
        <span>กำลังโหลดข้อมูลพื้นที่...</span>
      </div>
    </div>
  </section>
</template>
