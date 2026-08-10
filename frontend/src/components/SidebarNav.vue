<script setup>
import { ref } from 'vue'

const items = [
  { n: 1, icon: '🏠', title: 'หน้าแรก', subtitle: 'ภาพรวมสถานการณ์', color: 'var(--pdm-orange)' },
  { n: 2, icon: '🗺', title: 'ระบบแจ้งเตือนภัย', subtitle: 'และชี้เป้าหมาย', color: 'var(--pdm-blue)' },
  { n: 3, icon: '📊', title: 'แดชบอร์ดติดตาม', subtitle: 'สถานการณ์และการช่วยเหลือ', color: 'var(--pdm-green)' },
  { n: 4, icon: '📚', title: 'คลังสื่อความรู้', subtitle: 'และทางเลือกการเกษตร', color: 'var(--pdm-orange)' },
  { n: 5, icon: '🧑\u200d💼', title: 'ระบบสนับสนุน', subtitle: 'เจ้าหน้าที่', color: 'var(--pdm-purple)' },
]

const active = ref(1)
</script>

<template>
  <nav class="side-nav">
    <button
      v-for="item in items"
      :key="item.n"
      class="nav-item"
      :class="{ active: active === item.n }"
      @click="active = item.n"
    >
      <span class="num" :style="{ background: item.color }">{{ item.n }}</span>
      <span class="icon">{{ item.icon }}</span>
      <span class="text">
        <strong>{{ item.title }}</strong>
        <small>{{ item.subtitle }}</small>
      </span>
    </button>
  </nav>
</template>

<style scoped>
.side-nav {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--bg-card);
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-md);
  padding: 12px;
  cursor: pointer;
  text-align: left;
  transition: box-shadow .15s, border-color .15s;
}
.nav-item:hover { box-shadow: var(--shadow-card); }
.nav-item.active { border-color: var(--pdm-green); background: #f2f9f4; }
.num {
  width: 22px; height: 22px;
  border-radius: 50%;
  color: #fff; font-size: 11px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.icon { font-size: 18px; }
.text { display: flex; flex-direction: column; line-height: 1.3; }
.text strong { font-size: 13px; }
.text small { font-size: 11px; color: var(--text-muted); }
</style>
