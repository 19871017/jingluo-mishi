<template>
  <view class="script-detail-page">
    <view v-if="loading" class="loading">加载中...</view>
    <template v-else-if="script">
      <ImageGallery :images="script.images" />

      <view class="script-header">
        <text class="script-name">{{ script.name }}</text>
        <text v-if="script.alias" class="script-alias">{{ script.alias }}</text>
      </view>

      <view class="script-meta">
        <view class="meta-item">
          <text class="meta-label">品牌</text>
          <text class="meta-value">{{ script.brand?.name }}</text>
        </view>
        <view class="meta-item">
          <text class="meta-label">分类</text>
          <text class="meta-value">{{ script.category?.name }}</text>
        </view>
        <view class="meta-item">
          <text class="meta-label">人数</text>
          <text class="meta-value">{{ formatPlayers(script.min_players, script.max_players) }}</text>
        </view>
        <view class="meta-item">
          <text class="meta-label">时长</text>
          <text class="meta-value">{{ formatDuration(script.duration) }}</text>
        </view>
      </view>

      <view class="script-desc">
        <view class="section-title">剧本简介</view>
        <text class="desc-text">{{ script.description }}</text>
      </view>

      <AttrTable v-if="script.theme_attrs" :data="script.theme_attrs" title="主题属性" />
      <AttrTable v-if="script.detail_attrs" :data="script.detail_attrs" title="详细信息" />
      <AuthPrice v-if="script.auth_info" :data="script.auth_info" />

      <view class="script-actions">
        <view class="action-item" @click="toggleLike">
          <text class="action-icon">{{ script.is_liked ? '❤️' : '🤍' }}</text>
          <text class="action-text">点赞</text>
        </view>
        <view class="action-item" @click="toggleCollect">
          <text class="action-icon">{{ script.is_collected ? '⭐' : '☆' }}</text>
          <text class="action-text">收藏</text>
        </view>
        <view class="action-item buy" @click="openPurchasePopup">
          <text class="action-icon">🛒</text>
          <text class="action-text">购买</text>
        </view>
        <button class="action-item share" open-type="share">
          <text class="action-icon">↗</text>
          <text class="action-text">转发</text>
        </button>
      </view>

      <view v-if="purchaseVisible" class="purchase-popup" @click="closePurchasePopup">
        <view class="purchase-mask"></view>
        <view class="purchase-dialog" @click.stop>
          <view class="purchase-title">提交购买意向</view>
          <input v-model="purchaseForm.city" class="purchase-input" placeholder="意向授权城市" />
          <input v-model="purchaseForm.contact_name" class="purchase-input" placeholder="联系人姓名" />
          <input v-model="purchaseForm.contact_phone" class="purchase-input" placeholder="联系电话" type="number" />
          <view class="purchase-actions">
            <view class="purchase-btn subtle" @click="closePurchasePopup">取消</view>
            <view class="purchase-btn strong" @click="submitPurchaseIntent">提交</view>
          </view>
        </view>
      </view>
    </template>
  </view>
</template>

<script>
import ImageGallery from './components/ImageGallery.vue'
import AttrTable from './components/AttrTable.vue'
import AuthPrice from './components/AuthPrice.vue'
import { scriptApi } from '../../services/api'
import { formatPlayers, formatDuration } from '../../utils/format'

export default {
  components: {
    ImageGallery,
    AttrTable,
    AuthPrice
  },
  data() {
    return {
      script: null,
      loading: false,
      purchaseVisible: false,
      purchaseForm: {
        city: '',
        contact_name: '',
        contact_phone: ''
      }
    }
  },
  onLoad(options) {
    this.fetchDetail(options.id)
  },
  methods: {
    formatPlayers,
    formatDuration,
    async fetchDetail(id) {
      this.loading = true
      try {
        this.script = await scriptApi.getDetail(id)
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    async toggleLike() {
      try {
        if (this.script.is_liked) {
          await scriptApi.unlike(this.script.id)
          this.script.is_liked = false
          this.script.like_count--
        } else {
          await scriptApi.like(this.script.id)
          this.script.is_liked = true
          this.script.like_count++
        }
      } catch (e) {
        console.error(e)
      }
    },
    async toggleCollect() {
      try {
        await scriptApi.collect(this.script.id)
        this.script.is_collected = !this.script.is_collected
        uni.showToast({
          title: this.script.is_collected ? '已收藏' : '已取消收藏',
          icon: 'none'
        })
      } catch (e) {
        console.error(e)
      }
    },
    openPurchasePopup() {
      this.purchaseVisible = true
    },
    closePurchasePopup() {
      this.purchaseVisible = false
    },
    async submitPurchaseIntent() {
      if (!this.purchaseForm.city || !this.purchaseForm.contact_name || !this.purchaseForm.contact_phone) {
        uni.showToast({ title: '请填写完整信息', icon: 'none' })
        return
      }

      try {
        await scriptApi.createPurchaseIntent(this.script.id, this.purchaseForm)
        uni.showToast({ title: '提交成功', icon: 'success' })
        this.purchaseVisible = false
        this.purchaseForm = { city: '', contact_name: '', contact_phone: '' }
      } catch (e) {
        console.error(e)
      }
    }
  },
  onShareAppMessage() {
    return {
      title: this.script?.name || '剧本详情',
      path: `/pages/script/script-detail?id=${this.script?.id || ''}`
    }
  }
}
</script>

<style scoped>
.script-detail-page {
  padding-bottom: 120rpx;
}

.script-header {
  padding: 24rpx;
  background: #fff;
}

.script-name {
  display: block;
  font-size: 36rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 10rpx;
}

.script-alias {
  font-size: 26rpx;
  color: #999;
}

.script-meta {
  display: flex;
  flex-wrap: wrap;
  background: #fff;
  padding: 20rpx 24rpx;
  margin-top: 2rpx;
}

.meta-item {
  width: 50%;
  margin-bottom: 16rpx;
}

.meta-label {
  font-size: 24rpx;
  color: #999;
  margin-right: 10rpx;
}

.meta-value {
  font-size: 26rpx;
  color: #333;
}

.script-desc {
  background: #fff;
  padding: 24rpx;
  margin-top: 20rpx;
}

.section-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 16rpx;
}

.desc-text {
  font-size: 28rpx;
  color: #666;
  line-height: 1.8;
}

.script-actions {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  gap: 18rpx;
  justify-content: space-around;
  padding: 20rpx 24rpx;
  background: #fff;
  box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.action-item {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10rpx;
  min-width: 120rpx;
  border: none;
  background: transparent;
  padding: 0;
}

.action-icon {
  font-size: 40rpx;
}

.action-text {
  font-size: 28rpx;
  color: #666;
}

.action-item.buy .action-text {
  color: #f97316;
  font-weight: 700;
}

.purchase-popup {
  position: fixed;
  inset: 0;
  z-index: 99;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32rpx;
}

.purchase-mask {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
}

.purchase-dialog {
  position: relative;
  z-index: 1;
  width: 100%;
  background: #fff;
  border-radius: 24rpx;
  padding: 28rpx;
}

.purchase-title {
  font-size: 32rpx;
  font-weight: 700;
  color: #333;
  margin-bottom: 20rpx;
}

.purchase-input {
  width: 100%;
  height: 84rpx;
  padding: 0 20rpx;
  margin-top: 14rpx;
  border-radius: 16rpx;
  background: #f7f7f8;
  font-size: 28rpx;
}

.purchase-actions {
  display: flex;
  gap: 16rpx;
  margin-top: 24rpx;
}

.purchase-btn {
  flex: 1;
  height: 82rpx;
  border-radius: 18rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28rpx;
  font-weight: 700;
}

.purchase-btn.subtle {
  background: #f3f4f6;
  color: #666;
}

.purchase-btn.strong {
  background: linear-gradient(135deg, #ffb24c, #ff7b00);
  color: #fff;
}

.loading {
  text-align: center;
  padding: 100rpx;
  color: #999;
}
</style>
