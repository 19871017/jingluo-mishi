<template>
  <view class="page-container">
    <template v-if="mode === 'publish'">
      <PublishForm @submit="submit" />
    </template>
    <template v-else-if="mode !== 'listing-detail'">
      <view class="card list-block">
        <text class="page-title">{{ titleMap[mode] }}</text>
      </view>
      <view v-for="item in list" :key="item.id || item.target_id" class="item card">
        <view class="content">
          <text class="title">{{ item.title || item.name || `记录 #${item.id || item.target_id}` }}</text>
          <text class="muted">{{ item.type || item.target_type || '' }} {{ item.price ? `· ${item.price}` : '' }}</text>
        </view>
      </view>
      <view v-if="!list.length" class="card empty-tip">暂无数据</view>
    </template>
    <template v-else>
      <view class="card list-block">
        <text class="page-title">话题详情</text>
      </view>
      <view v-if="detail" class="item card">
        <view class="content">
          <text class="title">{{ detail.title }}</text>
          <text class="muted">{{ detail.user_nickname || '匿名用户' }}</text>
          <text class="muted">{{ detail.description }}</text>
          <button class="primary-btn" @click="toggleInterest">{{ interested ? '取消关注' : '关注话题' }}</button>
        </view>
      </view>
    </template>
  </view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import PublishForm from '../market/components/PublishForm.vue'
import { api } from '../../services/api'
import { useAuthStore } from '../../stores/auth'

const page = getCurrentPages().slice(-1)[0]
const mode = ref(page?.options?.mode || 'publish')
const list = ref([])
const detail = ref(null)
const interested = ref(false)
const auth = useAuthStore()
const titleMap = {
  listings: '我的话题',
  interests: '关注的话题',
  recent: '最近浏览',
}

async function submit(payload) {
  if (!auth.isLoggedIn) {
    uni.showToast({ title: '请先登录后发起话题', icon: 'none' })
    return
  }
  await api.createBbsPost({
    title: payload.title,
    content: payload.description,
    images: payload.images || [],
    video: payload.video || '',
    video_cover: payload.video_cover || ''
  })
  uni.showToast({ title: '帖子已发布', icon: 'success' })
  setTimeout(() => {
    uni.navigateBack()
  }, 800)
}

async function loadList() {
  if (mode.value === 'listings') {
    const data = await api.getMyListings()
    list.value = data.list || []
  }
  if (mode.value === 'interests') {
    const data = await api.getInterests()
    list.value = data.list || []
  }
  if (mode.value === 'recent') {
    const data = await api.getRecentViews()
    list.value = data.list || []
  }
  if (mode.value === 'listing-detail') {
    const market = await api.getMarket('', 1, 200)
    const id = page?.options?.id
    detail.value = [...(market.featured || []), ...(market.listings || [])].find((item) => String(item.id) === String(id))
    interested.value = false
  }
}

async function toggleInterest() {
  if (!detail.value) return
  if (!auth.isLoggedIn) {
    uni.showToast({ title: '请先登录后再关注话题', icon: 'none' })
    return
  }
  const data = await api.toggleListingInterest(detail.value.id)
  interested.value = !!data.active
  uni.showToast({ title: interested.value ? '已关注话题' : '已取消关注', icon: 'none' })
}

onMounted(loadList)
</script>

<style scoped>
.list-block {
  padding: 24rpx;
  margin-bottom: 20rpx;
}

.page-title {
  font-size: 34rpx;
  font-weight: 700;
}

.item {
  padding: 22rpx;
  margin-bottom: 18rpx;
}

.content {
  display: flex;
  flex-direction: column;
  gap: 10rpx;
}

.title {
  font-size: 30rpx;
  font-weight: 700;
}

.empty-tip {
  padding: 30rpx;
  text-align: center;
}
</style>
