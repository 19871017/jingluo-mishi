<template>
  <scroll-view scroll-y class="market-page">
    <view class="market-shell">
      <view class="market-hero">
        <view class="hero-glow hero-glow-left"></view>
        <view class="hero-glow hero-glow-right"></view>
        <text class="hero-caption">社区交流</text>
        <text class="hero-title">玩家交流社区</text>
        <text class="hero-desc">发起讨论、分享踩店体验、交流剧本观点和运营心得</text>
      </view>

      <view class="summary-card card">
        <view>
          <text class="summary-title">社区概览</text>
          <text class="summary-subtitle">当前共 {{ market.total || (market.listings || []).length }} 条交流内容</text>
        </view>
        <text class="summary-action" @click="goPublish">发起话题</text>
      </view>

      <view class="sort-row card">
        <text v-for="item in sortOptions" :key="item.value" class="sort-chip" :class="{ active: sort === item.value }" @click="changeSort(item.value)">{{ item.label }}</text>
      </view>

      <view v-if="market.featured?.length" class="section-card card">
        <view class="section-head">
          <view>
            <text class="section-title">精选讨论</text>
            <text class="section-subtitle">优先展示近期值得看的热门话题</text>
          </view>
        </view>
        <scroll-view scroll-x class="featured-scroll" show-scrollbar="false">
          <view class="featured-row">
            <view v-for="item in market.featured || []" :key="item.id" class="featured-card" @click="goDetail(item.id)">
              <text class="featured-badge">精选</text>
              <text class="featured-title">{{ item.title }}</text>
              <text class="featured-desc">{{ item.description || '暂无内容摘要' }}</text>
              <text class="featured-meta">{{ item.user_nickname || '匿名用户' }} · {{ formatDate(item.created_at) }}</text>
            </view>
          </view>
        </scroll-view>
      </view>

      <view class="section-head page-section-head">
        <view>
          <text class="section-title">最新交流</text>
          <text class="section-subtitle">社区最新发帖与讨论内容</text>
        </view>
      </view>

      <view class="listing-list">
        <ListingCard v-for="item in market.listings || []" :key="item.id" :item="item" />
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { useAuthStore } from '../../stores/auth'
import ListingCard from './components/ListingCard.vue'
import { syncCustomTabBar } from '../../utils/tabbar'

const market = ref({ listings: [], featured: [], total: 0 })
const auth = useAuthStore()
const sort = ref('latest')
const sortOptions = [
  { label: '最新', value: 'latest' },
  { label: '最热', value: 'hot' },
  { label: '精选', value: 'featured' },
]

async function fetchMarket() {
  market.value = await api.getMarket('', 1, 50, sort.value)
}

async function changeSort(value) {
  if (sort.value === value) return
  sort.value = value
  await fetchMarket()
}

function goPublish() {
  if (!auth.isLoggedIn) {
    uni.showToast({ title: '请先登录后发起话题', icon: 'none' })
    return
  }
  uni.navigateTo({ url: '/pages/user/publish' })
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/market/market-detail?id=${id}` })
}

function formatDate(value) {
  return String(value || '').slice(0, 10) || '最近更新'
}

onMounted(fetchMarket)
onMounted(() => syncCustomTabBar(3))
</script>

<style scoped>
.market-page {
  height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(255, 181, 72, 0.12), transparent 20%),
    linear-gradient(180deg, #fff7ef 0%, #f6f7fb 24%, #f6f7fb 100%);
}

.market-shell {
  padding: 24rpx;
}

.market-hero {
  position: relative;
  overflow: hidden;
  padding: 28rpx 24rpx 24rpx;
  border-radius: 30rpx;
  background: linear-gradient(135deg, #ffb64a 0%, #ff8d1a 58%, #ff6f00 100%);
  box-shadow: 0 16rpx 40rpx rgba(255, 132, 0, 0.16);
}

.hero-glow { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.12); }
.hero-glow-left { top: -80rpx; left: -40rpx; width: 220rpx; height: 220rpx; }
.hero-glow-right { top: 20rpx; right: -60rpx; width: 260rpx; height: 260rpx; }

.hero-caption,.hero-title,.hero-desc { position: relative; z-index: 1; display: block; color: #fff; }
.hero-caption { font-size: 20rpx; letter-spacing: 1.2rpx; opacity: .8; }
.hero-title { margin-top: 12rpx; font-size: 44rpx; font-weight: 800; line-height: 1.25; }
.hero-desc { margin-top: 10rpx; font-size: 24rpx; opacity: .92; line-height: 1.7; }

.summary-card { margin-top: 22rpx; padding: 24rpx; display:flex; align-items:center; justify-content:space-between; gap:16rpx; }
.summary-title { display:block; font-size:30rpx; font-weight:800; color:#1f2937; line-height:1.4; }
.summary-subtitle { display:block; margin-top:8rpx; font-size:22rpx; color:#8b8b8b; line-height:1.6; }
.summary-action { padding:10rpx 18rpx; border-radius:999rpx; background:#fff1de; color:#f97316; font-size:22rpx; font-weight:700; white-space:nowrap; }
.sort-row { margin-top: 18rpx; padding: 18rpx; display:flex; gap:12rpx; }
.sort-chip { padding: 12rpx 20rpx; border-radius: 999rpx; background:#f8fafc; color:#4b5563; font-size:22rpx; font-weight:700; }
.sort-chip.active { background: linear-gradient(135deg,#ffb24c,#ff7b00); color:#fff; box-shadow: 0 10rpx 20rpx rgba(255,138,31,.18); }

.section-card { margin-top: 22rpx; padding: 24rpx; }
.section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16rpx; margin-bottom:18rpx; }
.section-title { display:block; font-size:32rpx; font-weight:800; color:#1f2937; line-height:1.35; }
.section-subtitle { display:block; margin-top:8rpx; font-size:22rpx; color:#9ca3af; line-height:1.6; }
.page-section-head { margin-top: 24rpx; }

.featured-scroll { white-space: nowrap; width: 100%; }
.featured-row { display:flex; gap:16rpx; }
.featured-card { width: 340rpx; flex-shrink: 0; padding: 22rpx; border-radius: 22rpx; background: linear-gradient(180deg, #fffaf5, #ffffff); border: 1rpx solid rgba(255, 138, 31, 0.08); }
.featured-badge { display:inline-flex; padding:8rpx 14rpx; border-radius:999rpx; background:#fff1de; color:#f97316; font-size:20rpx; font-weight:700; }
.featured-title { display:block; margin-top:16rpx; font-size:28rpx; font-weight:800; color:#1f2937; line-height:1.45; }
.featured-desc { display:block; margin-top:10rpx; font-size:22rpx; color:#6b7280; line-height:1.6; min-height:72rpx; }
.featured-meta { display:block; margin-top:14rpx; font-size:20rpx; color:#9ca3af; }

.listing-list { display:flex; flex-direction:column; gap:20rpx; margin-top: 18rpx; }
</style>
