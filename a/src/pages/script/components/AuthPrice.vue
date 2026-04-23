<template>
  <view class="auth-price">
    <view class="price-title">授权价格参考</view>
    <view v-if="data.auth_services" class="services">
      <text class="service-tag" v-for="service in data.auth_services" :key="service">
        {{ formatService(service) }}
      </text>
    </view>
    <view v-if="data.city_prices" class="city-prices">
      <view v-for="(price, city) in data.city_prices" :key="city" class="city-item">
        <text class="city-name">{{ formatCity(city) }}</text>
        <text class="city-price">{{ formatPrice(price) }}</text>
      </view>
    </view>
    <view v-if="data.auth_status" class="auth-status">
      <text class="status-text">授权状态：{{ formatStatus(data.auth_status) }}</text>
    </view>
  </view>
</template>

<script>
import { formatPrice } from '../../utils/format'

export default {
  props: {
    data: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    formatPrice,
    formatService(service) {
      const map = {
        full: '全授权',
        script_only: '仅剧本',
        scene_only: '仅场景'
      }
      return map[service] || service
    },
    formatCity(city) {
      const map = {
        tier1: '一线城市',
        tier2: '二线城市',
        tier3: '三线城市'
      }
      return map[city] || city
    },
    formatStatus(status) {
      const map = {
        authorized: '已授权',
        pending: '待授权',
        unauthorized: '未授权'
      }
      return map[status] || status
    }
  }
}
</script>

<style scoped>
.auth-price {
  background: #fff;
  padding: 24rpx;
  margin-top: 20rpx;
  border-radius: 16rpx;
}

.price-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
}

.services {
  display: flex;
  gap: 16rpx;
  margin-bottom: 20rpx;
}

.service-tag {
  padding: 8rpx 20rpx;
  background: #e6f7ff;
  color: #1890ff;
  border-radius: 20rpx;
  font-size: 24rpx;
}

.city-prices {
  display: flex;
  flex-direction: column;
  gap: 12rpx;
}

.city-item {
  display: flex;
  justify-content: space-between;
  padding: 10rpx 0;
  border-bottom: 1rpx solid #f5f5f5;
}

.city-item:last-child {
  border-bottom: none;
}

.city-name {
  font-size: 26rpx;
  color: #666;
}

.city-price {
  font-size: 26rpx;
  color: #ff4d4f;
  font-weight: bold;
}

.auth-status {
  margin-top: 16rpx;
}

.status-text {
  font-size: 26rpx;
  color: #52c41a;
}
</style>
