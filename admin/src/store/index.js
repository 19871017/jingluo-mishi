import { defineStore } from 'pinia'

export const useStore = defineStore('admin', {
  state: () => ({
    token: localStorage.getItem('admin_token') || '',
    adminInfo: JSON.parse(localStorage.getItem('admin_info') || 'null')
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    isBrandAdmin: (state) => state.adminInfo?.role === 'brand',
    brandId: (state) => state.adminInfo?.brand_id || null,
    isConstructorAdmin: (state) => state.adminInfo?.role === 'constructor',
    constructorUserId: (state) => state.adminInfo?.constructor_user_id || null,
  },

  actions: {
    login({ token, admin }) {
      this.token = token
      this.adminInfo = admin
      localStorage.setItem('admin_token', token)
      localStorage.setItem('admin_info', JSON.stringify(admin))
    },
    logout() {
      this.token = ''
      this.adminInfo = null
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_info')
    }
  }
})

export default useStore
