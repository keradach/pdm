<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const items = [
  { route: '/', title: 'หน้าแรก', subtitle: 'ภาพรวมสถานการณ์', color: 'var(--pdm-orange)', icon: 'home' },
  { route: '/warning', title: 'ระบบแจ้งเตือนภัย', subtitle: '', color: 'var(--pdm-blue)', icon: 'map' },
  // { route: '/monitoring', title: 'แดชบอร์ดติดตาม', subtitle: 'สถานการณ์และการช่วยเหลือ', color: 'var(--pdm-green)', icon: 'chart' },
  { route: '/knowledge', title: 'คลังความรู้สู้ภัยพิบัติด้านพืช', subtitle: '', color: 'var(--pdm-orange)', icon: 'book' },
  { route: '/support', title: 'ระบบสนับสนุนเจ้าหน้าที่', subtitle: '', color: 'var(--pdm-purple)', icon: 'staff' },
]

const emoji = {
  home: '\u{1F3E0}',
  map: '\u{1F5FA}',
  chart: '\u{1F4CA}',
  book: '\u{1F4DA}',
  staff: '\u{1F9D1}\u200D\u{1F4BC}',
}

const active = computed(() => route.path)
</script>

<template>
  <nav class="side-nav">
    <button v-for="(item, index) in items" :key="item.route" class="nav-item" :class="{ active: active === item.route }"
      @click="router.push(item.route)">
      <!-- <span class="num" :style="{ background: item.color }">{{ index + 1 }}</span> -->
      <span class="icon">{{ emoji[item.icon] }}</span>
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

.nav-item:hover {
  box-shadow: var(--shadow-card);
}

.nav-item.active {
  border-color: var(--pdm-green);
  background: #f2f9f4;
}

.num {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon {
  font-size: 18px;
}

.text {
  display: flex;
  flex-direction: column;
  line-height: 1.3;
}

.text strong {
  font-size: 13px;
}

.text small {
  font-size: 11px;
  color: var(--text-muted);
}

@media (max-width: 900px) {
  .side-nav {
    flex-direction: row;
    overflow-x: auto;
    padding-bottom: 2px;
    -webkit-overflow-scrolling: touch;
  }

  .nav-item {
    flex: 0 0 190px;
  }
}
</style>