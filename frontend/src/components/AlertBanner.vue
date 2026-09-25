<script setup>
defineProps({
  alert: { type: Object, default: null },
})
</script>

<template>
  <div v-if="alert" class="flex flex-wrap items-center gap-6 bg-gradient-to-r from-[#c22a20] to-pdm-red text-white p-[14px_22px]">
    <div class="flex items-center gap-[10px] flex-1 min-w-[260px]">
      <span class="text-[26px]">⚠</span>
      <div>
        <div class="font-bold text-[15px]">{{ alert.title }}</div>
        <div class="text-[13px] opacity-90">{{ alert.message }}</div>
      </div>
    </div>
    <div class="flex flex-col items-start gap-1 flex-1 min-w-[260px]">
      <div class="text-[11px] opacity-[0.85]">จังหวัดเฝ้าระวัง</div>
      <div class="flex gap-1.5 flex-wrap">
        <span v-for="p in alert.watch_provinces" :key="p" class="bg-white/18 border border-white/40 p-[3px_10px] rounded-full text-xs">{{ p }}</span>
      </div>
    </div>
    <div class="flex flex-col items-end gap-1">
      <div class="text-[11px] opacity-[0.85]">ระดับความเสี่ยง</div>
      <div class="bg-white text-pdm-red font-extrabold p-[4px_14px] rounded-[6px] text-[15px]">{{ (alert.risk_level || '').toUpperCase() }}</div>
    </div>
  </div>
</template>
