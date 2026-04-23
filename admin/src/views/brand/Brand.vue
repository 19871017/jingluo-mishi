<template>
  <div class="brand-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">品牌管理</div>
            <div class="header-desc">在这里维护品牌资料、审核状态，以及品牌方后台登录账号。</div>
          </div>

          <div class="header-actions">
            <el-form :inline="true" :model="searchForm">
              <el-form-item label="审核状态">
                <el-select v-model="searchForm.status" clearable placeholder="全部状态" style="width: 140px">
                  <el-option label="待审核" value="pending" />
                  <el-option label="已通过" value="approved" />
                  <el-option label="已拒绝" value="rejected" />
                </el-select>
              </el-form-item>
              <el-form-item>
                <el-button type="primary" @click="handleSearch">筛选</el-button>
              </el-form-item>
            </el-form>
            <el-button type="primary" @click="openDialog()">新增品牌</el-button>
          </div>
        </div>
      </template>

      <el-table :data="list" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="品牌名称" min-width="180" />
        <el-table-column label="品牌 Logo" width="120">
          <template #default="{ row }">
            <el-image v-if="row.logo" :src="row.logo" class="table-logo" fit="cover" />
            <span v-else class="muted-text">未上传</span>
          </template>
        </el-table-column>
        <el-table-column prop="account_username" label="品牌后台账号" min-width="180" />
        <el-table-column prop="status" label="审核状态" width="100">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)">{{ statusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" min-width="180" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button v-if="row.status === 'pending'" type="success" link @click="handleAudit(row, 'approved')">
              通过
            </el-button>
            <el-button v-if="row.status === 'pending'" type="danger" link @click="handleAudit(row, 'rejected')">
              拒绝
            </el-button>
            <el-button type="primary" link @click="openDialog(row)">编辑</el-button>
            <el-button type="info" link @click="openDetail(row)">详情</el-button>
            <el-button type="danger" link @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-model:current-page="page"
        style="margin-top: 20px"
        background
        layout="total, prev, pager, next"
        :total="total"
        @current-change="fetchList"
      />
    </el-card>

    <el-dialog v-model="dialogVisible" :title="form.id ? '编辑品牌' : '新增品牌'" width="640px">
      <el-form :model="form" label-width="110px">
        <el-form-item label="品牌名称">
          <el-input v-model="form.name" placeholder="请输入品牌名称" />
        </el-form-item>
        <el-form-item label="品牌 Logo">
          <el-upload :http-request="uploadLogo" :show-file-list="false" accept=".jpg,.jpeg,.png,.gif,.webp">
            <el-button type="primary">上传 Logo</el-button>
          </el-upload>
          <el-input v-model="form.logo" placeholder="上传后会自动回填，也可以直接粘贴图片地址" style="margin-top: 12px" />
          <el-image v-if="form.logo" :src="form.logo" class="preview-logo" fit="cover" />
        </el-form-item>
        <el-form-item label="品牌介绍">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="4"
            placeholder="这里的介绍会展示在小程序品牌详情页"
          />
        </el-form-item>
        <el-form-item label="审核状态">
          <el-select v-model="form.status" placeholder="请选择状态" style="width: 100%">
            <el-option label="待审核" value="pending" />
            <el-option label="已通过" value="approved" />
            <el-option label="已拒绝" value="rejected" />
          </el-select>
        </el-form-item>

        <el-divider content-position="left">品牌方后台账号</el-divider>
        <el-form-item label="登录账号">
          <el-input v-model="form.account_username" placeholder="例如：brand_demo" />
        </el-form-item>
        <el-form-item label="登录密码">
          <el-input
            v-model="form.account_password"
            type="password"
            show-password
            placeholder="新建默认 admin123；编辑时留空表示不修改"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">保存</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailDialogVisible" title="品牌详情" width="560px">
      <el-descriptions :column="1" border>
        <el-descriptions-item label="品牌名称">{{ currentBrand?.name }}</el-descriptions-item>
        <el-descriptions-item label="品牌后台账号">
          {{ currentBrand?.account_username || '未配置' }}
        </el-descriptions-item>
        <el-descriptions-item label="品牌介绍">
          {{ currentBrand?.description || '暂无品牌介绍' }}
        </el-descriptions-item>
        <el-descriptions-item label="审核状态">
          <el-tag :type="statusType(currentBrand?.status)">{{ statusText(currentBrand?.status) }}</el-tag>
        </el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { brandApi, uploadApi } from '../../api'

const list = ref([])
const total = ref(0)
const page = ref(1)
const dialogVisible = ref(false)
const detailDialogVisible = ref(false)
const currentBrand = ref(null)
const searchForm = reactive({ status: '' })
const form = reactive({
  id: null,
  name: '',
  logo: '',
  description: '',
  status: 'pending',
  account_username: '',
  account_password: '',
})

function statusText(status) {
  return {
    pending: '待审核',
    approved: '已通过',
    rejected: '已拒绝',
  }[status] || status
}

function statusType(status) {
  return {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
  }[status] || 'info'
}

function resetForm() {
  Object.assign(form, {
    id: null,
    name: '',
    logo: '',
    description: '',
    status: 'pending',
    account_username: '',
    account_password: '',
  })
}

async function fetchList() {
  const data = await brandApi.list({ page: page.value, limit: 20, status: searchForm.status })
  list.value = data.list || []
  total.value = data.total || 0
}

function handleSearch() {
  page.value = 1
  fetchList()
}

function openDialog(row = null) {
  if (row) {
    Object.assign(form, {
      id: row.id,
      name: row.name,
      logo: row.logo || '',
      description: row.description || '',
      status: row.status,
      account_username: row.account_username || '',
      account_password: '',
    })
  } else {
    resetForm()
  }
  dialogVisible.value = true
}

async function uploadLogo(options) {
  try {
    const payload = await uploadApi.image(options.file)
    form.logo = payload?.url || payload?.data?.url || ''
    ElMessage.success('Logo 上传成功')
    options.onSuccess?.(payload)
  } catch (error) {
    console.error(error)
    ElMessage.error('Logo 上传失败')
    options.onError?.(error)
  }
}

async function handleSubmit() {
  const payload = {
    name: form.name,
    logo: form.logo,
    description: form.description,
    status: form.status,
    account_username: form.account_username,
    account_password: form.account_password,
  }

  try {
    if (form.id) {
      await brandApi.update(form.id, payload)
      ElMessage.success('品牌已更新')
    } else {
      await brandApi.create(payload)
      ElMessage.success('品牌已创建')
    }
    dialogVisible.value = false
    await fetchList()
  } catch (error) {
    console.error(error)
  }
}

async function handleAudit(row, status) {
  try {
    await brandApi.audit(row.id, status)
    ElMessage.success('审核成功')
    await fetchList()
  } catch (error) {
    console.error(error)
  }
}

async function openDetail(row) {
  currentBrand.value = await brandApi.detail(row.id)
  detailDialogVisible.value = true
}

async function handleDelete(row) {
  try {
    await ElMessageBox.confirm(`确定删除品牌“${row.name}”吗？`, '删除确认', { type: 'warning' })
    await brandApi.delete(row.id)
    ElMessage.success('删除成功')
    await fetchList()
  } catch (error) {
    if (error !== 'cancel') {
      console.error(error)
    }
  }
}

onMounted(fetchList)
</script>

<style scoped>
.card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-title {
  font-size: 18px;
  font-weight: 700;
  color: #303133;
}

.header-desc {
  margin-top: 6px;
  font-size: 13px;
  color: #909399;
}

.table-logo,
.preview-logo {
  width: 72px;
  height: 72px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #ebeef5;
}

.preview-logo {
  margin-top: 12px;
}

.muted-text {
  color: #909399;
}
</style>
