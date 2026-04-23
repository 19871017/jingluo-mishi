<template>
  <view class="script-card-list">
    <view v-for="item in list" :key="item.id" class="script-card" @click="goDetail(item)">
      <image class="script-thumbnail" :src="item.thumbnail || '/static/images/default-script.png'" mode="aspectFill" />
      <view class="script-info">
        <text class="script-name">{{ item.name }}</text>
        <text class="script-brand" v-if="item.brand">{{ item.brand.name }}</text>
        <view class="script-meta">
          <text class="script-likes">❤️ {{ item.like_count || 0 }}</text>
          <text class="script-views">👁 {{ item.view_count || 0 }}</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  props: {
    list: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    goDetail(item) {
      uni.navigateTo({ url: `/pages/script/script-detail?id=${item.id}` })
    }
  }
}
</script>

<style scoped>
.script-card-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20rpx;
}

.script-card {
  background: #fff;
  border-radius: 16rpx;
  overflow: hidden;
}

.script-thumbnail {
  width: 100%;
  height: 240rpx;
}

.script-info {
  padding: 16rpx;
}

.script-name {
  display: block;
  font-size: 28rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 8rpx;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.script-brand {
  display: block;
  font-size: 24rpx;
  color: #999;
  margin-bottom: 8rpx;
}

.script-meta {
  display: flex;
  gap: 16rpx;
}

.script-likes,
.script-views {
  font-size: 22rpx;
  color: #999;
}
</style>
