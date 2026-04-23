const DEVTOOLS_BASE_URL = 'http://127.0.0.1:8090'
const DEVICE_BASE_URL = 'http://192.168.31.31:8090'
const SYSTEM_INFO = typeof uni !== 'undefined' && typeof uni.getSystemInfoSync === 'function' ? uni.getSystemInfoSync() : null
const IS_DEVTOOLS = SYSTEM_INFO?.platform === 'devtools'
const BASE_URL = IS_DEVTOOLS ? DEVTOOLS_BASE_URL : DEVICE_BASE_URL
const ASSET_BASE_URL = BASE_URL
const REQUEST_TIMEOUT = 15000
const pendingRequests = new Map()

function toAssetUrl(url) {
  if (!url) return ''
  if (/^https?:\/\//.test(url)) return url
  if (url.startsWith('/uploads/')) return `${ASSET_BASE_URL}${url}`
  if (url.startsWith('/')) return `${ASSET_BASE_URL}${url}`
  if (url.startsWith('uploads/')) return `${ASSET_BASE_URL}/${url}`
  return `${ASSET_BASE_URL}/${url}`
}

function generateRequestKey(url, method, data) {
  return `${method}:${url}:${JSON.stringify(data || {})}`
}

function cancelPendingRequest(key) {
  const controller = pendingRequests.get(key)
  if (controller) {
    clearTimeout(controller.timer)
    pendingRequests.delete(key)
  }
}

function normalizeErrorMessage(message, statusCode) {
  const text = String(message || '').trim()
  if (statusCode === 401) return '请先登录后再操作'
  if (statusCode === 403) return '当前操作暂无权限'
  if (statusCode === 404) return '内容不存在或已下线'
  if (text === 'Unauthorized') return '请先登录后再操作'
  if (text === 'Invalid token') return '登录状态已失效，请重新登录'
  if (text === 'User not found') return '当前用户状态异常，请重新登录'
  if (text === 'Validation Failed') return '提交信息不完整，请检查后重试'
  if (text === 'Route Not Found') return '访问的功能不存在'
  if (text === 'Server Error') return '服务异常，请稍后再试'
  return text || '请求失败'
}

function unwrapResponse(response) {
  if (response && typeof response === 'object' && Object.prototype.hasOwnProperty.call(response, 'data')) {
    return response.data
  }
  return response
}

const DEFAULT_CACHE_TTL = 120 // 默认缓存 120 秒

/**
 * cache 参数说明：
 *   false    — 不缓存
 *   true     — 缓存 DEFAULT_CACHE_TTL 秒
 *   number   — 缓存指定秒数（如 60 = 1分钟, 300 = 5分钟）
 */
function getCacheTTL(cache) {
  if (cache === false || cache === 0) return 0
  if (cache === true) return DEFAULT_CACHE_TTL
  return Number(cache) || 0
}

function getCachedData(key) {
  try {
    const entry = uni.getStorageSync(key)
    if (!entry || typeof entry !== 'object' || !entry._ts) return null
    if (Date.now() - entry._ts > entry._ttl * 1000) {
      uni.removeStorageSync(key)
      return null
    }
    return entry._data
  } catch {
    return null
  }
}

function setCachedData(key, data, ttl) {
  try {
    uni.setStorageSync(key, { _data: data, _ts: Date.now(), _ttl: ttl })
  } catch {
    // storage quota exceeded, ignore
  }
}

function request({ url, method = 'GET', data, auth = false, loading = false, cache = false }) {
  const token = uni.getStorageSync('user_token')
  const requestKey = generateRequestKey(url, method, data)
  const cacheTTL = getCacheTTL(cache)

  if (cacheTTL > 0) {
    const cachedData = getCachedData(`cache_${requestKey}`)
    if (cachedData !== null) {
      return Promise.resolve(cachedData)
    }
  }

  cancelPendingRequest(requestKey)

  if (loading) {
    uni.showLoading({ title: '加载中', mask: true })
  }

  const controller = { aborted: false }
  const timer = setTimeout(() => {
    controller.aborted = true
    uni.showToast({ title: '请求超时', icon: 'none' })
  }, REQUEST_TIMEOUT)

  pendingRequests.set(requestKey, { timer, controller })

  return new Promise((resolve, reject) => {
    if (controller.aborted) {
      pendingRequests.delete(requestKey)
      reject(new Error('请求超时'))
      return
    }

    uni.request({
      url: `${BASE_URL}${url}`,
      method,
      data,
      timeout: REQUEST_TIMEOUT,
      header: {
        'Content-Type': 'application/json',
        ...(auth && token ? { Authorization: `Bearer ${token}` } : {}),
      },
      success: ({ statusCode, data: response }) => {
        clearTimeout(timer)
        pendingRequests.delete(requestKey)
        if (loading) uni.hideLoading()

        if (statusCode >= 200 && statusCode < 300) {
          if (response?.code && response.code !== 200) {
            const message = normalizeErrorMessage(response?.msg || response?.message, statusCode)
            uni.showToast({ title: message, icon: 'none' })
            reject(response)
            return
          }

          const payload = unwrapResponse(response)
          if (cacheTTL > 0) {
            setCachedData(`cache_${requestKey}`, payload, cacheTTL)
          }
          resolve(payload)
          return
        }

        uni.showToast({
          title: normalizeErrorMessage(response?.msg || response?.message, statusCode),
          icon: 'none',
        })
        reject(response)
      },
      fail: (error) => {
        clearTimeout(timer)
        pendingRequests.delete(requestKey)
        if (loading) uni.hideLoading()

        if (!error.errMsg?.includes('abort')) {
          uni.showToast({
            title: '网络异常',
            icon: 'none',
          })
        }
        reject(error)
      },
    })
  })
}

export { toAssetUrl, request }
