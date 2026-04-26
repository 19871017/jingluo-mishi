<template>
  <div class="construction-case">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">施工案例审核</div>
            <div class="header-desc">审核施工方提交的案例内容，确认图片、视频和项目说明后再公开展示。</div>
          </div>
        </div>
      </template>

      <el-table :data="caseList" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="案例标题" min-width="220" />
        <el-table-column label="轮播置顶" width="120">
          <template #default="{ row }">
            <el-tag v-if="Number(row.is_featured) > 0" type="success" effect="plain">已置顶</el-tag>
            <el-tag v-else effect="plain">未置顶</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="featured_sort" label="轮播排序" width="100" />
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
            <el-button type="warning" link @click="handleFeatured(row)">
              {{ Number(row.is_featured) > 0 ? '取消置顶' : '设为轮播' }}
            </el-button>
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

    <el-dialog v-model="dialogVisible" title="案例详情" width="70%">
      <el-descriptions :column="1" border>
        <el-descriptions-item label="案例标题">{{ currentCase?.title }}</el-descriptions-item>
        <el-descriptions-item label="案例说明">{{ currentCase?.description }}</el-descriptions-item>
        <el-descriptions-item label="案例图片">
          <div class="media-list">
            <el-image
              v-for="(image, index) in currentCaseImages"
              :key="index"
              :src="image"
              fit="cover"
              class="case-image"
              @click="previewImage(image)"
            />
          </div>
        </el-descriptions-item>
        <el-descriptions-item label="案例视频">
          <div class="media-list">
            <video
              v-for="(video, index) in currentCaseVideos"
              :key="`${video}-${index}`"
              :src="video"
              class="case-video"
              controls
            />
          </div>
          <span v-if="!currentCaseVideos.length" class="muted-text">暂无视频</span>
        </el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentCase?.created_at }}</el-descriptions-item>
        <el-descriptions-item label="审核状态">
          <el-tag :type="getStatusType(currentCase?.status)">{{ getStatusText(currentCase?.status) }}</el-tag>
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
import { constructionApi } from '../../api'

const caseList = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const dialogVisible = ref(false)
const currentCase = ref(null)
const previewVisible = ref(false)
const previewImageUrl = ref('')

const currentCaseImages = computed(() => normalizeMedia(currentCase.value?.images))
const currentCaseVideos = computed(() => normalizeMedia(currentCase.value?.videos))

function normalizeMedia(value) {
  if (!value) return []
  if (Array.isArray(value)) return value.filter(Boolean)
  try {
    const parsed = JSON.parse(value)
    return Array.isArray(parsed) ? parsed.filter(Boolean) : [parsed].filter(Boolean)
  } catch (error) {
    return String(value)
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean)
  }
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
  try {
    const data = await constructionApi.getCases({
      page: currentPage.value,
      limit: pageSize.value,
    })
    caseList.value = data.list || []
    total.value = data.total || 0
  } catch (error) {
    console.error('加载施工案例失败:', error)
  }
}

function handleSizeChange(size) {
  pageSize.value = size
  loadCases()
}

function handleCurrentChange(current) {
  currentPage.value = current
  loadCases()
}

async function handleApprove(caseItem) {
  try {
    await constructionApi.approveCase(caseItem.id)
    await loadCases()
  } catch (error) {
    console.error('通过案例失败:', error)
  }
}

async function handleReject(caseItem) {
  try {
    await constructionApi.rejectCase(caseItem.id)
    await loadCases()
  } catch (error) {
    console.error('驳回案例失败:', error)
  }
}

async function handleFeatured(caseItem) {
  try {
    const nextFeatured = Number(caseItem.is_featured || 0) > 0 ? 0 : 1
    await constructionApi.featuredCase(caseItem.id, {
      featured: nextFeatured,
      featured_sort: Number(caseItem.featured_sort || 0),
    })
    await loadCases()
  } catch (error) {
    console.error('设置案例轮播失败:', error)
  }
}

function handleView(caseItem) {
  currentCase.value = caseItem
  dialogVisible.value = true
}

async function handleDelete(caseItem) {
  try {
    await constructionApi.deleteCase(caseItem.id)
    await loadCases()
  } catch (error) {
    console.error('删除案例失败:', error)
  }
}

function previewImage(imageUrl) {
  previewImageUrl.value = imageUrl
  previewVisible.value = true
}

onMounted(loadCases)
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

.media-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.case-image,
.case-video {
  width: 160px;
  height: 110px;
  border-radius: 10px;
  overflow: hidden;
}

.case-image {
  cursor: pointer;
}

.muted-text {
  color: #909399;
}
</style>
