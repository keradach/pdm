<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const form = ref({
  email: '',
  password: '',
})
const showPassword = ref(false)
const submitting = ref(false)

async function handleSubmit() {
  submitting.value = true
  try {
    await auth.login(form.value)
    router.push('/')
  } catch (e) {
    // error is set in store
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card card">
      <div class="auth-header">
        <div class="logo">DOAE</div>
        <h1>เข้าสู่ระบบเจ้าหน้าที่</h1>
        <p>PDM - Plant Disaster Monitoring Platform</p>
      </div>

      <div v-if="auth.error" class="error-box">{{ auth.error }}</div>

      <form @submit.prevent="handleSubmit" class="auth-form">
        <div class="field">
          <label for="email">อีเมล</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            autocomplete="email"
            placeholder="you@example.com"
          />
        </div>

        <div class="field">
          <label for="password">รหัสผ่าน</label>
          <div class="password-wrap">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              autocomplete="current-password"
              placeholder="••••••••"
            />
            <button
              type="button"
              class="toggle-btn"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? '🙈' : '👁' }}
            </button>
          </div>
        </div>

        <button type="submit" class="submit-btn" :disabled="submitting">
          {{ submitting ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
        </button>
      </form>

      <p class="switch-link">
        ยังไม่มีบัญชี?
        <router-link to="/register">สมัครสมาชิก</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: linear-gradient(135deg, var(--pdm-green-deep), var(--pdm-green));
}

.auth-card {
  width: 100%;
  max-width: 420px;
  padding: 36px 32px;
}

.auth-header {
  text-align: center;
  margin-bottom: 24px;
}

.logo {
  display: inline-block;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 22px;
  background: var(--pdm-green-deep);
  color: #fff;
  padding: 8px 16px;
  border-radius: 10px;
  margin-bottom: 16px;
}

.auth-header h1 {
  font-size: 22px;
  color: var(--text-main);
  margin-bottom: 6px;
}

.auth-header p {
  font-size: 13px;
  color: var(--text-muted);
  margin: 0;
}

.error-box {
  background: #fdecea;
  color: var(--pdm-red);
  border: 1px solid #f5c6c2;
  border-radius: var(--radius-md);
  padding: 10px 14px;
  font-size: 13px;
  margin-bottom: 16px;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
}

.field input {
  padding: 11px 14px;
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-md);
  font-size: 14px;
  font-family: var(--font-body);
  transition: border-color 0.2s;
}

.field input:focus {
  outline: none;
  border-color: var(--pdm-green);
  box-shadow: 0 0 0 3px rgba(46, 125, 79, 0.12);
}

.password-wrap {
  position: relative;
}

.password-wrap input {
  width: 100%;
  padding-right: 44px;
}

.toggle-btn {
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
  padding: 6px;
  border-radius: 6px;
}

.toggle-btn:hover {
  background: var(--bg-page);
}

.submit-btn {
  margin-top: 4px;
  padding: 12px;
  background: var(--pdm-green);
  color: #fff;
  border: none;
  border-radius: var(--radius-md);
  font-size: 15px;
  font-weight: 600;
  font-family: var(--font-body);
  cursor: pointer;
  transition: background 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: var(--pdm-green-deep);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.switch-link {
  text-align: center;
  margin: 20px 0 0;
  font-size: 14px;
  color: var(--text-muted);
}

.switch-link a {
  color: var(--pdm-green);
  font-weight: 600;
  text-decoration: none;
}

.switch-link a:hover {
  text-decoration: underline;
}
</style>