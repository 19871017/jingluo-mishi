import { request } from './http'

export const api = {
  getHome() {
    return request({ url: `/api/home?_t=${Date.now()}` })
  },
  searchScripts(keyword, page = 1, limit = 20) {
    return request({ url: `/api/scripts/search?keyword=${encodeURIComponent(keyword)}&page=${page}&limit=${limit}`, cache: page === 1 ? 60 : false })
  },
  getScriptDetail(id) {
    return request({ url: `/api/scripts/${id}`, cache: 120 })
  },
  getCategories() {
    return request({ url: '/api/categories?v=category-taxonomy-12', cache: 1800 })
  },
  getCityRegions() {
    return request({ url: '/api/meta/cities', cache: 1800 })
  },
  getCategoryScripts(id, filters = {}) {
    const query = []
    if (filters.keyword) query.push(`keyword=${encodeURIComponent(filters.keyword)}`)
    if (filters.priceMin) query.push(`priceMin=${encodeURIComponent(filters.priceMin)}`)
    if (filters.priceMax) query.push(`priceMax=${encodeURIComponent(filters.priceMax)}`)
    if (filters.playersMin) query.push(`playersMin=${encodeURIComponent(filters.playersMin)}`)
    if (filters.playersMax) query.push(`playersMax=${encodeURIComponent(filters.playersMax)}`)
    if (filters.durationRange) query.push(`durationRange=${encodeURIComponent(filters.durationRange)}`)
    if (filters.horrorLevel) query.push(`horrorLevel=${encodeURIComponent(filters.horrorLevel)}`)
    if (filters.difficulty) query.push(`difficulty=${encodeURIComponent(filters.difficulty)}`)
    if (filters.roomSize) query.push(`roomSize=${encodeURIComponent(filters.roomSize)}`)
    if (filters.areaPreset) query.push(`areaPreset=${encodeURIComponent(filters.areaPreset)}`)
    if (filters.areaMin) query.push(`areaMin=${encodeURIComponent(filters.areaMin)}`)
    if (filters.areaMax) query.push(`areaMax=${encodeURIComponent(filters.areaMax)}`)
    if (filters.roomCountMin) query.push(`roomCountMin=${encodeURIComponent(filters.roomCountMin)}`)
    if (filters.roomCountMax) query.push(`roomCountMax=${encodeURIComponent(filters.roomCountMax)}`)
    if (filters.rotationMin) query.push(`rotationMin=${encodeURIComponent(filters.rotationMin)}`)
    if (filters.rotationMax) query.push(`rotationMax=${encodeURIComponent(filters.rotationMax)}`)
    if (filters.npcMin) query.push(`npcMin=${encodeURIComponent(filters.npcMin)}`)
    if (filters.npcMax) query.push(`npcMax=${encodeURIComponent(filters.npcMax)}`)
    if (filters.corridorCountMin) query.push(`corridorCountMin=${encodeURIComponent(filters.corridorCountMin)}`)
    if (filters.corridorCountMax) query.push(`corridorCountMax=${encodeURIComponent(filters.corridorCountMax)}`)
    if (filters.authStatus) query.push(`authStatus=${encodeURIComponent(filters.authStatus)}`)
    if (Array.isArray(filters.types) && filters.types.length) query.push(`types=${encodeURIComponent(filters.types.join(','))}`)
    if (Array.isArray(filters.authCities) && filters.authCities.length) query.push(`authCities=${encodeURIComponent(filters.authCities.join(','))}`)
    if (Array.isArray(filters.authorizedCities) && filters.authorizedCities.length) query.push(`authorizedCities=${encodeURIComponent(filters.authorizedCities.join(','))}`)
    if (Array.isArray(filters.authServices) && filters.authServices.length) query.push(`authServices=${encodeURIComponent(filters.authServices.join(','))}`)
    if (Array.isArray(filters.suitablePlayers) && filters.suitablePlayers.length) query.push(`suitablePlayers=${encodeURIComponent(filters.suitablePlayers.join(','))}`)
    if (Array.isArray(filters.features) && filters.features.length) query.push(`features=${encodeURIComponent(filters.features.join(','))}`)
    return request({ url: `/api/categories/${id}/scripts${query.length ? `?${query.join('&')}` : ''}` })
  },
  getBrands(page = 1, limit = 20) {
    return request({ url: `/api/brands?page=${page}&limit=${limit}` })
  },
  getBrandDetail(id) {
    return request({ url: `/api/brands/${id}`, cache: 120 })
  },
  getConstructors(page = 1, limit = 20) {
    return request({ url: `/api/constructors?page=${page}&limit=${limit}` })
  },
  getConstructorDetail(id) {
    return request({ url: `/api/constructors/${id}`, cache: 120 })
  },
  getConstructionCases(brandName = '', page = 1, limit = 20) {
    const query = [`page=${page}`, `limit=${limit}`]
    if (brandName) query.push(`brand_name=${encodeURIComponent(brandName)}`)
    return request({ url: `/api/construction-cases?${query.join('&')}` })
  },
  getConstructionCaseDetail(id) {
    return request({ url: `/api/construction-cases/${id}` })
  },
  createConstructionCase(payload) {
    return request({ url: '/api/construction-cases', method: 'POST', data: payload, auth: true })
  },
  getConstructionCasePermission() {
    return request({ url: '/api/user/construction-case-permission', auth: true })
  },
  createConstructionCasePermission(payload) {
    return request({ url: '/api/construction-case-permission', method: 'POST', data: payload, auth: true })
  },
  getMyConstructionCases(page = 1, limit = 20) {
    return request({ url: `/api/user/construction-cases?page=${page}&limit=${limit}`, auth: true })
  },
  updateConstructionCase(id, payload) {
    return request({ url: `/api/construction-cases/${id}`, method: 'PUT', data: payload, auth: true })
  },
  deleteConstructionCase(id) {
    return request({ url: `/api/construction-cases/${id}`, method: 'DELETE', auth: true })
  },
  getMarket(type = '', page = 1, limit = 20, sort = 'latest') {
    return request({ url: `/api/market?type=${type}&page=${page}&limit=${limit}&sort=${encodeURIComponent(sort)}` })
  },
  getMarketDetail(id) {
    return request({ url: `/api/market/${id}` })
  },
  getTrendReport(startDate = '', endDate = '') {
    const query = []
    if (startDate) query.push(`start_date=${encodeURIComponent(startDate)}`)
    if (endDate) query.push(`end_date=${encodeURIComponent(endDate)}`)
    return request({ url: `/api/trend/report${query.length ? `?${query.join('&')}` : ''}`, cache: 300 })
  },
  login(payload) {
    return request({ url: '/api/user/login', method: 'POST', data: payload })
  },
  getUserInfo() {
    return request({ url: '/api/user/profile', auth: true })
  },
  likeScript(id) {
    return request({ url: `/api/scripts/${id}/like`, method: 'POST', auth: true })
  },
  collectScript(id) {
    return request({ url: `/api/scripts/${id}/collect`, method: 'POST', auth: true })
  },
  followBrand(id) {
    return request({ url: `/api/brands/${id}/follow`, method: 'POST', auth: true })
  },
  getFavorites() {
    return request({ url: '/api/user/favorites', auth: true })
  },
  getFollows() {
    return request({ url: '/api/user/follows', auth: true })
  },
  getMyListings() {
    return request({ url: '/api/user/listings', auth: true })
  },
  getInterests() {
    return request({ url: '/api/user/interests', auth: true })
  },
  getRecentViews() {
    return request({ url: '/api/user/recent-views', auth: true })
  },
  markView(targetType, targetId) {
    return request({ url: '/api/user/views', method: 'POST', data: { target_type: targetType, target_id: targetId }, auth: true })
  },
  createMarketListing(payload) {
    return request({ url: '/api/market/listings', method: 'POST', data: payload, auth: true })
  },
  toggleListingInterest(id) {
    return request({ url: `/api/market/listings/${id}/interest`, method: 'POST', auth: true })
  },
  toggleListingLike(id) {
    return request({ url: `/api/market/listings/${id}/like`, method: 'POST', auth: true })
  },
  getMarketComments(id) {
    return request({ url: `/api/market/listings/${id}/comments` })
  },
  createMarketComment(id, payload) {
    return request({ url: `/api/market/listings/${id}/comments`, method: 'POST', data: payload, auth: true })
  },
  deleteMarketComment(id) {
    return request({ url: `/api/market/comments/${id}`, method: 'DELETE', auth: true })
  },
  getMyScripts(page = 1, limit = 20) {
    return request({ url: `/api/user/scripts?page=${page}&limit=${limit}`, auth: true })
  },
  createScript(payload) {
    return request({ url: '/api/scripts', method: 'POST', data: payload, auth: true })
  },
}
