<template>
  <view class="skeleton">
    <view v-if="type === 'card'" class="skeleton-card">
      <view class="skeleton-image"></view>
      <view class="skeleton-content">
        <view class="skeleton-line short"></view>
        <view class="skeleton-line"></view>
        <view class="skeleton-line medium"></view>
      </view>
    </view>
    <view v-else-if="type === 'list'" class="skeleton-list">
      <view v-for="i in count" :key="i" class="skeleton-item">
        <view class="skeleton-avatar"></view>
        <view class="skeleton-info">
          <view class="skeleton-line"></view>
          <view class="skeleton-line short"></view>
        </view>
      </view>
    </view>
    <view v-else class="skeleton-text">
      <view v-for="i in count" :key="i" class="skeleton-line" :class="{ short: i % 2 !== 0 }"></view>
    </view>
  </view>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'text',
  },
  count: {
    type: Number,
    default: 3,
  },
})
</script>

<style scoped>
.skeleton {
  width: 100%;
}

.skeleton-card {
  background: #ffffff;
  border-radius: 24rpx;
  overflow: hidden;
  border: 1rpx solid rgba(255, 138, 31, 0.08);
}

.skeleton-image {
  width: 100%;
  height: 200rpx;
  background: linear-gradient(90deg, #f5f5f5 25%, #e5e5e5 50%, #f5f5f5 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-content {
  padding: 20rpx;
}

.skeleton-line {
  height: 24rpx;
  background: #f5f5f5;
  border-radius: 8rpx;
  margin-bottom: 12rpx;
}

.skeleton-line.short {
  width: 60%;
}

.skeleton-line.medium {
  width: 80%;
}

.skeleton-list {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.skeleton-item {
  display: flex;
  gap: 20rpx;
  padding: 22rpx;
  background: #ffffff;
  border-radius: 24rpx;
  border: 1rpx solid rgba(255, 138, 31, 0.08);
}

.skeleton-avatar {
  width: 96rpx;
  height: 96rpx;
  border-radius: 18rpx;
  background: #f5f5f5;
}

.skeleton-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12rpx;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>
