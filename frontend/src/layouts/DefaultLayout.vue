<script setup>
import HeaderBar from "@/components/HeaderBar.vue";
import SidebarNav from "@/components/SidebarNav.vue";
</script>

<template>
  <div class="min-h-full pb-5">
    <HeaderBar />

    <div
      class="grid items-start gap-4 px-[22px] py-4 grid-cols-[minmax(180px,200px)_minmax(0,1fr)] max-[900px]:grid-cols-1 max-[900px]:gap-3 max-[900px]:px-[14px] max-[900px]:py-3 max-[640px]:px-[10px] max-[640px]:py-[10px]"
    >
      <aside class="min-w-0 sticky top-4 max-[900px]:static">
        <slot name="nav">
          <SidebarNav />
        </slot>
      </aside>

      <main class="min-w-0 flex flex-col gap-3.5">
        <slot />
      </main>
    </div>
  </div>
</template>