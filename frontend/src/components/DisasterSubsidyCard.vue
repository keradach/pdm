<script setup>
import { computed, reactive, ref, watch } from 'vue'
import provinceBoundaries from '@/assets/th_adm1.json'
import districtBoundariesUrl from '@/assets/th_adm2.json?url'
import subdistrictBoundariesUrl from '@/assets/th_adm3.json?url'

const props = defineProps({
  data: { type: Object, default: null },
  filters: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
})

const emit = defineEmits(['search'])
const form = reactive({ ...props.filters })
const districts = ref([])
const subdistricts = ref([])
const areaOptionsLoading = ref(false)
const areaOptionsError = ref(null)
let districtsLoaded = false
let subdistrictsLoaded = false

const provinceOptions = computed(() =>
  provinceBoundaries.features
    .map(({ properties }) => ({
      code: normalizeAreaCode(properties.ADM1_PCODE, 2),
      name: properties.ADM1_TH,
    }))
    .sort((a, b) => a.name.localeCompare(b.name, 'th')),
)
const districtOptions = computed(() =>
  districts.value
    .filter((feature) =>
      normalizeAreaCode(feature.properties.ADM1_PCODE, 2) === form.province_code,
    )
    .map(({ properties }) => ({
      code: normalizeAreaCode(properties.ADM2_PCODE, 4),
      name: properties.ADM2_TH,
    }))
    .sort((a, b) => a.name.localeCompare(b.name, 'th')),
)
const subdistrictOptions = computed(() =>
  subdistricts.value
    .filter((feature) =>
      normalizeAreaCode(feature.properties.ADM2_PCODE, 4) === form.amphur_code,
    )
    .map(({ properties }) => ({
      code: normalizeAreaCode(properties.ADM3_PCODE, 6),
      name: properties.ADM3_TH,
    }))
    .sort((a, b) => a.name.localeCompare(b.name, 'th')),
)

const disasterTypes = [
  { code: '1', name: 'อุทกภัย' },
  { code: '2', name: 'ภัยแล้ง' },
  { code: '3', name: 'อัคคีภัย' },
  { code: '4', name: 'ไฟป่า/หมอกควัน' },
  { code: '5', name: 'ภัยหนาว' },
  { code: '6', name: 'ดินโคลนถล่ม' },
  { code: '7', name: 'วาตภัย' },
  { code: '8', name: 'ภัยจากช้างป่า' },
  { code: '9', name: 'ภัยอื่นๆ' },
  { code: '10', name: 'ศัตรูพืชระบาด' },
  { code: '11', name: 'ฝนทิ้งช่วง' },
  { code: '12', name: 'ภาวะฝนแล้ง' },
]


function normalizeAreaCode(value, length) {
  return String(value ?? '').replace(/^TH/i, '').padStart(length, '0')
}

async function loadAreaOptions(url, assign) {
  areaOptionsLoading.value = true
  areaOptionsError.value = null
  try {
    const response = await fetch(url)
    if (!response.ok) {
      throw new Error(`ไม่สามารถโหลดรายการพื้นที่ได้ (HTTP ${response.status})`)
    }
    const boundaries = await response.json()
    if (!Array.isArray(boundaries?.features)) {
      throw new Error('รูปแบบข้อมูลพื้นที่ไม่ถูกต้อง')
    }
    assign(boundaries.features)
  } catch (error) {
    areaOptionsError.value = 'ไม่สามารถโหลดรายการพื้นที่ได้ กรุณาลองเลือกใหม่อีกครั้ง'
    console.error('load disaster subsidy area options error:', error)
  } finally {
    areaOptionsLoading.value = false
  }
}

async function onProvinceChange() {
  form.amphur_code = null
  form.tambon_code = null
  if (!districtsLoaded) {
    await loadAreaOptions(districtBoundariesUrl, (features) => {
      districts.value = features
      districtsLoaded = true
    })
  }
}

async function onDistrictChange() {
  form.tambon_code = null
  if (!subdistrictsLoaded) {
    await loadAreaOptions(subdistrictBoundariesUrl, (features) => {
      subdistricts.value = features
      subdistrictsLoaded = true
    })
  }
}

watch(
  () => props.filters,
  (filters) => Object.assign(form, filters),
  { deep: true },
)

function submitFilters() {
  const selectedType = disasterTypes.find(
    (type) => type.code === String(form.danger_type_id ?? ''),
  )
  const filters = {
    ...form,
    danger_type_id: selectedType ? Number(selectedType.code) : null,
    danger_type_name: selectedType?.name ?? null,
    province_code: form.province_code || null,
    amphur_code: form.amphur_code || null,
    tambon_code: form.tambon_code || null,
  }
  const level = !filters.province_code
    ? 'province'
    : !filters.amphur_code
      ? 'district'
      : !filters.tambon_code
        ? 'subdistrict'
        : 'selectedSubdistrict'
  const areas = level === 'district'
    ? districtOptions.value
    : level === 'subdistrict'
      ? subdistrictOptions.value
      : []

  emit('search', {
    filters,
    areas,
    level,
  })
}

const areaLevel = computed(() => {
  if (!props.filters.province_code) return 'province'
  if (!props.filters.amphur_code) return 'district'
  if (!props.filters.tambon_code) return 'subdistrict'
  return 'selectedSubdistrict'
})

const areaLevelLabels = {
  province: 'จังหวัด',
  district: 'อำเภอ',
  subdistrict: 'ตำบล',
  selectedSubdistrict: 'ตำบล',
}

const selectedAreaName = computed(() => {
  if (areaLevel.value === 'selectedSubdistrict') {
    return subdistrictOptions.value.find(
      (area) => area.code === props.filters.tambon_code,
    )?.name
  }
  if (areaLevel.value === 'subdistrict') {
    return districtOptions.value.find(
      (area) => area.code === props.filters.amphur_code,
    )?.name
  }
  if (areaLevel.value === 'district') {
    return provinceOptions.value.find(
      (area) => area.code === props.filters.province_code,
    )?.name
  }
  return null
})

const areaRows = computed(() => {
  if (!props.data) return []

  const groupNames = {
    province: ['by_province', 'by_area', 'provinces', 'province_data', 'area_data', 'rows'],
    district: ['by_amphur', 'by_district', 'by_area', 'amphurs', 'districts', 'district_data', 'area_data', 'rows'],
    subdistrict: ['by_tambon', 'by_subdistrict', 'by_area', 'tambons', 'subdistricts', 'subdistrict_data', 'area_data', 'rows'],
  }[areaLevel.value] || []
  const sources = [props.data, props.data.summary, props.data.data].filter(Boolean)

  for (const source of sources) {
    if (Array.isArray(source)) return source
    for (const key of groupNames) {
      if (Array.isArray(source[key])) return source[key]
    }
  }

  if (areaLevel.value === 'selectedSubdistrict' && props.data.summary) {
    return [{
      area_name: selectedAreaName.value || 'ตำบลที่เลือก',
      total_farmer: props.data.summary.total_farmer,
      damage_rai: props.data.summary.damage_rai,
      damage_ngan: props.data.summary.damage_ngan,
      damage_wa: props.data.summary.damage_wa,
      damage_rai_decimal: props.data.summary.damage_rai_decimal,
      repair_rai: props.data.summary.repair_rai,
      repair_ngan: props.data.summary.repair_ngan,
      repair_wa: props.data.summary.repair_wa,
      repair_rai_decimal: props.data.summary.repair_rai_decimal,
      by_plant_group: props.data.summary.by_plant_group || [],
    }]
  }

  return []
})

const getAreaName = (row) =>
  row.area_name ??
  row.name ??
  row.province_name ??
  row.amphur_name ??
  row.district_name ??
  row.tambon_name ??
  row.subdistrict_name ??
  'ไม่ระบุ'

function formatNumber(value, fractionDigits = 0) {
  const number = Number(value)
  if (!Number.isFinite(number)) return '-'
  return number.toLocaleString('th-TH', { maximumFractionDigits: fractionDigits })
}

function formatArea(row, prefix) {
  let rai = row[`${prefix}_rai_decimal`]
  if (rai == null && row[`${prefix}_rai`] != null) {
    rai =
      Number(row[`${prefix}_rai`]) +
      Number(row[`${prefix}_ngan`] || 0) / 4 +
      Number(row[`${prefix}_wa`] || 0) / 160
  }
  return rai == null ? '-' : `${formatNumber(rai, 2)}`
}

const plantGroups = computed(() => {
  const groups = new Map([
    [1, { id: 1, name: 'ข้าว' }],
    [2, { id: 2, name: 'พืชไร่พืชผัก' }],
    [3, { id: 3, name: 'ไม้ผลไม้ยืนต้น' }],
  ])
  for (const group of props.data?.summary?.by_plant_group || []) {
    if (!groups.has(group.plant_group_id)) {
      groups.set(group.plant_group_id, {
        id: group.plant_group_id,
        name: group.plant_group_name,
      })
    }
  }
  for (const area of areaRows.value) {
    for (const group of area.by_plant_group || []) {
      if (!groups.has(group.plant_group_id)) {
        groups.set(group.plant_group_id, {
          id: group.plant_group_id,
          name: group.plant_group_name,
        })
      }
    }
  }
  return [...groups.values()]
})

const sortState = reactive({
  key: 'damage_total',
  order: 'desc',
})

function toggleSort(key) {
  if (sortState.key === key) {
    sortState.order = sortState.order === 'desc' ? 'asc' : 'desc'
  } else {
    sortState.key = key
    sortState.order = key === 'area_name' ? 'asc' : 'desc'
  }
}

function getDamageValue(row) {
  if (!row) return 0
  let rai = row.damage_rai_decimal
  if (rai == null && row.damage_rai != null) {
    rai =
      Number(row.damage_rai) +
      Number(row.damage_ngan || 0) / 4 +
      Number(row.damage_wa || 0) / 160
  }
  const num = Number(rai)
  return Number.isFinite(num) ? num : 0
}

function getRepairValue(row) {
  if (!row) return 0
  let rai = row.repair_rai_decimal
  if (rai == null && row.repair_rai != null) {
    rai =
      Number(row.repair_rai) +
      Number(row.repair_ngan || 0) / 4 +
      Number(row.repair_wa || 0) / 160
  }
  const num = Number(rai)
  return Number.isFinite(num) ? num : 0
}

function getRowSortValue(row, key) {
  if (!row) return 0
  if (key === 'area_name') {
    return getAreaName(row) || ''
  }
  if (key === 'damage_total') {
    return getDamageValue(row)
  }
  if (key === 'repair_total') {
    return getRepairValue(row)
  }
  if (key.startsWith('damage_group_')) {
    const groupId = Number(key.replace('damage_group_', ''))
    const group = (row.by_plant_group || []).find((g) => g.plant_group_id === groupId)
    return getDamageValue(group)
  }
  if (key.startsWith('repair_group_')) {
    const groupId = Number(key.replace('repair_group_', ''))
    const group = (row.by_plant_group || []).find((g) => g.plant_group_id === groupId)
    return getRepairValue(group)
  }
  return 0
}

const sortedAreaRows = computed(() => {
  const { key, order } = sortState
  return [...areaRows.value].sort((a, b) => {
    const valA = getRowSortValue(a, key)
    const valB = getRowSortValue(b, key)
    let comparison = 0
    if (typeof valA === 'string' || typeof valB === 'string') {
      comparison = String(valA).localeCompare(String(valB), 'th')
    } else {
      comparison = Number(valA || 0) - Number(valB || 0)
    }
    if (comparison !== 0) {
      return order === 'asc' ? comparison : -comparison
    }
    return (getAreaName(a) || '').localeCompare(getAreaName(b) || '', 'th')
  })
})

const tableRows = computed(() =>
  sortedAreaRows.value.map((area, index) => ({
    ...area,
    rowKey: area.area_code || area.province_code || area.amphur_code || area.tambon_code || index,
    groups: new Map(
      (area.by_plant_group || []).map((group) => [group.plant_group_id, group]),
    ),
  })),
)

const summaryRow = computed(() => {
  const summary = props.data?.summary
  if (!summary) return null
  return {
    ...summary,
    area_name: 'รวมทั้งหมด',
    groups: new Map(
      (summary.by_plant_group || []).map((group) => [group.plant_group_id, group]),
    ),
  }
})

const summaryPlantGroups = computed(() => {
  const list = props.data?.summary?.by_plant_group
  if (!Array.isArray(list) || !list.length) return []
  return list
})

const metrics = [
  { key: 'total_farmer', label: 'เกษตรกรได้รับความช่วยเหลือ', unit: 'ราย' },
  { key: 'damage', prefix: 'damage', isArea: true, label: 'พื้นที่เสียหาย', unit: 'ไร่' },
  { key: 'repair', prefix: 'repair', isArea: true, label: 'พื้นที่ฟื้นฟู/ซ่อมแซม', unit: 'ไร่' },
]

function getMetricValue(metric) {
  if (!props.data?.summary) return '-'
  if (metric.isArea) {
    return formatArea(props.data.summary, metric.prefix)
  }
  return formatNumber(props.data.summary[metric.key], metric.fractionDigits || 0)
}
</script>

<template>
  <section class="card min-w-0 p-4" aria-labelledby="disaster-subsidy-heading" :aria-busy="loading">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
      <div>
        <h2 id="disaster-subsidy-heading" class="flex items-center gap-2 text-base font-semibold text-ink">
          <span aria-hidden="true">🌱</span>
          ข้อมูลการให้ความช่วยเหลือผู้ประสบภัยด้านพืช
        </h2>
        <p class="mt-1 text-xs text-muted">สรุปเกษตรกรและพื้นที่ที่ได้รับความช่วยเหลือ</p>
      </div>
      <button type="submit" form="disaster-subsidy-filters"
        class="rounded-md bg-pdm-green-deep px-4 py-2 text-sm font-semibold text-white hover:bg-pdm-green-deep/90 disabled:cursor-wait disabled:opacity-60"
        :disabled="loading || areaOptionsLoading">
        {{ loading ? 'กำลังโหลด...' : 'ค้นหาข้อมูล' }}
      </button>
    </div>

    <form id="disaster-subsidy-filters" class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4"
      @submit.prevent="submitFilters">
      <label class="flex flex-col gap-1 text-xs text-muted">
        ตั้งแต่เดือน
        <input v-model="form.date_from" type="month" required
          class="min-w-0 rounded border border-edge bg-white px-2 py-2 text-sm text-ink focus:outline-none focus:ring-1 focus:ring-pdm-green-deep" />
      </label>
      <label class="flex flex-col gap-1 text-xs text-muted">
        ถึงเดือน
        <input v-model="form.date_to" type="month" required :min="form.date_from"
          class="min-w-0 rounded border border-edge bg-white px-2 py-2 text-sm text-ink focus:outline-none focus:ring-1 focus:ring-pdm-green-deep" />
      </label>
      <label class="flex flex-col gap-1 text-xs text-muted">
        ชื่อประเภทภัย
        <select v-model="form.danger_type_id"
          class="min-w-0 rounded border border-edge bg-white px-2 py-2 text-sm text-ink focus:outline-none focus:ring-1 focus:ring-pdm-green-deep">
          <option :value="null">ทุกประเภทภัย</option>
          <option v-for="type in disasterTypes" :key="type.code" :value="type.code">
            {{ type.name }}
          </option>
        </select>
      </label>
      <label class="flex flex-col gap-1 text-xs text-muted">
        จังหวัด
        <select v-model="form.province_code"
          class="min-w-0 rounded border border-edge bg-white px-2 py-2 text-sm text-ink focus:outline-none focus:ring-1 focus:ring-pdm-green-deep"
          @change="onProvinceChange">
          <option :value="null">ทุกจังหวัด</option>
          <option v-for="province in provinceOptions" :key="province.code" :value="province.code">
            {{ province.name }}
          </option>
        </select>
      </label>
      <label class="flex flex-col gap-1 text-xs text-muted">
        อำเภอ
        <select v-model="form.amphur_code" :disabled="!form.province_code || areaOptionsLoading"
          class="min-w-0 rounded border border-edge bg-white px-2 py-2 text-sm text-ink focus:outline-none focus:ring-1 focus:ring-pdm-green-deep disabled:cursor-not-allowed disabled:bg-gray-100"
          @change="onDistrictChange">
          <option :value="null">ทุกอำเภอ</option>
          <option v-for="district in districtOptions" :key="district.code" :value="district.code">
            {{ district.name }}
          </option>
        </select>
      </label>
      <label class="flex flex-col gap-1 text-xs text-muted">
        ตำบล
        <select v-model="form.tambon_code" :disabled="!form.amphur_code || areaOptionsLoading"
          class="min-w-0 rounded border border-edge bg-white px-2 py-2 text-sm text-ink focus:outline-none focus:ring-1 focus:ring-pdm-green-deep disabled:cursor-not-allowed disabled:bg-gray-100">
          <option :value="null">ทุกตำบล</option>
          <option v-for="subdistrict in subdistrictOptions" :key="subdistrict.code" :value="subdistrict.code">
            {{ subdistrict.name }}
          </option>
        </select>
      </label>
    </form>

    <p v-if="areaOptionsError"
      class="mb-3 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
      {{ areaOptionsError }}
    </p>
    <p v-if="error" class="mb-3 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
      {{ error }}
    </p>
    <p v-else-if="loading && !data" class="py-5 text-center text-sm text-muted">
      กำลังโหลดข้อมูล...
    </p>
    <p v-else-if="!data?.summary && !loading" class="py-5 text-center text-sm text-muted">
      ไม่พบข้อมูลตามตัวกรองที่เลือก
    </p>

    <template v-if="data?.summary">
      <div class="mb-4 grid gap-3 sm:grid-cols-3">
        <article v-for="metric in metrics" :key="metric.key" class="rounded-lg border border-edge bg-page px-3 py-3">
          <p class="text-xs text-muted">{{ metric.label }}</p>
          <p class="mt-1 text-xl font-bold text-ink">
            {{ getMetricValue(metric) }}
            <span class="text-sm font-medium text-muted">{{ metric.unit }}</span>
          </p>
        </article>
      </div>

      <div v-if="summaryPlantGroups.length" class="mb-4">
        <p class="mb-1.5 text-xs font-semibold text-muted">สรุปแยกตามกลุ่มพืช (จากข้อมูลภาพรวม)</p>
        <div class="grid gap-2 sm:grid-cols-3">
          <div v-for="group in summaryPlantGroups" :key="group.plant_group_id"
            class="rounded-lg border border-edge bg-page p-3 text-xs">
            <p class="font-bold text-ink text-sm">{{ group.plant_group_name }}</p>
            <div class="mt-2 flex items-center justify-between text-muted">
              <span>พื้นที่เสียหาย:</span>
              <span class="font-semibold text-rose-600">{{ formatArea(group, 'damage') }} ไร่</span>
            </div>
            <div class="mt-1 flex items-center justify-between text-muted">
              <span>ช่วยเหลือแล้ว:</span>
              <span class="font-semibold text-pdm-green-deep">{{ formatArea(group, 'repair') }} ไร่</span>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-2 flex flex-wrap items-center justify-between gap-2 text-sm font-semibold text-ink">
        <div>
          <span>ข้อมูลราย{{ areaLevelLabels[areaLevel] }}{{ selectedAreaName ? ` — ${selectedAreaName}` : '' }}</span>
          <span v-if="areaRows.length > 0" class="ml-1 text-xs font-normal text-muted">
            (ทั้งหมด {{ areaRows.length }} รายการ{{ areaRows.length > 20 ? ' — เลื่อนดูเพิ่มเติมได้' : '' }})
          </span>
        </div>
        <span class="text-xs font-normal text-muted">
          💡 คลิกที่หัวคอลัมน์เพื่อเรียงลำดับ
        </span>
      </div>
      <div class="max-h-[680px] overflow-auto rounded-lg border border-edge">
        <table class="w-full min-w-[1100px] border-collapse text-sm">
          <thead class="sticky top-0 z-20 bg-pdm-green-deep text-left text-white shadow-sm">
            <tr>
              <th rowspan="2"
                class="sticky left-0 top-0 z-30 cursor-pointer select-none border border-white/30 bg-pdm-green-deep px-3 py-2 font-semibold transition-colors hover:bg-[#184a32]"
                :title="`คลิกเพื่อเรียงตามชื่อ${areaLevelLabels[areaLevel]}`"
                @click="toggleSort('area_name')">
                <div class="flex items-center justify-between gap-2">
                  <span>{{ areaLevelLabels[areaLevel] }}</span>
                  <span class="text-xs" :class="sortState.key === 'area_name' ? 'text-white' : 'text-white/40'">
                    {{ sortState.key === 'area_name' ? (sortState.order === 'asc' ? '▲' : '▼') : '↕' }}
                  </span>
                </div>
              </th>
              <th rowspan="2"
                class="cursor-pointer select-none border border-white/30 bg-pdm-green-deep px-3 py-2 font-semibold transition-colors hover:bg-[#184a32]"
                title="คลิกเพื่อเรียงตามพื้นที่เสียหายทั้งหมด"
                @click="toggleSort('damage_total')">
                <div class="flex items-center justify-between gap-2">
                  <span>เสียหายทั้งหมด (ไร่)</span>
                  <span class="text-xs" :class="sortState.key === 'damage_total' ? 'text-white' : 'text-white/40'">
                    {{ sortState.key === 'damage_total' ? (sortState.order === 'asc' ? '▲' : '▼') : '↕' }}
                  </span>
                </div>
              </th>
              <th :colspan="plantGroups.length" class="border border-white/30 px-3 py-2 text-center font-semibold">
                เสียหาย (ไร่)
              </th>
              <th rowspan="2"
                class="cursor-pointer select-none border border-white/30 bg-pdm-green-deep px-3 py-2 font-semibold transition-colors hover:bg-[#184a32]"
                title="คลิกเพื่อเรียงตามพื้นที่ช่วยเหลือทั้งหมด"
                @click="toggleSort('repair_total')">
                <div class="flex items-center justify-between gap-2">
                  <span>ช่วยเหลือทั้งหมด (ไร่)</span>
                  <span class="text-xs" :class="sortState.key === 'repair_total' ? 'text-white' : 'text-white/40'">
                    {{ sortState.key === 'repair_total' ? (sortState.order === 'asc' ? '▲' : '▼') : '↕' }}
                  </span>
                </div>
              </th>
              <th :colspan="plantGroups.length"
                class="border border-white/30 bg-[#174a32] px-3 py-2 text-center font-semibold">
                ได้รับความช่วยเหลือ (ไร่)
              </th>
            </tr>
            <tr>
              <th v-for="group in plantGroups" :key="`damage-${group.id}`"
                class="min-w-[150px] cursor-pointer select-none border border-white/30 bg-pdm-green-deep px-3 py-2 text-center font-semibold transition-colors hover:bg-[#184a32]"
                :title="`คลิกเพื่อเรียงตามความเสียหาย (${group.name})`"
                @click="toggleSort(`damage_group_${group.id}`)">
                <div class="flex items-center justify-center gap-1.5">
                  <span>{{ group.name }}</span>
                  <span class="text-xs" :class="sortState.key === `damage_group_${group.id}` ? 'text-white' : 'text-white/40'">
                    {{ sortState.key === `damage_group_${group.id}` ? (sortState.order === 'asc' ? '▲' : '▼') : '↕' }}
                  </span>
                </div>
              </th>
              <th v-for="(group, index) in plantGroups" :key="`repair-${group.id}`"
                class="min-w-[150px] cursor-pointer select-none border border-white/30 bg-[#174a32] px-3 py-2 text-center font-semibold transition-colors hover:bg-[#123b28]"
                :title="`คลิกเพื่อเรียงตามการช่วยเหลือ (${group.name})`"
                @click="toggleSort(`repair_group_${group.id}`)">
                <div class="flex items-center justify-center gap-1.5">
                  <span>{{ group.name }}</span>
                  <span class="text-xs" :class="sortState.key === `repair_group_${group.id}` ? 'text-white' : 'text-white/40'">
                    {{ sortState.key === `repair_group_${group.id}` ? (sortState.order === 'asc' ? '▲' : '▼') : '↕' }}
                  </span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in tableRows" :key="row.rowKey" class="odd:bg-white even:bg-page hover:bg-pdm-green-deep/5">
              <th
                class="sticky left-0 z-10 whitespace-nowrap border border-edge bg-inherit px-3 py-2 text-left font-semibold text-ink">
                {{ getAreaName(row) }}
              </th>
              <th
                class="whitespace-nowrap border-l-2 border-l-pdm-green-deep bg-pdm-green-deep/5 px-3 py-2 text-left font-semibold text-ink">
                {{ formatArea(row, 'damage') }}
              </th>
              <td v-for="group in plantGroups" :key="`damage-${group.id}`"
                class="whitespace-nowrap border border-edge px-3 py-2 text-center text-ink">
                {{ formatArea(row.groups.get(group.id) || {}, 'damage') }}
              </td>
              <th
                class="whitespace-nowrap border-l-2 border-l-pdm-green-deep bg-pdm-green-deep/5 px-3 py-2 text-left font-semibold text-ink">
                {{ formatArea(row, 'repair') }}
              </th>
              <td v-for="(group, index) in plantGroups" :key="`repair-${group.id}`"
                class="whitespace-nowrap border border-edge px-3 py-2 text-center text-ink">
                {{ formatArea(row.groups.get(group.id) || {}, 'repair') }}
              </td>
            </tr>
            <tr v-if="!tableRows.length">
              <td :colspan="3 + plantGroups.length * 2" class="border border-edge px-3 py-4 text-center text-muted">
                ไม่พบข้อมูลราย{{ areaLevelLabels[areaLevel] }}จาก API
              </td>
            </tr>
          </tbody>
          <tfoot v-if="summaryRow" class="sticky bottom-0 z-20 border-t-2 border-pdm-green-deep bg-[#edf7f1] text-sm shadow-[0_-2px_4px_rgba(0,0,0,0.06)]">
            <tr class="font-bold text-ink">
              <th
                class="sticky left-0 bottom-0 z-30 whitespace-nowrap border border-edge bg-[#e2f0e7] px-3 py-2.5 text-left font-bold text-pdm-green-deep">
                {{ summaryRow.area_name }}
              </th>
              <th
                class="whitespace-nowrap border-l-2 border-l-pdm-green-deep bg-[#edf7f1] px-3 py-2.5 text-left font-bold text-ink">
                {{ formatArea(summaryRow, 'damage') }}
              </th>
              <td v-for="group in plantGroups" :key="`summary-damage-${group.id}`"
                class="whitespace-nowrap border border-edge bg-[#edf7f1] px-3 py-2.5 text-center font-bold text-ink">
                {{ formatArea(summaryRow.groups.get(group.id) || {}, 'damage') }}
              </td>
              <th
                class="whitespace-nowrap border-l-2 border-l-pdm-green-deep bg-[#edf7f1] px-3 py-2.5 text-left font-bold text-ink">
                {{ formatArea(summaryRow, 'repair') }}
              </th>
              <td v-for="group in plantGroups" :key="`summary-repair-${group.id}`"
                class="whitespace-nowrap border border-edge bg-[#edf7f1] px-3 py-2.5 text-center font-bold text-ink">
                {{ formatArea(summaryRow.groups.get(group.id) || {}, 'repair') }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
      <p class="mt-1 text-[10px] text-muted">
        แหล่งข้อมูล:
        <a href="https://disaster.doae.go.th" target="_blank" rel="noreferrer"
          class="hover:underline">disaster.doae.go.th</a>
        {{ `ข้อมูล ณ วันที่ ${new Date().toLocaleDateString('th-TH')}` }}
      </p>
    </template>
  </section>
</template>
