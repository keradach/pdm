import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('pdm_user') || 'null'),
    token: localStorage.getItem('pdm_token') || '',
    loading: false,
    error: '',
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
  },

  actions: {
    async register(payload) {
      this.loading = true
      this.error = ''
      try {
        const data = await api.register(payload)
        this.setAuth(data)
        return data
      } catch (e) {
        this.error = e.response?.data?.message || 'เกิดข้อผิดพลาดในการสมัครสมาชิก'
        throw e
      } finally {
        this.loading = false
      }
    },

    async login(payload) {
      this.loading = true
      this.error = ''
      try {
        const data = await api.login(payload)
        this.setAuth(data)
        return data
      } catch (e) {
        this.error = e.response?.data?.errors?.email?.[0] || e.response?.data?.message || 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchMe() {
      if (!this.token) return null
      try {
        const data = await api.getMe()
        this.user = data.user
        localStorage.setItem('pdm_user', JSON.stringify(data.user))
        return data.user
      } catch (e) {
        this.logout()
        return null
      }
    },

    async logout() {
      try {
        if (this.token) await api.logout()
      } catch (e) {
        // ignore logout errors
      }
      this.clearAuth()
    },

    setAuth(data) {
      this.token = data.token
      this.user = data.user
      localStorage.setItem('pdm_token', data.token)
      localStorage.setItem('pdm_user', JSON.stringify(data.user))
    },

    clearAuth() {
      this.token = ''
      this.user = null
      localStorage.removeItem('pdm_token')
      localStorage.removeItem('pdm_user')
    },
  },
})