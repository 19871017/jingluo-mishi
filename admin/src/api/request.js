import axios from 'axios'
import { ElMessage } from 'element-plus'
import { useStore } from '../store'
import router from '../router'

const request = axios.create({
  baseURL: '/api',
  timeout: 10000,
})

request.interceptors.request.use(
  (config) => {
    const store = useStore()
    if (store.token) {
      config.headers.Authorization = `Bearer ${store.token}`
      config.headers['Admin-Token'] = store.token
    }
    return config
  },
  (error) => Promise.reject(error)
)

request.interceptors.response.use(
  (response) => {
    const res = response.data

    if (res && typeof res === 'object' && Object.prototype.hasOwnProperty.call(res, 'code')) {
      if (res.code !== 200) {
        ElMessage.error(res.msg || '请求失败')
        return Promise.reject(res)
      }
      return res.data
    }

    return res
  },
  (error) => {
    if (error.response?.status === 401) {
      const store = useStore()
      const nextLoginPath = store.isBrandAdmin
        ? '/brand-login'
        : (store.isConstructorAdmin ? '/constructor-login' : '/login')
      store.logout()
      router.push(nextLoginPath)
      ElMessage.error('登录状态已失效，请重新登录')
    } else {
      const message = error.response?.data?.msg || error.message || '网络错误'
      ElMessage.error(message)
    }

    return Promise.reject(error)
  }
)

export default request
