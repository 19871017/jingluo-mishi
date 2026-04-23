<template>
  <scroll-view scroll-y class="sub-page">
    <view class="sub-shell">
      <view class="hero card">
        <text class="hero-title">我的收藏</text>
        <text class="hero-desc">共 {{ list.length }} 条收藏内容，集中查看你标记过的剧本</text>
      </view>

      <view class="list-block">
        <view v-for="item in list" :key="item.id" class="item card" @click="goDetail(item.id)">
          <view class="sideband"></view>
          <image class="thumb" :src="toAssetUrl(item.thumbnail) || 'https://dummyimage.com/280x280/5f7cff/ffffff&text=Script'" mode="aspectFill" />
          <view class="content">
            <text class="title">{{ displayName(item) }}</text>
            <text class="meta">点赞 {{ item.like_count || 0 }}</text>
            <text class="desc">快速回到你收藏过的重点剧本内容</text>
          </view>
        </view>
      </view>

      <EmptyState v-if="!list.length" title="暂无收藏" description="收藏喜欢的剧本后会显示在这里" />
    </view>
  </scroll-view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { toAssetUrl } from '../../services/http'
import EmptyState from '../../components/EmptyState.vue'

const list = ref([])

async function fetchData() {
  const data = await api.getFavorites()
  list.value = data.list || []
}

function displayName(item) {
  const name = String(item?.name || '').trim()
  if (!name) return '未命名剧本'
  if (/^[a-z]\d+_[a-z0-9]+$/i.test(name)) return '数据异常剧本'
  return name
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/script/script-detail?id=${id}` })
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
.item { position:relative; display:flex; gap:18rpx; padding:22rpx 18rpx 22rpx 28rpx; overflow:hidden; }
.sideband { position:absolute; left:0; top:0; bottom:0; width:10rpx; background:linear-gradient(180deg,#ffb24c,#ff7b00); }
.thumb { width: 150rpx; height: 150rpx; border-radius: 18rpx; flex-shrink:0; }
.content { flex:1; display:flex; flex-direction:column; justify-content:center; gap:10rpx; }
.title { font-size:30rpx; font-weight:800; color:#1f2937; }
.meta { font-size:22rpx; color:#f97316; }
.desc { font-size:22rpx; color:#9ca3af; line-height:1.6; }
</style>
