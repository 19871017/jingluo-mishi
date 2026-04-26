<template>
  <scroll-view scroll-y class="detail-page">
    <view class="detail-shell" v-if="detail">
      <view class="hero card">
        <image class="hero-logo" :src="toAssetUrl(detail.logo) || fallbackLogo" mode="aspectFill" />
        <view class="hero-body">
          <view class="hero-topline">
            <view>
              <text class="hero-caption">品牌详情</text>
              <text class="hero-title">{{ detail.name }}</text>
            </view>
            <text class="hero-badge">{{ detail.status || 'approved' }}</text>
          </view>
          <text class="hero-desc">{{ detail.description || '暂无品牌介绍，后续将补充品牌定位与合作信息。' }}</text>
          <view class="meta-row">
            <text class="meta-chip">{{ detail.follower_count || 0 }} 关注</text>
            <text class="meta-chip">{{ detail.total_authorizations || 0 }} 授权</text>
            <text class="meta-chip">{{ scriptList.length }} 剧本</text>
          </view>
        </view>
      </view>

      <view class="section-card card">
        <view class="section-head">
          <view>
            <text class="section-title">旗下剧本</text>
            <text class="section-subtitle">浏览该品牌当前已上架的剧本内容</text>
          </view>
        </view>

        <view v-if="scriptList.length" class="script-list">
          <view v-for="item in scriptList" :key="item.id" class="script-item" @click="goScript(item.id)">
            <image class="script-cover" :src="toAssetUrl(item.thumbnail) || fallbackLogo" mode="aspectFill" />
            <view class="script-body">
              <view class="script-topline">
                <text class="script-title">{{ item.name }}</text>
                <text class="script-price">{{ formatScriptPrice(item) }}</text>
              </view>
              <text class="script-meta">{{ item.min_players || 0 }}-{{ item.max_players || 0 }}人 · {{ item.duration || 0 }}分钟 · {{ item.type || '剧本' }}</text>
              <text class="script-stats">{{ item.view_count || 0 }} 浏览 · {{ item.like_count || 0 }} 点赞 · {{ item.collect_count || 0 }} 收藏</text>
            </view>
          </view>
        </view>

        <EmptyState v-else title="暂无品牌剧本" description="当前品牌还没有可展示的剧本内容。" />
      </view>
    </view>

    <EmptyState v-else title="品牌不存在" description="请返回上一页重新选择品牌。" />
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { toAssetUrl } from '../../services/http'
import EmptyState from '../../components/EmptyState.vue'
import { formatScriptPrice } from '../../utils/format'

const fallbackLogo = 'https://dummyimage.com/320x320/111827/ffffff&text=Brand'
const detail = ref(null)

const scriptList = computed(() => detail.value?.scripts || [])

async function loadDetail() {
  const { id } = getCurrentPages().slice(-1)[0].options
  const response = await api.getBrandDetail(id)
  detail.value = response || null
}

function goScript(id) {
  uni.navigateTo({ url: `/pages/script/script-detail?id=${id}` })
}

onMounted(loadDetail)
</script>

<style scoped>
.detail-page { height: 100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.detail-shell { padding: 24rpx; }
.hero { display: flex; gap: 22rpx; padding: 24rpx; }
.hero-logo { width: 180rpx; height: 180rpx; border-radius: 28rpx; background: #fff7ed; flex-shrink: 0; }
.hero-body { flex: 1; }
.hero-topline { display: flex; justify-content: space-between; gap: 16rpx; }
.hero-caption { display: block; font-size: 20rpx; color: #9ca3af; }
.hero-title { display: block; margin-top: 10rpx; font-size: 38rpx; font-weight: 800; color: #1f2937; }
.hero-badge { padding: 10rpx 16rpx; border-radius: 999rpx; background: #fff1de; color: #f97316; font-size: 22rpx; font-weight: 700; height: fit-content; }
.hero-desc { display: block; margin-top: 16rpx; font-size: 24rpx; line-height: 1.7; color: #6b7280; }
.meta-row { display: flex; flex-wrap: wrap; gap: 12rpx; margin-top: 18rpx; }
.meta-chip { padding: 8rpx 14rpx; border-radius: 14rpx; background: #f5f5f5; color: #666; font-size: 22rpx; }
.section-card { margin-top: 24rpx; padding: 24rpx; }
.section-title { display: block; font-size: 30rpx; font-weight: 800; color: #1f2937; }
.section-subtitle { display: block; margin-top: 8rpx; font-size: 22rpx; color: #9ca3af; }
.script-list { display: flex; flex-direction: column; gap: 18rpx; margin-top: 20rpx; }
.script-item { display: flex; gap: 16rpx; padding: 18rpx; border-radius: 20rpx; background: #fafafa; }
.script-cover { width: 160rpx; height: 160rpx; border-radius: 20rpx; background: #e5e7eb; flex-shrink: 0; }
.script-body { flex: 1; display: flex; flex-direction: column; gap: 10rpx; }
.script-topline { display: flex; align-items: flex-start; justify-content: space-between; gap: 14rpx; }
.script-title { flex: 1; font-size: 28rpx; font-weight: 700; color: #1f2937; line-height: 1.5; }
.script-price { font-size: 24rpx; font-weight: 700; color: #f97316; }
.script-meta, .script-stats { font-size: 22rpx; color: #6b7280; line-height: 1.7; }
</style>
