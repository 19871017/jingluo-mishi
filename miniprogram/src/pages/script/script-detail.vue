<template>
  <scroll-view scroll-y class="detail-page">
    <view class="detail-shell" v-if="detail">
      <ImageGallery :items="galleryItems" />

      <view class="hero card">
        <view class="hero-topline">
          <view>
            <text class="hero-caption">剧本详情</text>
            <text class="hero-title">{{ detail.name }}</text>
          </view>
          <text class="hero-price">{{ formatScriptPrice(detail) }}</text>
        </view>
        <text class="hero-meta">{{ detail.min_players || 0 }}-{{ detail.max_players || 0 }} 人 · {{ detail.duration || 0 }} 分钟 · {{ detail.type || detail.category?.name || '剧本' }}</text>
        <text class="hero-brand" @click="goBrand(detail.brand?.id)">所属品牌：{{ detail.brand?.name || '未关联品牌' }}</text>
        <text class="hero-desc">{{ detail.description || '暂无剧本简介。' }}</text>
        <view class="meta-row">
          <text class="meta-chip">{{ detail.view_count || 0 }} 浏览</text>
          <text class="meta-chip">{{ detail.like_count || 0 }} 点赞</text>
          <text class="meta-chip">{{ detail.collect_count || 0 }} 收藏</text>
          <text class="meta-chip">{{ detail.purchase_count || 0 }} 已购</text>
        </view>
        <view class="action-row">
          <button class="secondary-btn" @click="toggleLike">点赞剧本</button>
          <button class="primary-btn" @click="toggleCollect">收藏剧本</button>
        </view>
      </view>

      <view v-if="detail.video_url" class="section-card card">
        <text class="section-title">视频预览</text>
        <video class="video-player" :src="toAssetUrl(detail.video_url)" controls object-fit="cover"></video>
      </view>

      <view class="section-card card">
        <text class="section-title">详细介绍</text>
        <text class="section-content">{{ detail.detail_content || detail.description || '暂无详细介绍。' }}</text>
      </view>

      <view v-if="themeEntries.length" class="section-card card">
        <text class="section-title">主题信息</text>
        <view class="info-list">
          <view v-for="item in themeEntries" :key="item.label" class="info-item">
            <text class="info-label">{{ item.label }}</text>
            <text class="info-value">{{ item.value }}</text>
          </view>
        </view>
      </view>

      <view v-if="detailEntries.length" class="section-card card">
        <text class="section-title">场地信息</text>
        <view class="info-list">
          <view v-for="item in detailEntries" :key="item.label" class="info-item">
            <text class="info-label">{{ item.label }}</text>
            <text class="info-value">{{ item.value }}</text>
          </view>
        </view>
      </view>

      <view v-if="authEntries.length" class="section-card card">
        <text class="section-title">授权信息</text>
        <view class="info-list">
          <view v-for="item in authEntries" :key="item.label" class="info-item">
            <text class="info-label">{{ item.label }}</text>
            <text class="info-value">{{ item.value }}</text>
          </view>
        </view>
      </view>
    </view>

    <EmptyState v-else title="剧本不存在" description="请返回上一页重新选择剧本。" />
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { toAssetUrl } from '../../services/http'
import { useAuthStore } from '../../stores/auth'
import EmptyState from '../../components/EmptyState.vue'
import ImageGallery from './components/ImageGallery.vue'
import { formatScriptPrice } from '../../utils/format'

const detail = ref(null)
const auth = useAuthStore()

const galleryItems = computed(() => {
  const images = detail.value?.images || []
  if (images.length) return images
  if (detail.value?.thumbnail) return [{ url: detail.value.thumbnail }]
  return []
})

const themeEntries = computed(() => mapEntries(detail.value?.theme_attrs))
const detailEntries = computed(() => mapEntries(detail.value?.detail_attrs))
const authEntries = computed(() => mapAuthEntries(detail.value?.auth_info))

async function loadDetail() {
  const { id } = getCurrentPages().slice(-1)[0].options
  detail.value = await api.getScriptDetail(id)
  try {
    await api.markView('script', id)
  } catch (_) {}
}

async function ensureLogin() {
  if (auth.isLoggedIn) return true
  uni.showToast({ title: '请先登录后再操作', icon: 'none' })
  return false
}

async function toggleLike() {
  if (!(await ensureLogin()) || !detail.value) return
  const data = await api.likeScript(detail.value.id)
  detail.value = {
    ...detail.value,
    like_count: Math.max(0, Number(detail.value.like_count || 0) + (data.active ? 1 : -1)),
  }
  uni.showToast({ title: data.active ? '已点赞剧本' : '已取消点赞', icon: 'none' })
}

async function toggleCollect() {
  if (!(await ensureLogin()) || !detail.value) return
  const data = await api.collectScript(detail.value.id)
  detail.value = {
    ...detail.value,
    collect_count: Math.max(0, Number(detail.value.collect_count || 0) + (data.active ? 1 : -1)),
  }
  uni.showToast({ title: data.active ? '已收藏剧本' : '已取消收藏', icon: 'none' })
}

function goBrand(id) {
  if (!id) return
  uni.navigateTo({ url: `/pages/brand/brand-detail?id=${id}` })
}

function mapEntries(source) {
  if (!source || typeof source !== 'object') return []
  return Object.entries(source)
    .filter(([, value]) => String(value || '').trim())
    .map(([label, value]) => ({ label, value: Array.isArray(value) ? value.join(' / ') : String(value) }))
}

function mapAuthEntries(source) {
  if (!source || typeof source !== 'object') return []
  return [
    { label: '授权状态', value: source.status || '' },
    { label: '授权服务', value: Array.isArray(source.services) ? source.services.join(' / ') : '' },
    { label: '可授权城市', value: Array.isArray(source.auth_cities) ? source.auth_cities.join(' / ') : '' },
    { label: '已授权城市', value: Array.isArray(source.authorized_cities) ? source.authorized_cities.join(' / ') : '' },
  ].filter((item) => item.value)
}

onMounted(loadDetail)
</script>

<style scoped>
.detail-page { height: 100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.detail-shell { padding: 24rpx; }
.hero { margin-top: 24rpx; padding: 24rpx; }
.hero-topline { display: flex; align-items: flex-start; justify-content: space-between; gap: 16rpx; }
.hero-caption { display: block; font-size: 20rpx; color: #9ca3af; }
.hero-title { display: block; margin-top: 10rpx; font-size: 38rpx; font-weight: 800; color: #1f2937; }
.hero-price { font-size: 28rpx; font-weight: 800; color: #f97316; }
.hero-meta, .hero-brand, .hero-desc { display: block; margin-top: 14rpx; font-size: 24rpx; line-height: 1.7; color: #6b7280; }
.hero-brand { color: #2563eb; }
.meta-row { display: flex; flex-wrap: wrap; gap: 12rpx; margin-top: 18rpx; }
.meta-chip { padding: 8rpx 14rpx; border-radius: 14rpx; background: #f5f5f5; color: #666; font-size: 22rpx; }
.action-row { display: flex; gap: 16rpx; margin-top: 22rpx; }
.primary-btn, .secondary-btn { flex: 1; height: 84rpx; border-radius: 16rpx; font-size: 26rpx; font-weight: 700; }
.primary-btn { background: linear-gradient(135deg,#ffb24c,#ff7b00); color: #fff; }
.secondary-btn { background: #fff1de; color: #f97316; }
.section-card { margin-top: 24rpx; padding: 24rpx; }
.section-title { display: block; font-size: 30rpx; font-weight: 800; color: #1f2937; margin-bottom: 16rpx; }
.section-content { font-size: 24rpx; color: #4b5563; line-height: 1.8; white-space: pre-wrap; }
.video-player { width: 100%; height: 360rpx; border-radius: 18rpx; background: #000; }
.info-list { display: flex; flex-direction: column; gap: 14rpx; }
.info-item { padding: 18rpx; border-radius: 18rpx; background: #fafafa; }
.info-label { display: block; font-size: 22rpx; color: #9ca3af; }
.info-value { display: block; margin-top: 8rpx; font-size: 24rpx; color: #1f2937; line-height: 1.7; }
</style>
