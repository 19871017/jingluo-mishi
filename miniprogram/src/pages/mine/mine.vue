<template>
  <view class="mine-page">
    <view class="user-info">
      <image class="avatar" src="https://img.yzcdn.cn/vant/logo.png" mode="aspectFill"></image>
      <view class="user-details">
        <text class="username">{{ userInfo?.username || '用户' }}</text>
        <text class="role">{{ roleText }}</text>
      </view>
    </view>

    <view class="menu-list">
      <view class="menu-section">
        <view 
          class="menu-item" 
          v-if="userRole === 'brand'"
          @click="navigateToPublishScript"
        >
          <text class="menu-icon">📝</text>
          <text class="menu-text">发布剧本</text>
          <text class="menu-arrow">→</text>
        </view>
        <view class="menu-item" @click="navigateToMyScripts">
          <text class="menu-icon">📚</text>
          <text class="menu-text">我的剧本</text>
          <text class="menu-arrow">→</text>
        </view>
        <view class="menu-item" @click="navigateToMyCases">
          <text class="menu-icon">🏗️</text>
          <text class="menu-text">我的施工案例</text>
          <text class="menu-arrow">→</text>
        </view>
        <view class="menu-item" @click="navigateToPermissionApply">
          <text class="menu-icon">📋</text>
          <text class="menu-text">权限申请</text>
          <text class="menu-arrow">→</text>
        </view>
      </view>

      <view class="menu-section">
        <view class="menu-item" @click="navigateToSettings">
          <text class="menu-icon">⚙️</text>
          <text class="menu-text">设置</text>
          <text class="menu-arrow">→</text>
        </view>
        <view class="menu-item" @click="navigateToAbout">
          <text class="menu-icon">ℹ️</text>
          <text class="menu-text">关于我们</text>
          <text class="menu-arrow">→</text>
        </view>
      </view>
    </view>

    <view class="logout-btn" @click="handleLogout">
      <text class="logout-text">退出登录</text>
    </view>
  </view>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStore } from '../../store'
import { useRouter } from 'vue-router'
import { api } from '../../services/api'

const store = useStore()
const router = useRouter()
const userInfo = ref(null)
const userRole = ref('member')

const roleText = computed(() => {
  if (userRole.value === 'brand') return '品牌方'
  if (userRole.value === 'constructor') return '施工方'
  if (userRole.value === 'admin') return '管理员'
  return '普通用户'
})

const loadUserInfo = async () => {
  if (!store.isLoggedIn) return
  
  try {
    // 加载用户信息
    const userData = await api.getUserInfo()
    userInfo.value = userData
    
    // 加载用户角色
    const permissionData = await api.getConstructionCasePermission()
    userRole.value = permissionData.current_role || store.profile?.role || 'member'
  } catch (error) {
    console.error('加载用户信息失败:', error)
  }
}

const navigateToPublishScript = () => {
  router.push('/publish-script')
}

const navigateToMyScripts = () => {
  router.push('/my-scripts')
}

const navigateToMyCases = () => {
  router.push('/my-cases')
}

const navigateToPermissionApply = () => {
  router.push('/case-permission')
}

const navigateToSettings = () => {
  router.push('/settings')
}

const navigateToAbout = () => {
  router.push('/about')
}

const handleLogout = () => {
  store.logout()
  router.push('/login')
}

onMounted(() => {
  loadUserInfo()
})
</script>

<style scoped>
.mine-page {
  min-height: 100vh;
  background-color: #f5f5f5;
  padding-bottom: 40rpx;
}

.user-info {
  display: flex;
  align-items: center;
  padding: 30rpx;
  background-color: #fff;
  margin-bottom: 20rpx;
}

.avatar {
  width: 120rpx;
  height: 120rpx;
  border-radius: 50%;
  margin-right: 20rpx;
}

.user-details {
  flex: 1;
}

.username {
  font-size: 32rpx;
  font-weight: 600;
  color: #333;
  margin-bottom: 8rpx;
  display: block;
}

.role {
  font-size: 24rpx;
  color: #666;
}

.menu-list {
  background-color: #fff;
  margin-bottom: 20rpx;
}

.menu-section {
  border-bottom: 1rpx solid #f0f0f0;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 30rpx;
  border-bottom: 1rpx solid #f0f0f0;
}

.menu-item:last-child {
  border-bottom: none;
}

.menu-icon {
  font-size: 32rpx;
  margin-right: 20rpx;
}

.menu-text {
  flex: 1;
  font-size: 28rpx;
  color: #333;
}

.menu-arrow {
  font-size: 24rpx;
  color: #999;
}

.logout-btn {
  margin: 40rpx 30rpx 0;
  padding: 24rpx;
  background-color: #fff;
  border-radius: 12rpx;
  text-align: center;
  border: 1rpx solid #e0e0e0;
}

.logout-text {
  font-size: 28rpx;
  color: #ff4d4f;
}
</style>