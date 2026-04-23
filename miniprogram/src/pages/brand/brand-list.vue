<template>
  <scroll-view scroll-y class="brand-page">
    <view class="brand-shell">
      <view class="brand-hero">
        <view class="hero-glow hero-glow-left"></view>
        <view class="hero-glow hero-glow-right"></view>
        <text class="hero-caption">品牌内容入口</text>
        <text class="hero-title">品牌方与施工方</text>
        <text class="hero-desc">
          在这里切换查看品牌介绍、品牌旗下剧本，以及施工方介绍与施工案例资料。
        </text>
      </view>

      <view class="switch-card card">
        <view class="switch-row">
          <view
            class="switch-pill"
            :class="{ active: activeTab === 'brand' }"
            @click="activeTab = 'brand'"
          >
            品牌方
          </view>
          <view
            class="switch-pill"
            :class="{ active: activeTab === 'constructor' }"
            @click="activeTab = 'constructor'"
          >
            施工方
          </view>
        </view>
        <text class="switch-desc">
          {{ activeTab === 'brand' ? '查看品牌介绍与品牌名下剧本。' : '查看施工方介绍与施工案例图片、说明和视频。' }}
        </text>
      </view>

      <template v-if="activeTab === 'brand'">
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
      </template>

      <template v-else>
        <view class="summary-card card">
          <view>
            <text class="summary-title">施工方总览</text>
            <text class="summary-subtitle">共 {{ constructorCount }} 个施工团队，展示介绍与案例资料。</text>
          </view>
          <text class="summary-chip">施工方 {{ constructorCount }}</text>
        </view>

        <view class="section-head">
          <view>
            <text class="section-title">施工方列表</text>
            <text class="section-subtitle">点击进入施工方详情，查看介绍、施工说明、图片与视频案例。</text>
          </view>
        </view>

        <view class="constructor-list">
          <view
            v-for="item in constructors.list || []"
            :key="item.id"
            class="constructor-card card"
            @click="goConstructorDetail(item.id)"
          >
            <image class="constructor-cover" :src="item.cover || fallbackCover" mode="aspectFill" />
            <view class="constructor-body">
              <view class="constructor-topline">
                <text class="constructor-name">{{ item.name }}</text>
                <text class="constructor-badge">{{ item.case_count || 0 }} 案例</text>
              </view>
              <text class="constructor-brand">服务品牌：{{ item.brand_name || '待补充' }}</text>
              <text class="constructor-desc">{{ item.description || '暂无施工方介绍。' }}</text>
              <view class="constructor-meta">
                <text class="meta-chip">{{ item.image_count || 0 }} 张图片</text>
                <text class="meta-chip">{{ item.video_count || 0 }} 个视频</text>
              </view>
            </view>
          </view>
        </view>

        <EmptyState
          v-if="!constructorCount"
          title="暂无施工方数据"
          description="当前还没有施工方介绍和案例内容。"
        />
      </template>
    </view>
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import EmptyState from '../../components/EmptyState.vue'
import BrandCard from './components/BrandCard.vue'
import { syncCustomTabBar } from '../../utils/tabbar'

const fallbackCover = 'https://dummyimage.com/320x240/111827/ffffff&text=Constructor'
const activeTab = ref('brand')
const brands = ref({ list: [] })
const constructors = ref({ list: [] })

const brandCount = computed(() => (brands.value.list || []).length)
const constructorCount = computed(() => (constructors.value.list || []).length)

async function fetchData() {
  const [brandData, constructorData] = await Promise.all([
    api.getBrands(1, 50),
    api.getConstructors(1, 50),
  ])
  brands.value = brandData || { list: [] }
  constructors.value = constructorData || { list: [] }
}

function goBrandDetail(id) {
  uni.navigateTo({ url: `/pages/brand/brand-detail?id=${id}` })
}

function goConstructorDetail(id) {
  uni.navigateTo({ url: `/pages/brand/constructor-detail?id=${id}` })
}

onMounted(fetchData)
onMounted(() => syncCustomTabBar(2))
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

.switch-card,
.summary-card {
  margin-top: 22rpx;
  padding: 24rpx;
}

.switch-row {
  display: flex;
  gap: 16rpx;
}

.switch-pill {
  flex: 1;
  text-align: center;
  padding: 16rpx 20rpx;
  border-radius: 999rpx;
  background: #f5f5f5;
  color: #666666;
  font-size: 26rpx;
  font-weight: 700;
}

.switch-pill.active {
  background: linear-gradient(135deg, #ffb24c, #ff7b00);
  color: #ffffff;
}

.switch-desc {
  display: block;
  margin-top: 14rpx;
  font-size: 22rpx;
  color: #8b8b8b;
  line-height: 1.6;
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

.brand-list,
.constructor-list {
  margin-top: 20rpx;
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.constructor-card {
  overflow: hidden;
}

.constructor-cover {
  width: 100%;
  height: 280rpx;
}

.constructor-body {
  padding: 22rpx;
}

.constructor-topline {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14rpx;
}

.constructor-name {
  font-size: 30rpx;
  font-weight: 800;
  color: #1f2937;
  line-height: 1.45;
}

.constructor-badge {
  padding: 8rpx 14rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 20rpx;
  font-weight: 700;
}

.constructor-brand {
  display: block;
  margin-top: 10rpx;
  font-size: 22rpx;
  color: #f97316;
}

.constructor-desc {
  display: block;
  margin-top: 12rpx;
  font-size: 22rpx;
  color: #6b7280;
  line-height: 1.7;
}

.constructor-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 10rpx;
  margin-top: 14rpx;
}

.meta-chip {
  padding: 8rpx 14rpx;
  border-radius: 12rpx;
  background: #f5f5f5;
  color: #666666;
  font-size: 22rpx;
}
</style>
