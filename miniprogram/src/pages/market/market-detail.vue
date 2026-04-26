<template>
  <scroll-view scroll-y class="market-detail-page">
    <view class="detail-shell">
      <view v-if="detail" class="hero card">
        <view class="hero-top">
          <view>
            <text class="hero-caption">话题详情</text>
            <text class="hero-title">{{ detail.title }}</text>
          </view>
          <text v-if="detail.is_featured" class="hero-badge">精选</text>
        </view>
        <view class="meta-row">
          <text class="meta-chip">{{ detail.user_nickname || '匿名用户' }}</text>
          <text class="meta-chip">{{ formatDate(detail.created_at) }}</text>
          <text class="meta-chip">{{ detail.status || 'approved' }}</text>
        </view>
        <text class="hero-desc">{{ detail.content || detail.description || '暂无讨论内容' }}</text>
        <view class="action-row">
          <button class="secondary-btn" @click="toggleLike">{{ liked ? '取消点赞' : '点赞话题' }}</button>
          <button class="primary-btn" @click="toggleInterest">{{ interested ? '取消关注' : '关注话题' }}</button>
        </view>
        <view class="hero-meta-row">
          <text class="hero-stat">{{ detail.like_count || 0 }} 点赞 · {{ comments.length }} 条评论</text>
        </view>
      </view>

      <view class="comment-card card">
        <view class="section-head">
          <view>
            <text class="section-title">评论区</text>
            <text class="section-subtitle">欢迎留下你的观点、体验和讨论</text>
          </view>
        </view>

        <view v-if="replyTarget" class="reply-banner">
          <text class="reply-text">正在回复：{{ replyTarget.user_nickname || '匿名用户' }}</text>
          <text class="reply-cancel" @click="cancelReply">取消</text>
        </view>

        <textarea v-model="commentText" class="comment-input" placeholder="写下你的评论内容" maxlength="500" />
        <button class="comment-submit" @click="submitComment">{{ replyTarget ? '发布回复' : '发布评论' }}</button>

        <view class="comment-list">
          <view v-for="item in commentThreads" :key="item.id" class="comment-item">
            <view class="comment-avatar">{{ (item.user_nickname || '匿').slice(0, 1) }}</view>
            <view class="comment-body">
              <view class="comment-topline">
                <text class="comment-name">{{ item.user_nickname || '匿名用户' }}</text>
                <view class="comment-actions">
                  <text class="comment-time">{{ formatDate(item.created_at) }}</text>
                  <text class="comment-reply" @click="startReply(item)">回复</text>
                  <text v-if="canDeleteComment(item)" class="comment-delete" @click="deleteComment(item.id)">删除</text>
                </view>
              </view>
              <text class="comment-content">{{ item.content }}</text>

              <view v-if="item.replies?.length" class="reply-list">
                <view v-for="reply in item.replies" :key="reply.id" class="reply-item">
                  <view class="reply-topline">
                    <text class="reply-name">{{ reply.user_nickname || '匿名用户' }}</text>
                    <view class="comment-actions">
                      <text class="comment-time">{{ formatDate(reply.created_at) }}</text>
                      <text class="comment-reply" @click="startReply(item)">回复</text>
                      <text v-if="canDeleteComment(reply)" class="comment-delete" @click="deleteComment(reply.id)">删除</text>
                    </view>
                  </view>
                  <text class="reply-content">{{ reply.content }}</text>
                </view>
              </view>
            </view>
          </view>
          <view v-if="!comments.length" class="comment-empty">还没有评论，来抢个沙发。</view>
        </view>
      </view>

      <view v-if="related.length" class="section">
        <view class="section-head">
          <view>
            <text class="section-title">更多交流</text>
            <text class="section-subtitle">继续浏览社区中的其他讨论内容</text>
          </view>
        </view>
        <view class="related-list">
          <view v-for="item in related" :key="item.id" class="related-item card" @click="goDetail(item.id)">
            <text class="related-title">{{ item.title }}</text>
            <text class="related-desc">{{ item.content || item.description || '暂无内容摘要' }}</text>
            <text class="related-meta">{{ item.user_nickname || '匿名用户' }} · {{ formatDate(item.created_at) }}</text>
          </view>
        </view>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { useAuthStore } from '../../stores/auth'

const detail = ref(null)
const interested = ref(false)
const related = ref([])
const comments = ref([])
const commentText = ref('')
const replyTarget = ref(null)
const auth = useAuthStore()
const liked = ref(false)

const commentThreads = computed(() => {
  const topLevel = comments.value.filter((item) => !item.parent_id)
  const replyMap = new Map()
  comments.value.filter((item) => item.parent_id).forEach((item) => {
    if (!replyMap.has(item.parent_id)) {
      replyMap.set(item.parent_id, [])
    }
    replyMap.get(item.parent_id).push(item)
  })
  return topLevel.map((item) => ({ ...item, replies: replyMap.get(item.id) || [] }))
})

async function fetchDetail() {
  const { id } = getCurrentPages().slice(-1)[0].options
  try {
    detail.value = await api.getBbsPostDetail(id)
    const relatedData = await api.getBbsPosts(1, 5, 'latest')
    related.value = (relatedData.list || []).filter((item) => String(item.id) !== String(id)).slice(0, 4)
    const commentData = await api.getBbsComments(id)
    comments.value = commentData.list || []
  } catch (error) {
    console.error('获取帖子详情失败:', error)
    uni.showToast({ title: '获取帖子详情失败', icon: 'none' })
  }
}

function toggleInterest() {
  uni.showToast({ title: '关注功能暂未开放', icon: 'none' })
}

async function toggleLike() {
  if (!auth.isLoggedIn) {
    uni.showToast({ title: '请先登录后再点赞', icon: 'none' })
    return
  }
  await api.likeBbsPost(detail.value.id)
  liked.value = true
  detail.value = {
    ...detail.value,
    like_count: Math.max(0, Number(detail.value.like_count || 0) + 1),
  }
  uni.showToast({ title: '已点赞话题', icon: 'none' })
}

async function submitComment() {
  if (!auth.isLoggedIn) {
    uni.showToast({ title: '请先登录后再评论', icon: 'none' })
    return
  }
  const content = commentText.value.trim()
  if (!content) {
    uni.showToast({ title: '请输入评论内容', icon: 'none' })
    return
  }
  await api.createBbsComment(detail.value.id, { content, parent_id: replyTarget.value?.id || null })
  commentText.value = ''
  replyTarget.value = null
  const commentData = await api.getBbsComments(detail.value.id)
  comments.value = commentData.list || []
  uni.showToast({ title: '评论已发布', icon: 'success' })
}

async function deleteComment(id) {
  if (!auth.isLoggedIn) {
    uni.showToast({ title: '请先登录后再操作', icon: 'none' })
    return
  }
  await api.deleteBbsComment(id)
  const commentData = await api.getBbsComments(detail.value.id)
  comments.value = commentData.list || []
  uni.showToast({ title: '评论已删除', icon: 'success' })
}

function canDeleteComment(item) {
  return auth.profile?.id && String(auth.profile.id) === String(item.user_id)
}

function startReply(item) {
  replyTarget.value = item
}

function cancelReply() {
  replyTarget.value = null
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/market/market-detail?id=${id}` })
}

function formatDate(value) {
  return String(value || '').slice(0, 10) || '最近更新'
}

onMounted(fetchDetail)
</script>

<style scoped>
.market-detail-page { height:100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.detail-shell { padding:24rpx; }
.hero { padding:28rpx; }
.hero-top { display:flex; align-items:flex-start; justify-content:space-between; gap:16rpx; }
.hero-caption { display:block; font-size:20rpx; color:#9ca3af; letter-spacing:1rpx; }
.hero-title { display:block; margin-top:10rpx; font-size:38rpx; font-weight:800; color:#1f2937; }
.hero-badge { padding:10rpx 16rpx; border-radius:999rpx; background:#fff1de; color:#f97316; font-size:22rpx; font-weight:700; }
.meta-row { display:flex; flex-wrap:wrap; gap:12rpx; margin-top:18rpx; }
.meta-chip { padding:8rpx 14rpx; border-radius:12rpx; background:#f5f5f5; color:#666; font-size:22rpx; }
.hero-desc { display:block; margin-top:20rpx; font-size:24rpx; line-height:1.8; color:#6b7280; }
.action-row { margin-top:22rpx; }
.secondary-btn { margin:0; flex:1; height:84rpx; border-radius:16rpx; background:#fff1de; color:#f97316; font-size:26rpx; font-weight:700; }
.hero-meta-row { margin-top: 16rpx; }
.hero-stat { font-size: 22rpx; color: #f97316; }
.comment-card { margin-top:24rpx; padding:24rpx; }
.reply-banner { display:flex; align-items:center; justify-content:space-between; gap:12rpx; margin-bottom:16rpx; padding:14rpx 18rpx; border-radius:14rpx; background:#fff1de; }
.reply-text { font-size:22rpx; color:#f97316; }
.reply-cancel { font-size:22rpx; color:#c2410c; }
.comment-input { width:100%; min-height:180rpx; padding:20rpx; border-radius:18rpx; background:#f8fafc; font-size:24rpx; }
.comment-submit { margin-top:16rpx; height:80rpx; border-radius:16rpx; background:linear-gradient(135deg,#ffb24c,#ff7b00); color:#fff; font-size:26rpx; font-weight:700; }
.comment-list { margin-top:20rpx; display:flex; flex-direction:column; gap:18rpx; }
.comment-item { display:flex; gap:14rpx; }
.comment-avatar { width:64rpx; height:64rpx; border-radius:50%; background:#fff1de; color:#f97316; display:flex; align-items:center; justify-content:center; font-size:24rpx; font-weight:700; flex-shrink:0; }
.comment-body { flex:1; padding:18rpx; border-radius:18rpx; background:#fafafa; }
.comment-topline { display:flex; align-items:center; justify-content:space-between; gap:12rpx; }
.comment-actions { display:flex; align-items:center; gap:14rpx; }
.comment-name { font-size:24rpx; font-weight:700; color:#1f2937; }
.comment-time { font-size:20rpx; color:#9ca3af; }
.comment-reply { font-size:20rpx; color:#2563eb; }
.comment-delete { font-size:20rpx; color:#f97316; }
.comment-content { display:block; margin-top:10rpx; font-size:22rpx; line-height:1.7; color:#4b5563; }
.reply-list { margin-top:16rpx; display:flex; flex-direction:column; gap:12rpx; }
.reply-item { padding:16rpx; border-radius:14rpx; background:#ffffff; border:1rpx solid #f3f4f6; }
.reply-topline { display:flex; align-items:center; justify-content:space-between; gap:12rpx; }
.reply-name { font-size:22rpx; font-weight:700; color:#1f2937; }
.reply-content { display:block; margin-top:8rpx; font-size:22rpx; line-height:1.7; color:#4b5563; }
.comment-empty { padding:24rpx 0; font-size:22rpx; color:#9ca3af; text-align:center; }
.section { margin-top:24rpx; }
.section-head { margin-bottom:18rpx; }
.section-title { display:block; font-size:32rpx; font-weight:800; color:#1f2937; }
.section-subtitle { display:block; margin-top:8rpx; font-size:22rpx; color:#9ca3af; }
.related-list { display:flex; flex-direction:column; gap:18rpx; }
.related-item { padding:22rpx; }
.related-title { display:block; font-size:28rpx; font-weight:800; color:#1f2937; }
.related-desc { display:block; margin-top:10rpx; font-size:22rpx; color:#6b7280; line-height:1.6; }
.related-meta { display:block; margin-top:14rpx; font-size:20rpx; color:#9ca3af; }
</style>
