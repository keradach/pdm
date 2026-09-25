<script setup>
import { ref, computed } from 'vue'

import floodImg from '@/assets/flood_69.png'
import droughtImg from '@/assets/drought_69.png'
import pestsImg from '@/assets/pests_69.png'

const props = defineProps({
  // [{ name_th, damaged_area_rai, ... }]
  provinces: { type: Array, default: () => [] },
})

// ---- ตารางประเภทภัย: รูปจาก assets + สีประจำภัย ----
const DISASTER_TYPES = [
  {
    key: 'flood',
    label: 'อุทกภัย',
    icon: '🌊',
    img: floodImg,
    color: '#2f6fb0',
  },
  {
    key: 'drought',
    label: 'ภัยแล้ง',
    icon: '☀',
    img: droughtImg,
    color: '#e8770f',
  },
  {
    key: 'pest',
    label: 'ศัตรูพืช',
    icon: '🐛',
    img: pestsImg,
    color: '#2e7d4f',
  },
  {
    key: 'windstorm',
    label: 'วาตภัย',
    icon: '🌪',
    img: droughtImg,
    color: '#f0b429',
  },
  {
    key: 'rainless',
    label: 'ฝนแล้ง',
    icon: '☀',
    img: droughtImg,
    color: '#3f51b5',
  },
  {
    key: 'mudslide',
    label: 'ดินโคลนถล่ม',
    icon: '🌋',
    img: droughtImg,
    color: '#8b4513',
  },
  {
    key: 'conflagration',
    label: 'อัคคีภัย',
    icon: '🔥',
    img: droughtImg,
    color: '#d1352b',
  },

]

const activeKey = ref('flood')

const active = computed(() =>
  DISASTER_TYPES.find((d) => d.key === activeKey.value) || DISASTER_TYPES[0]
)

// จังหวัด 10 อันดับพื้นที่เสียหายสูงสุด (จากข้อมูลที่ส่งเข้ามา)
const topProvinces = computed(() => props.provinces.slice(0, 10))
</script>

<template>
  <div class="card overflow-hidden">
    <div class="card-header bg-pdm-green-deep">
      <span class="text-base">🛰</span>
      <span class="text-[15px]">สถานการณ์ภัยพิบัติด้านพืช</span>
    </div>

    <!-- TabBar เลือกประเภทภัย -->
    <div class="flex gap-2 p-[12px_14px] border-b border-edge flex-wrap" role="tablist">
      <button v-for="d in DISASTER_TYPES" :key="d.key" class="flex-1 min-w-[120px] p-[9px_12px] border border-edge rounded-[10px] bg-[#fafcfa] text-ink font-body text-[13px] font-semibold cursor-pointer inline-flex items-center justify-center gap-1.5 hover:border-pdm-green transition-colors" :class="d.key === activeKey ? 'text-white' : ''"
        :style="d.key === activeKey ? { background: d.color, borderColor: d.color } : {}" role="tab"
        :aria-selected="d.key === activeKey" @click="activeKey = d.key">
        <span class="text-[15px]">{{ d.icon }}</span>
        {{ d.label }}
      </button>
    </div>

    <!-- เนื้อหาการ์ดตามประเภทภัยที่เลือก -->
    <div class="p-[14px]">
      <!-- รูปจาก assets ตามชื่อไฟล์ -->
      <img :src="active.img" :alt="active.label" class="w-full h-auto rounded-[10px] block border border-edge" />

      <div class="flex items-center justify-between gap-2 m-[12px_0_8px] flex-wrap">
        <span class="text-[13px] font-bold" :style="{ color: active.color }">{{ active.icon }} {{ active.label }}</span>
        <span class="text-xs text-muted">10 จังหวัด พื้นที่เสียหายสูงสุด</span>
      </div>

      <!-- รายการจังหวัด -->
      <ul v-if="topProvinces.length" class="list-none m-0 p-0 max-h-[260px] overflow-y-auto">
        <li v-for="(p, i) in topProvinces" :key="p.name_th" class="flex items-center gap-[10px] p-[7px_6px] border-b border-dashed border-edge text-[13px] last:border-0">
          <span class="w-[22px] h-[22px] rounded-full text-white text-xs font-bold inline-flex items-center justify-center shrink-0" :style="{ background: active.color }">{{ i + 1 }}</span>
          <span class="flex-1 font-medium">{{ p.name_th }}</span>
          <span class="font-bold tabular-nums">{{ Number(p.damaged_area_rai).toLocaleString() }}</span>
          <span class="text-muted text-xs">ไร่</span>
        </li>
      </ul>
      <p v-else class="text-muted text-[13px] text-center p-[24px_0]">ยังไม่มีข้อมูล</p>
    </div>
  </div>
</template>
