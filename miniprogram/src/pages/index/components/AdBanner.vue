<template>
  <view class="ad-list">
    <image v-for="item in items" :key="item.id" class="ad-image" :src="toAssetUrl(item.image) || 'https://dummyimage.com/800x480/18b89a/ffffff&text=Ad'" mode="aspectFill" />
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
.ad-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20rpx;
}

.ad-image {
  width: 100%;
  height: 180rpx;
  border-radius: 20rpx;
}
</style>
