<template>
  <scroll-view scroll-y class="manage-page">
    <view class="manage-shell">
      <view class="hero card">
        <text class="hero-title">我的施工案例</text>
        <text class="hero-desc">共 {{ list.length }} 条案例记录，待审核内容会保留在这里，可继续编辑后重新提交。</text>
      </view>

      <view class="list-block">
        <view v-for="item in list" :key="item.id" class="item card">
          <image class="thumb" :src="item.cover || (item.images && item.images[0]) || fallbackCover" mode="aspectFill" />
          <view class="content">
            <view class="topline">
              <text class="title">{{ item.project_name }}</text>
              <text class="status" :class="item.status">{{ statusText(item.status) }}</text>
            </view>
            <text class="meta">{{ item.brand_name }} · {{ item.phase }} · {{ formatDate(item.created_at) }}</text>
            <text class="desc">{{ item.description }}</text>
            <text v-if="item.reject_reason" class="reject-reason">驳回原因：{{ item.reject_reason }}</text>
            <view class="actions">
              <text class="action" @click="goEdit(item.id)">编辑</text>
              <text class="action danger" @click="remove(item.id)">删除</text>
            </view>
          </view>
        </view>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'

const list = ref([])
const fallbackCover = 'https://dummyimage.com/320x220/111827/ffffff&text=Case'

async function fetchData() {
  const data = await api.getMyConstructionCases(1, 100)
  list.value = data.list || []
}

function statusText(status) {
  if (status === 'published') return '已发布'
  if (status === 'pending') return '待审核'
  if (status === 'rejected') return '已驳回'
  return status || '待处理'
}

function formatDate(value) {
  return String(value || '').slice(0, 10) || '最近更新'
}

function goEdit(id) {
  uni.navigateTo({ url: `/pages/case/case-publish?id=${id}` })
}

async function remove(id) {
  await api.deleteConstructionCase(id)
  uni.showToast({ title: '案例已删除', icon: 'success' })
  fetchData()
}

onMounted(fetchData)
</script>

<style scoped>
.manage-page { height:100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.manage-shell { padding:24rpx; }
.hero { padding:24rpx; }
.hero-title { display:block; font-size:34rpx; font-weight:800; color:#1f2937; }
.hero-desc { display:block; margin-top:10rpx; font-size:22rpx; line-height:1.7; color:#9ca3af; }
.list-block { margin-top:20rpx; display:flex; flex-direction:column; gap:18rpx; }
.item { display:flex; gap:18rpx; padding:22rpx; }
.thumb { width:180rpx; height:140rpx; border-radius:18rpx; flex-shrink:0; }
.content { flex:1; display:flex; flex-direction:column; gap:10rpx; }
.topline { display:flex; align-items:flex-start; justify-content:space-between; gap:12rpx; }
.title { font-size:28rpx; font-weight:800; color:#1f2937; line-height:1.4; }
.status { padding:8rpx 12rpx; border-radius:999rpx; font-size:20rpx; font-weight:700; white-space:nowrap; }
.status.published { background:#eefaf6; color:#0f9f6e; }
.status.pending { background:#fff1de; color:#f97316; }
.status.rejected { background:#fee2e2; color:#dc2626; }
.meta { font-size:22rpx; color:#f97316; }
.desc { font-size:22rpx; line-height:1.6; color:#6b7280; }
.reject-reason { font-size:22rpx; line-height:1.6; color:#dc2626; }
.actions { display:flex; gap:18rpx; margin-top:auto; }
.action { font-size:22rpx; color:#2563eb; }
.action.danger { color:#dc2626; }
</style>
