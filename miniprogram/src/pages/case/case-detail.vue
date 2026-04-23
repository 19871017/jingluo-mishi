<template>
  <scroll-view scroll-y class="detail-page">
    <view class="detail-shell" v-if="detail">
      <view class="hero card">
        <image class="hero-cover" :src="detail.cover" mode="aspectFill" />
        <view class="hero-body">
          <view class="hero-topline">
            <text class="hero-title">{{ detail.projectName }}</text>
            <text class="hero-badge">{{ detail.phase }}</text>
          </view>
          <text class="hero-brand">{{ detail.brandName }}</text>
          <text class="hero-desc">{{ detail.description }}</text>
        </view>
      </view>

      <view class="section-card card">
        <text class="section-title">施工说明</text>
        <view class="note-list">
          <view v-for="(item, index) in detail.notes || []" :key="index" class="note-item">
            <text class="note-index">{{ index + 1 }}</text>
            <text class="note-text">{{ item }}</text>
          </view>
        </view>
      </view>

      <view class="section-card card" v-if="detail.images?.length">
        <text class="section-title">施工图片</text>
        <view class="image-grid">
          <image v-for="(item, index) in detail.images" :key="index" class="gallery-image" :src="item" mode="aspectFill" @click="previewImage(index)" />
        </view>
      </view>

      <view class="section-card card" v-if="detail.videos?.length">
        <text class="section-title">施工视频</text>
        <view class="video-list">
          <video v-for="(item, index) in detail.videos" :key="index" class="video-player" :src="item" controls object-fit="cover"></video>
        </view>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'

const detail = ref(null)

async function loadDetail() {
  const { id } = getCurrentPages().slice(-1)[0].options
  detail.value = await api.getConstructionCaseDetail(id)
}

function previewImage(index) {
  uni.previewImage({ current: index, urls: detail.value.images || [] })
}

onMounted(loadDetail)
</script>

<style scoped>
.detail-page { height:100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.detail-shell { padding:24rpx; }
.hero { overflow:hidden; }
.hero-cover { width:100%; height:320rpx; }
.hero-body { padding:24rpx; }
.hero-topline { display:flex; align-items:flex-start; justify-content:space-between; gap:14rpx; }
.hero-title { font-size:34rpx; font-weight:800; color:#1f2937; line-height:1.35; }
.hero-badge { padding:8rpx 14rpx; border-radius:999rpx; background:#fff1de; color:#f97316; font-size:20rpx; font-weight:700; }
.hero-brand { display:block; margin-top:10rpx; font-size:22rpx; color:#f97316; }
.hero-desc { display:block; margin-top:14rpx; font-size:24rpx; color:#6b7280; line-height:1.75; }
.section-card { margin-top:24rpx; padding:24rpx; }
.section-title { display:block; font-size:30rpx; font-weight:800; color:#1f2937; margin-bottom:16rpx; }
.note-list { display:flex; flex-direction:column; gap:14rpx; }
.note-item { display:flex; gap:12rpx; align-items:flex-start; padding:18rpx; border-radius:18rpx; background:#fafafa; }
.note-index { width:40rpx; height:40rpx; border-radius:50%; background:#fff1de; color:#f97316; display:flex; align-items:center; justify-content:center; font-size:20rpx; font-weight:700; flex-shrink:0; }
.note-text { font-size:22rpx; color:#4b5563; line-height:1.7; }
.image-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16rpx; }
.gallery-image { width:100%; height:220rpx; border-radius:18rpx; }
.video-list { display:flex; flex-direction:column; gap:18rpx; }
.video-player { width:100%; height:360rpx; border-radius:18rpx; background:#000; }
</style>
