<template>
  <scroll-view scroll-y class="brand-page">
    <view class="brand-shell">
      <view class="brand-hero">
        <view class="hero-glow hero-glow-left"></view>
        <view class="hero-glow hero-glow-right"></view>
        <text class="hero-caption">品牌内容入口</text>
        <text class="hero-title">品牌方</text>
        <text class="hero-desc">
          在这里查看品牌介绍、品牌旗下剧本，以及品牌维度的内容总览。
        </text>
      </view>

      <view class="summary-card card">
        <view>
          <text class="summary-title">品牌方总览</text>
          <text class="summary-subtitle">共 {{ brandCount }} 个品牌内容节点</text>
        </view>
        <text class="summary-chip">品牌 {{ brandCount }}</text>
      </view>

      <view class="section-head">
        <view>
          <text class="section-title">品牌方列表</text>
          <text class="section-subtitle">按品牌维度浏览介绍与旗下剧本。</text>
        </view>
      </view>

      <view class="brand-list">
        <BrandCard
          v-for="item in brands.list || []"
          :key="item.id"
          :item="item"
          @click="goBrandDetail(item.id)"
        />
      </view>

      <EmptyState
        v-if="!brandCount"
        title="暂无品牌数据"
        description="稍后刷新再试试。"
      />
    </view>
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import EmptyState from '../../components/EmptyState.vue'
import BrandCard from './components/BrandCard.vue'
import { syncCustomTabBar } from '../../utils/tabbar'

const brands = ref({ list: [] })

const brandCount = computed(() => (brands.value.list || []).length)

async function fetchData() {
  const brandData = await api.getBrands(1, 50)
  brands.value = brandData || { list: [] }
}

function goBrandDetail(id) {
  uni.navigateTo({ url: `/pages/brand/brand-detail?id=${id}` })
}

onMounted(fetchData)
onMounted(() => syncCustomTabBar(1))
</script>

<style scoped>
.brand-page {
  height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(255, 181, 72, 0.12), transparent 20%),
    linear-gradient(180deg, #fff7ef 0%, #f6f7fb 24%, #f6f7fb 100%);
}

.brand-shell {
  padding: 24rpx;
}

.brand-hero {
  position: relative;
  overflow: hidden;
  padding: 28rpx 24rpx 24rpx;
  border-radius: 30rpx;
  background: linear-gradient(135deg, #ffb64a 0%, #ff8d1a 58%, #ff6f00 100%);
  box-shadow: 0 16rpx 40rpx rgba(255, 132, 0, 0.16);
}

.hero-glow {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.12);
}

.hero-glow-left {
  top: -80rpx;
  left: -40rpx;
  width: 220rpx;
  height: 220rpx;
}

.hero-glow-right {
  top: 20rpx;
  right: -60rpx;
  width: 260rpx;
  height: 260rpx;
}

.hero-caption,
.hero-title,
.hero-desc {
  position: relative;
  z-index: 1;
  display: block;
  color: #ffffff;
}

.hero-caption {
  font-size: 20rpx;
  letter-spacing: 1rpx;
  opacity: 0.8;
}

.hero-title {
  margin-top: 12rpx;
  font-size: 42rpx;
  font-weight: 800;
}

.hero-desc {
  margin-top: 10rpx;
  font-size: 24rpx;
  opacity: 0.92;
  line-height: 1.7;
}

.summary-card {
  margin-top: 22rpx;
  padding: 24rpx;
}

.summary-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.summary-title {
  display: block;
  font-size: 32rpx;
  font-weight: 800;
  color: #1f2937;
}

.summary-subtitle {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #8b8b8b;
}

.summary-chip {
  padding: 10rpx 16rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 22rpx;
  font-weight: 700;
}

.section-head {
  margin-top: 24rpx;
}

.section-title {
  display: block;
  font-size: 34rpx;
  font-weight: 800;
  color: #1f2937;
}

.section-subtitle {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #9ca3af;
}

.brand-list {
  margin-top: 20rpx;
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.meta-chip {
  padding: 8rpx 14rpx;
  border-radius: 12rpx;
  background: #f5f5f5;
  color: #666666;
  font-size: 22rpx;
}
</style>
