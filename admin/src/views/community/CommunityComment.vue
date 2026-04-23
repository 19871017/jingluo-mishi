<template>
  <div class="community-comment">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">社区评论管理</div>
            <div class="header-desc">审核评论内容，及时处理违规评论和刷屏内容。</div>
          </div>
        </div>
      </template>

      <el-table :data="commentList" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="post_id" label="所属帖子 ID" width="120" />
        <el-table-column prop="content" label="评论内容" min-width="320">
          <template #default="{ row }">
            <span class="comment-content">{{ row.content }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="审核状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">{{ getStatusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" min-width="180" />
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button v-if="row.status === 'pending'" type="success" link @click="handleApprove(row)">通过</el-button>
            <el-button v-if="row.status === 'pending'" type="danger" link @click="handleReject(row)">驳回</el-button>
            <el-button type="danger" link @click="handleDelete(row)">删除</el-button>
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
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { communityAdminApi } from '../../api'

const commentList = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)

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

async function loadComments() {
  try {
    const data = await communityAdminApi.getComments({
      page: currentPage.value,
      limit: pageSize.value,
    })
    commentList.value = data.list || []
    total.value = data.total || 0
  } catch (error) {
    console.error('加载社区评论失败:', error)
  }
}

function handleSizeChange(size) {
  pageSize.value = size
  loadComments()
}

function handleCurrentChange(current) {
  currentPage.value = current
  loadComments()
}

async function handleApprove(comment) {
  try {
    await communityAdminApi.approveComment(comment.id)
    await loadComments()
  } catch (error) {
    console.error('通过评论失败:', error)
  }
}

async function handleReject(comment) {
  try {
    await communityAdminApi.rejectComment(comment.id)
    await loadComments()
  } catch (error) {
    console.error('驳回评论失败:', error)
  }
}

async function handleDelete(comment) {
  try {
    await communityAdminApi.deleteComment(comment.id)
    await loadComments()
  } catch (error) {
    console.error('删除评论失败:', error)
  }
}

onMounted(loadComments)
</script>

<style scoped>
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
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

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.comment-content {
  max-width: 420px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
