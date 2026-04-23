<template>
  <scroll-view scroll-y class="sub-page">
    <view class="sub-shell">
      <view class="hero card">
        <text class="hero-title">关注的话题</text>
        <text class="hero-desc">共 {{ list.length }} 条关注记录，持续追踪你关心的交流讨论</text>
      </view>

      <view class="list-block">
        <view v-for="item in list" :key="item.id" class="topic-item card" @click="goDetail(item.id)">
          <text class="topic-title">{{ item.title }}</text>
          <text class="topic-meta">{{ item.user_nickname || '匿名用户' }} · {{ item.created_at?.slice(0, 10) || '最近更新' }}</text>
          <text class="topic-desc">点击进入话题详情，继续查看讨论内容</text>
        </view>
      </view>

      <view v-if="!list.length" class="card empty">暂无关注的话题</view>
    </view>
  </scroll-view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'

const list = ref([])

async function fetchData() {
  const data = await api.getInterests()
  list.value = data.list || []
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/market/market-detail?id=${id}` })
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
