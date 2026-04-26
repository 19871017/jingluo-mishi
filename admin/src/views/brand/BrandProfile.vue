<template>
  <div class="brand-profile">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>品牌方个人设置</span>
        </div>
      </template>

      <el-form label-width="110px" class="profile-form">
        <el-form-item label="品牌名称">
          <el-input v-model="profile.name" placeholder="请输入品牌名称" />
        </el-form-item>
        <el-form-item label="品牌 Logo">
          <el-upload :http-request="uploadLogo" :show-file-list="false" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
            <el-button type="primary">上传 Logo</el-button>
          </el-upload>
          <el-input v-model="profile.logo" placeholder="上传后会自动回填，也可以直接粘贴图片地址" style="margin-top: 12px" />
          <el-image v-if="profile.logo" :src="profile.logo" class="preview-logo" fit="cover" />
        </el-form-item>
        <el-form-item label="品牌背景图">
          <el-upload :http-request="uploadCover" :show-file-list="false" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
            <el-button type="primary">上传背景图</el-button>
          </el-upload>
          <el-input v-model="profile.cover_image" placeholder="上传后会自动回填，也可以直接粘贴图片地址" style="margin-top: 12px" />
          <el-image v-if="profile.cover_image" :src="profile.cover_image" class="preview-cover" fit="cover" />
        </el-form-item>
        <el-form-item label="品牌介绍">
          <el-input
            v-model="profile.description"
            type="textarea"
            :rows="6"
            placeholder="请输入品牌介绍、擅长方向、授权说明等内容"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :loading="saving" @click="saveProfile">保存设置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { brandProfileApi, uploadApi } from '../../api'

const saving = ref(false)
const profile = reactive({
  name: '',
  logo: '',
  cover_image: '',
  description: '',
})

async function loadProfile() {
  const data = await brandProfileApi.getProfile()
  Object.assign(profile, data || {})
}

async function uploadLogo(options) {
  try {
    const payload = await uploadApi.image(options.file)
    profile.logo = payload?.url || payload?.data?.url || ''
    options.onSuccess?.(payload)
  } catch (error) {
    options.onError?.(error)
  }
}

async function uploadCover(options) {
  try {
    const payload = await uploadApi.image(options.file)
    profile.cover_image = payload?.url || payload?.data?.url || ''
    options.onSuccess?.(payload)
  } catch (error) {
    options.onError?.(error)
  }
}

async function saveProfile() {
  saving.value = true
  try {
    await brandProfileApi.updateProfile(profile)
    await loadProfile()
  } finally {
    saving.value = false
  }
}

onMounted(loadProfile)
</script>

<style scoped>
.brand-profile {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.profile-form {
  max-width: 760px;
}

.preview-logo {
  width: 72px;
  height: 72px;
  margin-top: 12px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #ebeef5;
}

.preview-cover {
  width: 100%;
  max-width: 320px;
  height: 120px;
  margin-top: 12px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #ebeef5;
}
</style>
