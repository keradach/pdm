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
  <header class="header">
    <div class="brand">
      <div class="logo">DOAE</div>
      <div class="title-block">
        <h1>
          PDM <span class="sep">|</span>
          <span class="th">ศูนย์ปฏิบัติการติดตามและแก้ไขปัญหาภัยพิบัติด้านพืช (ศตก)</span>
        </h1>
        <p class="subtitle">Plant Disaster Monitoring and Response Center (PDMRC)</p>
      </div>
    </div>
    <div class="right-side">
      <a class="url-pill" href="https://pdm.doae.go.th" target="_blank" rel="noopener">
        https://pdm.doae.go.th
      </a>

      <!-- Not logged in: show login / register links -->
      <nav v-if="!auth.isAuthenticated" class="auth-nav">
        <router-link to="/login" class="nav-link">เข้าสู่ระบบ</router-link>
        <router-link to="/register" class="nav-link nav-link-primary">สมัครสมาชิก</router-link>
      </nav>

      <!-- Logged in: show user info + logout -->
      <div v-else class="user-menu">
        <span class="user-name">{{ auth.user?.name }}</span>
        <span class="user-role">{{ auth.user?.role === 'admin' ? 'ผู้ดูแลระบบ' : 'เจ้าหน้าที่' }}</span>
        <button class="logout-btn" @click="handleLogout">ออกจากระบบ</button>
      </div>
    </div>
  </header>
</template>

<style scoped>
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px 22px;
  background: linear-gradient(90deg, var(--pdm-green-deep), var(--pdm-green));
  color: #fff;
  flex-wrap: wrap;
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.logo {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 20px;
  background: #fff;
  color: var(--pdm-green-deep);
  padding: 6px 12px;
  border-radius: 8px;
}

h1 {
  font-size: 18px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.sep {
  opacity: 0.5;
}

.th {
  font-size: 15px;
  font-weight: 500;
}

.subtitle {
  margin: 2px 0 0;
  font-size: 12px;
  opacity: 0.85;
}

.right-side {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.url-pill {
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.35);
  color: #fff;
  text-decoration: none;
  padding: 8px 16px;
  border-radius: 999px;
  font-size: 13px;
  white-space: nowrap;
}

.url-pill:hover {
  background: rgba(255, 255, 255, 0.25);
}

/* ---- Auth nav (not logged in) ---- */
.auth-nav {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-link {
  color: #fff;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.35);
  background: rgba(255, 255, 255, 0.1);
  transition: background 0.2s;
  white-space: nowrap;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.25);
}

.nav-link-primary {
  background: #fff;
  color: var(--pdm-green-deep);
  border-color: #fff;
}

.nav-link-primary:hover {
  background: rgba(255, 255, 255, 0.9);
}

/* ---- User menu (logged in) ---- */
.user-menu {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 999px;
  padding: 6px 8px 6px 16px;
}

.user-name {
  font-size: 13px;
  font-weight: 600;
}

.user-role {
  font-size: 11px;
  background: rgba(255, 255, 255, 0.2);
  padding: 3px 10px;
  border-radius: 999px;
}

.logout-btn {
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: #fff;
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 12px;
  font-family: var(--font-body);
  cursor: pointer;
  transition: background 0.2s;
}

.logout-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>