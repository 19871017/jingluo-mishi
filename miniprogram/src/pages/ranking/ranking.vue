<template>
  <scroll-view scroll-y class="ranking-page" :refresher-enabled="true" :refresher-triggered="refreshing" @refresherrefresh="onRefresh">
    <view class="hero-section">
      <view class="hero-halo hero-halo-left"></view>
      <view class="hero-halo hero-halo-right"></view>
      <view class="hero-inner">
        <text class="hero-caption">Top Sales Ranking</text>
        <view class="hero-title-row">
          <text class="hero-laurel">❦</text>
          <text class="hero-title">{{ headerTitle }}</text>
          <text class="hero-laurel">❦</text>
        </view>
        <text class="hero-desc">根据 {{ periodHint }} 授权登记与内容热度综合计算</text>
        <view class="hero-summary-row">
          <view class="hero-summary-card">
            <text class="hero-summary-label">入榜数量</text>
            <text class="hero-summary-value">{{ filteredList.length }}</text>
          </view>
          <view class="hero-summary-card">
            <text class="hero-summary-label">冠军剧本</text>
            <text class="hero-summary-value small">{{ championName }}</text>
          </view>
          <view class="hero-summary-card">
            <text class="hero-summary-label">最高热度</text>
            <text class="hero-summary-value">{{ championScore }}</text>
          </view>
        </view>
      </view>
    </view>

    <view class="content-shell">
      <view class="toolbar">
        <view>
          <text class="toolbar-title">热销榜</text>
          <text class="toolbar-subtitle">按周期查看不同榜期表现</text>
        </view>
        <view class="toolbar-right">
          <text v-for="(label, index) in periodLabels" :key="label" class="period-chip" :class="{ active: activePeriodIndex === index }" @click="activePeriodIndex = index">{{ label }}</text>
        </view>
      </view>

      <view class="filters">
        <text v-for="item in filters" :key="item.value" class="filter-chip" :class="{ active: activeFilter === item.value }" @click="activeFilter = item.value">{{ item.label }}</text>
      </view>

      <view class="top-podium" v-if="filteredList.length >= 3">
        <view v-for="(item, index) in filteredList.slice(0, 3)" :key="item.id" class="podium-card" :class="`podium-${index + 1}`" @click="goDetail(item.id)">
          <view class="podium-rank">{{ index + 1 }}</view>
          <image class="podium-cover" :src="toAssetUrl(item.thumbnail) || fallbackCover" mode="aspectFill" />
          <text class="podium-name">{{ item.name }}</text>
          <text class="podium-score">热度 {{ computeScore(item) }}</text>
        </view>
      </view>

      <view class="ranking-list">
        <view v-for="(item, index) in filteredList" :key="item.id" class="ranking-card card" :class="{ 'ranking-card-top': index < 3 }" @click="goDetail(item.id)">
          <view class="ranking-sideband" :class="badgeClass(index)"></view>
          <view class="ranking-ribbon" :class="badgeClass(index)">
            <text class="ranking-ribbon-number">{{ index + 1 }}</text>
          </view>
          <image class="ranking-cover" :src="toAssetUrl(item.thumbnail) || fallbackCover" mode="aspectFill" />
          <view class="ranking-body">
            <view class="ranking-topline">
              <text class="ranking-title">{{ item.name }}</text>
              <text class="ranking-hot">热度 {{ computeScore(item) }}</text>
            </view>
            <text class="ranking-meta">{{ item.min_players || 2 }}-{{ item.max_players || 8 }}人 | {{ item.duration || 60 }}分钟 | {{ item.type || '沉浸' }}</text>
            <view class="tag-row">
              <text v-for="tag in resolveTags(item)" :key="tag" class="tag-chip">{{ tag }}</text>
            </view>
            <view class="ranking-footer">
              <text class="ranking-price">{{ resolvePrice(item) }}</text>
              <text class="ranking-stats">{{ item.view_count || 0 }} 浏览 · {{ item.like_count || 0 }} 点赞 · {{ item.collect_count || 0 }} 收藏 · {{ item.purchase_count || 0 }} 已购</text>
            </view>
          </view>
        </view>
      </view>
    </view>

    <view class="floating-actions">
      <view class="share-main" @click="shareRanking">
        <text class="share-main-icon">↗</text>
        <text class="share-main-text">分享热榜</text>
      </view>
      <view class="share-sub" @click="captureHint">
        <text class="share-sub-text">转发截图</text>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { toAssetUrl } from '../../services/http'
import { formatScriptPrice } from '../../utils/format'

const fallbackCover = 'https://dummyimage.com/240x320/111827/ffffff&text=Rank'
const filters = [
  { label: '全部', value: 'all' },
  { label: '非恐', value: 'non-horror' },
  { label: '恐怖', value: 'horror' },
]
const periodLabels = ['周榜', '月榜', '总榜']

const list = ref([])
const refreshing = ref(false)
const activeFilter = ref('all')
const activePeriodIndex = ref(1)

const periodHint = computed(() => {
  if (activePeriodIndex.value === 0) return '近 7 天'
  if (activePeriodIndex.value === 1) return '近 30 天'
  return '近 90 天'
})

const headerTitle = computed(() => {
  const date = new Date()
  if (activePeriodIndex.value === 0) {
    return `${date.getFullYear()}年第${getWeekOfYear(date)}周热销榜`
  }
  if (activePeriodIndex.value === 1) {
    return `${date.getFullYear()}年${date.getMonth() + 1}月热销榜`
  }
  return `${date.getFullYear()}年度热销榜`
})

const periodFilteredList = computed(() => {
  const now = Date.now()
  const daySpan = activePeriodIndex.value === 0 ? 7 : activePeriodIndex.value === 1 ? 30 : 90
  const minTime = now - daySpan * 24 * 60 * 60 * 1000

  return list.value.filter((item) => {
    const createdTime = new Date(item.created_at || 0).getTime()
    return !createdTime || createdTime >= minTime
  })
})

const filteredList = computed(() => {
  const sorted = [...periodFilteredList.value].sort((a, b) => computeScore(b) - computeScore(a))

  if (activeFilter.value === 'horror') {
    return sorted.filter((item) => isHorror(item))
  }
  if (activeFilter.value === 'non-horror') {
    return sorted.filter((item) => !isHorror(item))
  }
  return sorted
})

const championName = computed(() => filteredList.value[0]?.name || '--')
const championScore = computed(() => computeScore(filteredList.value[0] || {}))

async function fetchRanking() {
  const res = await api.searchScripts('', 1, 50)
  list.value = res.list || []
}

async function onRefresh() {
  refreshing.value = true
  await fetchRanking()
  refreshing.value = false
  uni.showToast({ title: '刷新成功', icon: 'success' })
}

function computeScore(item) {
  return Number(item.like_count || 0) * 2 + Number(item.view_count || 0)
}

function isHorror(item) {
  const horrorLevel = Number(item.horror_level || 0)
  return horrorLevel >= 3 || String(item.type || '').includes('恐')
}

function resolveTags(item) {
  const tags = [item.play_mode, item.mechanics, item.mission_type].filter(Boolean)
  if (!tags.length && item.type) {
    tags.push(item.type)
  }
  return tags.slice(0, 3)
}

function resolvePrice(item) {
  return formatScriptPrice(item, '¥面议')
}

function badgeClass(index) {
  if (index === 0) return 'top1'
  if (index === 1) return 'top2'
  if (index === 2) return 'top3'
  return 'normal'
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/script/script-detail?id=${id}` })
}

function shareRanking() {
  uni.showToast({ title: '可使用右上角分享', icon: 'none' })
}

function captureHint() {
  uni.showToast({ title: '可直接截图转发', icon: 'none' })
}

function formatDate(value) {
  if (!value) return '最近更新'
  return String(value).slice(0, 10)
}

function getWeekOfYear(date) {
  const firstDay = new Date(date.getFullYear(), 0, 1)
  const pastDays = Math.floor((date - firstDay) / 86400000)
  return Math.ceil((pastDays + firstDay.getDay() + 1) / 7)
}

onMounted(fetchRanking)
</script>

<style scoped>
.ranking-page {
  height: 100vh;
  background: linear-gradient(180deg, #ff8a00 0%, #ffd49e 300rpx, #fff7f0 301rpx, #fff7f0 100%);
}

.hero-section {
  position: relative;
  overflow: hidden;
  height: 500rpx;
  padding: 52rpx 28rpx 0;
}

.hero-halo {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.12);
}

.hero-halo-left {
  top: 70rpx;
  left: -60rpx;
  width: 220rpx;
  height: 220rpx;
}

.hero-halo-right {
  top: 40rpx;
  right: -50rpx;
  width: 260rpx;
  height: 260rpx;
}

.hero-inner {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #ffffff;
  text-align: center;
}

.hero-caption {
  font-size: 22rpx;
  opacity: 0.8;
  letter-spacing: 1rpx;
}

.hero-title-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14rpx;
  margin-top: 28rpx;
}

.hero-laurel {
  font-size: 56rpx;
  opacity: 0.9;
}

.hero-title {
  font-size: 54rpx;
  font-weight: 800;
}

.hero-desc {
  margin-top: 16rpx;
  font-size: 24rpx;
  opacity: 0.92;
}

.hero-summary-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14rpx;
  width: 100%;
  margin-top: 34rpx;
}

.hero-summary-card {
  padding: 18rpx 14rpx;
  border-radius: 18rpx;
  background: rgba(255, 255, 255, 0.14);
  backdrop-filter: blur(6px);
}

.hero-summary-label {
  display: block;
  font-size: 20rpx;
  opacity: 0.8;
}

.hero-summary-value {
  display: block;
  margin-top: 10rpx;
  font-size: 28rpx;
  font-weight: 800;
}

.hero-summary-value.small {
  font-size: 24rpx;
}

.content-shell {
  margin-top: -36rpx;
  padding: 0 20rpx 180rpx;
}

.toolbar {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 18rpx;
}

.toolbar-title {
  display: block;
  font-size: 52rpx;
  font-weight: 800;
  color: #ff7b00;
}

.toolbar-subtitle {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #a27d56;
}

.toolbar-right {
  display: flex;
  gap: 10rpx;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.period-chip {
  padding: 12rpx 22rpx;
  border-radius: 999rpx;
  background: rgba(255, 255, 255, 0.7);
  color: #8a6d4b;
  font-size: 22rpx;
  font-weight: 700;
}

.period-chip.active {
  background: #ff8a00;
  color: #ffffff;
  box-shadow: 0 10rpx 20rpx rgba(255, 138, 0, 0.2);
}

.filters {
  display: flex;
  gap: 16rpx;
  margin-top: 24rpx;
}

.filter-chip {
  padding: 18rpx 30rpx;
  border-radius: 18rpx;
  background: #ffffff;
  color: #6b7280;
  font-size: 26rpx;
  font-weight: 700;
}

.filter-chip.active {
  background: #ff8a00;
  color: #ffffff;
  box-shadow: 0 10rpx 20rpx rgba(255, 138, 0, 0.22);
}

.top-podium {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14rpx;
  margin-top: 26rpx;
}

.podium-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 18rpx 12rpx;
  border-radius: 22rpx;
  background: #ffffff;
  box-shadow: 0 14rpx 30rpx rgba(17, 24, 39, 0.05);
}

.podium-1 {
  transform: translateY(-10rpx);
  background: linear-gradient(180deg, #fff7e8, #ffffff);
}

.podium-rank {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44rpx;
  height: 44rpx;
  border-radius: 50%;
  background: #ff8a00;
  color: #ffffff;
  font-size: 24rpx;
  font-weight: 800;
}

.podium-cover {
  width: 100%;
  height: 170rpx;
  margin-top: 14rpx;
  border-radius: 16rpx;
}

.podium-name {
  margin-top: 12rpx;
  font-size: 24rpx;
  font-weight: 800;
  color: #1f2937;
  text-align: center;
}

.podium-score {
  margin-top: 8rpx;
  font-size: 20rpx;
  color: #ff8a00;
}

.ranking-list {
  margin-top: 18rpx;
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.ranking-card {
  position: relative;
  display: flex;
  gap: 20rpx;
  padding: 22rpx 22rpx 22rpx 34rpx;
  border: none;
  border-radius: 26rpx;
  box-shadow: 0 14rpx 30rpx rgba(17, 24, 39, 0.05);
  overflow: hidden;
}

.ranking-card-top {
  background: linear-gradient(180deg, #fffdfa, #ffffff);
}

.ranking-sideband {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 12rpx;
}

.ranking-sideband.top1,
.ranking-ribbon.top1 {
  background: linear-gradient(180deg, #ffd54f, #ff9800);
}

.ranking-sideband.top2,
.ranking-ribbon.top2 {
  background: linear-gradient(180deg, #d3dce6, #94a3b8);
}

.ranking-sideband.top3,
.ranking-ribbon.top3 {
  background: linear-gradient(180deg, #ffc2a3, #ff7f50);
}

.ranking-sideband.normal,
.ranking-ribbon.normal {
  background: linear-gradient(180deg, #eceff3, #d1d5db);
}

.ranking-ribbon {
  position: absolute;
  left: 16rpx;
  top: 18rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 42rpx;
  height: 58rpx;
  border-radius: 10rpx 10rpx 16rpx 16rpx;
  box-shadow: 0 8rpx 16rpx rgba(17, 24, 39, 0.08);
}

.ranking-ribbon-number {
  color: #ffffff;
  font-size: 22rpx;
  font-weight: 800;
}

.ranking-cover {
  width: 164rpx;
  height: 224rpx;
  border-radius: 18rpx;
  background: #f3f4f6;
  flex-shrink: 0;
}

.ranking-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.ranking-topline {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16rpx;
}

.ranking-title {
  font-size: 34rpx;
  font-weight: 800;
  color: #1f2937;
}

.ranking-hot {
  padding: 8rpx 14rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 20rpx;
  white-space: nowrap;
}

.ranking-meta {
  margin-top: 12rpx;
  font-size: 22rpx;
  color: #8f8f8f;
  line-height: 1.6;
}

.tag-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10rpx;
  margin-top: 14rpx;
  min-height: 42rpx;
}

.tag-chip {
  padding: 8rpx 14rpx;
  border-radius: 12rpx;
  background: #f4f4f5;
  color: #666666;
  font-size: 20rpx;
}

.ranking-footer {
  display: flex;
  flex-direction: column;
  gap: 10rpx;
  margin-top: auto;
}

.ranking-price {
  font-size: 30rpx;
  font-weight: 800;
  color: #ff8a00;
}

.ranking-stats {
  font-size: 20rpx;
  color: #9ca3af;
}

.floating-actions {
  position: fixed;
  right: 24rpx;
  bottom: 54rpx;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 16rpx;
}

.share-main {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 144rpx;
  height: 144rpx;
  border-radius: 50%;
  background: linear-gradient(135deg, #ff9d19, #ff7b00);
  box-shadow: 0 16rpx 30rpx rgba(255, 123, 0, 0.25);
}

.share-main-icon {
  font-size: 44rpx;
  color: #ffffff;
}

.share-main-text {
  margin-top: 8rpx;
  font-size: 24rpx;
  color: #ffffff;
}

.share-sub {
  padding: 20rpx 34rpx;
  border-radius: 999rpx;
  background: #ffffff;
  box-shadow: 0 12rpx 24rpx rgba(17, 24, 39, 0.08);
}

.share-sub-text {
  font-size: 24rpx;
  font-weight: 700;
  color: #1f2937;
}
</style>
