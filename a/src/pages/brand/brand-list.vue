<template>
  <view class="brand-list-page">
    <view v-if="loading" class="loading">加载中...</view>
    <view v-else-if="brands.length === 0" class="empty">暂无品牌</view>
    <view v-else class="brand-list">
      <view
        v-for="brand in brands"
        :key="brand.id"
        class="brand-card"
        @click="goDetail(brand.id)"
      >
        <image class="brand-logo" :src="brand.logo" mode="aspectFill" />
        <view class="brand-info">
          <text class="brand-name">{{ brand.name }}</text>
          <text class="brand-followers">{{ brand.follower_count }}人关注</text>
        </view>
      </view>
      <view v-if="hasMore" class="loading" @click="loadMore">加载更多</view>
    </view>
  </view>
</template>

<script>
import { brandApi } from '../../services/api'

export default {
  data() {
    return {
      brands: [],
      page: 1,
      limit: 20,
      total: 0,
      loading: false,
      hasMore: true
    }
  },
  onLoad() {
    this.fetchBrands()
  },
  onPullDownRefresh() {
    this.fetchBrands().finally(() => {
      uni.stopPullDownRefresh()
    })
  },
  methods: {
    async fetchBrands() {
      this.loading = true
      try {
        const data = await brandApi.list({ page: this.page, limit: this.limit })
        this.brands = [...this.brands, ...data.list]
        this.total = data.total
        this.hasMore = this.brands.length < this.total
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    loadMore() {
      this.page++
      this.fetchBrands()
    },
    goDetail(id) {
      uni.navigateTo({ url: `/pages/brand/brand-detail?id=${id}` })
    }
  }
}
</script>

<style scoped>
.brand-list-page {
  padding: 24rpx;
}

.brand-card {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
}

.brand-logo {
  width: 100rpx;
  height: 100rpx;
  border-radius: 50%;
  margin-right: 20rpx;
}

.brand-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 10rpx;
}

.brand-followers {
  font-size: 24rpx;
  color: #999;
}

.loading, .empty {
  text-align: center;
  padding: 40rpx;
  color: #999;
}
</style>
