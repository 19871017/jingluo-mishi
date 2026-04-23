<template>
  <view class="brand-detail-page">
    <view v-if="loading" class="loading">加载中...</view>
    <template v-else-if="brand">
      <view class="brand-header">
        <image class="brand-logo" :src="brand.logo" mode="aspectFill" />
        <view class="brand-info">
          <text class="brand-name">{{ brand.name }}</text>
          <text class="brand-desc">{{ brand.description }}</text>
        </view>
      </view>

      <view class="brand-stats">
        <view class="stat-item">
          <text class="stat-value">{{ brand.total_authorizations }}</text>
          <text class="stat-label">累计授权</text>
        </view>
        <view class="stat-item">
          <text class="stat-value">{{ brand.total_views }}</text>
          <text class="stat-label">浏览量</text>
        </view>
        <view class="stat-item">
          <text class="stat-value">{{ brand.total_likes }}</text>
          <text class="stat-label">获赞</text>
        </view>
        <view class="stat-item">
          <text class="stat-value">{{ brand.follower_count }}</text>
          <text class="stat-label">关注</text>
        </view>
      </view>

      <view class="brand-actions">
        <button
          class="btn"
          :class="brand.is_followed ? 'btn-default' : 'btn-primary'"
          @click="toggleFollow"
        >
          {{ brand.is_followed ? '已关注' : '关注' }}
        </button>
      </view>

      <view class="section">
        <view class="section-title">热门剧本</view>
        <ScriptCard :list="brand.hot_scripts" />
      </view>

      <view class="section">
        <view class="section-title">全部剧本</view>
        <ScriptCard :list="brand.all_scripts" />
      </view>
    </template>
  </view>
</template>

<script>
import ScriptCard from '../../components/ScriptCard.vue'
import { brandApi } from '../../services/api'

export default {
  components: {
    ScriptCard
  },
  data() {
    return {
      brand: null,
      loading: false
    }
  },
  onLoad(options) {
    this.fetchBrandDetail(options.id)
  },
  methods: {
    async fetchBrandDetail(id) {
      this.loading = true
      try {
        this.brand = await brandApi.getDetail(id)
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    async toggleFollow() {
      try {
        if (this.brand.is_followed) {
          await brandApi.unfollow(this.brand.id)
          this.brand.is_followed = false
          this.brand.follower_count--
        } else {
          await brandApi.follow(this.brand.id)
          this.brand.is_followed = true
          this.brand.follower_count++
        }
      } catch (e) {
        console.error(e)
      }
    }
  }
}
</script>

<style scoped>
.brand-detail-page {
  padding: 24rpx;
}

.brand-header {
  display: flex;
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
}

.brand-logo {
  width: 120rpx;
  height: 120rpx;
  border-radius: 50%;
  margin-right: 20rpx;
}

.brand-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 10rpx;
}

.brand-desc {
  font-size: 26rpx;
  color: #666;
  line-height: 1.5;
}

.brand-stats {
  display: flex;
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
}

.stat-item {
  flex: 1;
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 8rpx;
}

.stat-label {
  font-size: 24rpx;
  color: #999;
}

.brand-actions {
  margin-bottom: 30rpx;
}

.btn {
  width: 100%;
  height: 80rpx;
  line-height: 80rpx;
  border-radius: 40rpx;
  font-size: 28rpx;
}

.btn-primary {
  background: #1890ff;
  color: #fff;
}

.btn-default {
  background: #f5f5f5;
  color: #666;
}

.section {
  margin-bottom: 30rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
}

.loading {
  text-align: center;
  padding: 100rpx;
  color: #999;
}
</style>
