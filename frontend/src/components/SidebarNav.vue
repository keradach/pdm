<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const items = [
  { route: '/', title: 'หน้าแรก', subtitle: 'ภาพรวมสถานการณ์', color: '#e8770f', icon: 'home' },
  { route: '/warning', title: 'ระบบแจ้งเตือนภัย', subtitle: '', color: '#2f6fb0', icon: 'map' },
  { route: '/knowledge', title: 'คลังความรู้สู้ภัยพิบัติด้านพืช', subtitle: '', color: '#e8770f', icon: 'book' },
  { route: '/support', title: 'ระบบสนับสนุนเจ้าหน้าที่', subtitle: '', color: '#6f4fa3', icon: 'staff' },
  { route: '/map', title: 'แผนที่อัจฉริยะ', subtitle: '', color: '#2e7d4f', icon: 'map' },
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
  <nav class="flex flex-col gap-[10px] max-[900px]:flex-row max-[900px]:overflow-x-auto max-[900px]:pb-0.5">
    <button v-for="(item, index) in items" :key="item.route" :class="[
      'grow-0 shrink-0 h-[60px] flex items-center gap-[10px] border border-edge rounded-[10px] p-3 cursor-pointer text-left hover:shadow-card',
      active === item.route ? 'border-pdm-green bg-[#f2f9f4] font-semibold' : 'bg-white',
    ]" @click="router.push(item.route)">
      <span class="icon text-lg">{{ emoji[item.icon] }}</span>
      <span class="flex flex-col leading-[1] min-w-0">
        <strong class="text-[13px]">{{ item.title }}</strong>
        <small class="text-[11px] text-muted">{{ item.subtitle }}</small>
      </span>
    </button>
  </nav>
</template>