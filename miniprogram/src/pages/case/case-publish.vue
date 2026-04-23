<template>
  <scroll-view scroll-y class="publish-page">
    <view class="publish-shell">
      <view class="hero card">
        <text class="hero-title">上传施工案例</text>
        <text class="hero-desc">品牌施工方可上传施工现场图片、视频和节点说明，方便加盟方同步查看落地过程。</text>
      </view>

      <view class="form-card card">
        <input v-model="form.brandName" class="field" placeholder="品牌名称" />
        <input v-model="form.projectName" class="field" placeholder="案例标题 / 项目名称" />
        <input v-model="form.phase" class="field" placeholder="施工阶段，如木作进场 / 完工验收" />
        <textarea v-model="form.description" class="field area" placeholder="案例说明，描述施工进度与关键节点" maxlength="1000" />
        <textarea v-model="notesText" class="field area small" placeholder="施工说明，每行一条" maxlength="1000" />

        <view class="upload-block">
          <view class="upload-head">
            <text class="upload-title">施工图片</text>
            <text class="upload-action" @click="chooseImages">添加图片</text>
          </view>
          <view v-if="form.images.length" class="preview-grid">
            <image v-for="(item, index) in form.images" :key="index" class="preview-image" :src="item" mode="aspectFill" @click="removeImage(index)" />
          </view>
        </view>

        <view class="upload-block">
          <view class="upload-head">
            <text class="upload-title">施工视频</text>
            <text class="upload-action" @click="chooseVideo">添加视频</text>
          </view>
          <view v-if="form.videos.length" class="video-preview-list">
            <view v-for="(item, index) in form.videos" :key="index" class="video-preview-item">
              <text class="video-name">视频 {{ index + 1 }}</text>
              <text class="video-remove" @click="removeVideo(index)">删除</text>
            </view>
          </view>
        </view>

        <button class="primary-btn" @click="submit">发布案例</button>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { api } from '../../services/api'
import { useAuthStore } from '../../stores/auth'

const form = reactive({
  brandName: '',
  projectName: '',
  phase: '',
  description: '',
  images: [],
  videos: [],
})
const notesText = ref('')
const auth = useAuthStore()
const page = getCurrentPages().slice(-1)[0]
const editingId = ref(page?.options?.id || '')

function chooseImages() {
  uni.chooseImage({
    count: 9,
    success: ({ tempFilePaths }) => {
      form.images = [...form.images, ...tempFilePaths].slice(0, 9)
    },
  })
}

function chooseVideo() {
  uni.chooseVideo({
    sourceType: ['album', 'camera'],
    success: ({ tempFilePath }) => {
      form.videos = [...form.videos, tempFilePath].slice(0, 3)
    },
  })
}

function removeImage(index) {
  form.images.splice(index, 1)
}

function removeVideo(index) {
  form.videos.splice(index, 1)
}

function submit() {
  if (!auth.isLoggedIn) {
    uni.showToast({ title: '请先登录后上传案例', icon: 'none' })
    return
  }
  if (!auth.canManageConstructionCases) {
    uni.showToast({ title: '当前账号暂无施工案例发布权限', icon: 'none' })
    return
  }
  if (!form.brandName || !form.projectName || !form.phase || !form.description) {
    uni.showToast({ title: '请先填写完整案例信息', icon: 'none' })
    return
  }

  uploadAndSubmit()
}

async function uploadAndSubmit() {
  uni.showLoading({ title: '提交中', mask: true })
  try {
    const uploadedImages = []
    for (const path of form.images) {
      uploadedImages.push(await uploadFile(path, 'image'))
    }

    const uploadedVideos = []
    for (const path of form.videos) {
      uploadedVideos.push(await uploadFile(path, 'video'))
    }

    const payload = {
      brand_name: form.brandName,
      project_name: form.projectName,
      phase: form.phase,
      description: form.description,
      cover: uploadedImages[0] || '',
      images: uploadedImages,
      videos: uploadedVideos,
      notes: notesText.value.split('\n').map((item) => item.trim()).filter(Boolean),
    }
    const response = editingId.value ? await api.updateConstructionCase(editingId.value, payload) : await api.createConstructionCase(payload)
    uni.hideLoading()
    uni.showToast({ title: editingId.value ? '案例已更新' : '案例已保存', icon: 'success' })
    setTimeout(() => {
      if (editingId.value) {
        uni.navigateTo({ url: `/pages/case/case-detail?id=${editingId.value}` })
      } else {
        uni.navigateTo({ url: `/pages/case/case-detail?id=${response.id}` })
      }
    }, 400)
  } catch (_) {
    uni.hideLoading()
  }
}

async function loadDetail() {
  if (!editingId.value) return
  const detail = await api.getConstructionCaseDetail(editingId.value)
  form.brandName = detail.brand_name || ''
  form.projectName = detail.project_name || ''
  form.phase = detail.phase || ''
  form.description = detail.description || ''
  form.images = detail.images || []
  form.videos = detail.videos || []
  notesText.value = (detail.notes || []).join('\n')
}

loadDetail()

function uploadFile(filePath, mediaType) {
  return new Promise((resolve, reject) => {
    uni.uploadFile({
      url: 'http://127.0.0.1:8090/api/upload',
      filePath,
      name: 'file',
      formData: {
        directory: 'cases',
        media_type: mediaType,
      },
      header: {
        Authorization: `Bearer ${uni.getStorageSync('user_token')}`,
      },
      success: ({ data, statusCode }) => {
        if (statusCode < 200 || statusCode >= 300) {
          reject(new Error('upload failed'))
          return
        }
        const parsed = JSON.parse(data)
        resolve(parsed.data?.url || parsed.file?.url || '')
      },
      fail: reject,
    })
  })
}
</script>

<style scoped>
.publish-page { height:100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.publish-shell { padding:24rpx; }
.hero { padding:24rpx; }
.hero-title { display:block; font-size:34rpx; font-weight:800; color:#1f2937; }
.hero-desc { display:block; margin-top:10rpx; font-size:22rpx; line-height:1.7; color:#9ca3af; }
.form-card { margin-top:20rpx; padding:24rpx; display:flex; flex-direction:column; gap:18rpx; }
.field { width:100%; min-height:84rpx; padding:20rpx; border-radius:16rpx; background:#f9fafb; font-size:24rpx; }
.area { height:220rpx; }
.area.small { height:180rpx; }
.upload-block { padding:18rpx; border-radius:18rpx; background:#fafafa; }
.upload-head { display:flex; align-items:center; justify-content:space-between; gap:12rpx; margin-bottom:14rpx; }
.upload-title { font-size:26rpx; font-weight:700; color:#1f2937; }
.upload-action { font-size:22rpx; color:#f97316; }
.preview-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12rpx; }
.preview-image { width:100%; height:160rpx; border-radius:16rpx; }
.video-preview-list { display:flex; flex-direction:column; gap:12rpx; }
.video-preview-item { display:flex; align-items:center; justify-content:space-between; gap:12rpx; padding:14rpx 16rpx; border-radius:14rpx; background:#fff; }
.video-name { font-size:22rpx; color:#374151; }
.video-remove { font-size:22rpx; color:#f97316; }
</style>
