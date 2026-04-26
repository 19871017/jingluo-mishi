<template>
  <div class="community-manage">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">社区管理</div>
            <div class="header-desc">管理BBS帖子和评论，支持审核、查看详情和删除操作。</div>
          </div>
        </div>
      </template>

      <el-tabs v-model="activeTab">
        <el-tab-pane label="帖子管理" name="posts">
          <el-table :data="postList" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="帖子标题" min-width="220">
          <template #default="{ row }">
            <div class="post-title">
              <span>{{ row.title }}</span>
              <el-tag v-if="isNewPost(row.created_at)" size="small" type="primary" effect="dark" class="post-tag">新帖</el-tag>
              <el-tag v-if="isHotPost(row.comment_count, row.like_count)" size="small" type="danger" effect="dark" class="post-tag">热帖</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="user_nickname" label="发布用户" min-width="120" />
        <el-table-column prop="view_count" label="浏览量" width="100" />
        <el-table-column prop="comment_count" label="评论数" width="100" />
        <el-table-column prop="like_count" label="点赞数" width="100" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">{{ getStatusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" min-width="180" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="info" link @click="handleViewPost(row)">详情</el-button>
            <el-button type="danger" link @click="handleDeletePost(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

          <div class="pagination-container">
            <el-pagination
              v-model:current-page="postPage.current"
              v-model:page-size="postPage.size"
              :page-sizes="[10, 20, 50, 100]"
              layout="total, sizes, prev, pager, next, jumper"
              :total="postTotal"
              @size-change="handlePostSizeChange"
              @current-change="handlePostCurrentChange"
            />
          </div>
        </el-tab-pane>

        <el-tab-pane label="评论管理" name="comments">
          <el-table :data="commentList" border>
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="post_id" label="帖子ID" width="100" />
            <el-table-column prop="user_nickname" label="评论用户" min-width="120" />
            <el-table-column prop="content" label="评论内容" min-width="200" />
            <el-table-column prop="like_count" label="点赞数" width="100" />
            <el-table-column prop="parent_id" label="回复ID" width="100">
              <template #default="{ row }">
                <el-tag size="small" :type="row.parent_id > 0 ? 'info' : 'default'">
                  {{ row.parent_id || '无' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="created_at" label="创建时间" min-width="180" />
            <el-table-column label="操作" width="150" fixed="right">
              <template #default="{ row }">
                <el-button type="danger" link @click="handleDeleteComment(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>

          <div class="pagination-container">
            <el-pagination
              v-model:current-page="commentPage.current"
              v-model:page-size="commentPage.size"
              :page-sizes="[10, 20, 50, 100]"
              layout="total, sizes, prev, pager, next, jumper"
              :total="commentTotal"
              @size-change="handleCommentSizeChange"
              @current-change="handleCommentCurrentChange"
            />
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <el-dialog v-model="postDialogVisible" title="帖子详情" width="70%">
      <el-descriptions :column="1" border>
        <el-descriptions-item label="帖子标题">{{ currentPost?.title }}</el-descriptions-item>
        <el-descriptions-item label="发布用户">{{ currentPost?.user_nickname }}</el-descriptions-item>
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
        <el-descriptions-item label="视频">
          <div v-if="currentPost?.video" class="video-container">
            <video :src="currentPost.video" controls class="post-video"></video>
          </div>
          <div v-else class="no-content">无视频</div>
        </el-descriptions-item>
        <el-descriptions-item label="浏览量">{{ currentPost?.view_count }}</el-descriptions-item>
        <el-descriptions-item label="评论数">{{ currentPost?.comment_count }}</el-descriptions-item>
        <el-descriptions-item label="点赞数">{{ currentPost?.like_count }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentPost?.created_at }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getStatusType(currentPost?.status)">{{ getStatusText(currentPost?.status) }}</el-tag>
        </el-descriptions-item>
      </el-descriptions>

      <template #footer>
        <el-button @click="postDialogVisible = false">关闭</el-button>
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
import request from '../../api/request'

const activeTab = ref('posts')

// 帖子管理
const postList = ref([])
const postTotal = ref(0)
const postPage = ref({ current: 1, size: 10 })
const postDialogVisible = ref(false)
const currentPost = ref(null)

// 评论管理
const commentList = ref([])
const commentTotal = ref(0)
const commentPage = ref({ current: 1, size: 10 })

// 图片预览
const previewVisible = ref(false)
const previewImageUrl = ref('')

const currentPostImages = computed(() => {
  return currentPost.value?.images || []
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

function isNewPost(createdAt) {
  const now = new Date()
  const postTime = new Date(createdAt)
  const diffHours = (now - postTime) / (1000 * 60 * 60)
  return diffHours <= 24
}

function isHotPost(commentCount, likeCount) {
  const commentCountNum = parseInt(commentCount) || 0
  const likeCountNum = parseInt(likeCount) || 0
  return commentCountNum >= 5 || likeCountNum >= 10
}

async function loadPosts() {
  try {
    const response = await request({
      url: '/bbs/posts',
      params: {
        page: postPage.value.current,
        limit: postPage.value.size
      }
    })
    postList.value = response.list || []
    postTotal.value = response.total || 0
  } catch (error) {
    console.error('加载帖子失败:', error)
  }
}

async function loadComments() {
  try {
    const response = await request({
      url: '/bbs/comments',
      params: {
        page: commentPage.value.current,
        limit: commentPage.value.size
      }
    })
    commentList.value = response.list || []
    commentTotal.value = response.total || 0
  } catch (error) {
    console.error('加载评论失败:', error)
  }
}

function handlePostSizeChange(size) {
  postPage.value.size = size
  loadPosts()
}

function handlePostCurrentChange(current) {
  postPage.value.current = current
  loadPosts()
}

function handleCommentSizeChange(size) {
  commentPage.value.size = size
  loadComments()
}

function handleCommentCurrentChange(current) {
  commentPage.value.current = current
  loadComments()
}

function handleViewPost(post) {
  currentPost.value = post
  postDialogVisible.value = true
}

async function handleDeletePost(post) {
  try {
    await request({
      url: `/bbs/posts/${post.id}`,
      method: 'DELETE'
    })
    await loadPosts()
  } catch (error) {
    console.error('删除帖子失败:', error)
  }
}

async function handleDeleteComment(comment) {
  try {
    await request({
      url: `/bbs/comments/${comment.id}`,
      method: 'DELETE'
    })
    await loadComments()
  } catch (error) {
    console.error('删除评论失败:', error)
  }
}

function previewImage(imageUrl) {
  previewImageUrl.value = imageUrl
  previewVisible.value = true
}

onMounted(() => {
  loadPosts()
  loadComments()
})
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

.video-container {
  margin-top: 10px;
}

.post-video {
  max-width: 100%;
  max-height: 300px;
  border-radius: 10px;
}

.no-content {
  color: #909399;
  padding: 20px;
  text-align: center;
  background: #f5f7fa;
  border-radius: 8px;
}

.post-title {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.post-tag {
  margin-left: 8px;
  font-size: 10px;
  height: 20px;
  line-height: 20px;
  padding: 0 8px;
}
</style>