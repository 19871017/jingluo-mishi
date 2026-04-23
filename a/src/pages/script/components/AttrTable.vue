<template>
  <view class="attr-table">
    <view class="table-title">{{ title }}</view>
    <view class="table-grid">
      <view v-for="(value, key) in data" :key="key" class="table-row">
        <text class="row-label">{{ formatKey(key) }}</text>
        <text class="row-value">{{ formatValue(key, value) }}</text>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: '属性'
    },
    data: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    formatKey(key) {
      const keyMap = {
        story_score: '剧情评分',
        puzzle_difficulty: '谜题难度',
        sound_light_score: '声光评分',
        mechanism_score: '机关评分',
        puzzle_count: '谜题数量',
        horror_level: '恐怖程度',
        npc_lines_score: 'NPC台词',
        performance_score: '演绎评分',
        interaction_score: '互动评分',
        room_size: '房间大小',
        room_count: '房间数量',
        corridor_count: '走廊数量',
        construction_price: '装修价格',
        mechanism_price: '机关价格',
        area: '面积',
        player_type: '玩家类型'
      }
      return keyMap[key] || key
    },
    formatValue(key, value) {
      if (key.includes('score') && typeof value === 'number') {
        return '★'.repeat(value)
      }
      if (key.includes('price') && typeof value === 'number') {
        return value >= 10000 ? (value / 10000) + '万' : value + '元'
      }
      if (key === 'horror_level' && typeof value === 'number') {
        return ['微恐', '中恐', '重恐'][value - 1] || value
      }
      if (key === 'room_size') {
        const sizeMap = { small: '小型', medium: '中型', large: '大型' }
        return sizeMap[value] || value
      }
      if (key === 'player_type') {
        const typeMap = { beginner: '新手', experienced: '老手', expert: '高手' }
        return typeMap[value] || value
      }
      return value
    }
  }
}
</script>

<style scoped>
.attr-table {
  background: #fff;
  padding: 24rpx;
  margin-top: 20rpx;
  border-radius: 16rpx;
}

.table-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
}

.table-grid {
  display: flex;
  flex-wrap: wrap;
}

.table-row {
  width: 50%;
  display: flex;
  padding: 10rpx 0;
}

.row-label {
  width: 140rpx;
  font-size: 26rpx;
  color: #999;
}

.row-value {
  flex: 1;
  font-size: 26rpx;
  color: #333;
}
</style>
