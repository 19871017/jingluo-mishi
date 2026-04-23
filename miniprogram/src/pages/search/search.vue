<template>
  <scroll-view scroll-y class="search-page">
    <view class="search-shell">
      <view class="search-hero">
        <view class="hero-glow hero-glow-left"></view>
        <view class="hero-glow hero-glow-right"></view>
        <text class="hero-caption">搜索中心</text>
        <text class="hero-title">剧本搜索与筛选</text>
        <text class="hero-desc">按关键词、题材、品牌、人数快速找到目标剧本</text>

        <view class="search-box">
          <input v-model="keyword" class="search-input" placeholder="搜索剧本名称、别名、简介" confirm-type="search" @confirm="fetchList" />
          <view class="search-btn" @click="fetchList">搜索</view>
        </view>
      </view>

      <view class="filter-card card">
        <view class="filter-head">
          <view>
            <text class="filter-title">筛选条件</text>
            <text class="filter-subtitle">组合条件后结果会即时收敛</text>
          </view>
          <text class="filter-reset" @click="resetFilters">重置</text>
        </view>

        <view class="filter-grid">
          <picker mode="selector" :range="types" @change="onTypeChange">
            <view class="picker-chip">题材：{{ selectedType || '全部' }}</view>
          </picker>
          <picker mode="selector" :range="brandNames" @change="onBrandChange">
            <view class="picker-chip">品牌：{{ selectedBrandName }}</view>
          </picker>
          <picker mode="selector" :range="categoryNames" @change="onCategoryChange">
            <view class="picker-chip">分类：{{ selectedCategoryName }}</view>
          </picker>
          <picker mode="selector" :range="sortOptions" @change="onSortChange">
            <view class="picker-chip">排序：{{ selectedSortLabel }}</view>
          </picker>
        </view>

        <view class="players-row">
          <input v-model="minPlayers" class="mini-input" type="number" placeholder="最少人数" />
          <input v-model="maxPlayers" class="mini-input" type="number" placeholder="最多人数" />
        </view>
      </view>

      <view class="summary-card card">
        <view>
          <text class="summary-title">搜索结果</text>
          <text class="summary-subtitle">共 {{ (result.list || []).length }} 条匹配内容</text>
        </view>
        <text class="summary-chip">{{ selectedSortLabel }}</text>
      </view>

      <view class="result-list">
        <view v-for="item in result.list || []" :key="item.id" class="result-item card" @click="goDetail(item.id)">
          <view class="result-sideband"></view>
          <image class="result-thumb" :src="toAssetUrl(item.thumbnail) || 'https://dummyimage.com/280x280/5f7cff/ffffff&text=Script'" mode="aspectFill" />
          <view class="result-content">
            <view class="result-topline">
              <text class="result-name">{{ item.name }}</text>
              <text class="result-hot">{{ item.like_count || 0 }} 点赞</text>
            </view>
            <text class="result-price">{{ formatResultPrice(item) }}</text>
            <text class="result-meta">{{ item.min_players || 4 }}-{{ item.max_players || 8 }}人 · {{ item.duration || 60 }}分钟 · {{ item.type || '沉浸' }}</text>
            <view class="tag-row">
              <text class="tag">{{ item.type || '沉浸' }}</text>
              <text class="tag">{{ item.min_players || 4 }}-{{ item.max_players || 8 }}人</text>
              <text class="tag">浏览 {{ item.view_count || 0 }}</text>
              <text class="tag">收藏 {{ item.collect_count || 0 }}</text>
            </view>
          </view>
        </view>

        <EmptyState v-if="!(result.list || []).length" title="没有搜到剧本" description="试试换个关键词或减少筛选条件" />
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { toAssetUrl } from '../../services/http'
import EmptyState from '../../components/EmptyState.vue'
import { SEARCH_TYPE_OPTIONS } from '../../constants/taxonomy'
import { formatScriptPrice } from '../../utils/format'

const STORAGE_KEY = 'mini_search_state'
const keyword = ref('')
const result = ref({ list: [] })
const types = SEARCH_TYPE_OPTIONS
const selectedType = ref('')
const brandNames = ref(['全部品牌'])
const categoryNames = ref(['全部分类'])
const brands = ref([])
const categories = ref([])
const selectedBrandId = ref('')
const selectedCategoryId = ref('')
const selectedBrandName = ref('全部品牌')
const selectedCategoryName = ref('全部分类')
const sortOptions = ['按热度', '按名称', '按人数']
const selectedSort = ref('hot')
const selectedSortLabel = ref('按热度')
const minPlayers = ref('')
const maxPlayers = ref('')

function saveState() {
  uni.setStorageSync(STORAGE_KEY, {
    keyword: keyword.value,
    type: selectedType.value,
    brandId: selectedBrandId.value,
    categoryId: selectedCategoryId.value,
    sort: selectedSort.value,
    minPlayers: minPlayers.value,
    maxPlayers: maxPlayers.value,
  })
}

function restoreState() {
  const saved = uni.getStorageSync(STORAGE_KEY) || {}
  keyword.value = saved.keyword || ''
  selectedType.value = saved.type || ''
  selectedBrandId.value = saved.brandId || ''
  selectedCategoryId.value = saved.categoryId || ''
  selectedSort.value = saved.sort || 'hot'
  minPlayers.value = saved.minPlayers || ''
  maxPlayers.value = saved.maxPlayers || ''
}

async function fetchList() {
  const data = await api.searchScripts(keyword.value, 1, 50)
  let rows = data.list || []
  rows = rows.filter((item) => {
    if (selectedType.value && item.type !== selectedType.value) return false
    if (selectedBrandId.value && String(item.brand_id || item.brand?.id || '') !== String(selectedBrandId.value)) return false
    if (selectedCategoryId.value && String(item.category_id || item.category?.id || '') !== String(selectedCategoryId.value)) return false
    if (minPlayers.value && Number(item.min_players || 0) < Number(minPlayers.value)) return false
    if (maxPlayers.value && Number(item.max_players || 0) > Number(maxPlayers.value)) return false
    return true
  })
  if (selectedSort.value === 'hot') rows.sort((a, b) => Number(b.like_count || 0) - Number(a.like_count || 0))
  if (selectedSort.value === 'name') rows.sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')))
  if (selectedSort.value === 'players') rows.sort((a, b) => Number(a.max_players || 0) - Number(b.max_players || 0))
  result.value = { list: rows }
  saveState()
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/script/script-detail?id=${id}` })
}

function formatResultPrice(item) {
  return formatScriptPrice(item, '价格待定')
}

function onTypeChange(event) {
  const index = Number(event.detail.value)
  selectedType.value = index === 0 ? '' : types[index]
  fetchList()
}

function onBrandChange(event) {
  const index = Number(event.detail.value)
  const brand = index === 0 ? null : brands.value[index - 1]
  selectedBrandId.value = brand?.id || ''
  selectedBrandName.value = brand?.name || '全部品牌'
  fetchList()
}

function onCategoryChange(event) {
  const index = Number(event.detail.value)
  const category = index === 0 ? null : categories.value[index - 1]
  selectedCategoryId.value = category?.id || ''
  selectedCategoryName.value = category?.name || '全部分类'
  fetchList()
}

function onSortChange(event) {
  const index = Number(event.detail.value)
  selectedSortLabel.value = sortOptions[index]
  selectedSort.value = index === 1 ? 'name' : index === 2 ? 'players' : 'hot'
  fetchList()
}

function resetFilters() {
  keyword.value = ''
  selectedType.value = ''
  selectedBrandId.value = ''
  selectedCategoryId.value = ''
  selectedBrandName.value = '全部品牌'
  selectedCategoryName.value = '全部分类'
  selectedSort.value = 'hot'
  selectedSortLabel.value = '按热度'
  minPlayers.value = ''
  maxPlayers.value = ''
  uni.removeStorageSync(STORAGE_KEY)
  fetchList()
}

onMounted(async () => {
  restoreState()
  const [brandData, categoryData] = await Promise.all([api.getBrands(1, 50), api.getCategories()])
  brands.value = brandData.list || []
  categories.value = categoryData.list || []
  brandNames.value = ['全部品牌', ...brands.value.map((item) => item.name)]
  categoryNames.value = ['全部分类', ...categories.value.map((item) => item.name)]
  if (selectedBrandId.value) {
    const found = brands.value.find((item) => String(item.id) === String(selectedBrandId.value))
    selectedBrandName.value = found?.name || '全部品牌'
  }
  if (selectedCategoryId.value) {
    const found = categories.value.find((item) => String(item.id) === String(selectedCategoryId.value))
    selectedCategoryName.value = found?.name || '全部分类'
  }
  const page = getCurrentPages().slice(-1)[0]
  if (page?.options?.keyword) {
    keyword.value = decodeURIComponent(page.options.keyword)
  }
  fetchList()
})
</script>

<style scoped>
.search-page {
  height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(255, 181, 72, 0.12), transparent 20%),
    linear-gradient(180deg, #fff7ef 0%, #f6f7fb 24%, #f6f7fb 100%);
}

.search-shell {
  padding: 24rpx;
}

.search-hero {
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
  left: -40rpx;
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
  color: #ffffff;
}

.hero-caption {
  font-size: 20rpx;
  letter-spacing: 1.2rpx;
  opacity: 0.8;
}

.hero-title {
  margin-top: 12rpx;
  font-size: 44rpx;
  font-weight: 800;
  line-height: 1.25;
}

.hero-desc {
  margin-top: 10rpx;
  font-size: 24rpx;
  opacity: 0.92;
  line-height: 1.7;
}

.search-box {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 14rpx;
  margin-top: 26rpx;
  padding: 12rpx;
  border-radius: 40rpx;
  background: rgba(255, 250, 245, 0.92);
}

.search-input {
  flex: 1;
  height: 72rpx;
  padding: 0 20rpx;
  font-size: 26rpx;
}

.search-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 132rpx;
  height: 72rpx;
  border-radius: 999rpx;
  background: linear-gradient(135deg, #ffb24c, #ff7b00);
  color: #ffffff;
  font-size: 26rpx;
  font-weight: 700;
}

.filter-card {
  margin-top: 22rpx;
  padding: 24rpx;
}

.filter-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16rpx;
}

.filter-title {
  display: block;
  font-size: 30rpx;
  font-weight: 800;
  color: #1f2937;
  line-height: 1.4;
}

.filter-subtitle {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #9ca3af;
  line-height: 1.6;
}

.filter-reset {
  padding: 10rpx 16rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 22rpx;
  font-weight: 700;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16rpx;
  margin-top: 22rpx;
}

.picker-chip {
  min-height: 78rpx;
  display: flex;
  align-items: center;
  padding: 0 18rpx;
  border-radius: 16rpx;
  background: #fafafa;
  color: #4b5563;
  font-size: 24rpx;
}

.players-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16rpx;
  margin-top: 16rpx;
}

.mini-input {
  height: 78rpx;
  padding: 0 18rpx;
  border-radius: 16rpx;
  background: #fafafa;
  font-size: 24rpx;
}

.summary-card {
  margin-top: 22rpx;
  padding: 24rpx;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.summary-title {
  display: block;
  font-size: 30rpx;
  font-weight: 800;
  color: #1f2937;
  line-height: 1.4;
}

.summary-subtitle {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #8b8b8b;
  line-height: 1.6;
}

.summary-chip {
  padding: 10rpx 16rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 22rpx;
  font-weight: 700;
}

.result-list {
  margin-top: 22rpx;
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.result-item {
  position: relative;
  display: flex;
  gap: 20rpx;
  padding: 22rpx 18rpx 22rpx 28rpx;
  overflow: hidden;
}

.result-sideband {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 10rpx;
  background: linear-gradient(180deg, #ffb24c, #ff7b00);
}

.result-thumb {
  width: 180rpx;
  height: 180rpx;
  border-radius: 18rpx;
}

.result-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 12rpx;
}

.result-topline {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12rpx;
}

.result-name {
  font-size: 30rpx;
  font-weight: 800;
  color: #1f2937;
}

.result-hot {
  padding: 8rpx 12rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 20rpx;
}

.result-price {
  font-size: 24rpx;
  color: #ff8a00;
  font-weight: 700;
}

.result-meta {
  font-size: 22rpx;
  color: #8f8f8f;
  line-height: 1.5;
}

.tag-row {
  display: flex;
  gap: 10rpx;
  flex-wrap: wrap;
}

.tag {
  padding: 6rpx 12rpx;
  border-radius: 12rpx;
  background: #f5f5f5;
  color: #666666;
  font-size: 22rpx;
}
</style>
