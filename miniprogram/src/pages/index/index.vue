<template>
  <scroll-view
    scroll-y
    class="page-scroll"
    :refresher-enabled="true"
    :refresher-triggered="refreshing"
    @refresherrefresh="onRefresh"
  >
    <view class="home-page">
      <view class="home-hero">
        <view class="hero-orb hero-orb-left"></view>
        <view class="hero-orb hero-orb-right"></view>

        <view class="search-bar" @click="goSearch()">
          <text class="search-icon">&#9906;</text>
          <text class="search-placeholder">请输入内容</text>
        </view>

        <view class="hero-copy">
          <text class="hero-caption">Escape Room Curation</text>
          <text class="hero-title">鲸落密室档案馆</text>
        </view>

        <view class="poster-section">
          <swiper
            class="poster-swiper"
            :style="{ height: `${posterHeight}rpx` }"
            circular
            autoplay
            interval="3600"
            duration="450"
            indicator-dots
            indicator-active-color="#ffffff"
            indicator-color="rgba(255,255,255,0.36)"
            @change="handlePosterChange"
          >
            <swiper-item v-for="item in posterItems" :key="item.id">
              <view class="poster-card" :class="{ portrait: posterIsPortrait }" @click="openPosterPreview(item)">
                <image class="poster-image" :src="posterImage(item)" :mode="posterImageMode" @load="handlePosterLoad" />
                <view class="poster-mask">
                  <text class="poster-mask-title">{{ posterTitle(item) }}</text>
                  <view v-if="posterMeta(item)" class="poster-meta-row">
                    <text class="poster-meta-chip">{{ posterMeta(item) }}</text>
                  </view>
                </view>
              </view>
            </swiper-item>
          </swiper>
          <view class="poster-dots">
            <view
              v-for="item in posterDots"
              :key="item"
              class="poster-dot"
              :class="{ active: item - 1 === activePosterIndex }"
            ></view>
          </view>
        </view>
      </view>

      <view class="channel-row card">
        <view v-for="item in channels" :key="item.key" class="channel-item" @click="item.action">
          <view class="channel-icon" :class="item.tone">
            <image class="channel-icon-image" :src="item.icon" mode="aspectFit" />
          </view>
          <text class="channel-label">{{ item.label }}</text>
        </view>
      </view>

      <view class="content-list">
        <view
          v-for="item in featuredScripts"
          :key="item.id"
          class="content-card card"
          @click="goScript(item.id)"
        >
          <view class="content-ribbon">HOT</view>
          <image class="content-cover" :src="toAssetUrl(item.thumbnail) || fallbackScript" mode="aspectFill" />
          <view class="content-body">
            <view class="content-topline">
              <text class="content-title">{{ item.name }}</text>
              <text class="content-score">{{ item.like_count || 0 }} 点赞</text>
            </view>
            <view class="content-tags">
              <text class="highlight-text">热门推荐</text>
              <text class="light-text">{{ formatSummaryPrice(item) }}</text>
            </view>
            <text class="content-meta">
              {{ item.min_players || 4 }}-{{ item.max_players || 8 }}人
              {{ item.duration || 60 }}分钟
              {{ item.type || '沉浸逃脱' }}
            </text>
            <view class="content-footer">
              <text class="content-price">{{ formatPrice(item) }}</text>
              <text class="content-stats">{{ item.view_count || item.total_views || 0 }} 浏览 · {{ item.like_count || 0 }} 点赞 · {{ item.collect_count || 0 }} 收藏 · {{ item.purchase_count || 0 }} 已购</text>
            </view>
          </view>
        </view>
      </view>

      <EmptyState
        v-if="!featuredScripts.length && !refreshing"
        title="暂无首页内容"
        description="下拉刷新后再试试"
      />

      <view v-if="previewVisible && previewPoster" class="poster-preview" @click="closePosterPreview">
        <view class="poster-preview-backdrop"></view>
        <view class="poster-preview-dialog">
          <image class="poster-preview-image" :src="posterImage(previewPoster)" mode="aspectFit" @click.stop="enterPosterDetail" />
          <view class="poster-preview-copy">
            <text class="poster-preview-title">大图预览</text>
            <text class="poster-preview-subtitle">再次点击图片进入详情页</text>
          </view>
          <view class="poster-preview-actions">
            <view class="poster-preview-btn subtle" @click.stop="closePosterPreview">关闭</view>
            <view class="poster-preview-btn strong" @click.stop="enterPosterDetail">进入详情</view>
          </view>
        </view>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { toAssetUrl } from '../../services/http'
import EmptyState from '../../components/EmptyState.vue'
import { syncCustomTabBar } from '../../utils/tabbar'
import { formatScriptPrice } from '../../utils/format'

const fallbackPoster = 'https://dummyimage.com/300x420/1f2937/ffffff&text=Poster'
const fallbackScript = 'https://dummyimage.com/240x240/111827/ffffff&text=Script'

const home = ref({ banners: [], ads: [], scripts: [] })
const refreshing = ref(false)
const previewVisible = ref(false)
const previewPoster = ref(null)
const posterHeight = ref(440)
const posterIsPortrait = ref(false)
const activePosterIndex = ref(0)

const posterImageMode = computed(() => (posterIsPortrait.value ? 'aspectFit' : 'aspectFill'))

function svgToDataUri(svg) {
  return `data:image/svg+xml;utf8,${encodeURIComponent(svg)}`
}

function channelIcon(type) {
  const palette = {
    warm: { stroke: '#f97316', fill: '#fff6ee', accent: '#fb923c' },
    strong: { stroke: '#2563eb', fill: '#f2f7ff', accent: '#60a5fa' },
    hot: { stroke: '#7c3aed', fill: '#f7f1ff', accent: '#a78bfa' },
    soft: { stroke: '#0f9f6e', fill: '#effcf7', accent: '#34d399' },
    neutral: { stroke: '#4b5563', fill: '#f8fafc', accent: '#94a3b8' },
  }
  const color = palette[type]
  const icons = {
    coming: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="6" y="6" width="52" height="52" rx="18" fill="${color.fill}"/><circle cx="32" cy="32" r="19" fill="none" stroke="${color.accent}" stroke-opacity=".22" stroke-width="2"/><path d="M22 41h20M24 27h16M30 22h4M27 27v13M37 27v13" stroke="${color.stroke}" stroke-width="3.2" stroke-linecap="round"/></svg>`,
    ranking: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="6" y="6" width="52" height="52" rx="18" fill="${color.fill}"/><circle cx="32" cy="32" r="19" fill="none" stroke="${color.accent}" stroke-opacity=".22" stroke-width="2"/><path d="M22 41h20" stroke="${color.stroke}" stroke-width="3.2" stroke-linecap="round"/><path d="M25 41V29M32 41V22M39 41V33" stroke="${color.stroke}" stroke-width="3.2" stroke-linecap="round"/><path d="M23 24l9 6 10-10" stroke="${color.accent}" stroke-width="2.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    trend: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="6" y="6" width="52" height="52" rx="18" fill="${color.fill}"/><circle cx="32" cy="32" r="19" fill="none" stroke="${color.accent}" stroke-opacity=".24" stroke-width="2"/><path d="M20 41h24" stroke="${color.stroke}" stroke-width="3" stroke-linecap="round"/><path d="M22 38l8-10 6 6 8-12" stroke="${color.stroke}" stroke-width="3.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/><circle cx="30" cy="28" r="2.3" fill="${color.accent}"/><circle cx="36" cy="34" r="2.3" fill="${color.accent}"/><circle cx="44" cy="22" r="2.3" fill="${color.accent}"/></svg>`,
    case: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="6" y="6" width="52" height="52" rx="18" fill="${color.fill}"/><circle cx="32" cy="32" r="19" fill="none" stroke="${color.accent}" stroke-opacity=".24" stroke-width="2"/><path d="M22 42h20" stroke="${color.stroke}" stroke-width="3" stroke-linecap="round"/><path d="M24 42l8-18h3l8 18" stroke="${color.stroke}" stroke-width="3.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M26 34h12" stroke="${color.accent}" stroke-width="2.8" stroke-linecap="round"/><path d="M42 22h4v4" stroke="${color.stroke}" stroke-width="3" fill="none" stroke-linecap="round"/></svg>`,
    community: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="6" y="6" width="52" height="52" rx="18" fill="${color.fill}"/><circle cx="32" cy="32" r="19" fill="none" stroke="${color.accent}" stroke-opacity=".22" stroke-width="2"/><path d="M20 24h24a4 4 0 0 1 4 4v11a4 4 0 0 1-4 4H33l-7 6v-6h-6a4 4 0 0 1-4-4V28a4 4 0 0 1 4-4Z" stroke="${color.stroke}" stroke-width="3" fill="none" stroke-linejoin="round"/><path d="M25 31h14M25 37h10" stroke="${color.accent}" stroke-width="2.8" stroke-linecap="round"/></svg>`,
  }
  return svgToDataUri(icons[type])
}

const channels = computed(() => [
  { key: 'coming-new', label: '敬请期待', icon: channelIcon('coming'), tone: 'tone-warm', action: () => goSearch('新品') },
  { key: 'ranking', label: '热门榜单', icon: channelIcon('ranking'), tone: 'tone-strong', action: goRanking },
  { key: 'trend', label: '主题趋势', icon: channelIcon('trend'), tone: 'tone-hot', action: goTrend },
  { key: 'construction-case', label: '施工案例', icon: channelIcon('case'), tone: 'tone-soft', action: goConstructionCases },
  { key: 'coming-market', label: '社区互动', icon: channelIcon('community'), tone: 'tone-soft', action: goMarket },
])

const posterItems = computed(() => {
  if (home.value.scripts?.length) {
    return home.value.scripts.slice(0, 3).map((item) => ({
      id: `script-${item.id}`,
      image: item.thumbnail,
      link: `script/${item.id}`,
      title: item.name,
      type: item.type,
      min_players: item.min_players,
      max_players: item.max_players,
      duration: item.duration,
      like_count: item.like_count,
    }))
  }

  return (home.value.banners || []).slice(0, 3).map((item) => ({
    ...item,
    title: '首页推荐',
  }))
})

const posterDots = computed(() => Array.from({ length: Math.max(1, posterItems.value.length) }, (_, index) => index + 1))
const featuredScripts = computed(() => (home.value.scripts || []).slice(0, 8))

function posterTitle(item) {
  return item?.title || item?.name || '剧本精选大图'
}

function posterMeta(item) {
  const players = item?.min_players && item?.max_players ? `${item.min_players}-${item.max_players}人` : ''
  const duration = item?.duration ? `${item.duration}分钟` : ''
  const type = item?.type || ''
  return [type, players, duration].filter(Boolean).join(' · ')
}

async function fetchHome() {
  try {
    const data = await api.getHome()
    home.value = {
      banners: data?.banners || [],
      ads: data?.ads || [],
      scripts: data?.scripts || [],
    }
  } catch (_) {
    home.value = { banners: [], ads: [], scripts: [] }
  }
}

async function onRefresh() {
  refreshing.value = true
  await fetchHome()
  refreshing.value = false
  uni.showToast({ title: '刷新成功', icon: 'success' })
}

function posterImage(item) {
  return toAssetUrl(item.image || item.thumbnail) || fallbackPoster
}

function handlePosterLoad(event) {
  const { width, height } = event?.detail || {}
  if (!width || !height) return

  const ratio = height / width
  posterIsPortrait.value = ratio > 1.15
  const nextHeight = Math.round(702 * ratio)

  posterHeight.value = Math.min(620, Math.max(320, nextHeight))
}

function handlePosterChange(event) {
  activePosterIndex.value = Number(event?.detail?.current || 0)
}

function openPosterPreview(item) {
  if (!item) return
  previewPoster.value = item
  previewVisible.value = true
}

function closePosterPreview() {
  previewVisible.value = false
  previewPoster.value = null
}

function enterPosterDetail() {
  const item = previewPoster.value
  closePosterPreview()
  handlePosterClick(item)
}

function handlePosterClick(item) {
  if (!item) {
    goSearch()
    return
  }

  const link = item.link || ''
  if (link.startsWith('script/')) {
    goScript(link.split('/')[1])
    return
  }
  if (link.startsWith('brand/')) {
    goBrandDetail(link.split('/')[1])
    return
  }
  if (link.startsWith('market')) {
    goMarket()
    return
  }
  goSearch()
}

function formatPrice(item) {
  return formatScriptPrice(item, '面议')
}

function formatSummaryPrice(item) {
  return formatScriptPrice(item, '综合热度优先')
}

function goSearch(keyword = '') {
  uni.navigateTo({ url: `/pages/search/search${keyword ? `?keyword=${encodeURIComponent(keyword)}` : ''}` })
}

function goConstructionCases() {
  uni.navigateTo({ url: '/pages/case/case-list' })
}

function goTrend() {
  uni.navigateTo({ url: '/pages/trend/trend' })
}

function goRanking() {
  uni.navigateTo({ url: '/pages/ranking/ranking' })
}

function goMarket() {
  uni.switchTab({ url: '/pages/market/market' })
}

function goScript(id) {
  uni.navigateTo({ url: `/pages/script/script-detail?id=${id}` })
}

function goBrandDetail(id) {
  uni.navigateTo({ url: `/pages/brand/brand-detail?id=${id}` })
}

onMounted(fetchHome)
onMounted(() => syncCustomTabBar(0))
</script>

<style scoped>
.page-scroll {
  height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(255, 181, 72, 0.12), transparent 22%),
    linear-gradient(180deg, #fff7ef 0%, #f6f7fb 24%, #f6f7fb 100%);
}

.home-page {
  padding: 24rpx;
}

.home-hero {
  position: relative;
  overflow: hidden;
  padding: 28rpx 24rpx 24rpx;
  border-radius: 30rpx;
  background: linear-gradient(135deg, #ffb64a 0%, #ff8d1a 58%, #ff6f00 100%);
  box-shadow: 0 16rpx 40rpx rgba(255, 132, 0, 0.16);
}

.hero-orb {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.12);
}

.hero-orb-left {
  top: -80rpx;
  left: -40rpx;
  width: 220rpx;
  height: 220rpx;
}

.hero-orb-right {
  top: 40rpx;
  right: -60rpx;
  width: 260rpx;
  height: 260rpx;
}

.search-bar {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 14rpx;
  height: 72rpx;
  padding: 0 24rpx;
  border-radius: 36rpx;
  background: rgba(255, 250, 245, 0.92);
}

.search-icon {
  color: #c0c0c0;
  font-size: 28rpx;
}

.search-placeholder {
  color: #b6b6b6;
  font-size: 28rpx;
}

.hero-copy {
  position: relative;
  z-index: 1;
  margin-top: 26rpx;
}

.hero-caption {
  display: block;
  font-size: 20rpx;
  color: rgba(255, 255, 255, 0.8);
  letter-spacing: 1.2rpx;
}

.hero-title {
  display: block;
  margin-top: 12rpx;
  font-size: 44rpx;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.25;
}

.hero-desc {
  display: block;
  margin-top: 12rpx;
  font-size: 24rpx;
  color: rgba(255, 255, 255, 0.92);
  line-height: 1.7;
}

.poster-section {
  position: relative;
  z-index: 1;
  margin-top: 28rpx;
}

.poster-swiper {
  width: 100%;
}

.poster-swiper :deep(.uni-swiper-wrapper),
.poster-swiper :deep(.wx-swiper-wrapper),
.poster-swiper :deep(swiper-item) {
  height: 100%;
}

.poster-card {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
  border-radius: 24rpx;
  box-shadow: 0 16rpx 34rpx rgba(17, 24, 39, 0.2);
  background: #111827;
}

.poster-card.portrait {
  background: linear-gradient(180deg, #0f172a, #1f2937);
}

.poster-image {
  width: 100%;
  height: 100%;
}

.poster-mask {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  gap: 6rpx;
  padding: 26rpx 24rpx 24rpx;
  background: linear-gradient(180deg, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, 0.72) 100%);
}

.poster-mask-title {
  color: #ffffff;
  font-size: 30rpx;
  font-weight: 800;
  line-height: 1.35;
}

.poster-meta-row {
  margin-top: 8rpx;
}

.poster-meta-chip {
  display: inline-flex;
  align-items: center;
  min-height: 42rpx;
  padding: 0 16rpx;
  border-radius: 999rpx;
  background: rgba(255, 255, 255, 0.16);
  color: #ffffff;
  font-size: 20rpx;
}

.poster-dots {
  display: flex;
  justify-content: center;
  gap: 10rpx;
  margin-top: 18rpx;
}

.poster-dot {
  width: 12rpx;
  height: 12rpx;
  border-radius: 999rpx;
  background: #d9d9d9;
}

.poster-dot.active {
  width: 30rpx;
  background: #ffffff;
}

.poster-preview {
  position: fixed;
  inset: 0;
  z-index: 99;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32rpx;
}

.poster-preview-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.84);
}

.poster-preview-dialog {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 680rpx;
  padding: 24rpx;
  border-radius: 28rpx;
  background: #0f172a;
  box-shadow: 0 20rpx 60rpx rgba(0, 0, 0, 0.3);
}

.poster-preview-image {
  width: 100%;
  height: 820rpx;
  border-radius: 22rpx;
  background: #111827;
}

.poster-preview-copy {
  margin-top: 22rpx;
}

.poster-preview-title {
  display: block;
  color: #ffffff;
  font-size: 32rpx;
  font-weight: 800;
}

.poster-preview-subtitle {
  display: block;
  margin-top: 8rpx;
  color: rgba(255, 255, 255, 0.72);
  font-size: 22rpx;
}

.poster-preview-actions {
  display: flex;
  gap: 16rpx;
  margin-top: 24rpx;
}

.poster-preview-btn {
  flex: 1;
  height: 78rpx;
  border-radius: 18rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26rpx;
  font-weight: 700;
}

.poster-preview-btn.subtle {
  background: rgba(255, 255, 255, 0.08);
  color: #e5e7eb;
}

.poster-preview-btn.strong {
  background: linear-gradient(135deg, #ffb24c, #ff7b00);
  color: #ffffff;
}

.channel-row {
  display: flex;
  justify-content: space-between;
  margin-top: 24rpx;
  padding: 26rpx 16rpx 20rpx;
  border-radius: 26rpx;
  background: linear-gradient(180deg, #fffdfa, #ffffff);
}

.channel-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 20%;
  gap: 10rpx;
}

.channel-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 84rpx;
  height: 84rpx;
  border-radius: 50%;
  border: 4rpx solid #f0d0ad;
  background: #fff7ef;
  box-shadow: 0 8rpx 16rpx rgba(17, 24, 39, 0.06);
}

.channel-icon-image {
  width: 48rpx;
  height: 48rpx;
}

.channel-icon.tone-strong {
  border-color: #ff9d33;
  color: #ff7d00;
}

.channel-icon.tone-hot {
  border-color: #ffb24c;
  color: #ff8a00;
}

.channel-icon.tone-soft {
  border-color: #e5d4bf;
  color: #d1ae84;
}

.channel-label {
  font-size: 22rpx;
  color: #6b7280;
  text-align: center;
  line-height: 1.5;
}

.content-list {
  margin-top: 24rpx;
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.content-card {
  position: relative;
  display: flex;
  gap: 20rpx;
  padding: 22rpx;
  border: none;
  border-radius: 26rpx;
  overflow: hidden;
}

.content-ribbon {
  position: absolute;
  right: 18rpx;
  top: 18rpx;
  padding: 8rpx 14rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 20rpx;
  font-weight: 700;
}

.content-cover {
  width: 164rpx;
  height: 182rpx;
  border-radius: 18rpx;
  background: #f1f1f1;
  flex-shrink: 0;
}

.content-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.content-topline {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14rpx;
  padding-right: 76rpx;
}

.content-title {
  font-size: 32rpx;
  font-weight: 700;
  color: #222222;
  line-height: 1.4;
}

.content-score {
  padding: 8rpx 12rpx;
  border-radius: 999rpx;
  background: #fff8ef;
  color: #f59e0b;
  font-size: 20rpx;
  white-space: nowrap;
}

.content-tags {
  display: flex;
  align-items: center;
  gap: 10rpx;
  margin-top: 12rpx;
}

.highlight-text {
  color: #ff8c00;
  font-size: 22rpx;
  font-weight: 700;
}

.light-text {
  color: #ff9d33;
  font-size: 22rpx;
}

.content-meta {
  margin-top: 12rpx;
  color: #8b8b8b;
  font-size: 24rpx;
  line-height: 1.7;
}

.content-footer {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-top: auto;
  padding-top: 18rpx;
}

.content-price {
  color: #ff7d00;
  font-size: 40rpx;
  font-weight: 800;
  line-height: 1;
}

.content-stats {
  color: #9a9a9a;
  font-size: 20rpx;
  line-height: 1.5;
}
</style>
