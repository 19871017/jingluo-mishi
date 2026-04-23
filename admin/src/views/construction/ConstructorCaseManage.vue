<template>
  <div class="constructor-case-manage">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>施工案例维护</span>
          <el-button type="primary" @click="openCreate">新增案例</el-button>
        </div>
      </template>

      <el-table :data="caseList" style="width: 100%">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="project_name" label="案例名称" />
        <el-table-column prop="brand_name" label="服务品牌" />
        <el-table-column prop="phase" label="施工阶段" />
        <el-table-column prop="status" label="状态" width="120">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)">
              {{ getStatusText(scope.row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="素材" width="150">
          <template #default="scope">
            {{ scope.row.images?.length || 0 }} 图 / {{ scope.row.videos?.length || 0 }} 视频
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="更新时间" width="180" />
        <el-table-column label="操作" width="180">
          <template #default="scope">
            <el-button type="primary" size="small" @click="openEdit(scope.row)">编辑</el-button>
            <el-button type="danger" size="small" @click="handleDelete(scope.row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="form.id ? '编辑案例' : '新增案例'" width="760px">
      <el-form label-width="100px" class="case-form">
        <el-form-item label="案例名称">
          <el-input v-model="form.project_name" placeholder="请输入案例名称" />
        </el-form-item>
        <el-form-item label="服务品牌">
          <el-input v-model="form.brand_name" placeholder="请输入服务品牌" />
        </el-form-item>
        <el-form-item label="施工阶段">
          <el-input v-model="form.phase" placeholder="如：设计中 / 已落地 / 交付完成" />
        </el-form-item>
        <el-form-item label="案例说明">
          <el-input v-model="form.description" type="textarea" :rows="4" placeholder="请输入案例说明" />
        </el-form-item>
        <el-form-item label="图文补充">
          <el-input
            v-model="notesText"
            type="textarea"
            :rows="4"
            placeholder="每行一条，可填写施工亮点、材料、工期说明"
          />
        </el-form-item>
        <el-form-item label="封面图">
          <div class="upload-row">
            <el-input v-model="form.cover" placeholder="上传后会自动回填，也可直接填写图片链接" />
            <el-upload :show-file-list="false" :before-upload="uploadCover">
              <el-button>上传封面</el-button>
            </el-upload>
          </div>
        </el-form-item>
        <el-form-item label="案例图片">
          <div class="media-block">
            <div class="media-list">
              <div v-for="(item, index) in form.images" :key="`${item}-${index}`" class="media-item">
                <span>{{ item }}</span>
                <el-button link type="danger" @click="removeMedia('images', index)">移除</el-button>
              </div>
            </div>
            <el-upload :show-file-list="false" multiple :before-upload="uploadGallery">
              <el-button>上传图片</el-button>
            </el-upload>
          </div>
        </el-form-item>
        <el-form-item label="案例视频">
          <div class="media-block">
            <div class="media-list">
              <div v-for="(item, index) in form.videos" :key="`${item}-${index}`" class="media-item">
                <span>{{ item }}</span>
                <el-button link type="danger" @click="removeMedia('videos', index)">移除</el-button>
              </div>
            </div>
            <el-upload :show-file-list="false" multiple :before-upload="uploadVideo">
              <el-button>上传视频</el-button>
            </el-upload>
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="saving" @click="saveCase">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { ElMessageBox } from 'element-plus'
import { constructorContentApi, uploadApi } from '../../api'

const dialogVisible = ref(false)
const saving = ref(false)
const caseList = ref([])
const notesText = ref('')

const emptyForm = () => ({
  id: null,
  brand_name: '',
  project_name: '',
  phase: '',
  cover: '',
  description: '',
  notes: [],
  images: [],
  videos: [],
})

const form = reactive(emptyForm())

function resetForm() {
  Object.assign(form, emptyForm())
  notesText.value = ''
}

function getStatusType(status) {
  if (status === 'approved') return 'success'
  if (status === 'rejected') return 'danger'
  return 'warning'
}

function getStatusText(status) {
  if (status === 'approved') return '已通过'
  if (status === 'rejected') return '已驳回'
  return '待审核'
}

async function loadCases() {
  const data = await constructorContentApi.getCases({ page: 1, limit: 100 })
  caseList.value = data.list || []
}

function openCreate() {
  resetForm()
  dialogVisible.value = true
}

function openEdit(row) {
  resetForm()
  Object.assign(form, {
    id: row.id,
    brand_name: row.brand_name || '',
    project_name: row.project_name || '',
    phase: row.phase || '',
    cover: row.cover || '',
    description: row.description || '',
    notes: row.notes || [],
    images: row.images || [],
    videos: row.videos || [],
  })
  notesText.value = (row.notes || []).join('\n')
  dialogVisible.value = true
}

function removeMedia(field, index) {
  form[field].splice(index, 1)
}

async function uploadCover(file) {
  const data = await uploadApi.image(file)
  form.cover = data.url
  return false
}

async function uploadGallery(file) {
  const data = await uploadApi.image(file)
  form.images.push(data.url)
  return false
}

async function uploadVideo(file) {
  const data = await uploadApi.image(file)
  form.videos.push(data.url)
  return false
}

async function saveCase() {
  saving.value = true
  try {
    const payload = {
      ...form,
      notes: notesText.value.split('\n').map(item => item.trim()).filter(Boolean),
    }

    if (form.id) {
      await constructorContentApi.updateCase(form.id, payload)
    } else {
      await constructorContentApi.createCase(payload)
    }

    dialogVisible.value = false
    await loadCases()
  } finally {
    saving.value = false
  }
}

async function handleDelete(row) {
  await ElMessageBox.confirm(`确认删除案例「${row.project_name}」吗？`, '删除提示', {
    type: 'warning',
  })
  await constructorContentApi.deleteCase(row.id)
  await loadCases()
}

onMounted(loadCases)
</script>

<style scoped>
.constructor-case-manage {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.case-form {
  max-width: 680px;
}

.upload-row {
  display: flex;
  gap: 12px;
  width: 100%;
}

.upload-row .el-input {
  flex: 1;
}

.media-block {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.media-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.media-item {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 12px;
  background: #f5f7fa;
  border-radius: 8px;
}

.media-item span {
  flex: 1;
  word-break: break-all;
}
</style>
