<template>
  <scroll-view scroll-y class="sub-page">
    <view class="sub-shell">
      <view class="hero card">
        <text class="hero-title">我的话题</text>
        <text class="hero-desc">共 {{ list.length }} 条话题记录，管理你在社区发起的交流内容</text>
      </view>

      <view class="list-block">
        <view v-for="item in list" :key="item.id" class="topic-item card">
          <text class="topic-title">{{ item.title }}</text>
          <text class="topic-meta">{{ item.user_nickname || '匿名用户' }} · {{ item.created_at?.slice(0, 10) || '最近更新' }}</text>
          <text class="topic-desc">状态：{{ item.status }}{{ item.description ? ` · ${item.description}` : '' }}</text>
        </view>
      </view>

      <view v-if="!list.length" class="card empty">暂无话题记录</view>
    </view>
  </scroll-view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'

const list = ref([])

async function fetchData() {
  const data = await api.getMyListings()
  list.value = data.list || []
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
