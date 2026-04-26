<template>
  <scroll-view scroll-y class="case-page">
    <view class="case-shell">
      <view class="case-hero">
        <view class="hero-glow hero-glow-left"></view>
        <view class="hero-glow hero-glow-right"></view>
        <text class="hero-caption">施工案例库</text>
        <text class="hero-title">品牌施工案例</text>
        <text class="hero-desc">施工方可沉淀现场图片、视频与施工说明，加盟方可按案例节点查看门店落地过程。</text>
      </view>

      <view class="summary-card card">
        <view>
          <text class="summary-title">案例总览</text>
          <text class="summary-subtitle">当前共 {{ filteredCases.length }} 个施工案例，支持品牌维度浏览</text>
        </view>
        <view v-if="auth.canManageConstructionCases" class="summary-actions">
          <text class="summary-action ghost" @click="goManage">我的案例</text>
          <text class="summary-action" @click="goPublish">上传案例</text>
        </view>
        <text v-else class="summary-note">加盟方可浏览案例，品牌方/施工方可上传与管理</text>
      </view>

      <scroll-view scroll-x class="brand-filter" show-scrollbar="false">
        <view class="brand-filter-row">
          <text class="brand-chip" :class="{ active: activeBrand === '' }" @click="changeBrand('')">全部品牌</text>
          <text v-for="item in brands" :key="item" class="brand-chip" :class="{ active: activeBrand === item }" @click="changeBrand(item)">{{ item }}</text>
        </view>
      </scroll-view>

      <view class="case-list">
        <view v-for="item in filteredCases" :key="item.id" class="case-card card" @click="goDetail(item.id)">
          <image class="case-cover" :src="item.cover" mode="aspectFill" />
          <view class="case-body">
            <view class="case-topline">
              <text class="case-title">{{ item.projectName }}</text>
              <text class="case-badge" :class="{ soft: !item.isOfficial }">{{ item.isOfficial ? '官方' : '上传' }}</text>
            </view>
            <text class="case-brand">{{ item.brandName }} · {{ item.phase }}</text>
            <text class="case-desc">{{ item.description }}</text>
            <view class="case-meta-row">
              <text class="meta-chip">{{ item.images.length }} 张图片</text>
              <text class="meta-chip">{{ item.videos.length }} 段视频</text>
              <text class="meta-chip">{{ formatDate(item.createdAt) }}</text>
            </view>
          </view>
        </view>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { useAuthStore } from '../../stores/auth'
import { syncCustomTabBar } from '../../utils/tabbar'

const cases = ref([])
const brands = ref([])
const activeBrand = ref('')
const auth = useAuthStore()

const filteredCases = computed(() => {
  if (!activeBrand.value) return cases.value
  return cases.value.filter((item) => item.brandName === activeBrand.value)
})

async function loadCases() {
  const data = await api.getConstructionCases('', 1, 100)
  cases.value = data.list || []
  brands.value = data.brands || []
}

function changeBrand(brand) {
  activeBrand.value = brand
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/case/case-detail?id=${id}` })
}

function goPublish() {
  uni.navigateTo({ url: '/pages/case/case-publish' })
}

function goManage() {
  uni.navigateTo({ url: '/pages/case/case-manage' })
}

function formatDate(value) {
  return String(value || '').slice(0, 10) || '最近更新'
}

onMounted(loadCases)
onMounted(() => syncCustomTabBar(2))
</script>

<style scoped>
.case-page { height: 100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.case-shell { padding: 24rpx; }
.case-hero { position: relative; overflow:hidden; padding:28rpx 24rpx 24rpx; border-radius:30rpx; background:linear-gradient(135deg,#ffb64a 0%,#ff8d1a 58%,#ff6f00 100%); box-shadow:0 16rpx 40rpx rgba(255,132,0,.16); }
.hero-glow { position:absolute; border-radius:50%; background:rgba(255,255,255,.12); }
.hero-glow-left { top:-80rpx; left:-40rpx; width:220rpx; height:220rpx; }
.hero-glow-right { top:20rpx; right:-60rpx; width:260rpx; height:260rpx; }
.hero-caption,.hero-title,.hero-desc { position:relative; z-index:1; display:block; color:#fff; }
.hero-caption { font-size:20rpx; letter-spacing:1.2rpx; opacity:.8; }
.hero-title { margin-top:12rpx; font-size:44rpx; font-weight:800; line-height:1.25; }
.hero-desc { margin-top:10rpx; font-size:24rpx; line-height:1.7; opacity:.92; }
.summary-card { margin-top:22rpx; padding:24rpx; display:flex; align-items:center; justify-content:space-between; gap:16rpx; }
.summary-title { display:block; font-size:30rpx; font-weight:800; color:#1f2937; line-height:1.4; }
.summary-subtitle { display:block; margin-top:8rpx; font-size:22rpx; color:#8b8b8b; line-height:1.6; }
.summary-actions { display:flex; gap:12rpx; }
.summary-action { padding:10rpx 18rpx; border-radius:999rpx; background:#fff1de; color:#f97316; font-size:22rpx; font-weight:700; white-space:nowrap; }
.summary-action.ghost { background:#f3f4f6; color:#4b5563; }
.summary-note { font-size:22rpx; color:#9ca3af; line-height:1.6; text-align:right; }
.brand-filter { margin-top:18rpx; white-space:nowrap; }
.brand-filter-row { display:flex; gap:12rpx; }
.brand-chip { padding:12rpx 20rpx; border-radius:999rpx; background:#fff; color:#4b5563; font-size:22rpx; font-weight:700; }
.brand-chip.active { background:linear-gradient(135deg,#ffb24c,#ff7b00); color:#fff; box-shadow:0 10rpx 20rpx rgba(255,138,31,.18); }
.case-list { margin-top:20rpx; display:flex; flex-direction:column; gap:20rpx; }
.case-card { overflow:hidden; }
.case-cover { width:100%; height:280rpx; }
.case-body { padding:22rpx; }
.case-topline { display:flex; align-items:flex-start; justify-content:space-between; gap:14rpx; }
.case-title { font-size:30rpx; font-weight:800; color:#1f2937; line-height:1.45; }
.case-badge { padding:8rpx 14rpx; border-radius:999rpx; background:#fff1de; color:#f97316; font-size:20rpx; font-weight:700; }
.case-badge.soft { background:#f3f4f6; color:#4b5563; }
.case-brand { display:block; margin-top:10rpx; font-size:22rpx; color:#f97316; }
.case-desc { display:block; margin-top:12rpx; font-size:22rpx; color:#6b7280; line-height:1.7; }
.case-meta-row { display:flex; flex-wrap:wrap; gap:10rpx; margin-top:14rpx; }
.meta-chip { padding:8rpx 14rpx; border-radius:12rpx; background:#f5f5f5; color:#666; font-size:22rpx; }
</style>
