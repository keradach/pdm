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
    color: 'var(--pdm-blue)',
  },
  {
    key: 'drought',
    label: 'ภัยแล้ง',
    icon: '☀',
    img: droughtImg,
    color: 'var(--pdm-orange)',
  },
  {
    key: 'pest',
    label: 'ศัตรูพืช',
    icon: '🐛',
    img: pestsImg,
    color: 'var(--pdm-green)',
  },
  {
    key: 'windstorm',
    label: 'วาตภัย',
    icon: '🌪',
    img: droughtImg,
    color: 'var(--pdm-yellow)',
  },
  {
    key: 'rainless',
    label: 'ฝนแล้ง',
    icon: '☀',
    img: droughtImg,
    color: 'var(--pdm-indigo)',
  },
  {
    key: 'mudslide',
    label: 'ดินโคลนถล่ม',
    icon: '🌋',
    img: droughtImg,
    color: 'var(--pdm-brown)',
  },
  {
    key: 'conflagration',
    label: 'อัคคีภัย',
    icon: '🔥',
    img: droughtImg,
    color: 'var(--pdm-red)',
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
  <div class="card disaster-type-card">
    <div class="card-header disaster-header">
      <span class="header-icon">🛰</span>
      <span class="header-title">สถานการณ์ภัยพิบัติด้านพืช</span>
    </div>

    <!-- TabBar เลือกประเภทภัย -->
    <div class="tabbar" role="tablist">
      <button v-for="d in DISASTER_TYPES" :key="d.key" class="tab" :class="{ active: d.key === activeKey }"
        :style="d.key === activeKey ? { background: d.color, borderColor: d.color } : {}" role="tab"
        :aria-selected="d.key === activeKey" @click="activeKey = d.key">
        <span class="tab-icon">{{ d.icon }}</span>
        {{ d.label }}
      </button>
    </div>

    <!-- เนื้อหาการ์ดตามประเภทภัยที่เลือก -->
    <div class="card-body">
      <!-- รูปจาก assets ตามชื่อไฟล์ -->
      <img :src="active.img" :alt="active.label" class="disaster-banner" />

      <div class="type-meta">
        <span class="type-name" :style="{ color: active.color }">{{ active.icon }} {{ active.label }}</span>
        <span class="type-count">10 จังหวัด พื้นที่เสียหายสูงสุด</span>
      </div>

      <!-- รายการจังหวัด -->
      <ul v-if="topProvinces.length" class="province-list">
        <li v-for="(p, i) in topProvinces" :key="p.name_th" class="province-row">
          <span class="rank" :style="{ background: active.color }">{{ i + 1 }}</span>
          <span class="p-name">{{ p.name_th }}</span>
          <span class="p-area">{{ Number(p.damaged_area_rai).toLocaleString() }}</span>
          <span class="p-unit">ไร่</span>
        </li>
      </ul>
      <p v-else class="empty">ยังไม่มีข้อมูล</p>
    </div>
  </div>
</template>

<style scoped>
.disaster-type-card {
  overflow: hidden;
}

.disaster-header {
  background: var(--pdm-green-deep);
}

.header-icon {
  font-size: 16px;
}

.header-title {
  font-size: 15px;
}

.tabbar {
  display: flex;
  gap: 8px;
  padding: 12px 14px;
  border-bottom: 1px solid var(--border-soft);
  flex-wrap: wrap;
}

.tab {
  flex: 1;
  min-width: 120px;
  padding: 9px 12px;
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-md);
  background: #fafcfa;
  color: var(--text-main);
  font-family: var(--font-body);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.tab:hover {
  border-color: var(--pdm-green);
}

.tab.active {
  color: #fff;
}

.tab-icon {
  font-size: 15px;
}

.card-body {
  padding: 14px;
}

.disaster-banner {
  width: 100%;
  height: auto;
  border-radius: var(--radius-md);
  display: block;
  border: 1px solid var(--border-soft);
}

.type-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin: 12px 0 8px;
  flex-wrap: wrap;
}

.type-name {
  font-size: 13px;
  font-weight: 700;
}

.type-count {
  font-size: 12px;
  color: var(--text-muted);
}

.province-list {
  list-style: none;
  margin: 0;
  padding: 0;
  max-height: 260px;
  overflow-y: auto;
}

.province-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 6px;
  border-bottom: 1px dashed var(--border-soft);
  font-size: 13px;
}

.province-row:last-child {
  border-bottom: none;
}

.rank {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.p-name {
  flex: 1;
  font-weight: 500;
}

.p-area {
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.p-unit {
  color: var(--text-muted);
  font-size: 12px;
}

.empty {
  color: var(--text-muted);
  font-size: 13px;
  text-align: center;
  padding: 24px 0;
}
</style>