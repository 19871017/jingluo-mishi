<template>
  <scroll-view scroll-y class="category-page" :refresher-enabled="true" :refresher-triggered="refreshing" @refresherrefresh="onRefresh">
    <view class="category-shell">
      <view class="category-hero">
        <view class="hero-glow hero-glow-left"></view>
        <view class="hero-glow hero-glow-right"></view>
        <text class="hero-caption">分类浏览</text>
        <text class="hero-title">分类浏览</text>
        <text class="hero-desc">按分类查看剧本内容，并通过高级筛选快速缩小范围</text>

        <scroll-view scroll-x class="category-tabs" show-scrollbar="false">
          <view class="category-tab-list">
            <view
              v-for="item in categories"
              :key="item.id"
              class="category-tab"
              :class="{ active: activeId === item.id }"
              @click="selectCategory(item.id)"
            >
              {{ item.name }}
            </view>
          </view>
        </scroll-view>
      </view>

      <view class="summary-card card">
        <view>
          <text class="summary-title">{{ currentCategoryName }}</text>
          <text class="summary-subtitle">已筛选出 {{ filteredItems.length }} / {{ rawItems.length }} 个剧本</text>
        </view>
        <view class="summary-meta">
          <text class="summary-chip">筛选 {{ activeFilterCount }} 项</text>
        </view>
      </view>

      <view class="filter-trigger card" @click="drawerVisible = true">
        <view>
          <text class="filter-trigger-title">高级筛选</text>
          <text class="filter-trigger-subtitle">城市、价格、人数、类型、标签等 20+ 条件</text>
        </view>
        <text class="filter-trigger-action">打开筛选</text>
      </view>

      <view v-if="selectedFilterChips.length" class="selected-bar card">
        <view class="selected-head">
          <text class="selected-title">已选条件</text>
          <text class="selected-clear" @click="resetFilters">清空全部</text>
        </view>
        <view class="selected-chip-list">
          <view v-for="chip in selectedFilterChips" :key="chip.key + chip.label" class="selected-chip" @click="removeFilterChip(chip)">
            <text class="selected-chip-text">{{ chip.label }}</text>
            <text class="selected-chip-close">×</text>
          </view>
        </view>
      </view>

      <view class="section-head">
        <view>
          <text class="section-title">分类列表</text>
          <text class="section-subtitle">展示符合当前条件的剧本内容</text>
        </view>
        <text class="section-badge">{{ filteredItems.length }} 条</text>
      </view>

      <view class="section">
        <ScriptList :items="filteredItems" @select="goDetail" />
      </view>

      <EmptyState v-if="!filteredItems.length && !refreshing" title="暂无匹配结果" description="试试减少筛选条件或切换分类" />

      <FilterDrawer
        :visible="drawerVisible"
        :sections="filterSections"
        :model-value="filterState"
        @close="drawerVisible = false"
        @apply="applyFilters"
      />
    </view>
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import EmptyState from '../../components/EmptyState.vue'
import FilterDrawer from './components/FilterDrawer.vue'
import ScriptList from './components/ScriptList.vue'
import { buildFilterSections, defaultFilterState } from './filterConfig'
import { syncCustomTabBar } from '../../utils/tabbar'

const categories = ref([])
const cityGroups = ref([])
const activeId = ref(null)
const scripts = ref({ scripts: [], filters: {} })
const refreshing = ref(false)
const drawerVisible = ref(false)
const filterState = ref({ ...defaultFilterState })

const filterSections = computed(() => buildFilterSections(scripts.value.filters?.dynamic || {}, cityGroups.value))
const rawItems = computed(() => scripts.value.scripts || [])
const currentCategoryName = computed(() => categories.value.find((item) => item.id === activeId.value)?.name || '当前分类')
const activeFilterCount = computed(() => countActiveFilters(filterState.value))
const selectedFilterChips = computed(() => buildSelectedFilterChips(filterState.value))
const filteredItems = computed(() => rawItems.value.filter((item) => matchesFilters(item, filterState.value)))

async function fetchCategories() {
  const data = await api.getCategories()
  categories.value = data.list || []
  if (categories.value.length) {
    activeId.value = categories.value[0].id
    await fetchScripts(activeId.value)
  }
}

async function fetchCityRegions() {
  const data = await api.getCityRegions()
  cityGroups.value = Array.isArray(data.list) ? data.list : []
}

async function fetchScripts(id) {
  scripts.value = await api.getCategoryScripts(id, filterState.value)
}

async function selectCategory(id) {
  if (activeId.value === id) return
  activeId.value = id
  await fetchScripts(id)
}

async function onRefresh() {
  refreshing.value = true
  uni.removeStorageSync('cache_GET:/api/categories:{}')
  await Promise.all([fetchCityRegions(), fetchCategories()])
  refreshing.value = false
  uni.showToast({ title: '刷新成功', icon: 'success' })
}

function applyFilters(nextState) {
  filterState.value = nextState
  drawerVisible.value = false
  if (activeId.value) {
    fetchScripts(activeId.value)
  }
}

function resetFilters() {
  filterState.value = { ...defaultFilterState }
  if (activeId.value) {
    fetchScripts(activeId.value)
  }
}

function removeFilterChip(chip) {
  const nextState = JSON.parse(JSON.stringify(filterState.value))
  if (chip.arrayKey) {
    nextState[chip.arrayKey] = (nextState[chip.arrayKey] || []).filter((item) => item !== chip.rawValue)
  } else if (chip.presetKey) {
    nextState[chip.presetKey] = ''
  } else if (chip.rangeKey) {
    nextState[chip.rangeKey] = ''
  } else {
    nextState[chip.key] = Array.isArray(nextState[chip.key]) ? [] : ''
  }
  applyFilters(nextState)
}

function matchesFilters(item, filters) {
  const price = Number(item.price_tier1 || 0)
  const name = String(item.name || '')
  const type = String(item.type || '')
  const duration = Number(item.duration || 0)
  const horrorLevel = Number(item.horror_level || 0)
  const difficulty = String(item.difficulty || '')
  const roomSize = String(item.room_size || '')
  const areaSize = Number(item.area_size || 0)
  const roomCount = normalizeCount(item.room_count)
  const rotationCount = normalizeCount(item.rotation_count)
  const npcCount = normalizeCount(item.npc_count)
  const corridorCount = normalizeCount(item.corridor_count)
  const authStatus = String(item.auth_status || '')
  const max = Number(item.max_players || 0)
  const features = normalizeList(item.features)
  const suitablePlayers = normalizeList(item.suitable_players)
  const authServices = normalizeList(item.auth_services)
  const authCities = normalizeList(item.auth_cities)
  const authorizedCities = normalizeList(item.authorized_cities)

  if (filters.keyword && !name.includes(filters.keyword)) return false
  if (filters.priceMin && price < Number(filters.priceMin)) return false
  if (filters.priceMax && price > Number(filters.priceMax)) return false
  if (filters.playersMin && max < Number(filters.playersMin)) return false
  if (filters.playersMax && max > Number(filters.playersMax)) return false
  if (filters.types.length && !filters.types.some((label) => type.includes(label) || normalizeType(label) === normalizeType(type))) return false
  if (filters.horrorLevel && !matchHorror(horrorLevel, filters.horrorLevel)) return false
  if (filters.difficulty && normalizeType(filters.difficulty) !== normalizeType(difficulty)) return false
  if (filters.roomSize && normalizeType(filters.roomSize) !== normalizeType(roomSize)) return false
  if (filters.features.length && !filters.features.every((tag) => containsTag(features, tag))) return false
  if (filters.areaPreset && !matchRangePreset(areaSize, filters.areaPreset)) return false
  if (filters.areaMin && areaSize < Number(filters.areaMin)) return false
  if (filters.areaMax && areaSize > Number(filters.areaMax)) return false
  if (filters.roomCountMin && !matchCountMin(roomCount, filters.roomCountMin)) return false
  if (filters.roomCountMax && !matchCountMax(roomCount, filters.roomCountMax)) return false
  if (filters.rotationMin && !matchCountMin(rotationCount, filters.rotationMin)) return false
  if (filters.rotationMax && !matchCountMax(rotationCount, filters.rotationMax)) return false
  if (filters.npcMin && !matchCountMin(npcCount, filters.npcMin)) return false
  if (filters.npcMax && !matchCountMax(npcCount, filters.npcMax)) return false
  if (filters.durationRange && !matchDuration(duration, filters.durationRange)) return false
  if (filters.corridorCountMin && !matchCountMin(corridorCount, filters.corridorCountMin)) return false
  if (filters.corridorCountMax && !matchCountMax(corridorCount, filters.corridorCountMax)) return false
  if (filters.suitablePlayers.length && !filters.suitablePlayers.every((tag) => containsTag(suitablePlayers, tag))) return false
  if (filters.authStatus && normalizeType(filters.authStatus) !== normalizeType(authStatus)) return false
  if (filters.authServices.length && !filters.authServices.every((tag) => containsTag(authServices, tag))) return false
  if (filters.authCities.length && !filters.authCities.every((city) => containsTag(authCities, city))) return false
  if (filters.authorizedCities.length && !filters.authorizedCities.every((city) => containsTag(authorizedCities, city))) return false

  return true
}

function normalizeType(label) {
  return String(label || '').replace(/\s+/g, '').toLowerCase()
}

function normalizeList(value) {
  return Array.isArray(value) ? value.map((item) => String(item || '')) : []
}

function containsTag(list, value) {
  return normalizeList(list).some((item) => normalizeType(item) === normalizeType(value))
}

function normalizeCount(value) {
  const text = String(value || '')
  if (!text) return ''
  if (text.includes('不可')) return '0'
  if (text.includes('10+')) return '10+'
  const match = text.match(/\d+/)
  return match ? match[0] : text
}

function countToNumber(value) {
  const normalized = normalizeCount(value)
  if (!normalized) return NaN
  if (normalized === '10+') return 10
  return Number(normalized)
}

function matchCountMin(current, minValue) {
  const currentNumber = countToNumber(current)
  const targetNumber = Number(minValue)
  if (Number.isNaN(currentNumber) || Number.isNaN(targetNumber)) return true
  return currentNumber >= targetNumber
}

function matchCountMax(current, maxValue) {
  const currentNumber = countToNumber(current)
  const targetNumber = Number(maxValue)
  if (Number.isNaN(currentNumber) || Number.isNaN(targetNumber)) return true
  return currentNumber <= targetNumber
}

function matchHorror(level, label) {
  const text = String(label)
  if (text.includes('重')) return level >= 5
  if (text.includes('中')) return level === 4
  if (text.includes('微')) return level >= 2 && level <= 3
  if (text.includes('非')) return level <= 1
  return true
}

function matchDuration(duration, label) {
  const text = String(label)
  if (text.includes('0-30')) return duration >= 0 && duration <= 30
  if (text.includes('31-60')) return duration >= 31 && duration <= 60
  if (text.includes('61-90')) return duration >= 61 && duration <= 90
  if (text.includes('91-120')) return duration >= 91 && duration <= 120
  return true
}

function matchRangePreset(value, preset) {
  if (!preset) return true
  const text = String(preset)
  const match = text.match(/(\d+)\D+(\d+)/)
  if (match) {
    return value >= Number(match[1]) && value <= Number(match[2])
  }
  const more = text.match(/(\d+)\D*\+/)
  if (more) {
    return value >= Number(more[1])
  }
  return true
}

function countActiveFilters(filters) {
  return Object.entries(filters).reduce((count, [, value]) => {
    if (Array.isArray(value)) return count + (value.length ? 1 : 0)
    return count + (value !== '' && value !== null && value !== undefined ? 1 : 0)
  }, 0)
}

function buildSelectedFilterChips(filters) {
  const chips = []

  if (filters.keyword) {
    chips.push({ key: 'keyword', label: `主题:${filters.keyword}` })
  }

  for (const [key, value] of Object.entries(filters)) {
    if (key === 'keyword') continue
    if (Array.isArray(value)) {
      value.forEach((item) => {
        chips.push({ key, arrayKey: key, rawValue: item, label: item })
      })
      continue
    }

    if (!value) continue

    if (key === 'priceMin') chips.push({ key, rangeKey: key, label: `最低价:${value}` })
    else if (key === 'priceMax') chips.push({ key, rangeKey: key, label: `最高价:${value}` })
    else if (key === 'playersMin') chips.push({ key, rangeKey: key, label: `最少人数:${value}` })
    else if (key === 'playersMax') chips.push({ key, rangeKey: key, label: `最多人数:${value}` })
    else if (key === 'areaMin') chips.push({ key, rangeKey: key, label: `最小面积:${value}` })
    else if (key === 'areaMax') chips.push({ key, rangeKey: key, label: `最大面积:${value}` })
    else if (key === 'roomCountMin') chips.push({ key, rangeKey: key, label: `最少房间:${value}` })
    else if (key === 'roomCountMax') chips.push({ key, rangeKey: key, label: `最多房间:${value}` })
    else if (key === 'rotationMin') chips.push({ key, rangeKey: key, label: `最少滚场:${value}` })
    else if (key === 'rotationMax') chips.push({ key, rangeKey: key, label: `最多滚场:${value}` })
    else if (key === 'npcMin') chips.push({ key, rangeKey: key, label: `最少NPC:${value}` })
    else if (key === 'npcMax') chips.push({ key, rangeKey: key, label: `最多NPC:${value}` })
    else if (key === 'corridorCountMin') chips.push({ key, rangeKey: key, label: `最少走廊:${value}` })
    else if (key === 'corridorCountMax') chips.push({ key, rangeKey: key, label: `最多走廊:${value}` })
    else if (key === 'areaPreset') chips.push({ key, presetKey: key, label: `面积:${value}` })
    else chips.push({ key, label: value })
  }

  return chips
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/script/script-detail?id=${id}` })
}

onMounted(async () => {
  await Promise.all([fetchCityRegions(), fetchCategories()])
  syncCustomTabBar(1)
})
</script>

<style scoped>
.category-page {
  height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(255, 181, 72, 0.12), transparent 20%),
    linear-gradient(180deg, #fff7ef 0%, #f6f7fb 24%, #f6f7fb 100%);
}

.category-shell {
  padding: 24rpx;
}

.category-hero {
  position: relative;
  overflow: hidden;
  padding: 28rpx 24rpx 24rpx;
  border-radius: 30rpx;
  background: linear-gradient(135deg, #ffb64a 0%, #ff8d1a 58%, #ff6f00 100%);
  box-shadow: 0 16rpx 40rpx rgba(255, 132, 0, 0.16);
}

.hero-glow {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.12);
}

.hero-glow-left {
  top: -80rpx;
  left: -30rpx;
  width: 220rpx;
  height: 220rpx;
}

.hero-glow-right {
  top: 20rpx;
  right: -60rpx;
  width: 260rpx;
  height: 260rpx;
}

.hero-caption,
.hero-title,
.hero-desc {
  position: relative;
  z-index: 1;
  display: block;
  color: #fff;
}

.hero-caption {
  font-size: 22rpx;
  letter-spacing: 2rpx;
  opacity: 0.82;
}

.hero-title {
  margin-top: 10rpx;
  font-size: 50rpx;
  font-weight: 800;
}

.hero-desc {
  margin-top: 10rpx;
  max-width: 560rpx;
  font-size: 24rpx;
  line-height: 1.6;
  opacity: 0.92;
}

.category-tabs {
  position: relative;
  z-index: 1;
  margin-top: 24rpx;
  white-space: nowrap;
}

.category-tab-list {
  display: inline-flex;
  gap: 14rpx;
  padding-bottom: 4rpx;
}

.category-tab {
  padding: 14rpx 24rpx;
  border-radius: 999rpx;
  background: rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.88);
  font-size: 24rpx;
  font-weight: 600;
}

.category-tab.active {
  background: #ffffff;
  color: #f97316;
  box-shadow: 0 10rpx 24rpx rgba(255, 255, 255, 0.24);
}

.card {
  background: rgba(255, 255, 255, 0.92);
  border-radius: 26rpx;
  box-shadow: 0 18rpx 40rpx rgba(15, 23, 42, 0.05);
  backdrop-filter: blur(8px);
}

.summary-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20rpx;
  margin-top: 22rpx;
  padding: 24rpx;
}

.summary-title,
.summary-subtitle {
  display: block;
}

.summary-title {
  font-size: 30rpx;
  font-weight: 700;
  color: #1f2937;
}

.summary-subtitle {
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #94a3b8;
}

.summary-chip {
  display: inline-flex;
  align-items: center;
  padding: 10rpx 20rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 22rpx;
  font-weight: 700;
}

.filter-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20rpx;
  margin-top: 20rpx;
  padding: 24rpx;
}

.filter-trigger-title,
.filter-trigger-subtitle {
  display: block;
}

.filter-trigger-title {
  font-size: 28rpx;
  font-weight: 700;
  color: #1f2937;
}

.filter-trigger-subtitle {
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #9ca3af;
}

.filter-trigger-action {
  color: #f97316;
  font-weight: 700;
  font-size: 24rpx;
}

.selected-bar {
  margin-top: 20rpx;
  padding: 22rpx 24rpx;
}

.selected-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16rpx;
}

.selected-title {
  font-size: 26rpx;
  font-weight: 700;
  color: #374151;
}

.selected-clear {
  font-size: 22rpx;
  color: #f97316;
}

.selected-chip-list {
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
  margin-top: 16rpx;
}

.selected-chip {
  display: inline-flex;
  align-items: center;
  gap: 8rpx;
  padding: 10rpx 16rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 22rpx;
}

.selected-chip-close {
  font-size: 20rpx;
}

.section-head {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16rpx;
  margin: 28rpx 0 16rpx;
}

.section-title,
.section-subtitle {
  display: block;
}

.section-title {
  font-size: 32rpx;
  font-weight: 800;
  color: #1f2937;
}

.section-subtitle {
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #94a3b8;
}

.section-badge {
  padding: 8rpx 16rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 22rpx;
  font-weight: 700;
}

.section {
  margin-bottom: 24rpx;
}
</style>
