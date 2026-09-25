<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'staff',
})
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const submitting = ref(false)

async function handleSubmit() {
  submitting.value = true
  try {
    await auth.register(form.value)
    router.push('/')
  } catch (e) {
    // error is set in store
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-5 bg-gradient-to-br from-pdm-green-deep to-pdm-green">
    <div class="card w-full max-w-[420px] p-[36px_32px]">
      <div class="text-center mb-6">
        <div class="inline-block font-display font-extrabold text-[22px] bg-pdm-green-deep text-white px-4 py-2 rounded-[10px] mb-4">DOAE</div>
        <h1 class="text-[22px] text-ink mb-1.5">สมัครสมาชิกเจ้าหน้าที่</h1>
        <p class="text-[13px] text-muted m-0">PDM - Plant Disaster Monitoring Platform</p>
      </div>

      <div v-if="auth.error" class="bg-[#fdecea] text-pdm-red border border-[#f5c6c2] rounded-[10px] p-[10px_14px] text-[13px] mb-4">{{ auth.error }}</div>

      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[13px] font-semibold text-ink" for="name">ชื่อ-นามสกุล</label>
          <input
    class="w-full pr-[44px] p-[11px_14px] border border-edge rounded-[10px] text-[14px] font-body focus:outline-none focus:border-pdm-green focus:shadow-[0_0_0_3px_rgba(46,125,79,0.12)]"
            id="name"
            v-model="form.name"
            type="text"
            required
            autocomplete="name"
            placeholder="ชื่อ นามสกุล"
          />
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[13px] font-semibold text-ink" for="email">อีเมล</label>
          <input
    class="w-full pr-[44px] p-[11px_14px] border border-edge rounded-[10px] text-[14px] font-body focus:outline-none focus:border-pdm-green focus:shadow-[0_0_0_3px_rgba(46,125,79,0.12)]"
            id="email"
            v-model="form.email"
            type="email"
            required
            autocomplete="email"
            placeholder="you@example.com"
          />
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[13px] font-semibold text-ink" for="role">บทบาท</label>
          <select
    class="w-full p-[11px_14px] border border-edge rounded-[10px] text-[14px] font-body bg-white focus:outline-none focus:border-pdm-green focus:shadow-[0_0_0_3px_rgba(46,125,79,0.12)]" id="role" v-model="form.role">
            <option value="staff">เจ้าหน้าที่</option>
            <option value="admin">ผู้ดูแลระบบ</option>
          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[13px] font-semibold text-ink" for="password">รหัสผ่าน</label>
          <div class="relative">
            <input
    class="w-full pr-[44px] p-[11px_14px] border border-edge rounded-[10px] text-[14px] font-body focus:outline-none focus:border-pdm-green focus:shadow-[0_0_0_3px_rgba(46,125,79,0.12)]"
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              minlength="8"
              autocomplete="new-password"
              placeholder="อย่างน้อย 8 ตัวอักษร"
            />
            <button
              type="button"
              class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-transparent border-0 cursor-pointer text-base p-1.5 rounded-[6px] hover:bg-page"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? '🙈' : '👁' }}
            </button>
          </div>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[13px] font-semibold text-ink" for="password_confirmation">ยืนยันรหัสผ่าน</label>
          <div class="relative">
            <input
    class="w-full pr-[44px] p-[11px_14px] border border-edge rounded-[10px] text-[14px] font-body focus:outline-none focus:border-pdm-green focus:shadow-[0_0_0_3px_rgba(46,125,79,0.12)]"
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              required
              minlength="8"
              autocomplete="new-password"
              placeholder="ยืนยันรหัสผ่าน"
            />
            <button
              type="button"
              class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-transparent border-0 cursor-pointer text-base p-1.5 rounded-[6px] hover:bg-page"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              {{ showConfirmPassword ? '🙈' : '👁' }}
            </button>
          </div>
        </div>

        <button type="submit" class="mt-1 p-3 bg-pdm-green text-white rounded-[10px] text-[15px] font-semibold font-body cursor-pointer enabled:hover:bg-pdm-green-deep disabled:opacity-60 disabled:cursor-not-allowed" :disabled="submitting">
          {{ submitting ? 'กำลังสมัครสมาชิก...' : 'สมัครสมาชิก' }}
        </button>
      </form>

      <p class="text-center mt-5 text-[14px] text-muted">
        มีบัญชีอยู่แล้ว?
        <router-link to="/login">เข้าสู่ระบบ</router-link>
      </p>
    </div>
  </div>
</template>
