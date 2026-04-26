<template>
  <scroll-view scroll-y class="user-page">
    <view class="user-shell">
      <view class="hero-card">
        <view class="hero-glow hero-glow-left"></view>
        <view class="hero-glow hero-glow-right"></view>

        <view class="profile-block">
          <view class="avatar">{{ auth.profile?.nickname?.slice(0, 1) || '游' }}</view>
          <view class="info">
            <text class="name">{{ auth.profile?.nickname || '未登录用户' }}</text>
            <text class="hero-tip">{{ auth.isLoggedIn ? '已登录，可参与社区交流和内容互动' : '登录后可发帖、收藏、关注与浏览记录同步' }}</text>
          </view>
          <button v-if="auth.isLoggedIn" class="logout-btn" @click="logout">退出</button>
        </view>

        <view v-if="auth.isLoggedIn" class="hero-badges">
          <text class="hero-badge">{{ currentRoleLabel }}</text>
          <text class="hero-badge soft">{{ auth.canManageConstructionCases ? '已具备施工案例权限' : '施工案例权限待申请' }}</text>
        </view>

        <view v-if="!auth.isLoggedIn" class="login-panel">
          <text class="login-panel-title">微信授权登录开放后可体验完整功能</text>
          <text class="login-panel-desc">登录后可发帖、评论、申请施工案例权限，并同步你的浏览与互动记录。</text>
          <button class="demo-login-btn" @click="demoLogin">演示登录</button>
        </view>
      </view>

      <view class="section-card card">
        <view class="section-head">
          <view>
            <text class="section-title">个人概览</text>
            <text class="section-subtitle">快速查看你的内容沉淀与互动数据</text>
          </view>
        </view>

        <view class="stats-grid">
          <view class="stat-card">
            <text class="stat-value">{{ stats.favorites }}</text>
            <text class="stat-label">收藏</text>
            <text class="stat-tip">剧本与品牌收藏</text>
          </view>
          <view class="stat-card">
            <text class="stat-value">{{ stats.follows }}</text>
            <text class="stat-label">关注</text>
            <text class="stat-tip">品牌动态持续追踪</text>
          </view>
          <view class="stat-card">
            <text class="stat-value">{{ stats.listings }}</text>
            <text class="stat-label">话题</text>
            <text class="stat-tip">已发布交流内容</text>
          </view>
          <view class="stat-card">
            <text class="stat-value">{{ stats.interests }}</text>
            <text class="stat-label">关注话题</text>
            <text class="stat-tip">正在关注的讨论</text>
          </view>
        </view>
      </view>

      <view class="section-card card quick-card">
        <view class="section-head compact">
          <view>
            <text class="section-title">快捷操作</text>
            <text class="section-subtitle">优先进入最常用的社区功能</text>
          </view>
        </view>

        <view class="quick-grid">
          <view class="quick-item" @click="goPublish">
            <view class="quick-icon warm">发</view>
            <text class="quick-title">发起话题</text>
            <text class="quick-desc">分享观点与交流经验</text>
          </view>
          <view class="quick-item" @click="goListings">
            <view class="quick-icon strong">帖</view>
            <text class="quick-title">我的话题</text>
            <text class="quick-desc">查看我发布的内容</text>
          </view>
          <view class="quick-item" @click="goInterests">
            <view class="quick-icon soft">关</view>
            <text class="quick-title">关注话题</text>
            <text class="quick-desc">持续追踪讨论进展</text>
          </view>
          <view class="quick-item" @click="goRecentViews">
            <view class="quick-icon neutral">览</view>
            <text class="quick-title">最近浏览</text>
            <text class="quick-desc">回看浏览过的内容</text>
          </view>
        </view>
      </view>

      <view class="section-card card">
        <view class="section-head compact">
          <view>
            <text class="section-title">内容中心</text>
            <text class="section-subtitle">围绕剧本、品牌、社区内容进行统一管理</text>
          </view>
        </view>

        <view class="menu-list">
          <view class="menu-item" @click="goFavorites">
            <view>
              <text class="menu-title">我的收藏</text>
              <text class="menu-desc">收藏的剧本、品牌与重点内容</text>
            </view>
            <text class="menu-arrow">›</text>
          </view>
          <view class="menu-item" @click="goFollows">
            <view>
              <text class="menu-title">我的关注</text>
              <text class="menu-desc">关注的品牌与持续跟进的内容</text>
            </view>
            <text class="menu-arrow">›</text>
          </view>
          <view class="menu-item" @click="goListings">
            <view>
              <text class="menu-title">我的话题</text>
              <text class="menu-desc">我发起的交流帖与社区内容</text>
            </view>
            <text class="menu-arrow">›</text>
          </view>
          <view class="menu-item" @click="goInterests">
            <view>
              <text class="menu-title">关注的话题</text>
              <text class="menu-desc">我正在关注与参与的讨论</text>
            </view>
            <text class="menu-arrow">›</text>
          </view>
          <view class="menu-item" @click="goRecentViews">
            <view>
              <text class="menu-title">最近浏览</text>
              <text class="menu-desc">回到最近看过的剧本、品牌与话题</text>
            </view>
            <text class="menu-arrow">›</text>
          </view>
          <view class="menu-item" @click="goCasePermission">
            <view>
              <text class="menu-title">案例权限申请</text>
              <text class="menu-desc">品牌方/施工方提交申请，通过后可上传施工案例</text>
            </view>
            <text class="menu-arrow">›</text>
          </view>
        </view>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { api } from '../../services/api'
import { syncCustomTabBar } from '../../utils/tabbar'

const auth = useAuthStore()
const stats = ref({ favorites: 0, follows: 0, listings: 0, interests: 0 })
const currentRoleLabel = computed(() => {
  if (auth.profile?.role === 'brand') return '品牌方'
  if (auth.profile?.role === 'constructor') return '施工方'
  if (auth.profile?.role === 'admin') return '管理员'
  return '普通用户'
})

async function loadStats() {
  if (!auth.isLoggedIn) return
  const [favorites, follows, listings, interests] = await Promise.all([
    api.getFavorites(),
    api.getFollows(),
    api.getMyListings(),
    api.getInterests(),
  ])
  stats.value = {
    favorites: (favorites.list || []).length,
    follows: (follows.list || []).length,
    listings: (listings.list || []).length,
    interests: (interests.list || []).length,
  }
}

function goFavorites() {
  uni.navigateTo({ url: '/pages/user/favorites' })
}

function goFollows() {
  uni.navigateTo({ url: '/pages/user/follows' })
}

function goPublish() {
  uni.navigateTo({ url: '/pages/user/publish' })
}

function goListings() {
  uni.navigateTo({ url: '/pages/user/listings' })
}

function goInterests() {
  uni.navigateTo({ url: '/pages/user/interests' })
}

function goRecentViews() {
  uni.navigateTo({ url: '/pages/user/recent' })
}

function goCasePermission() {
  uni.navigateTo({ url: '/pages/case/case-permission' })
}

function logout() {
  auth.logout()
  stats.value = { favorites: 0, follows: 0, listings: 0, interests: 0 }
}

async function demoLogin() {
  await auth.quickLogin()
  uni.showToast({ title: '演示登录成功', icon: 'success' })
  loadStats()
}

onMounted(loadStats)
onMounted(() => syncCustomTabBar(4))
</script>

<style scoped>
.user-page {
  height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(255, 181, 72, 0.12), transparent 20%),
    linear-gradient(180deg, #fff7ef 0%, #f6f7fb 24%, #f6f7fb 100%);
}

.user-shell {
  padding: 24rpx;
}

.hero-card {
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

.profile-block {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 20rpx;
}

.avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 112rpx;
  height: 112rpx;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
  font-size: 42rpx;
  font-weight: 800;
  box-shadow: inset 0 0 0 2rpx rgba(255, 255, 255, 0.18);
}

.info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10rpx;
}

.name {
  font-size: 36rpx;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.25;
}

.hero-tip {
  font-size: 22rpx;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.92);
}

.logout-btn {
  margin: 0;
  min-width: 120rpx;
  height: 72rpx;
  border-radius: 999rpx;
  background: rgba(255, 255, 255, 0.18);
  color: #ffffff;
  font-size: 24rpx;
}

.hero-badges {
  position: relative;
  z-index: 1;
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
  margin-top: 22rpx;
}

.hero-badge {
  padding: 10rpx 16rpx;
  border-radius: 999rpx;
  background: rgba(255, 255, 255, 0.18);
  color: #ffffff;
  font-size: 22rpx;
  font-weight: 700;
}

.hero-badge.soft {
  background: rgba(255, 255, 255, 0.12);
}

.login-panel {
  position: relative;
  z-index: 1;
  margin-top: 24rpx;
  padding: 20rpx 22rpx;
  border-radius: 18rpx;
  background: rgba(255, 255, 255, 0.14);
  backdrop-filter: blur(8rpx);
}

.login-panel-title {
  display: block;
  font-size: 26rpx;
  font-weight: 700;
  color: #ffffff;
  line-height: 1.45;
}

.login-panel-desc {
  display: block;
  margin-top: 10rpx;
  font-size: 22rpx;
  line-height: 1.65;
  color: rgba(255, 255, 255, 0.9);
}

.demo-login-btn {
  margin-top: 20rpx;
  padding: 16rpx 32rpx;
  border-radius: 999rpx;
  background: linear-gradient(135deg, #ffb24c, #ff7b00);
  color: #fff;
  font-size: 26rpx;
  font-weight: 700;
  box-shadow: 0 10rpx 20rpx rgba(255, 138, 31, 0.3);
}

.section-card {
  margin-top: 24rpx;
  padding: 24rpx;
}

.section-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16rpx;
  margin-bottom: 20rpx;
}

.section-head.compact {
  margin-bottom: 18rpx;
}

.section-title {
  display: block;
  font-size: 32rpx;
  font-weight: 800;
  color: #1f2937;
  line-height: 1.35;
}

.section-subtitle {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #9ca3af;
  line-height: 1.6;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 18rpx;
}

.stat-card {
  padding: 22rpx;
  border-radius: 20rpx;
  background: linear-gradient(180deg, #fffaf5, #ffffff);
  border: 1rpx solid rgba(255, 138, 31, 0.08);
}

.stat-value {
  display: block;
  font-size: 40rpx;
  font-weight: 800;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  display: block;
  margin-top: 10rpx;
  font-size: 24rpx;
  font-weight: 700;
  color: #4b5563;
}

.stat-tip {
  display: block;
  margin-top: 8rpx;
  font-size: 20rpx;
  color: #9ca3af;
}

.quick-card {
  background: linear-gradient(180deg, #fffdfa, #ffffff);
}

.quick-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 18rpx;
}

.quick-item {
  padding: 22rpx;
  border-radius: 20rpx;
  background: #fafafa;
}

.quick-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 64rpx;
  height: 64rpx;
  border-radius: 18rpx;
  font-size: 26rpx;
  font-weight: 800;
}

.quick-icon.warm { background: #fff1de; color: #f97316; }
.quick-icon.strong { background: #edf5ff; color: #2563eb; }
.quick-icon.soft { background: #eefaf6; color: #0f9f6e; }
.quick-icon.neutral { background: #f3f4f6; color: #4b5563; }

.quick-title {
  display: block;
  margin-top: 16rpx;
  font-size: 28rpx;
  font-weight: 800;
  color: #1f2937;
  line-height: 1.35;
}

.quick-desc {
  display: block;
  margin-top: 10rpx;
  font-size: 20rpx;
  line-height: 1.6;
  color: #9ca3af;
}

.menu-list {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
}

.menu-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20rpx;
  padding: 22rpx 0;
  border-bottom: 1rpx solid #f3f4f6;
}

.menu-item:last-child {
  border-bottom: 0;
  padding-bottom: 4rpx;
}

.menu-title {
  display: block;
  font-size: 28rpx;
  font-weight: 700;
  color: #1f2937;
  line-height: 1.35;
}

.menu-desc {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  line-height: 1.6;
  color: #9ca3af;
}

.menu-arrow {
  font-size: 34rpx;
  color: #c0c4cc;
}
</style>
