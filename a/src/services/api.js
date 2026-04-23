import { get, post, put, del } from './request'

export const homeApi = {
  getHomeData: () => get('/home')
}

export const scriptApi = {
  search: (params) => get('/scripts/search', params),
  getDetail: (id) => get(`/scripts/${id}`),
  like: (id) => post(`/scripts/${id}/like`),
  unlike: (id) => post(`/scripts/${id}/unlike`),
  collect: (id) => post(`/scripts/${id}/collect`),
  createPurchaseIntent: (id, data) => post(`/scripts/${id}/purchase-intent`, data)
}

export const categoryApi = {
  list: () => get('/categories'),
  getScripts: (id, params) => get(`/categories/${id}/scripts`, params)
}

export const brandApi = {
  list: (params) => get('/brands', params),
  getDetail: (id) => get(`/brands/${id}`),
  follow: (id) => post(`/brands/${id}/follow`),
  unfollow: (id) => post(`/brands/${id}/unfollow`)
}

export const marketApi = {
  list: (params) => get('/market', params),
  getDetail: (id) => get(`/market/${id}`),
  createListing: (data) => post('/market/listings', data)
}

export const userApi = {
  login: (code) => post('/user/login', { code }),
  getProfile: () => get('/user/profile'),
  updateProfile: (data) => put('/user/profile', data),
  getFavorites: (params) => get('/user/favorites', params),
  getFollows: (params) => get('/user/follows', params)
}
