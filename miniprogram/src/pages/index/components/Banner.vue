<template>
  <swiper class="banner-swiper" circular autoplay interval="4000" duration="500">
    <swiper-item v-for="item in items" :key="item.id">
      <view class="banner-slide">
        <image class="banner-image" :src="toAssetUrl(item.image) || 'https://dummyimage.com/1200x720/7c5cff/ffffff&text=Banner'" mode="aspectFill" />
        <view class="banner-copy">
          <text class="banner-title">{{ bannerTitle(item.link) }}</text>
          <text class="banner-desc">{{ bannerDesc(item.link) }}</text>
        </view>
      </view>
    </swiper-item>
  </swiper>
</template>

<script setup>
import { toAssetUrl } from '../../../services/http'

defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})

function bannerTitle(link) {
  if ((link || '').startsWith('script/')) return '重点剧本推荐'
  if ((link || '').startsWith('brand/')) return '品牌合作专题'
  if ((link || '').startsWith('market')) return '市集热点入口'
  return '首页主推内容'
}

function bannerDesc(link) {
  if ((link || '').startsWith('script/')) return '适合承接剧本详情流量和重点内容曝光'
  if ((link || '').startsWith('brand/')) return '适合品牌联动和授权导流'
  if ((link || '').startsWith('market')) return '适合市集求购和转让内容承接'
  return '活动、剧本、品牌、市集都可以在这里承接曝光'
}
</script>

<style scoped>
.banner-swiper {
  width: 100%;
  height: 320rpx;
  border-radius: 24rpx;
  overflow: hidden;
}

.banner-slide {
  position: relative;
  width: 100%;
  height: 100%;
}

.banner-image {
  width: 100%;
  height: 100%;
}

.banner-copy {
  position: absolute;
  left: 24rpx;
  right: 24rpx;
  bottom: 24rpx;
  padding: 20rpx;
  border-radius: 20rpx;
  background: rgba(15, 23, 42, 0.35);
  backdrop-filter: blur(8px);
}

.banner-title {
  display: block;
  color: #ffffff;
  font-size: 32rpx;
  font-weight: 700;
  margin-bottom: 8rpx;
}

.banner-desc {
  color: rgba(255,255,255,0.9);
  font-size: 24rpx;
}
</style>
