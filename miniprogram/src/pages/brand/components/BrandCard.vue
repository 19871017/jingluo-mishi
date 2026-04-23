<template>
  <view class="brand-card card" @click="$emit('click')">
    <view class="sideband"></view>
    <image class="logo" :src="toAssetUrl(item.logo) || 'https://dummyimage.com/200x200/18b89a/ffffff&text=Brand'" mode="aspectFill" />
    <view class="meta">
      <view class="topline">
        <text class="name">{{ item.name }}</text>
        <text class="hot">{{ item.total_likes || 0 }} 热度</text>
      </view>
      <text class="muted">关注 {{ item.follower_count || 0 }} · 浏览 {{ item.total_views || 0 }}</text>
      <text class="desc">品牌授权内容与热度概况一览</text>
      <view class="tags">
        <text class="tag">授权 {{ item.total_authorizations || 0 }}</text>
        <text class="tag">点赞 {{ item.total_likes || 0 }}</text>
      </view>
    </view>
  </view>
</template>

<script setup>
import { toAssetUrl } from '../../../services/http'

defineProps({
  item: {
    type: Object,
    default: () => ({}),
  },
})

defineEmits(['click'])
</script>

<style scoped>
.brand-card {
  position: relative;
  display: flex;
  align-items: center;
  gap: 20rpx;
  padding: 22rpx 20rpx 22rpx 28rpx;
  overflow: hidden;
}

.sideband {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 10rpx;
  background: linear-gradient(180deg, #ffb24c, #ff7b00);
}

.logo {
  width: 116rpx;
  height: 116rpx;
  border-radius: 22rpx;
  background: #f5f5f5;
  flex-shrink: 0;
}

.meta {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10rpx;
}

.topline {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12rpx;
}

.name {
  font-size: 30rpx;
  font-weight: 800;
  color: #1f2937;
}

.hot {
  padding: 8rpx 12rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 20rpx;
}

.desc {
  font-size: 22rpx;
  color: #8b8b8b;
}

.tags {
  display: flex;
  gap: 10rpx;
  flex-wrap: wrap;
}

.tag {
  padding: 8rpx 14rpx;
  border-radius: 12rpx;
  background: #f5f5f5;
  color: #666666;
  font-size: 22rpx;
}
</style>
