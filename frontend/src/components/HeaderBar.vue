<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

async function handleLogout() {
  await auth.logout()
  router.push('/')
}
</script>

<template>
  <header
    class="flex flex-wrap items-center justify-between gap-4 px-[22px] py-[14px] bg-gradient-to-r from-pdm-green-deep to-pdm-green text-white max-[640px]:flex-col max-[640px]:items-stretch max-[640px]:gap-3 max-[640px]:px-[14px] max-[640px]:py-3">
    <div class="flex items-center gap-[14px] min-w-0 max-[640px]:items-start max-[640px]:gap-[10px] max-[640px]:w-full">
      <div class="font-display font-extrabold text-[20px] bg-white text-pdm-green-deep px-3 py-1.5 rounded-[8px]">
        DOAE
      </div>
      <div class="min-w-0 break-words">
        <h1
          class="text-lg font-bold flex items-center gap-2 flex-wrap leading-snug max-[640px]:text-[15px] max-[640px]:block">
          PDMC <span class="opacity-50 max-[640px]:hidden">|</span>
          <span class="th text-[15px] font-medium max-[640px]:block max-[640px]:mt-0.5 max-[640px]:text-xs">
            ศูนย์ปฏิบัติการติดตามและแก้ไขปัญหาภัยพิบัติด้านพืช (ศตก)
          </span>
        </h1>
        <p class="subtitle mt-0.5 text-xs opacity-[0.85] max-[640px]:text-[10px]">
          Plant Disaster Monitoring and Response Center (PDMC)
        </p>
      </div>
    </div>

    <div class="right-side flex flex-wrap items-center gap-3 max-[640px]:items-stretch max-[640px]:w-full">
      <!-- <a
        class="url-pill bg-white/15 border border-white/35 text-white no-underline rounded-full px-4 py-2 text-[13px] whitespace-nowrap hover:bg-white/25 max-[640px]:w-full max-[640px]:justify-center max-[640px]:truncate"
        href="https://pdm.doae.go.th"
        target="_blank"
        rel="noopener"
      >
        https://pdmrc.doae.go.th
      </a> -->

      <!-- Not logged in: show login / register links -->
      <nav v-if="!auth.isAuthenticated"
        class="auth-nav flex items-center gap-2 max-[640px]:w-full max-[640px]:justify-center">
        <router-link to="/login"
          class="nav-link text-white no-underline text-[13px] font-semibold rounded-full px-4 py-2 border border-white/35 bg-white/10 hover:bg-white/25 whitespace-nowrap">เข้าสู่ระบบ</router-link>
        <!-- <router-link to="/register"
          class="nav-link nav-link-primary bg-white text-pdm-green-deep border-white hover:bg-white/90 text-[13px] font-semibold no-underline rounded-full px-4 py-2 whitespace-nowrap">สมัครสมาชิก</router-link> -->
      </nav>

      <!-- Logged in: show user info + logout -->
      <div v-else
        class="user-menu flex items-center gap-[10px] bg-white/12 border border-white/25 rounded-full pl-4 pr-2 py-1.5 flex-wrap max-[640px]:w-full max-[640px]:justify-center max-[640px]:rounded-[10px]">
        <span class="text-[13px] font-semibold">{{ auth.user?.name }}</span>
        <span class="user-role text-[11px] bg-white/20 px-[10px] py-[3px] rounded-full">
          {{ auth.user?.role === 'admin' ? 'ผู้ดูแลระบบ' : 'เจ้าหน้าที่' }}
        </span>
        <button
          class="bg-white/15 border border-white/30 text-white px-[14px] py-1.5 rounded-full text-xs font-body cursor-pointer hover:bg-white/30"
          @click="handleLogout">
          ออกจากระบบ
        </button>
      </div>
    </div>
  </header>
</template>