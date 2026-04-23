<template>
  <view class="script-list">
    <view v-for="item in items" :key="item.id" class="script-item card" @click="$emit('select', item.id)">
      <view class="sideband"></view>
      <image class="cover" :src="toAssetUrl(item.thumbnail) || 'https://dummyimage.com/360x280/f59e0b/ffffff&text=%E5%89%A7%E6%9C%AC'" mode="aspectFill" />
      <view class="meta">
        <view class="topline">
          <text class="title">{{ item.name }}</text>
          <text class="hot">点赞 {{ item.like_count || 0 }}</text>
        </view>
        <text class="muted">{{ item.min_players }}-{{ item.max_players }} 人 · {{ item.duration }} 分钟</text>
        <view class="tags">
          <text class="tag">{{ item.type || '沉浸' }}</text>
          <text class="tag">Lv.{{ item.horror_level || 0 }}</text>
        </view>
      </view>
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

defineEmits(['select'])
</script>

<style scoped>
.script-list {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.script-item {
  position: relative;
  display: flex;
  gap: 18rpx;
  padding: 22rpx 18rpx 22rpx 28rpx;
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

.cover {
  width: 180rpx;
  height: 150rpx;
  border-radius: 18rpx;
}

.meta {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 10rpx;
}

.topline {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12rpx;
}

.title {
  font-size: 30rpx;
  font-weight: 800;
}

.hot {
  padding: 8rpx 12rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 20rpx;
}

.tags {
  display: flex;
  gap: 10rpx;
  flex-wrap: wrap;
}

.tag {
  padding: 6rpx 12rpx;
  border-radius: 4rpx;
  background: #f5f5f5;
  color: #666666;
  font-size: 22rpx;
}
</style>
