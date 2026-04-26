<template>
  <scroll-view scroll-y class="detail-page">
    <view class="detail-shell" v-if="detail">
      <view class="hero card">
        <image class="hero-cover" :src="detail.cover || fallbackCover" mode="aspectFill" />
        <view class="hero-body">
          <view class="hero-topline">
            <view>
              <text class="hero-caption">施工方详情</text>
              <text class="hero-title">{{ detail.name }}</text>
            </view>
            <text class="hero-badge">{{ detail.case_count || 0 }} 案例</text>
          </view>
          <text class="hero-brand">服务品牌：{{ detail.brand_name || '待补充' }}</text>
          <text class="hero-desc">{{ detail.description || '暂无施工方介绍。' }}</text>
          <view class="meta-row">
            <text class="meta-chip">{{ detail.image_count || 0 }} 张图片</text>
            <text class="meta-chip">{{ detail.video_count || 0 }} 个视频</text>
          </view>
        </view>
      </view>

      <view class="section-card card">
        <view class="section-head">
          <view>
            <text class="section-title">施工案例</text>
            <text class="section-subtitle">查看该施工方最近发布的案例资料</text>
          </view>
        </view>

        <view v-if="cases.length" class="case-list">
          <view v-for="item in cases" :key="item.id" class="case-item" @click="goCase(item.id)">
            <image class="case-cover" :src="item.cover || fallbackCover" mode="aspectFill" />
            <view class="case-body">
              <view class="case-topline">
                <text class="case-title">{{ item.projectName || item.project_name || item.title || '施工案例' }}</text>
                <text class="case-badge">{{ item.phase || '案例' }}</text>
              </view>
              <text class="case-desc">{{ item.description || '暂无案例说明。' }}</text>
              <text class="case-meta">{{ item.brandName || item.brand_name || '品牌待补充' }} · {{ formatDate(item.createdAt || item.created_at) }}</text>
            </view>
          </view>
        </view>

        <EmptyState v-else title="暂无施工案例" description="当前施工方还没有可展示的案例内容。" />
      </view>
    </view>

    <EmptyState v-else title="施工方不存在" description="请返回上一页重新选择施工方。" />
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import EmptyState from '../../components/EmptyState.vue'

const fallbackCover = 'https://dummyimage.com/320x240/111827/ffffff&text=Constructor'
const detail = ref(null)

const cases = computed(() => detail.value?.cases || [])

async function loadDetail() {
  const { id } = getCurrentPages().slice(-1)[0].options
  try {
    detail.value = await api.getConstructorDetail(id)
  } catch (_) {
    const data = await api.getConstructors(1, 100)
    detail.value = (data?.list || []).find((item) => String(item.id) === String(id)) || null
  }
}

function goCase(id) {
  uni.navigateTo({ url: `/pages/case/case-detail?id=${id}` })
}

function formatDate(value) {
  return String(value || '').slice(0, 10) || '最近更新'
}

onMounted(loadDetail)
</script>

<style scoped>
.detail-page { height: 100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.detail-shell { padding: 24rpx; }
.hero { overflow: hidden; }
.hero-cover { width: 100%; height: 320rpx; }
.hero-body { padding: 24rpx; }
.hero-topline { display: flex; justify-content: space-between; gap: 16rpx; }
.hero-caption { display: block; font-size: 20rpx; color: #9ca3af; }
.hero-title { display: block; margin-top: 10rpx; font-size: 38rpx; font-weight: 800; color: #1f2937; }
.hero-badge { padding: 10rpx 16rpx; border-radius: 999rpx; background: #fff1de; color: #f97316; font-size: 22rpx; font-weight: 700; height: fit-content; }
.hero-brand { display: block; margin-top: 14rpx; font-size: 22rpx; color: #f97316; }
.hero-desc { display: block; margin-top: 14rpx; font-size: 24rpx; line-height: 1.7; color: #6b7280; }
.meta-row { display: flex; flex-wrap: wrap; gap: 12rpx; margin-top: 18rpx; }
.meta-chip { padding: 8rpx 14rpx; border-radius: 14rpx; background: #f5f5f5; color: #666; font-size: 22rpx; }
.section-card { margin-top: 24rpx; padding: 24rpx; }
.section-title { display: block; font-size: 30rpx; font-weight: 800; color: #1f2937; }
.section-subtitle { display: block; margin-top: 8rpx; font-size: 22rpx; color: #9ca3af; }
.case-list { display: flex; flex-direction: column; gap: 18rpx; margin-top: 20rpx; }
.case-item { display: flex; gap: 16rpx; padding: 18rpx; border-radius: 20rpx; background: #fafafa; }
.case-cover { width: 168rpx; height: 168rpx; border-radius: 20rpx; background: #e5e7eb; flex-shrink: 0; }
.case-body { flex: 1; display: flex; flex-direction: column; gap: 10rpx; }
.case-topline { display: flex; align-items: flex-start; justify-content: space-between; gap: 14rpx; }
.case-title { flex: 1; font-size: 28rpx; font-weight: 700; color: #1f2937; line-height: 1.5; }
.case-badge { padding: 8rpx 12rpx; border-radius: 12rpx; background: #eef2ff; color: #4f46e5; font-size: 20rpx; }
.case-desc, .case-meta { font-size: 22rpx; color: #6b7280; line-height: 1.7; }
</style>
