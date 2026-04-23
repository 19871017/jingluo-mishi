<template>
  <view class="market-page">
    <view class="market-tabs">
      <view
        v-for="tab in tabs"
        :key="tab.value"
        class="tab-item"
        :class="{ active: currentType === tab.value }"
        @click="switchTab(tab.value)"
      >
        {{ tab.label }}
      </view>
    </view>

    <view v-if="loading" class="loading">加载中...</view>
    <template v-else>
      <view v-if="currentType === 'sell'" class="featured-section">
        <view class="section-title">精选推荐</view>
        <scroll-view scroll-x class="featured-scroll">
          <view v-for="item in featured" :key="item.id" class="featured-item" @click="goDetail(item.id)">
            <image class="featured-image" :src="item.images[0]" mode="aspectFill" />
            <text class="featured-title">{{ item.title }}</text>
            <text class="featured-price">{{ formatPrice(item.price) }}</text>
          </view>
        </scroll-view>
      </view>

      <view class="listing-section">
        <view class="section-title">{{ currentType === 'buy' ? '求购信息' : '出售信息' }}</view>
        <view v-if="listings.length === 0" class="empty">暂无信息</view>
        <view v-else class="listing-list">
          <view
            v-for="item in listings"
            :key="item.id"
            class="listing-card"
            @click="goDetail(item.id)"
          >
            <image class="listing-image" :src="item.images[0]" mode="aspectFill" />
            <view class="listing-info">
              <text class="listing-title">{{ item.title }}</text>
              <text class="listing-desc">{{ item.description }}</text>
              <view class="listing-footer">
                <text class="listing-price">{{ formatPrice(item.price) }}</text>
                <text class="listing-user">{{ item.user?.nickname }}</text>
              </view>
            </view>
          </view>
        </view>
        <view v-if="hasMore" class="loading" @click="loadMore">加载更多</view>
      </view>
    </template>
  </view>
</template>

<script>
import { marketApi } from '../../services/api'
import { formatPrice } from '../../utils/format'

export default {
  data() {
    return {
      tabs: [
        { label: '出售', value: 'sell' },
        { label: '求购', value: 'buy' }
      ],
      currentType: 'sell',
      featured: [],
      listings: [],
      page: 1,
      limit: 20,
      total: 0,
      loading: false,
      hasMore: true
    }
  },
  onLoad() {
    this.fetchData()
  },
  onPullDownRefresh() {
    this.fetchData().finally(() => {
      uni.stopPullDownRefresh()
    })
  },
  methods: {
    formatPrice,
    switchTab(type) {
      this.currentType = type
      this.page = 1
      this.listings = []
      this.featured = []
      this.hasMore = true
      this.fetchData()
    },
    async fetchData() {
      this.loading = true
      try {
        const data = await marketApi.list({
          type: this.currentType,
          page: this.page,
          limit: this.limit
        })
        this.featured = data.featured || []
        this.listings = [...this.listings, ...data.listings]
        this.total = data.total
        this.hasMore = this.listings.length < this.total
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    loadMore() {
      this.page++
      this.fetchData()
    },
    goDetail(id) {
      uni.navigateTo({ url: `/pages/market/market-detail?id=${id}` })
    }
  }
}
</script>

<style scoped>
.market-page {
  padding: 24rpx;
}

.market-tabs {
  display: flex;
  gap: 40rpx;
  margin-bottom: 30rpx;
}

.tab-item {
  font-size: 30rpx;
  color: #999;
  padding-bottom: 10rpx;
  border-bottom: 4rpx solid transparent;
}

.tab-item.active {
  color: #1890ff;
  border-bottom-color: #1890ff;
}

.featured-scroll {
  white-space: nowrap;
  margin-bottom: 30rpx;
}

.featured-item {
  display: inline-block;
  width: 240rpx;
  margin-right: 20rpx;
  background: #fff;
  border-radius: 12rpx;
  overflow: hidden;
}

.featured-image {
  width: 240rpx;
  height: 180rpx;
}

.featured-title {
  display: block;
  padding: 16rpx;
  font-size: 26rpx;
  color: #333;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.featured-price {
  display: block;
  padding: 0 16rpx 16rpx;
  font-size: 24rpx;
  color: #ff4d4f;
}

.section-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
}

.listing-list {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.listing-card {
  display: flex;
  background: #fff;
  border-radius: 16rpx;
  padding: 20rpx;
}

.listing-image {
  width: 200rpx;
  height: 150rpx;
  border-radius: 12rpx;
  margin-right: 20rpx;
}

.listing-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.listing-title {
  font-size: 28rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 10rpx;
}

.listing-desc {
  font-size: 24rpx;
  color: #666;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  flex: 1;
}

.listing-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10rpx;
}

.listing-price {
  font-size: 28rpx;
  color: #ff4d4f;
  font-weight: bold;
}

.listing-user {
  font-size: 24rpx;
  color: #999;
}

.loading, .empty {
  text-align: center;
  padding: 40rpx;
  color: #999;
}
</style>
