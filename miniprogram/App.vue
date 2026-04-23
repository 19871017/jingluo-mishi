<template>
  <view />
</template>

<script setup>
</script>

<style>
page {
  min-height: 100%;
  background: #f5f7fb;
  color: #1f2937;
  font-size: 28rpx;
}

view,
text,
swiper,
swiper-item,
image,
button,
input,
textarea,
scroll-view {
  box-sizing: border-box;
}

.page-container {
  padding: 24rpx;
}

.card {
  background: #ffffff;
  border-radius: 24rpx;
  box-shadow: 0 10rpx 30rpx rgba(15, 23, 42, 0.05);
}

.section-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20rpx;
  font-size: 34rpx;
  font-weight: 700;
}

.muted {
  color: #6b7280;
}

.tag {
  display: inline-flex;
  align-items: center;
  padding: 10rpx 18rpx;
  border-radius: 999rpx;
  background: #eef2ff;
  color: #4f46e5;
  font-size: 22rpx;
}

.primary-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 88rpx;
  border-radius: 18rpx;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: #ffffff;
  font-size: 30rpx;
  font-weight: 600;
}
</style>
