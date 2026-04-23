<template>
  <view class="listing-card card" @click="goDetail">
    <view class="listing-header">
      <view class="author-row">
        <view class="avatar">{{ (item.user_nickname || '匿').slice(0, 1) }}</view>
        <view class="author-meta">
          <text class="author-name">{{ item.user_nickname || '匿名用户' }}</text>
          <text class="author-time">{{ formatDate(item.created_at) }}</text>
        </view>
      </view>
      <text v-if="item.is_featured" class="listing-badge">精选</text>
    </view>
    <text class="listing-title">{{ item.title }}</text>
    <text class="listing-desc muted">{{ item.description }}</text>
    <view class="meta-row">
      <text class="meta-tag">交流帖</text>
      <text class="meta-tag">{{ item.status || 'approved' }}</text>
      <text class="meta-tag">{{ item.like_count || 0 }} 点赞</text>
      <text class="meta-tag">{{ item.comment_count || 0 }} 评论</text>
      <text class="meta-tag">欢迎参与讨论</text>
    </view>
  </view>
</template>

<script setup>
const props = defineProps({
  item: {
    type: Object,
    default: () => ({}),
  },
})

function goDetail() {
  uni.navigateTo({ url: `/pages/market/market-detail?id=${props.item.id}` })
}

function formatDate(value) {
  return String(value || '').slice(0, 10) || '最近更新'
}
</script>

<style scoped>
.listing-card {
  padding: 22rpx;
}

.listing-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 14rpx;
}

.author-row {
  display: flex;
  align-items: center;
  gap: 12rpx;
}

.avatar {
  width: 64rpx;
  height: 64rpx;
  border-radius: 50%;
  background: #fff1de;
  color: #f97316;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24rpx;
  font-weight: 700;
}

.author-meta {
  display: flex;
  flex-direction: column;
  gap: 6rpx;
}

.author-name {
  font-size: 24rpx;
  font-weight: 700;
  color: #1f2937;
}

.author-time {
  font-size: 20rpx;
  color: #9ca3af;
}

.listing-badge {
  padding: 8rpx 14rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 20rpx;
  font-weight: 700;
}

.listing-title {
  display: block;
  font-size: 30rpx;
  font-weight: 700;
  margin-bottom: 10rpx;
}

.meta-row {
  display: flex;
  gap: 10rpx;
  flex-wrap: wrap;
  margin-top: 14rpx;
}

.meta-tag {
  padding: 8rpx 16rpx;
  border-radius: 999rpx;
  background: #f3f4f6;
  color: #4b5563;
  font-size: 22rpx;
}

.featured {
  background: #fef3c7;
  color: #a16207;
}
</style>
