<template>
  <div class="category-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">分类管理</div>
            <div class="header-desc">维护小程序分类页与剧本发布时可选的 12 个中文分类名称和排序，需与类型体系保持一致。</div>
          </div>
          <el-button type="primary" @click="handleAdd">新增分类</el-button>
        </div>
      </template>

      <el-table :data="list" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="分类名称" min-width="220" />
        <el-table-column prop="sort_order" label="排序值" width="120" />
        <el-table-column prop="created_at" label="创建时间" min-width="180" />
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button type="danger" link @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="isEdit ? '编辑分类' : '新增分类'" width="420px">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="88px">
        <el-form-item label="分类名称" prop="name">
          <el-input v-model="form.name" placeholder="例如：儿童密室、沉浸演绎、角色扮演" />
        </el-form-item>
        <el-form-item label="排序值" prop="sort_order">
          <el-input-number v-model="form.sort_order" :min="0" style="width: 100%" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { categoryApi } from '../../api'

const list = ref([])
const dialogVisible = ref(false)
const isEdit = ref(false)
const formRef = ref(null)
const editId = ref(null)

const form = reactive({
  name: '',
  sort_order: 0,
})

const rules = {
  name: [{ required: true, message: '请输入分类名称', trigger: 'blur' }],
}

async function fetchList() {
  try {
    list.value = await categoryApi.list()
  } catch (error) {
    console.error(error)
  }
}

function handleAdd() {
  isEdit.value = false
  editId.value = null
  form.name = ''
  form.sort_order = 0
  dialogVisible.value = true
}

function handleEdit(row) {
  isEdit.value = true
  editId.value = row.id
  form.name = row.name
  form.sort_order = Number(row.sort_order || 0)
  dialogVisible.value = true
}

async function handleDelete(row) {
  await ElMessageBox.confirm(`确定删除分类“${row.name}”吗？`, '删除确认', { type: 'warning' })
  try {
    await categoryApi.delete(row.id)
    ElMessage.success('分类已删除')
    fetchList()
  } catch (error) {
    console.error(error)
  }
}

async function handleSubmit() {
  await formRef.value.validate()
  try {
    if (isEdit.value) {
      await categoryApi.update(editId.value, form)
      ElMessage.success('分类已更新')
    } else {
      await categoryApi.create(form)
      ElMessage.success('分类已创建')
    }
    dialogVisible.value = false
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
  align-items: center;
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
