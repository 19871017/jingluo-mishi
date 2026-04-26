<template>
  <div class="popup-ad-page">
    <el-card class="card-container">
      <template #header>
        <div class="card-header">
          <span>弹出广告管理</span>
          <el-button type="primary" @click="openAddDialog">
            <el-icon><Plus /></el-icon>
            添加广告
          </el-button>
        </div>
      </template>

      <el-table :data="popupAds" style="width: 100%">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column label="广告图片" width="120">
          <template #default="scope">
            <el-image
              :src="scope.row.image"
              fit="cover"
              style="width: 60px; height: 80px"
              :preview-src-list="[scope.row.image]"
            />
          </template>
        </el-table-column>
        <el-table-column label="关联剧本">
          <template #default="scope">
            <span>{{ scope.row.script_name || '未知剧本' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="sort_order" label="排序" width="100" />
        <el-table-column prop="is_active" label="状态" width="100">
          <template #default="scope">
            <el-switch
              v-model="scope.row.is_active"
              active-value="1"
              inactive-value="0"
              @change="updateAdStatus(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="180" />
        <el-table-column label="操作" width="150">
          <template #default="scope">
            <el-button type="primary" size="small" @click="openEditDialog(scope.row)">
              编辑
            </el-button>
            <el-button type="danger" size="small" @click="deleteAd(scope.row.id)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-if="total > 0"
        :current-page="page"
        :page-size="pageSize"
        layout="prev, pager, next"
        :total="total"
        @current-change="handleCurrentChange"
        style="margin-top: 20px"
      />
    </el-card>

    <!-- 新增/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '添加弹出广告' : '编辑弹出广告'"
      width="500px"
    >
      <el-form :model="form" :rules="rules" ref="formRef">
        <el-form-item label="广告图片" prop="image" class="image-upload-item">
          <el-upload
            class="image-uploader"
            :action="uploadUrl"
            :show-file-list="false"
            :on-success="handleImageUpload"
            :before-upload="beforeImageUpload"
            :auto-upload="true"
          >
            <div v-if="form.image" class="image-preview-container">
              <img :src="form.image" class="image-preview" />
              <div class="image-preview-overlay">
                <span class="image-preview-text">点击更换图片</span>
              </div>
            </div>
            <div v-else class="image-upload-placeholder">
              <el-icon class="upload-icon"><Plus /></el-icon>
              <div class="upload-text">
                <div class="upload-title">点击或拖拽上传</div>
                <div class="upload-subtitle">支持 JPG、PNG 格式，大小不超过 2MB</div>
                <div class="upload-tip">建议尺寸：600x800px</div>
              </div>
            </div>
          </el-upload>
        </el-form-item>

        <el-form-item label="关联剧本" prop="script_id">
          <el-select v-model="form.script_id" placeholder="选择剧本">
            <el-option
              v-for="script in scripts"
              :key="script.id"
              :label="script.name"
              :value="script.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="排序" prop="sort_order">
          <el-input-number v-model="form.sort_order" :min="0" />
        </el-form-item>

        <el-form-item label="状态">
          <el-switch v-model="form.is_active" active-value="1" inactive-value="0" />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="submitForm">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { ref, onMounted, reactive } from 'vue'
import { Plus } from '@element-plus/icons-vue'
import request from '../../api/request'

export default {
  name: 'PopupAd',
  setup() {
    const popupAds = ref([])
    const scripts = ref([])
    const total = ref(0)
    const page = ref(1)
    const pageSize = ref(20)
    const dialogVisible = ref(false)
    const dialogType = ref('add')
    const formRef = ref(null)
    const uploadUrl = '/api/upload'

    const form = reactive({
      image: '',
      script_id: '',
      is_active: 1,
      sort_order: 0
    })

    const rules = {
      image: [
        { required: true, message: '请上传广告图片', trigger: 'blur' }
      ],
      script_id: [
        { required: true, message: '请选择关联剧本', trigger: 'change' }
      ]
    }

    const loadPopupAds = async () => {
      try {
        const response = await request.get('/admin/popup-ads')
        popupAds.value = response.list
        total.value = response.list.length
      } catch (error) {
        console.error('加载广告失败:', error)
      }
    }

    const loadScripts = async () => {
      try {
        const response = await request.get('/admin/scripts')
        scripts.value = response.list || []
      } catch (error) {
        console.error('加载剧本失败:', error)
      }
    }

    const openAddDialog = () => {
      dialogType.value = 'add'
      Object.assign(form, {
        image: '',
        script_id: '',
        is_active: 1,
        sort_order: 0
      })
      dialogVisible.value = true
    }

    const openEditDialog = (row) => {
      dialogType.value = 'edit'
      Object.assign(form, row)
      dialogVisible.value = true
    }

    const submitForm = async () => {
      if (!formRef.value) return
      await formRef.value.validate(async (valid) => {
        if (valid) {
          try {
            if (dialogType.value === 'add') {
              await request.post('/admin/popup-ads', form)
            } else {
              await request.put(`/admin/popup-ads/${form.id}`, form)
            }
            dialogVisible.value = false
            loadPopupAds()
          } catch (error) {
            console.error('保存失败:', error)
          }
        }
      })
    }

    const deleteAd = async (id) => {
      try {
        await request.delete(`/admin/popup-ads/${id}`)
        loadPopupAds()
      } catch (error) {
        console.error('删除失败:', error)
      }
    }

    const updateAdStatus = async (row) => {
      try {
        await request.put(`/admin/popup-ads/${row.id}`, {
          ...row
        })
      } catch (error) {
        console.error('更新状态失败:', error)
        row.is_active = !row.is_active
      }
    }

    const handleImageUpload = (response) => {
      if (response.code === 200) {
        form.image = response.data.url
      }
    }

    const beforeImageUpload = (file) => {
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png'
      const isLt2M = file.size / 1024 / 1024 < 2

      if (!isJPG) {
        ElMessage.error('只能上传 JPG/PNG 图片!')
      }
      if (!isLt2M) {
        ElMessage.error('图片大小不能超过 2MB!')
      }
      return isJPG && isLt2M
    }

    const handleCurrentChange = (currentPage) => {
      page.value = currentPage
      loadPopupAds()
    }

    onMounted(() => {
      loadPopupAds()
      loadScripts()
    })

    return {
      popupAds,
      scripts,
      total,
      page,
      pageSize,
      dialogVisible,
      dialogType,
      formRef,
      form,
      rules,
      uploadUrl,
      Plus,
      openAddDialog,
      openEditDialog,
      submitForm,
      deleteAd,
      updateAdStatus,
      handleImageUpload,
      beforeImageUpload,
      handleCurrentChange
    }
  }
}
</script>

<style scoped>
.popup-ad-page {
  padding: 20px;
}

.card-container {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.image-upload-item {
  margin-bottom: 24px;
}

.image-uploader {
  display: block;
  width: 100%;
}

.image-upload-placeholder {
  border: 2px dashed #d9d9d9;
  border-radius: 12px;
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f8f9fa;
  margin: 0;
}

.image-upload-placeholder:hover {
  border-color: #409eff;
  background: #e6f7ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.upload-icon {
  font-size: 48px;
  color: #409eff;
  margin-bottom: 16px;
}

.upload-text {
  margin-top: 16px;
}

.upload-title {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 8px;
}

.upload-subtitle {
  font-size: 14px;
  color: #606266;
  margin-bottom: 8px;
}

.upload-tip {
  font-size: 12px;
  color: #909399;
  background: #f0f9eb;
  padding: 4px 12px;
  border-radius: 12px;
  display: inline-block;
  margin-top: 8px;
}

.image-preview-container {
  position: relative;
  width: 100%;
  max-width: 300px;
  margin: 0 auto;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.image-preview-container:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.image-preview {
  width: 100%;
  height: auto;
  max-height: 400px;
  object-fit: cover;
  display: block;
}

.image-preview-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
  cursor: pointer;
}

.image-preview-container:hover .image-preview-overlay {
  opacity: 1;
}

.image-preview-text {
  color: #fff;
  font-size: 16px;
  font-weight: 500;
  background: rgba(0, 0, 0, 0.7);
  padding: 12px 24px;
  border-radius: 24px;
  backdrop-filter: blur(4px);
}

.dialog-footer {
  width: 100%;
  text-align: right;
}
</style>