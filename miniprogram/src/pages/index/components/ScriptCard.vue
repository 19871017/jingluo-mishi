<template>
  <view class="script-card card" @click="$emit('click')">
    <image class="thumb" :src="toAssetUrl(item.thumbnail) || 'https://dummyimage.com/640x420/5f7cff/ffffff&text=Script'" mode="aspectFill" />
    <view class="content">
      <text class="name">{{ item.name }}</text>
      <view class="bottom">
        <text class="tag">点赞 {{ item.like_count || 0 }}</text>
        <text class="arrow">查看详情</text>
      </view>
      <view class="meta">
        <text class="meta-tag">{{ item.type || '沉浸' }}</text>
        <text class="meta-tag">{{ item.min_players || 4 }}-{{ item.max_players || 8 }}人</text>
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
.script-card {
  overflow: hidden;
}

.thumb {
  width: 100%;
  height: 200rpx;
}

.content {
  padding: 16rpx;
}

.name {
  display: block;
  font-size: 28rpx;
  font-weight: 500;
  margin-bottom: 12rpx;
}

.bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.arrow {
  color: #2563eb;
  font-size: 24rpx;
}

.meta {
  display: flex;
  gap: 10rpx;
  flex-wrap: wrap;
  margin-top: 10rpx;
}

.meta-tag {
  padding: 6rpx 12rpx;
  border-radius: 4rpx;
  background: #f5f5f5;
  color: #666666;
  font-size: 22rpx;
}
</style>
