<template>
  <div class="construction-permission">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">入驻审核</div>
            <div class="header-desc">这里集中审核品牌方和施工方的入驻申请，审核通过后自动生成对应后台账号。</div>
          </div>
        </div>
      </template>

      <div class="section-block">
        <div class="section-title">品牌方申请</div>
        <el-table :data="brandPermissionList" style="width: 100%">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="brand_name" label="品牌名称" min-width="180" />
          <el-table-column prop="contact_name" label="联系人" width="120" />
          <el-table-column prop="contact_phone" label="联系电话" width="160" />
          <el-table-column prop="status" label="状态" width="110">
            <template #default="scope">
              <el-tag :type="getStatusType(scope.row.status)">{{ getStatusText(scope.row.status) }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="created_at" label="申请时间" width="180" />
          <el-table-column label="操作" width="220">
            <template #default="scope">
              <el-button v-if="scope.row.status === 'pending'" type="primary" size="small" @click="handleApprove(scope.row)">通过</el-button>
              <el-button v-if="scope.row.status === 'pending'" type="danger" size="small" @click="handleReject(scope.row)">驳回</el-button>
              <el-button type="info" size="small" @click="handleView(scope.row)">查看详情</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>

      <div class="section-block second-block">
        <div class="section-title">施工方申请</div>
        <el-table :data="constructorPermissionList" style="width: 100%">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="company_name" label="施工方名称" min-width="180" />
          <el-table-column prop="brand_name" label="服务品牌" min-width="160" />
          <el-table-column prop="contact_name" label="联系人" width="120" />
          <el-table-column prop="contact_phone" label="联系电话" width="160" />
          <el-table-column prop="account_username" label="后台账号" width="160">
            <template #default="scope">
              {{ scope.row.account_username || '-' }}
            </template>
          </el-table-column>
          <el-table-column prop="status" label="状态" width="110">
            <template #default="scope">
              <el-tag :type="getStatusType(scope.row.status)">{{ getStatusText(scope.row.status) }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="created_at" label="申请时间" width="180" />
          <el-table-column label="操作" width="220">
            <template #default="scope">
              <el-button v-if="scope.row.status === 'pending'" type="primary" size="small" @click="handleApprove(scope.row)">通过</el-button>
              <el-button v-if="scope.row.status === 'pending'" type="danger" size="small" @click="handleReject(scope.row)">驳回</el-button>
              <el-button type="info" size="small" @click="handleView(scope.row)">查看详情</el-button>
            </template>
          </el-table-column>
        </el-table>
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
        <el-descriptions-item label="后台账号" v-if="currentPermission?.role_type === 'constructor'">
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
          <el-tag :type="getStatusType(currentPermission?.status)">{{ getStatusText(currentPermission?.status) }}</el-tag>
        </el-descriptions-item>
      </el-descriptions>
      <template #footer>
        <el-button @click="dialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="rejectDialogVisible" title="驳回申请" width="50%">
      <el-form :model="rejectForm" label-width="80px">
        <el-form-item label="驳回原因">
          <el-input v-model="rejectForm.review_note" type="textarea" rows="4" placeholder="请输入驳回原因" />
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
import { ref, onMounted, computed } from 'vue'
import { constructionApi } from '../../api'

const permissionList = ref([])
const dialogVisible = ref(false)
const rejectDialogVisible = ref(false)
const currentPermission = ref(null)
const rejectForm = ref({ review_note: '' })

const brandPermissionList = computed(() => permissionList.value.filter((item) => item.role_type === 'brand'))
const constructorPermissionList = computed(() => permissionList.value.filter((item) => item.role_type === 'constructor'))

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
  const data = await constructionApi.getPermissions({ page: 1, limit: 100 })
  permissionList.value = data.list || []
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

.section-block {
  margin-top: 8px;
}

.second-block {
  margin-top: 28px;
}

.section-title {
  margin-bottom: 14px;
  font-size: 16px;
  font-weight: 700;
  color: #303133;
}
</style>
