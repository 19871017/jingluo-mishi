import { defineStore } from 'pinia'
import { userApi } from '../services/api'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: uni.getStorageSync('token') || '',
    userInfo: null
  }),

  getters: {
    isLoggedIn: (state) => !!state.token
  },

  actions: {
    async login(code) {
      try {
        const data = await userApi.login(code)
        this.token = data.token
        this.userInfo = data.user
        uni.setStorageSync('token', data.token)
        uni.setStorageSync('userInfo', data.user)
        return data
      } catch (e) {
        throw e
      }
    },

    async fetchProfile() {
      try {
        const data = await userApi.getProfile()
        this.userInfo = data
        return data
      } catch (e) {
        throw e
      }
    },

    logout() {
      this.token = ''
      this.userInfo = null
      uni.removeStorageSync('token')
      uni.removeStorageSync('userInfo')
    }
  }
})
