<template>
  <view class="my-scripts-page">
    <view class="page-header">
      <text class="header-title">我的剧本</text>
      <text class="header-back" @click="navigateBack">返回</text>
    </view>

    <view class="scripts-list" v-if="scripts.length > 0">
      <view class="script-item" v-for="script in scripts" :key="script.id">
        <image 
          :src="script.cover_image || 'https://img.yzcdn.cn/vant/ipad.jpeg'" 
          class="script-cover" 
          mode="aspectFill"
        />
        <view class="script-info">
          <text class="script-title">{{ script.name }}</text>
          <text class="script-meta">
            {{ script.min_players }}-{{ script.max_players }}人 | {{ script.duration }}分钟
          </text>
          <text class="script-status" :class="getStatusClass(script.status)">
            {{ getStatusText(script.status) }}
          </text>
        </view>
      </view>
    </view>

    <view class="empty-state" v-else>
      <text class="empty-icon">📚</text>
      <text class="empty-text">暂无剧本</text>
      <text class="empty-desc" v-if="userRole === 'brand'">
        您可以发布新的剧本
      </text>
    </view>
  </view>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../services/api'
import { useStore } from '../../store'

const router = useRouter()
const store = useStore()
const scripts = ref([])
const userRole = ref('member')

const navigateBack = () => {
  router.back()
}

const getStatusClass = (status) => {
  if (status === 'approved') return 'status-approved'
  if (status === 'rejected') return 'status-rejected'
  return 'status-pending'
}

const getStatusText = (status) => {
  if (status === 'approved') return '已通过'
  if (status === 'rejected') return '已拒绝'
  return '待审核'
}

const loadUserRole = async () => {
  try {
    const permissionData = await api.getConstructionCasePermission()
    userRole.value = permissionData.current_role || store.profile?.role || 'member'
  } catch (error) {
    console.error('加载用户角色失败:', error)
  }
}

const loadMyScripts = async () => {
  try {
    const data = await api.getMyScripts()
    scripts.value = data.list
  } catch (error) {
    console.error('加载剧本失败:', error)
  }
}

onMounted(async () => {
  await loadUserRole()
  await loadMyScripts()
})
</script>

<style scoped>
.my-scripts-page {
  min-height: 100vh;
  background-color: #f5f5f5;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 30rpx;
  background-color: #fff;
  border-bottom: 1rpx solid #f0f0f0;
  position: sticky;
  top: 0;
  z-index: 10;
}

.header-title {
  font-size: 32rpx;
  font-weight: 600;
  color: #333;
}

.header-back {
  font-size: 28rpx;
  color: #666;
}

.scripts-list {
  padding: 20rpx;
}

.script-item {
  display: flex;
  background-color: #fff;
  border-radius: 12rpx;
  overflow: hidden;
  margin-bottom: 20rpx;
  box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);
}

.script-cover {
  width: 160rpx;
  height: 220rpx;
}

.script-info {
  flex: 1;
  padding: 20rpx;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.script-title {
  font-size: 32rpx;
  font-weight: 600;
  color: #333;
  margin-bottom: 12rpx;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.script-meta {
  font-size: 24rpx;
  color: #666;
  margin-bottom: 16rpx;
}

.script-status {
  font-size: 22rpx;
  padding: 4rpx 12rpx;
  border-radius: 12rpx;
  align-self: flex-start;
}

.status-pending {
  background-color: #fff3cd;
  color: #856404;
}

.status-approved {
  background-color: #d4edda;
  color: #155724;
}

.status-rejected {
  background-color: #f8d7da;
  color: #721c24;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 0;
  background-color: #fff;
  margin: 20rpx;
  border-radius: 12rpx;
}

.empty-icon {
  font-size: 80rpx;
  margin-bottom: 20rpx;
}

.empty-text {
  font-size: 32rpx;
  color: #999;
  margin-bottom: 12rpx;
}

.empty-desc {
  font-size: 24rpx;
  color: #666;
}
</style>