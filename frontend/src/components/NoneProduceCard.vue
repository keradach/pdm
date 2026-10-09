<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  /** ข้อมูลจาก store.noneProduceData */
  data: { type: Array, default: () => [] },
  /** วันที่เกิดภัย (YYYY-MM-DD) ที่ store ใช้อยู่ */
  date: { type: String, default: '2026-09-25' },
  /** ปริมาณน้ำฝนเฉลี่ย 24 ชม. (riskmap) — ใช้สำหรับ join กับ noneProduce */
  rainAverageData: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:date'])

// ใช้ local date เพื่อ two-way binding กับ date picker
// const localDate = ref(props.date)
const localDate = ref("2026-09-25");
// watch(() => props.date, (v) => { localDate.value = v })

function onDateChange() {
  emit('update:date', localDate.value)
}

const normalizeAdminCode = (code) => String(code ?? '').replace(/^TH/i, '').padStart(2, '0')

// สร้าง Map จาก admin_code → avg_rain_24h เพื่อ join ได้เร็ว
const rainMap = computed(() => {
  const m = new Map()
  props.rainAverageData.forEach(r => m.set(normalizeAdminCode(r.admin_code), r.avg_rain_24h))
  return m
})

// รายการ sort ตาม total_plant มากสุดก่อน + แนบข้อมูลฝน
const rows = computed(() =>
  [...props.data]
    .sort((a, b) => Number(b.total_plant) - Number(a.total_plant))
    .map(item => ({
      ...item,
      avg_rain_24h: rainMap.value.get(normalizeAdminCode(item.province_code)) ?? null,
    }))
)

// สรุปรวมประเทศ
const totals = computed(() => ({
  farmers: rows.value.reduce((s, r) => s + Number(r.total_farmers || 0), 0),
  plant: rows.value.reduce((s, r) => s + Number(r.total_plant || 0), 0),
}))

// แสดง top-N rows และ toggle all
const showAll = ref(false)
const TOP = 10
const displayRows = computed(() => showAll.value ? rows.value : rows.value.slice(0, TOP))

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
</script>

<template>
  <section class="card min-w-0 p-3" aria-labelledby="noneproduce-heading">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
      <h2 id="noneproduce-heading" class="text-[14px] font-semibold text-ink flex items-center gap-1.5">
        <span>🌾</span>
        <span>พื้นที่เกษตรยังไม่เก็บเกี่ยว</span>
      </h2>

      <!-- Date picker -->
      <div class="flex items-center gap-2">
        <label for="noneproduce-date" class="text-[11px] text-muted whitespace-nowrap">วันที่คาดว่าเกิดภัย</label>
        <input id="noneproduce-date" v-model="localDate" type="date"
          class="border border-edge rounded px-2 py-0.5 text-[12px] text-ink bg-white focus:outline-none focus:ring-1 focus:ring-pdm-green-deep"
          @change="onDateChange" />
      </div>
    </div>

    <!-- ข้อมูลไม่มี -->
    <p v-if="!data.length" class="text-center text-xs text-muted py-6">
      ไม่พบข้อมูล — กรุณาตรวจสอบวันที่หรือการเชื่อมต่อ API
    </p>

    <template v-else>
      <!-- Summary chips -->
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
      <div class="overflow-x-auto rounded-lg border border-edge">
        <table class="w-full text-[12px] text-left">
          <thead>
            <tr class="bg-pdm-green-deep text-white">
              <th class="px-3 py-2 font-semibold">#</th>
              <th class="px-3 py-2 font-semibold">จังหวัด</th>
              <th class="px-3 py-2 font-semibold text-right">เกษตรกร (ราย)</th>
              <th class="px-3 py-2 font-semibold text-right">พื้นที่ (ไร่)</th>
              <th class="px-3 py-2 font-semibold text-center">ฝน 24 ชม. (มม.)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, idx) in displayRows" :key="row.province_code"
              :class="idx % 2 === 0 ? 'bg-white' : 'bg-page/60'"
              class="border-t border-edge hover:bg-pdm-green-deep/5 transition-colors">
              <td class="px-3 py-2 text-muted font-mono">{{ idx + 1 }}</td>
              <td class="px-3 py-2 font-medium text-ink">{{ row.province_name }}</td>
              <td class="px-3 py-2 text-right text-ink">{{ formatNumber(row.total_farmers) }}</td>
              <td class="px-3 py-2 text-right text-ink">{{ formatRai(row.total_plant) }}</td>
              <td class="px-3 py-2 text-center">
                <span v-if="row.avg_rain_24h !== null"
                  class="inline-block rounded px-1.5 py-0.5 text-[11px] font-semibold"
                  :style="{ backgroundColor: rainBadgeStyle(row.avg_rain_24h).bg, color: rainBadgeStyle(row.avg_rain_24h).text }">
                  {{ rainBadgeStyle(row.avg_rain_24h).label }}
                </span>
                <span v-else class="text-muted">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Show more / less -->
      <div v-if="rows.length > TOP" class="mt-2 text-center">
        <button class="text-[12px] text-pdm-green-deep hover:underline cursor-pointer" @click="showAll = !showAll">
          {{ showAll ? `ซ่อน (แสดง ${TOP} แรก)` : `ดูทั้งหมด ${rows.length} จังหวัด` }}
        </button>
      </div>

      <!-- Legend ฝน -->
      <div class="mt-3 flex flex-wrap gap-2 text-[10px] text-muted border-t border-edge pt-2">
        <span class="font-medium text-ink">ระดับฝน 24 ชม.:</span>
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
        ·
        <a href="https://riskmap.doae.go.th" target="_blank" rel="noreferrer"
          class="hover:underline">riskmap.doae.go.th</a>
      </p>
    </template>
  </section>
</template>
