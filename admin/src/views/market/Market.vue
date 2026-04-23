<template>
  <div class="market-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">市场管理</div>
            <div class="header-desc">审核玩家发布的出售信息与求购信息，并决定是否加入首页精选。</div>
          </div>

          <el-form :inline="true" :model="searchForm">
            <el-form-item label="审核状态">
              <el-select v-model="searchForm.status" placeholder="全部状态" clearable style="width: 140px">
                <el-option label="待审核" value="pending" />
                <el-option label="已通过" value="approved" />
                <el-option label="已拒绝" value="rejected" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="handleSearch">筛选</el-button>
            </el-form-item>
          </el-form>
        </div>
      </template>

      <el-table :data="list" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="信息标题" min-width="220" />
        <el-table-column prop="type" label="类型" width="100">
          <template #default="{ row }">
            <el-tag :type="row.type === 'sell' ? 'success' : 'warning'">
              {{ row.type === 'sell' ? '出售' : '求购' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="price" label="价格" width="120">
          <template #default="{ row }">{{ row.price || '面议' }}</template>
        </el-table-column>
        <el-table-column prop="status" label="审核状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">{{ getStatusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="is_featured" label="首页精选" width="100">
          <template #default="{ row }">
            <el-tag :type="row.is_featured ? 'success' : 'info'">{{ row.is_featured ? '是' : '否' }}</el-tag>
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
            <el-button type="warning" link @click="handleFeatured(row)">
              {{ row.is_featured ? '取消精选' : '设为精选' }}
            </el-button>
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
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { marketApi } from '../../api'

const list = ref([])
const total = ref(0)
const page = ref(1)
const searchForm = reactive({
  status: '',
})

function getStatusType(status) {
  return { pending: 'warning', approved: 'success', rejected: 'danger' }[status] || 'info'
}

function getStatusText(status) {
  return { pending: '待审核', approved: '已通过', rejected: '已拒绝' }[status] || status
}

async function fetchList() {
  try {
    const data = await marketApi.list({ page: page.value, limit: 20, status: searchForm.status })
    list.value = data.list || []
    total.value = data.total || 0
  } catch (error) {
    console.error(error)
  }
}

function handleSearch() {
  page.value = 1
  fetchList()
}

async function handleAudit(row, status) {
  try {
    await marketApi.audit(row.id, status)
    ElMessage.success('审核成功')
    fetchList()
  } catch (error) {
    console.error(error)
  }
}

async function handleFeatured(row) {
  try {
    await marketApi.featured(row.id, !row.is_featured)
    ElMessage.success(row.is_featured ? '已取消精选' : '已设为精选')
    fetchList()
  } catch (error) {
    console.error(error)
  }
}

async function handleDelete(row) {
  await ElMessageBox.confirm(`确定删除市场信息“${row.title}”吗？`, '删除确认', { type: 'warning' })
  try {
    await marketApi.delete(row.id)
    ElMessage.success('删除成功')
    fetchList()
  } catch (error) {
    console.error(error)
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
</style>
