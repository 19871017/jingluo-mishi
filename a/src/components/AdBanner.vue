<template>
  <view class="ad-banner">
    <view v-for="item in ads" :key="item.id" class="ad-item" @click="onClick(item)">
      <image class="ad-image" :src="item.image" mode="aspectFill" />
    </view>
  </view>
</template>

<script>
export default {
  props: {
    ads: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    onClick(item) {
      if (item.link) {
        if (item.link.startsWith('brand/')) {
          const id = item.link.split('/')[1]
          uni.navigateTo({ url: `/pages/brand/brand-detail?id=${id}` })
        }
      }
    }
  }
}
</script>

<style scoped>
.ad-banner {
  display: flex;
  gap: 16rpx;
  padding: 20rpx 24rpx;
}

.ad-item {
  flex: 1;
}

.ad-image {
  width: 100%;
  height: 160rpx;
  border-radius: 12rpx;
}
</style>
