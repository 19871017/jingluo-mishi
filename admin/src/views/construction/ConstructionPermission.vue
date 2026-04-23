<template>
  <div class="construction-permission">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>施工权限申请管理</span>
        </div>
      </template>

      <el-table :data="permissionList" style="width: 100%">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="role_type" label="申请身份" width="100">
          <template #default="scope">
            {{ scope.row.role_type === 'brand' ? '品牌方' : '施工方' }}
          </template>
        </el-table-column>
        <el-table-column prop="brand_name" label="品牌名称" />
        <el-table-column prop="company_name" label="公司名称" />
        <el-table-column prop="contact_name" label="联系人" />
        <el-table-column prop="contact_phone" label="联系电话" />
        <el-table-column prop="account_username" label="后台账号" width="160">
          <template #default="scope">
            {{ scope.row.account_username || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="110">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)">
              {{ getStatusText(scope.row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="申请时间" width="180" />
        <el-table-column label="操作" width="200">
          <template #default="scope">
            <el-button
              v-if="scope.row.status === 'pending'"
              type="primary"
              size="small"
              @click="handleApprove(scope.row)"
            >
              通过
            </el-button>
            <el-button
              v-if="scope.row.status === 'pending'"
              type="danger"
              size="small"
              @click="handleReject(scope.row)"
            >
              驳回
            </el-button>
            <el-button type="info" size="small" @click="handleView(scope.row)">
              查看详情
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-container">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <el-dialog v-model="dialogVisible" title="申请详情" width="50%">
      <el-descriptions :column="1" border>
        <el-descriptions-item label="申请身份">
          {{ currentPermission?.role_type === 'brand' ? '品牌方' : '施工方' }}
        </el-descriptions-item>
        <el-descriptions-item label="品牌名称">
          {{ currentPermission?.brand_name || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="公司名称">
          {{ currentPermission?.company_name || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="联系人">
          {{ currentPermission?.contact_name || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="联系电话">
          {{ currentPermission?.contact_phone || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="施工方后台账号" v-if="currentPermission?.role_type === 'constructor'">
          {{ currentPermission?.account_username || '审核通过后自动生成，默认密码 admin123' }}
        </el-descriptions-item>
        <el-descriptions-item label="申请说明">
          {{ currentPermission?.reason || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="审核备注">
          {{ currentPermission?.review_note || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="申请时间">
          {{ currentPermission?.created_at }}
        </el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getStatusType(currentPermission?.status)">
            {{ getStatusText(currentPermission?.status) }}
          </el-tag>
        </el-descriptions-item>
      </el-descriptions>
      <template #footer>
        <el-button @click="dialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="rejectDialogVisible" title="驳回申请" width="50%">
      <el-form :model="rejectForm" label-width="80px">
        <el-form-item label="驳回原因">
          <el-input
            v-model="rejectForm.review_note"
            type="textarea"
            rows="4"
            placeholder="请输入驳回原因"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="rejectDialogVisible = false">取消</el-button>
        <el-button type="danger" @click="submitReject">确认驳回</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { constructionApi } from '../../api'

const permissionList = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const dialogVisible = ref(false)
const rejectDialogVisible = ref(false)
const currentPermission = ref(null)
const rejectForm = ref({ review_note: '' })

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

async function loadPermissions() {
  const data = await constructionApi.getPermissions({
    page: currentPage.value,
    limit: pageSize.value,
  })
  permissionList.value = data.list || []
  total.value = data.total || 0
}

function handleSizeChange(size) {
  pageSize.value = size
  loadPermissions()
}

function handleCurrentChange(current) {
  currentPage.value = current
  loadPermissions()
}

async function handleApprove(permission) {
  await constructionApi.approvePermission(permission.id)
  await loadPermissions()
}

function handleReject(permission) {
  currentPermission.value = permission
  rejectForm.value = { review_note: '' }
  rejectDialogVisible.value = true
}

async function submitReject() {
  await constructionApi.rejectPermission(currentPermission.value.id, {
    review_note: rejectForm.value.review_note,
  })
  rejectDialogVisible.value = false
  await loadPermissions()
}

function handleView(permission) {
  currentPermission.value = permission
  dialogVisible.value = true
}

onMounted(loadPermissions)
</script>

<style scoped>
.construction-permission {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>
