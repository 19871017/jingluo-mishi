<template>
  <view class="gallery-shell">
    <swiper class="gallery" circular indicator-dots indicator-active-color="#ffffff" indicator-color="rgba(255,255,255,0.35)">
      <swiper-item v-for="item in items" :key="item.id || item.url">
        <image class="gallery-image" :src="toAssetUrl(item.url) || 'https://dummyimage.com/800x600/8896b8/ffffff&text=Gallery'" mode="aspectFill" />
      </swiper-item>
    </swiper>
    <view class="gallery-overlay">
      <text class="gallery-caption">沉浸式剧本展示</text>
      <text class="gallery-hint">左右滑动查看场景图</text>
    </view>
  </view>
</template>

<script setup>
import { toAssetUrl } from '../../../services/http'

defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<style scoped>
.gallery-shell {
  position: relative;
}

.gallery {
  width: 100%;
  height: 460rpx;
  border-radius: 30rpx;
  overflow: hidden;
  box-shadow: 0 16rpx 32rpx rgba(17, 24, 39, 0.12);
}

.gallery-image {
  width: 100%;
  height: 100%;
}

.gallery-overlay {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 28rpx 24rpx;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.55));
  border-radius: 0 0 30rpx 30rpx;
  pointer-events: none;
}

.gallery-caption {
  display: block;
  font-size: 28rpx;
  font-weight: 700;
  color: #ffffff;
}

.gallery-hint {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: rgba(255, 255, 255, 0.85);
}
</style>
