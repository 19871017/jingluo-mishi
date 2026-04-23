<template>
  <div class="community-post">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">社区帖子管理</div>
            <div class="header-desc">审核用户发布的社区帖子，并查看图片内容与互动数据。</div>
          </div>
        </div>
      </template>

      <el-table :data="postList" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="帖子标题" min-width="220" />
        <el-table-column prop="view_count" label="浏览量" width="100" />
        <el-table-column prop="comment_count" label="评论数" width="100" />
        <el-table-column prop="like_count" label="点赞数" width="100" />
        <el-table-column prop="status" label="审核状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">{{ getStatusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" min-width="180" />
        <el-table-column label="操作" width="230" fixed="right">
          <template #default="{ row }">
            <el-button v-if="row.status === 'pending'" type="success" link @click="handleApprove(row)">通过</el-button>
            <el-button v-if="row.status === 'pending'" type="danger" link @click="handleReject(row)">驳回</el-button>
            <el-button type="info" link @click="handleView(row)">详情</el-button>
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

    <el-dialog v-model="dialogVisible" title="帖子详情" width="70%">
      <el-descriptions :column="1" border>
        <el-descriptions-item label="帖子标题">{{ currentPost?.title }}</el-descriptions-item>
        <el-descriptions-item label="帖子内容">{{ currentPost?.content }}</el-descriptions-item>
        <el-descriptions-item label="帖子图片">
          <div class="image-list">
            <el-image
              v-for="(image, index) in currentPostImages"
              :key="index"
              :src="image"
              fit="cover"
              class="post-image"
              @click="previewImage(image)"
            />
          </div>
        </el-descriptions-item>
        <el-descriptions-item label="浏览量">{{ currentPost?.view_count }}</el-descriptions-item>
        <el-descriptions-item label="评论数">{{ currentPost?.comment_count }}</el-descriptions-item>
        <el-descriptions-item label="点赞数">{{ currentPost?.like_count }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentPost?.created_at }}</el-descriptions-item>
        <el-descriptions-item label="审核状态">
          <el-tag :type="getStatusType(currentPost?.status)">{{ getStatusText(currentPost?.status) }}</el-tag>
        </el-descriptions-item>
      </el-descriptions>

      <template #footer>
        <el-button @click="dialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>

    <el-image-viewer
      v-if="previewVisible"
      :url-list="[previewImageUrl]"
      @close="previewVisible = false"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { communityAdminApi } from '../../api'

const postList = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const dialogVisible = ref(false)
const currentPost = ref(null)
const previewVisible = ref(false)
const previewImageUrl = ref('')

const currentPostImages = computed(() => {
  if (!currentPost.value?.images) return []
  try {
    return JSON.parse(currentPost.value.images)
  } catch (error) {
    return [currentPost.value.images]
  }
})

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

async function loadPosts() {
  try {
    const data = await communityAdminApi.getPosts({
      page: currentPage.value,
      limit: pageSize.value,
    })
    postList.value = data.list || []
    total.value = data.total || 0
  } catch (error) {
    console.error('加载社区帖子失败:', error)
  }
}

function handleSizeChange(size) {
  pageSize.value = size
  loadPosts()
}

function handleCurrentChange(current) {
  currentPage.value = current
  loadPosts()
}

async function handleApprove(post) {
  try {
    await communityAdminApi.approvePost(post.id)
    await loadPosts()
  } catch (error) {
    console.error('通过帖子失败:', error)
  }
}

async function handleReject(post) {
  try {
    await communityAdminApi.rejectPost(post.id)
    await loadPosts()
  } catch (error) {
    console.error('驳回帖子失败:', error)
  }
}

function handleView(post) {
  currentPost.value = post
  dialogVisible.value = true
}

async function handleDelete(post) {
  try {
    await communityAdminApi.deletePost(post.id)
    await loadPosts()
  } catch (error) {
    console.error('删除帖子失败:', error)
  }
}

function previewImage(imageUrl) {
  previewImageUrl.value = imageUrl
  previewVisible.value = true
}

onMounted(loadPosts)
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

.image-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.post-image {
  width: 140px;
  height: 100px;
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
}
</style>
