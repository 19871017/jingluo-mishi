<template>
  <scroll-view scroll-y class="sub-page">
    <view class="sub-shell">
      <view class="hero card">
        <text class="hero-title">最近浏览</text>
        <text class="hero-desc">共 {{ list.length }} 条浏览记录，快速返回之前查看过的内容</text>
      </view>

      <view class="list-block">
        <view v-for="item in list" :key="`${item.target_type}-${item.target_id}`" class="topic-item card" @click="goDetail(item)">
          <text class="topic-title">{{ item.title || `记录 #${item.target_id}` }}</text>
          <text class="topic-meta">{{ typeLabel(item.target_type) }}{{ item.extra_name ? ` · ${extraLabel(item.extra_name)}` : '' }}</text>
          <text class="topic-desc">点击继续浏览对应内容详情</text>
        </view>
      </view>

      <view v-if="!list.length" class="card empty">暂无浏览记录</view>
    </view>
  </scroll-view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'

const list = ref([])

async function fetchData() {
  const data = await api.getRecentViews()
  list.value = data.list || []
}

function goDetail(item) {
  if (item.target_type === 'script') uni.navigateTo({ url: `/pages/script/script-detail?id=${item.target_id}` })
  if (item.target_type === 'brand') uni.navigateTo({ url: `/pages/brand/brand-detail?id=${item.target_id}` })
  if (item.target_type === 'listing') uni.navigateTo({ url: `/pages/market/market-detail?id=${item.target_id}` })
}

function typeLabel(type) {
  if (type === 'script') return '剧本'
  if (type === 'brand') return '品牌'
  if (type === 'listing') return '社区话题'
  return type || '内容'
}

function extraLabel(value) {
  if (value === 'discussion') return '交流内容'
  if (value === 'brand') return '品牌内容'
  if (value === 'script') return '剧本内容'
  if (value === 'buy' || value === 'sell') return '历史社区内容'
  return value
}

onMounted(fetchData)
</script>

<style scoped>
.sub-page { height: 100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.sub-shell { padding: 24rpx; }
.hero { padding: 24rpx; }
.hero-title { display:block; font-size:34rpx; font-weight:800; color:#1f2937; }
.hero-desc { display:block; margin-top:10rpx; font-size:22rpx; line-height:1.6; color:#9ca3af; }
.list-block { margin-top: 20rpx; display:flex; flex-direction:column; gap:18rpx; }
.topic-item { padding: 24rpx; }
.topic-title { display:block; font-size:30rpx; font-weight:800; color:#1f2937; }
.topic-meta { display:block; margin-top:10rpx; font-size:22rpx; color:#f97316; }
.topic-desc { display:block; margin-top:12rpx; font-size:22rpx; line-height:1.6; color:#9ca3af; }
.empty { margin-top:20rpx; padding:30rpx; text-align:center; }
</style>
