<template>
  <div class="home-content">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="轮播图管理" name="banners">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>首页轮播图</span>
              <el-button type="primary" @click="openBannerDialog()">新增轮播图</el-button>
            </div>
          </template>

          <el-table :data="banners" border>
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column label="图片" width="220">
              <template #default="{ row }">
                <el-image
                  v-if="row.image"
                  :src="row.image"
                  :preview-src-list="[row.image]"
                  fit="cover"
                  class="table-image"
                  preview-teleported
                />
                <span v-else class="empty-text">未上传</span>
              </template>
            </el-table-column>
            <el-table-column prop="link" label="跳转链接" min-width="260" />
            <el-table-column prop="sort_order" label="排序" width="100" />
            <el-table-column label="操作" width="180" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" link @click="openBannerDialog(row)">编辑</el-button>
                <el-button type="danger" link @click="handleDeleteBanner(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="专题图片管理" name="ads">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>首页专题大图</span>
              <el-button type="primary" @click="openAdDialog()">新增专题图</el-button>
            </div>
          </template>

          <el-table :data="ads" border>
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column label="图片" width="220">
              <template #default="{ row }">
                <el-image
                  v-if="row.image"
                  :src="row.image"
                  :preview-src-list="[row.image]"
                  fit="cover"
                  class="table-image"
                  preview-teleported
                />
                <span v-else class="empty-text">未上传</span>
              </template>
            </el-table-column>
            <el-table-column prop="link" label="跳转链接" min-width="260" />
            <el-table-column prop="sort_order" label="排序" width="100" />
            <el-table-column label="操作" width="180" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" link @click="openAdDialog(row)">编辑</el-button>
                <el-button type="danger" link @click="handleDeleteAd(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>
    </el-tabs>

    <el-dialog v-model="bannerDialogVisible" :title="bannerForm.id ? '编辑轮播图' : '新增轮播图'" width="620px">
      <el-form :model="bannerForm" label-width="100px">
        <el-form-item label="图片">
          <div class="upload-block">
            <el-upload
              :http-request="uploadBannerFile"
              :show-file-list="false"
              :before-upload="beforeImageUpload"
              accept=".jpg,.jpeg,.png,.gif"
            >
              <el-button type="primary">上传图片</el-button>
            </el-upload>
            <span class="upload-tip">支持 JPG / PNG / GIF，大小不超过 5MB</span>
          </div>
          <el-input v-model="bannerForm.image" placeholder="上传后会自动回填图片地址，也可手动填写" />
          <el-image
            v-if="bannerForm.image"
            :src="bannerForm.image"
            :preview-src-list="[bannerForm.image]"
            fit="cover"
            class="form-image"
            preview-teleported
          />
        </el-form-item>
        <el-form-item label="跳转链接">
          <el-input v-model="bannerForm.link" placeholder="例如：script/12 跳转剧本详情，brand/3 跳转品牌详情" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="bannerForm.sort_order" :min="0" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="bannerDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmitBanner">保存</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="adDialogVisible" :title="adForm.id ? '编辑专题图片' : '新增专题图片'" width="620px">
      <el-form :model="adForm" label-width="100px">
        <el-form-item label="图片">
          <div class="upload-block">
            <el-upload
              :http-request="uploadAdFile"
              :show-file-list="false"
              :before-upload="beforeImageUpload"
              accept=".jpg,.jpeg,.png,.gif"
            >
              <el-button type="primary">上传图片</el-button>
            </el-upload>
            <span class="upload-tip">支持 JPG / PNG / GIF，大小不超过 5MB</span>
          </div>
          <el-input v-model="adForm.image" placeholder="上传后会自动回填图片地址，也可手动填写" />
          <el-image
            v-if="adForm.image"
            :src="adForm.image"
            :preview-src-list="[adForm.image]"
            fit="cover"
            class="form-image"
            preview-teleported
          />
        </el-form-item>
        <el-form-item label="跳转链接">
          <el-input v-model="adForm.link" placeholder="例如：script/12 跳转剧本详情，留空则仅展示图片" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="adForm.sort_order" :min="0" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="adDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmitAd">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { homeApi, uploadApi } from '../../api'

const activeTab = ref('banners')
const banners = ref([])
const ads = ref([])
const bannerDialogVisible = ref(false)
const adDialogVisible = ref(false)

const createBannerForm = () => ({
  id: null,
  image: '',
  link: '',
  sort_order: 0,
})

const createAdForm = () => ({
  id: null,
  image: '',
  link: '',
  sort_order: 0,
})

const bannerForm = reactive(createBannerForm())
const adForm = reactive(createAdForm())

function assignForm(target, source) {
  Object.assign(target, source)
}

function resetBannerForm() {
  assignForm(bannerForm, createBannerForm())
}

function resetAdForm() {
  assignForm(adForm, createAdForm())
}

function normalizeListPayload(data) {
  if (Array.isArray(data)) return data
  if (Array.isArray(data?.list)) return data.list
  return []
}

function extractUploadedUrl(payload) {
  return payload?.url || payload?.data?.url || payload?.path || ''
}

function beforeImageUpload(file) {
  const isImage = ['image/jpeg', 'image/png', 'image/gif'].includes(file.type)
  const isLt5M = file.size / 1024 / 1024 < 5

  if (!isImage) {
    ElMessage.error('只能上传 JPG、PNG、GIF 图片')
    return false
  }

  if (!isLt5M) {
    ElMessage.error('图片大小不能超过 5MB')
    return false
  }

  return true
}

async function fetchBanners() {
  const data = await homeApi.getBanners()
  banners.value = normalizeListPayload(data)
}

async function fetchAds() {
  const data = await homeApi.getAds()
  ads.value = normalizeListPayload(data)
}

function openBannerDialog(row = null) {
  if (row) {
    assignForm(bannerForm, {
      id: row.id,
      image: row.image || '',
      link: row.link || '',
      sort_order: Number(row.sort_order || 0),
    })
  } else {
    resetBannerForm()
  }

  bannerDialogVisible.value = true
}

function openAdDialog(row = null) {
  if (row) {
    assignForm(adForm, {
      id: row.id,
      image: row.image || '',
      link: row.link || '',
      sort_order: Number(row.sort_order || 0),
    })
  } else {
    resetAdForm()
  }

  adDialogVisible.value = true
}

async function uploadBannerFile(options) {
  try {
    const payload = await uploadApi.image(options.file)
    const imageUrl = extractUploadedUrl(payload)
    if (!imageUrl) throw new Error('上传成功但没有返回图片地址')
    bannerForm.image = imageUrl
    ElMessage.success('轮播图上传成功')
    options.onSuccess?.(payload)
  } catch (error) {
    console.error(error)
    ElMessage.error('轮播图上传失败')
    options.onError?.(error)
  }
}

async function uploadAdFile(options) {
  try {
    const payload = await uploadApi.image(options.file)
    const imageUrl = extractUploadedUrl(payload)
    if (!imageUrl) throw new Error('上传成功但没有返回图片地址')
    adForm.image = imageUrl
    ElMessage.success('专题图上传成功')
    options.onSuccess?.(payload)
  } catch (error) {
    console.error(error)
    ElMessage.error('专题图上传失败')
    options.onError?.(error)
  }
}

async function handleSubmitBanner() {
  if (!bannerForm.image) {
    ElMessage.warning('请先上传轮播图图片')
    return
  }

  const payload = {
    image: bannerForm.image,
    link: bannerForm.link,
    sort_order: bannerForm.sort_order,
  }

  if (bannerForm.id) {
    await homeApi.updateBanner(bannerForm.id, payload)
    ElMessage.success('轮播图更新成功')
  } else {
    await homeApi.createBanner(payload)
    ElMessage.success('轮播图创建成功')
  }

  bannerDialogVisible.value = false
  resetBannerForm()
  await fetchBanners()
}

async function handleSubmitAd() {
  if (!adForm.image) {
    ElMessage.warning('请先上传专题图片')
    return
  }

  const payload = {
    image: adForm.image,
    link: adForm.link,
    sort_order: adForm.sort_order,
  }

  if (adForm.id) {
    await homeApi.updateAd(adForm.id, payload)
    ElMessage.success('专题图更新成功')
  } else {
    await homeApi.createAd(payload)
    ElMessage.success('专题图创建成功')
  }

  adDialogVisible.value = false
  resetAdForm()
  await fetchAds()
}

async function handleDeleteBanner(row) {
  try {
    await ElMessageBox.confirm(`确认删除轮播图 #${row.id} 吗？`, '删除确认', { type: 'warning' })
    await homeApi.deleteBanner(row.id)
    ElMessage.success('轮播图已删除')
    await fetchBanners()
  } catch (error) {
    if (error !== 'cancel') console.error(error)
  }
}

async function handleDeleteAd(row) {
  try {
    await ElMessageBox.confirm(`确认删除专题图 #${row.id} 吗？`, '删除确认', { type: 'warning' })
    await homeApi.deleteAd(row.id)
    ElMessage.success('专题图已删除')
    await fetchAds()
  } catch (error) {
    if (error !== 'cancel') console.error(error)
  }
}

onMounted(() => {
  fetchBanners()
  fetchAds()
})
</script>

<style scoped>
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.table-image {
  width: 120px;
  height: 72px;
  border-radius: 8px;
}

.form-image {
  width: 240px;
  height: 140px;
  margin-top: 12px;
  border-radius: 10px;
  border: 1px solid #ebeef5;
}

.upload-block {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.upload-tip,
.empty-text {
  color: #909399;
  font-size: 13px;
}
</style>
