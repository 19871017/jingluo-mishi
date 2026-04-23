<template>
  <view class="empty-state card">
    <text class="empty-title">{{ title }}</text>
    <text class="empty-desc">{{ description }}</text>
  </view>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: '暂无数据',
  },
  description: {
    type: String,
    default: '请稍后再试',
  },
})
</script>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 72rpx 28rpx;
  gap: 16rpx;
  text-align: center;
}

.empty-title {
  font-size: 30rpx;
  font-weight: 800;
  color: #1f2937;
  line-height: 1.4;
}

.empty-desc {
  color: #9ca3af;
  font-size: 24rpx;
  line-height: 1.7;
}
</style>
