<template>
  <slot />
</template>

<style>
page {
  min-height: 100%;
  background: #ffffff;
  color: #1f2937;
  font-size: 28rpx;
  line-height: 1.6;
  font-family: "PingFang SC", "Helvetica Neue", Arial, sans-serif;
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

input,
textarea,
button {
  font: inherit;
}

.card {
  background: #ffffff;
  border-radius: 24rpx;
  border: 1rpx solid rgba(255, 138, 31, 0.08);
  box-shadow: 0 12rpx 28rpx rgba(17, 24, 39, 0.04);
}

.section-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20rpx;
  font-size: 32rpx;
  font-weight: 800;
  letter-spacing: 0.3rpx;
}

.muted {
  color: #9ca3af;
}

.tag {
  display: inline-flex;
  align-items: center;
  padding: 8rpx 16rpx;
  border-radius: 12rpx;
  background: #f5f5f5;
  color: #666666;
  font-size: 22rpx;
  line-height: 1.4;
}

.primary-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 84rpx;
  border-radius: 16rpx;
  background: linear-gradient(135deg, #ffb24c, #ff7b00);
  color: #ffffff;
  font-size: 28rpx;
  font-weight: 700;
  letter-spacing: 0.4rpx;
  box-shadow: 0 12rpx 22rpx rgba(255, 138, 31, 0.18);
}
</style>
