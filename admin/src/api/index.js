import request from './request'

export const adminApi = {
  login: (data) => request.post('/admin/login', data),
  logout: () => request.post('/admin/logout'),
  profile: () => request.get('/admin/profile'),
  getStats: () => request.get('/admin/stats/overview')
}

export const brandPortalApi = {
  login: (data) => request.post('/brand/login', data),
}

export const brandProfileApi = {
  getProfile: () => request.get('/admin/brand-profile'),
  updateProfile: (data) => request.put('/admin/brand-profile', data)
}

export const constructorPortalApi = {
  login: (data) => request.post('/constructor/login', data),
}

export const categoryApi = {
  list: () => request.get('/admin/categories'),
  create: (data) => request.post('/admin/categories', data),
  update: (id, data) => request.put(`/admin/categories/${id}`, data),
  delete: (id) => request.delete(`/admin/categories/${id}`)
}

export const featureTagApi = {
  list: () => request.get('/admin/feature-tags'),
  create: (data) => request.post('/admin/feature-tags', data),
  update: (id, data) => request.put(`/admin/feature-tags/${id}`, data),
  delete: (id) => request.delete(`/admin/feature-tags/${id}`)
}

export const suitablePlayerTagApi = {
  list: () => request.get('/admin/suitable-player-tags'),
  create: (data) => request.post('/admin/suitable-player-tags', data),
  update: (id, data) => request.put(`/admin/suitable-player-tags/${id}`, data),
  delete: (id) => request.delete(`/admin/suitable-player-tags/${id}`)
}

export const scriptTypeTagApi = {
  list: () => request.get('/admin/script-type-tags'),
  create: (data) => request.post('/admin/script-type-tags', data),
  update: (id, data) => request.put(`/admin/script-type-tags/${id}`, data),
  delete: (id) => request.delete(`/admin/script-type-tags/${id}`)
}

export const brandApi = {
  list: (params) => request.get('/admin/brands', { params }),
  detail: (id) => request.get(`/admin/brands/${id}`),
  create: (data) => request.post('/admin/brands', data),
  update: (id, data) => request.put(`/admin/brands/${id}`, data),
  delete: (id) => request.delete(`/admin/brands/${id}`),
  audit: (id, status) => request.put(`/admin/brands/${id}/audit`, { status })
}

export const scriptApi = {
  list: (params) => request.get('/admin/scripts', { params }),
  create: (data) => request.post('/admin/scripts', data),
  update: (id, data) => request.put(`/admin/scripts/${id}`, data),
  delete: (id) => request.delete(`/admin/scripts/${id}`),
  audit: (id, status) => request.put(`/admin/scripts/${id}/audit`, { status })
}

export const scriptPurchaseIntentApi = {
  list: () => request.get('/admin/script-purchase-intents')
}

export const marketApi = {
  list: (params) => request.get('/admin/market/listings', { params }),
  audit: (id, status) => request.put(`/admin/market/listings/${id}/audit`, { status }),
  featured: (id, featured) => request.put(`/admin/market/listings/${id}/featured`, { featured }),
  delete: (id) => request.delete(`/admin/market/listings/${id}`)
}

export const homeApi = {
  getBanners: () => request.get('/admin/home/banners'),
  createBanner: (data) => request.post('/admin/home/banners', data),
  updateBanner: (id, data) => request.put(`/admin/home/banners/${id}`, data),
  deleteBanner: (id) => request.delete(`/admin/home/banners/${id}`),
  getAds: () => request.get('/admin/home/ads'),
  createAd: (data) => request.post('/admin/home/ads', data),
  updateAd: (id, data) => request.put(`/admin/home/ads/${id}`, data),
  deleteAd: (id) => request.delete(`/admin/home/ads/${id}`)
}

export const uploadApi = {
  image: (file) => {
    const formData = new FormData()
    formData.append('file', file)

    return request.post('/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },
  video: (file) => {
    const formData = new FormData()
    formData.append('file', file)

    return request.post('/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }
}

export const metaApi = {
  getCities: () => request.get('/meta/cities'),
}

export const constructionApi = {
  getPermissions: (params) => request.get('/admin/construction-permissions', { params }),
  approvePermission: (id) => request.put(`/admin/construction-permissions/${id}/approve`),
  rejectPermission: (id, data) => request.put(`/admin/construction-permissions/${id}/reject`, data),
  getCases: (params) => request.get('/admin/construction-cases', { params }),
  approveCase: (id) => request.put(`/admin/construction-cases/${id}/approve`),
  rejectCase: (id) => request.put(`/admin/construction-cases/${id}/reject`),
  featuredCase: (id, data) => request.put(`/admin/construction-cases/${id}/featured`, data),
  deleteCase: (id) => request.delete(`/admin/construction-cases/${id}`)
}

export const constructorContentApi = {
  getProfile: () => request.get('/admin/construction/profile'),
  updateProfile: (data) => request.put('/admin/construction/profile', data),
  getCases: (params) => request.get('/admin/construction/cases', { params }),
  createCase: (data) => request.post('/admin/construction/cases', data),
  updateCase: (id, data) => request.put(`/admin/construction/cases/${id}`, data),
  deleteCase: (id) => request.delete(`/admin/construction/cases/${id}`)
}

export const communityAdminApi = {
  getPosts: (params) => request.get('/admin/community/posts', { params }),
  approvePost: (id) => request.put(`/admin/community/posts/${id}/approve`),
  rejectPost: (id) => request.put(`/admin/community/posts/${id}/reject`),
  deletePost: (id) => request.delete(`/admin/community/posts/${id}`),
  getComments: (params) => request.get('/admin/community/comments', { params }),
  approveComment: (id) => request.put(`/admin/community/comments/${id}/approve`),
  rejectComment: (id) => request.put(`/admin/community/comments/${id}/reject`),
  deleteComment: (id) => request.delete(`/admin/community/comments/${id}`),
}
