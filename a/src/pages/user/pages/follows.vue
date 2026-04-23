<template>
  <view class="follows-page">
    <view v-if="loading" class="loading">加载中...</view>
    <view v-else-if="follows.length === 0" class="empty">暂无关注</view>
    <view v-else class="follow-list">
      <view
        v-for="brand in follows"
        :key="brand.id"
        class="follow-item"
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
import { userApi } from '../../services/api'

export default {
  data() {
    return {
      follows: [],
      page: 1,
      limit: 20,
      loading: false,
      hasMore: true
    }
  },
  onLoad() {
    this.fetchFollows()
  },
  onPullDownRefresh() {
    this.fetchFollows().finally(() => {
      uni.stopPullDownRefresh()
    })
  },
  methods: {
    async fetchFollows() {
      this.loading = true
      try {
        const data = await userApi.getFollows({ page: this.page, limit: this.limit })
        this.follows = [...this.follows, ...data.list]
        this.hasMore = data.list.length === this.limit
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    loadMore() {
      this.page++
      this.fetchFollows()
    },
    goDetail(id) {
      uni.navigateTo({ url: `/pages/brand/brand-detail?id=${id}` })
    }
  }
}
</script>

<style scoped>
.follows-page {
  padding: 24rpx;
}

.follow-list {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.follow-item {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
}

.brand-logo {
  width: 100rpx;
  height: 100rpx;
  border-radius: 50%;
  margin-right: 20rpx;
}

.brand-info {
  flex: 1;
}

.brand-name {
  display: block;
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
  padding: 100rpx;
  color: #999;
}
</style>
