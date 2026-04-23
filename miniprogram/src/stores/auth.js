import { defineStore } from 'pinia'
import { api } from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: uni.getStorageSync('user_token') || '',
    profile: uni.getStorageSync('user_profile') || null,
  }),
  getters: {
    isLoggedIn: (state) => !!state.token,
    canManageConstructionCases: (state) => ['constructor', 'brand', 'admin'].includes(state.profile?.role),
  },
  actions: {
    async quickLogin() {
      const openid = `demo-${Date.now()}`
      const response = await api.login({
        openid,
        nickname: '演示用户',
        avatar: '',
        role: 'constructor',
      })
      this.token = response.token
      this.profile = response.user
      uni.setStorageSync('user_token', response.token)
      uni.setStorageSync('user_profile', response.user)
    },
    logout() {
      this.token = ''
      this.profile = null
      uni.removeStorageSync('user_token')
      uni.removeStorageSync('user_profile')
    },
  },
})
