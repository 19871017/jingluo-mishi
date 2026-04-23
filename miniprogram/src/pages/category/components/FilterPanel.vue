<template>
  <view class="filter-panel card">
    <view class="filter-head">
      <view>
        <text class="filter-main-title">多维筛选</text>
        <text class="filter-subtitle">点击标签快速缩小查找范围</text>
      </view>
      <text class="reset-btn" @click="$emit('reset')">重置</text>
    </view>

    <view v-for="(values, key) in filters" :key="key" class="filter-group">
      <text class="filter-title">{{ titleMap[key] || key }}</text>
      <view class="filter-tags">
        <text
          v-for="value in values"
          :key="value"
          class="filter-tag"
          :class="{ active: selected[key] === value }"
          @click="$emit('toggle', { key, value })"
        >
          {{ formatValue(key, value) }}
        </text>
      </view>
    </view>
  </view>
</template>

<script setup>
defineProps({
  filters: {
    type: Object,
    default: () => ({}),
  },
  selected: {
    type: Object,
    default: () => ({}),
  },
})

defineEmits(['toggle', 'reset'])

const titleMap = {
  price_range: '价格区间',
  players: '组队人数',
  horror_level: '恐怖等级',
}

function formatValue(key, value) {
  if (key === 'horror_level') {
    return `Lv.${value}`
  }
  return value
}
</script>

<style scoped>
.filter-panel {
  margin-top: 20rpx;
  padding: 24rpx;
  border-radius: 24rpx;
}

.filter-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16rpx;
  margin-bottom: 22rpx;
}

.filter-main-title {
  display: block;
  font-size: 30rpx;
  font-weight: 800;
  color: #1f2937;
}

.filter-subtitle {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #9ca3af;
}

.reset-btn {
  padding: 10rpx 16rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 22rpx;
  font-weight: 700;
}

.filter-group + .filter-group {
  margin-top: 22rpx;
}

.filter-title {
  display: block;
  margin-bottom: 14rpx;
  font-size: 24rpx;
  font-weight: 700;
  color: #4b5563;
}

.filter-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
}

.filter-tag {
  padding: 12rpx 18rpx;
  border-radius: 14rpx;
  background: #f8f8f8;
  font-size: 22rpx;
  color: #666666;
}

.filter-tag.active {
  background: #ff8a00;
  color: #ffffff;
  box-shadow: 0 10rpx 20rpx rgba(255, 138, 0, 0.18);
}
</style>
