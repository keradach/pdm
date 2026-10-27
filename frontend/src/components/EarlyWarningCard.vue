<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  forecastData: {
    type: Object,
    default: null,
  },
  horizon: {
    type: String,
    default: '3d', // '1d', '3d', '7d'
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['set-horizon', 'select-province'])

// ควบคุมการแสดงแผงวิธีประเมินความเสี่ยงและมาตรวัด (Methodology Drawer)
const showMethodology = ref(false)

const summary = computed(() => props.forecastData?.summary || {
  critical_provinces: 0,
  high_provinces: 0,
  watch_provinces: 0,
  normal_provinces: 0,
  total_at_risk_rai: 0,
  total_at_risk_farmers: 0,
  peak_risk_window: '-',
})

const topAlerts = computed(() => props.forecastData?.top_alerts || [])

const currentLevelName = computed(() => {
  const lvl = props.forecastData?.level || 'province'
  if (lvl === 'subdistrict') return 'ตำบล'
  if (lvl === 'district') return 'อำเภอ'
  return 'จังหวัด'
})

const horizonButtons = [
  { key: '1d', label: '24 ชั่วโมง' },
  { key: '3d', label: '3 วัน' },
  { key: '7d', label: '7 วัน' },
]

const horizonText = computed(() => {
  switch (props.horizon) {
    case '1d': return '24 ชั่วโมง'
    case '7d': return '7 วัน'
    default: return '3 วัน (72 ชม.)'
  }
})

// เกณฑ์วัดตามช่วงเวลาพยากรณ์และระดับพื้นที่ (จังหวัด/อำเภอ/ตำบล)
const activeHorizonThresholds = computed(() => {
  const lvl = props.forecastData?.level || 'province'

  // เกณฑ์พื้นที่พืชยังไม่เก็บเกี่ยวตามระดับการบริหารจัดการ
  const plantCrit = lvl === 'subdistrict' ? '800 ไร่' : (lvl === 'district' ? '4,000 ไร่' : '25,000 ไร่')
  const plantHigh = lvl === 'subdistrict' ? '400 ไร่' : (lvl === 'district' ? '2,500 ไร่' : '20,000 ไร่')

  // เกณฑ์ปริมาณฝนสะสม (มม.) ปรับตามขนาดพื้นที่รับน้ำและช่วงเวลา
  let critRain = 85
  let highRain = 50
  let watchRain = 25

  if (lvl === 'subdistrict') {
    critRain = props.horizon === '1d' ? 50 : (props.horizon === '7d' ? 95 : 65)
    highRain = props.horizon === '1d' ? 30 : (props.horizon === '7d' ? 60 : 38)
    watchRain = props.horizon === '1d' ? 15 : (props.horizon === '7d' ? 30 : 18)
  } else if (lvl === 'district') {
    critRain = props.horizon === '1d' ? 55 : (props.horizon === '7d' ? 105 : 75)
    highRain = props.horizon === '1d' ? 35 : (props.horizon === '7d' ? 68 : 45)
    watchRain = props.horizon === '1d' ? 18 : (props.horizon === '7d' ? 35 : 22)
  } else {
    critRain = props.horizon === '1d' ? 65 : (props.horizon === '7d' ? 120 : 85)
    highRain = props.horizon === '1d' ? 40 : (props.horizon === '7d' ? 75 : 50)
    watchRain = props.horizon === '1d' ? 20 : (props.horizon === '7d' ? 40 : 25)
  }

  return {
    critical: {
      rain: `≥ ${critRain} มม. (หรือ ≥ ${highRain} มม. ร่วมกับพืชยังไม่เก็บเกี่ยว ≥ ${plantCrit})`,
      levelDesc: 'เสี่ยงน้ำท่วมขังแปลงพืชสูงมาก',
      impact: 'เสี่ยงน้ำป่าไหลหลากและน้ำท่วมขังฉับพลัน แปลงพืชเสี่ยงจมน้ำเสียหายทันที',
      action: 'เฝ้าระวังน้ำท่วมขัง เร่งเสริมคันกั้นน้ำ และเตรียมเครื่องสูบน้ำระบายออกจากแปลง',
    },
    high: {
      rain: `≥ ${highRain} มม. (หรือ ≥ ${watchRain} มม. ร่วมกับพืชยังไม่เก็บเกี่ยว ≥ ${plantHigh})`,
      levelDesc: 'เฝ้าระวังน้ำหลากท่วมแปลงในที่ลุ่มต่ำ',
      impact: 'แปลงพืชในที่ลุ่มต่ำริมแม่น้ำลำคลองเสี่ยงได้รับความเสียหายจากน้ำหลาก',
      action: 'เตรียมความพร้อมรับมือฝนสะสม ตรวจสอบทางระบายน้ำ และย้ายผลผลิตสู่ที่สูง',
    },
    watch: {
      rain: `≥ ${watchRain} มม.`,
      levelDesc: 'ติดตามสภาพอากาศและน้ำท่วมขังเฉพาะจุด',
      impact: 'มีฝนตกปานกลางถึงหนักเป็นระยะ อาจมีน้ำท่วมขังชั่วคราวในพื้นที่ระบายน้ำช้า',
      action: 'ติดตามข่าวสารพยากรณ์อากาศอย่างใกล้ชิด และสำรวจแปลงพืชหลังฝนตก',
    },
    normal: {
      rain: `< ${watchRain} มม.`,
      levelDesc: 'ความเสี่ยงต่ำ สภาพอากาศปกติ',
      impact: 'ไม่มีผลกระทบรุนแรงต่อแปลงพืช',
      action: 'สภาพอากาศปกติ สามารถทำการเพาะปลูกและใส่ปุ๋ยบำรุงตามรอบปกติ',
    },
  }
})

function formatNumber(val) {
  const n = Number(val)
  if (!Number.isFinite(n)) return '-'
  return n.toLocaleString('th-TH')
}

function getRiskBadge(level) {
  switch (level) {
    case 'critical':
      return { label: 'วิกฤต', bg: 'bg-red-600', text: 'text-white' }
    case 'high':
      return { label: 'เสี่ยงสูง', bg: 'bg-orange-500', text: 'text-white' }
    case 'watch':
      return { label: 'เฝ้าระวัง', bg: 'bg-amber-400', text: 'text-gray-900' }
    default:
      return { label: 'ปกติ', bg: 'bg-emerald-600', text: 'text-white' }
  }
}
</script>

<template>
  <div class="card p-3 rounded-lg border border-edge bg-white shadow-sm flex flex-col gap-3 min-w-0">
    <!-- Header with Live Pulse and Horizon Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-edge/60 pb-2.5">
      <div class="flex items-center gap-2 min-w-0">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
        </span>
        <div>
          <h2 class="text-[14px] font-bold text-ink flex items-center gap-1.5 leading-tight">
            <span>🚨 ศูนย์คาดการณ์และเตือนภัยพิบัติด้านพืชล่วงหน้า</span>
          </h2>
          <p class="text-[11px] text-muted">
            วิเคราะห์ฝนคาดการณ์ร่วมกับทะเบียนพื้นที่ยังไม่เก็บเกี่ยว ({{ props.forecastData?.target_range || 'ล่วงหน้า' }})
          </p>
        </div>
      </div>

      <!-- Actions: Time Horizon Selector & Methodology Toggle -->
      <div class="flex flex-wrap items-center gap-1.5">
        <button
          type="button"
          @click="showMethodology = !showMethodology"
          :class="[
            'flex items-center gap-1 px-2.5 py-1 rounded text-[11px] font-semibold transition border cursor-pointer',
            showMethodology
              ? 'bg-emerald-50 text-pdm-green-deep border-emerald-300 shadow-2xs'
              : 'bg-white text-muted hover:text-ink border-edge hover:bg-page'
          ]"
          title="คลิกเพื่อดูเกณฑ์และวิธีประเมินความเสี่ยง"
        >
          <span>ℹ️ วิธีประเมินความเสี่ยงและมาตรวัด</span>
          <span class="text-[10px]">{{ showMethodology ? '▲ ซ่อน' : '▼ ดู' }}</span>
        </button>

        <div class="flex items-center gap-1 bg-page p-1 rounded-md border border-edge text-[12px]">
          <span class="text-[11px] text-muted px-1.5 hidden sm:inline">ช่วงเวลา:</span>
          <button
            v-for="btn in horizonButtons"
            :key="btn.key"
            type="button"
            :class="[
              'px-2.5 py-1 rounded text-[12px] font-medium transition-all cursor-pointer',
              horizon === btn.key
                ? 'bg-pdm-green-deep text-white shadow-xs font-semibold'
                : 'text-muted hover:text-ink hover:bg-white'
            ]"
            @click="emit('set-horizon', btn.key)"
          >
            {{ btn.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- METHODOLOGY & RISK CRITERIA PANEL (แผงอธิบายวิธีประเมินความเสี่ยงและมาตรวัด) -->
    <div
      v-if="showMethodology"
      class="bg-gradient-to-br from-emerald-50/70 via-white to-sky-50/50 rounded-xl p-3.5 border border-emerald-200 text-xs shadow-xs flex flex-col gap-3 transition-all"
    >
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-emerald-100 pb-2">
        <div class="flex items-center gap-2">
          <span class="text-base">📐</span>
          <div>
            <h3 class="font-bold text-emerald-950 text-[13px]">
              เกณฑ์และระเบียบวิธีประเมินความเสี่ยงภัยพิบัติด้านพืช (Risk Assessment Methodology)
            </h3>
            <p class="text-[11px] text-emerald-800">
              ระบบประเมิน 2 มิติ (ฝนคาดการณ์สภาพอากาศ + ทะเบียนเกษตรกรผู้ปลูกพืช) ณ ช่วงเวลาพยากรณ์: <b class="text-emerald-900 underline">{{ horizonText }}</b>
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="showMethodology = false"
          class="text-gray-400 hover:text-gray-600 text-xs p-1 rounded hover:bg-white/80 cursor-pointer"
          title="ปิดคำอธิบาย"
        >
          ✕ ปิด
        </button>
      </div>

      <!-- 2 Input Dimensions (แหล่งข้อมูลที่ใช้) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-[11px]">
        <div class="bg-white/90 p-2.5 rounded-lg border border-sky-100 flex items-start gap-2 shadow-2xs">
          <span class="text-lg">🌧️</span>
          <div>
            <p class="font-bold text-sky-900">มิติที่ 1: สภาพอากาศและฝนสะสมคาดการณ์ (Hazard)</p>
            <p class="text-gray-600 text-[11px] mt-0.5">
              ดึงข้อมูลพยากรณ์ฝนล่วงหน้าจาก <b>Open-Meteo Weather API</b> (Precipitation Sum) พิกัด 77 จังหวัด ราย 24 ชม., 3 วัน และ 7 วัน ร่วมกับโอกาสเกิดฝนและความเร็วลม
            </p>
          </div>
        </div>

        <div class="bg-white/90 p-2.5 rounded-lg border border-emerald-100 flex items-start gap-2 shadow-2xs">
          <span class="text-lg">🌾</span>
          <div>
            <p class="font-bold text-emerald-900">มิติที่ 2: ทะเบียนเกษตรกรและพื้นที่เพาะปลูก (Exposure)</p>
            <p class="text-gray-600 text-[11px] mt-0.5">
              เชื่อมโยงข้อมูลแปลงเพาะปลูกที่ <b>ยังไม่เก็บเกี่ยว (ไร่)</b> และจำนวนเกษตรกร (ราย) จากระบบทะเบียนเกษตรกร กรมส่งเสริมการเกษตร (<b>eFarmer DOAE</b>) รายชนิดพืชเศรษฐกิจ
            </p>
          </div>
        </div>
      </div>

      <!-- 4-Tier Risk Measurement Matrix (มาตรวัดระดับความเสี่ยง 4 ระดับ) -->
      <div>
        <p class="text-[11px] font-bold text-gray-700 mb-1.5 flex items-center justify-between">
          <span>📊 มาตรวัดระดับความเสี่ยง (Risk Tiers) สำหรับระดับ{{ currentLevelName }} ช่วงเวลา {{ horizonText }}:</span>
          <span class="text-[10px] text-muted font-normal">*เกณฑ์ปรับขนาดอัตโนมัติตามระดับ{{ currentLevelName }}และช่วงเวลา</span>
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2">
          <!-- วิกฤต -->
          <div class="bg-red-50/90 border border-red-200 rounded-lg p-2.5 flex flex-col justify-between gap-1 shadow-2xs">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="font-bold text-red-900 text-xs flex items-center gap-1">
                  <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                  <span>วิกฤต (Critical)</span>
                </span>
                <span class="text-[10px] font-bold text-red-700 bg-red-100 px-1.5 py-0.5 rounded">75-100 คะแนน</span>
              </div>
              <p class="text-[11px] font-bold text-red-800 mb-1">
                {{ activeHorizonThresholds.critical.levelDesc }}
              </p>
              <div class="text-[10px] text-gray-700 space-y-0.5">
                <div><b>เกณฑ์ฝน:</b> {{ activeHorizonThresholds.critical.rain }}</div>
                <div><b>ผลกระทบ:</b> {{ activeHorizonThresholds.critical.impact }}</div>
              </div>
            </div>
            <div class="text-[10px] text-red-900 bg-white/80 rounded p-1.5 border border-red-200/60 mt-1.5">
              <b>มาตรการ:</b> {{ activeHorizonThresholds.critical.action }}
            </div>
          </div>

          <!-- เสี่ยงสูง -->
          <div class="bg-orange-50/90 border border-orange-200 rounded-lg p-2.5 flex flex-col justify-between gap-1 shadow-2xs">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="font-bold text-orange-950 text-xs flex items-center gap-1">
                  <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                  <span>เสี่ยงสูง (High)</span>
                </span>
                <span class="text-[10px] font-bold text-orange-800 bg-orange-100 px-1.5 py-0.5 rounded">50-74 คะแนน</span>
              </div>
              <p class="text-[11px] font-bold text-orange-900 mb-1">
                {{ activeHorizonThresholds.high.levelDesc }}
              </p>
              <div class="text-[10px] text-gray-700 space-y-0.5">
                <div><b>เกณฑ์ฝน:</b> {{ activeHorizonThresholds.high.rain }}</div>
                <div><b>ผลกระทบ:</b> {{ activeHorizonThresholds.high.impact }}</div>
              </div>
            </div>
            <div class="text-[10px] text-orange-900 bg-white/80 rounded p-1.5 border border-orange-200/60 mt-1.5">
              <b>มาตรการ:</b> {{ activeHorizonThresholds.high.action }}
            </div>
          </div>

          <!-- เฝ้าระวัง -->
          <div class="bg-amber-50/90 border border-amber-200 rounded-lg p-2.5 flex flex-col justify-between gap-1 shadow-2xs">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="font-bold text-amber-950 text-xs flex items-center gap-1">
                  <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                  <span>เฝ้าระวัง (Watch)</span>
                </span>
                <span class="text-[10px] font-bold text-amber-900 bg-amber-100 px-1.5 py-0.5 rounded">25-49 คะแนน</span>
              </div>
              <p class="text-[11px] font-bold text-amber-900 mb-1">
                {{ activeHorizonThresholds.watch.levelDesc }}
              </p>
              <div class="text-[10px] text-gray-700 space-y-0.5">
                <div><b>เกณฑ์ฝน:</b> {{ activeHorizonThresholds.watch.rain }}</div>
                <div><b>ผลกระทบ:</b> {{ activeHorizonThresholds.watch.impact }}</div>
              </div>
            </div>
            <div class="text-[10px] text-amber-900 bg-white/80 rounded p-1.5 border border-amber-200/60 mt-1.5">
              <b>มาตรการ:</b> {{ activeHorizonThresholds.watch.action }}
            </div>
          </div>

          <!-- ปกติ -->
          <div class="bg-emerald-50/90 border border-emerald-200 rounded-lg p-2.5 flex flex-col justify-between gap-1 shadow-2xs">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="font-bold text-emerald-950 text-xs flex items-center gap-1">
                  <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                  <span>ปกติ (Normal)</span>
                </span>
                <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded">0-24 คะแนน</span>
              </div>
              <p class="text-[11px] font-bold text-emerald-900 mb-1">
                {{ activeHorizonThresholds.normal.levelDesc }}
              </p>
              <div class="text-[10px] text-gray-700 space-y-0.5">
                <div><b>เกณฑ์ฝน:</b> {{ activeHorizonThresholds.normal.rain }}</div>
                <div><b>ผลกระทบ:</b> {{ activeHorizonThresholds.normal.impact }}</div>
              </div>
            </div>
            <div class="text-[10px] text-emerald-900 bg-white/80 rounded p-1.5 border border-emerald-200/60 mt-1.5">
              <b>มาตรการ:</b> {{ activeHorizonThresholds.normal.action }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading / Error states -->
    <div v-if="loading" class="py-4 text-center text-xs text-pdm-blue">
      กำลังประมวลผลโมเดลคาดการณ์ความเสี่ยงภัย...
    </div>
    <div v-else-if="error" class="p-2 text-xs text-red-600 bg-red-50 rounded border border-red-200">
      {{ error }}
    </div>

    <!-- Main Content -->
    <template v-else>
      <!-- Metric Cards Banner -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        <div class="rounded-lg border border-red-200 bg-red-50/70 p-2.5 flex items-center gap-2">
          <span class="text-[22px]">🛑</span>
          <div>
            <p class="text-[10px] text-red-700 font-medium leading-none">{{ currentLevelName }}เสี่ยงวิกฤต</p>
            <p class="text-[17px] font-black text-red-700 mt-1">{{ summary.critical_areas ?? summary.critical_provinces ?? 0 }} <span class="text-[11px] font-normal">แห่ง</span></p>
          </div>
        </div>

        <div class="rounded-lg border border-orange-200 bg-orange-50/70 p-2.5 flex items-center gap-2">
          <span class="text-[22px]">⚠️</span>
          <div>
            <p class="text-[10px] text-orange-800 font-medium leading-none">{{ currentLevelName }}เสี่ยงสูง</p>
            <p class="text-[17px] font-black text-orange-800 mt-1">{{ summary.high_areas ?? summary.high_provinces ?? 0 }} <span class="text-[11px] font-normal">แห่ง</span></p>
          </div>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-2.5 flex items-center gap-2">
          <span class="text-[22px]">🌾</span>
          <div>
            <p class="text-[10px] text-emerald-800 font-medium leading-none">พื้นที่พืชเสี่ยงกระทบ</p>
            <p class="text-[17px] font-black text-emerald-800 mt-1">{{ formatNumber(summary.total_at_risk_rai) }} <span class="text-[11px] font-normal">ไร่</span></p>
          </div>
        </div>

        <div class="rounded-lg border border-blue-200 bg-blue-50/60 p-2.5 flex items-center gap-2">
          <span class="text-[22px]">⏳</span>
          <div>
            <p class="text-[10px] text-blue-800 font-medium leading-none">ช่วงเฝ้าระวังสูงสุด</p>
            <p class="text-[13px] font-bold text-blue-900 mt-1">{{ summary.peak_risk_window }}</p>
          </div>
        </div>
      </div>

      <!-- Top Alerts & Actionable Insights -->
      <div v-if="topAlerts.length > 0" class="flex flex-col gap-2">
        <div class="flex items-center justify-between text-[12px] font-semibold text-ink">
          <span>⚡ พื้นที่เฝ้าระวังด่วนที่สุด 5 อันดับแรก</span>
          <span class="text-[11px] text-muted font-normal">คลิกเลือกพื้นที่เพื่อซูมแผนที่</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
          <div
            v-for="(alert, idx) in topAlerts.slice(0, 3)"
            :key="alert.id || idx"
            class="rounded-lg border border-edge bg-page/50 p-2.5 hover:bg-page hover:border-pdm-green-deep transition-all cursor-pointer flex flex-col justify-between gap-1.5 shadow-2xs"
            @click="emit('select-province', alert.id)"
          >
            <div class="flex items-start justify-between gap-1.5">
              <div>
                <span class="font-bold text-[13px] text-ink">
                  {{ alert.area_name ? ((alert.level === 'subdistrict' ? 'ต.' : (alert.level === 'district' ? 'อ.' : 'จ.')) + alert.area_name) : alert.province_name }}
                </span>
                <span class="ml-1.5 text-[11px] text-muted">({{ alert.primary_crop }})</span>
              </div>
              <span
                :class="[
                  'px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider',
                  getRiskBadge(alert.risk_level).bg,
                  getRiskBadge(alert.risk_level).text
                ]"
              >
                {{ getRiskBadge(alert.risk_level).label }}
              </span>
            </div>

            <div class="text-[11px] text-muted flex items-center justify-between">
              <span>ฝนคาดการณ์: <strong class="text-ink font-semibold">{{ alert.forecast_rain }} มม.</strong></span>
              <span>พืชเสี่ยง: <strong class="text-ink font-semibold">{{ formatNumber(alert.unharvested_rai) }} ไร่</strong></span>
            </div>

            <p class="text-[11px] text-ink/80 line-clamp-2 bg-white/70 rounded p-1 border border-edge/60">
              💡 {{ alert.recommendation }}
            </p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

