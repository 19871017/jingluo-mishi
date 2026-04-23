<template>
  <div class="constructor-profile">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>施工方介绍维护</span>
          <el-tag :type="profile.status === 'approved' ? 'success' : 'warning'">
            {{ profile.status === 'approved' ? '已授权' : '待审核' }}
          </el-tag>
        </div>
      </template>

      <el-form label-width="110px" class="profile-form">
        <el-form-item label="施工方名称">
          <el-input v-model="profile.company_name" placeholder="请输入施工方名称" />
        </el-form-item>
        <el-form-item label="服务品牌">
          <el-input v-model="profile.brand_name" placeholder="请输入服务品牌或代表品牌" />
        </el-form-item>
        <el-form-item label="联系人">
          <el-input v-model="profile.contact_name" placeholder="请输入联系人" />
        </el-form-item>
        <el-form-item label="联系电话">
          <el-input v-model="profile.contact_phone" placeholder="请输入联系电话" />
        </el-form-item>
        <el-form-item label="施工方介绍">
          <el-input
            v-model="profile.description"
            type="textarea"
            :rows="6"
            placeholder="请输入施工方介绍、擅长风格、施工经验等内容"
          />
        </el-form-item>
        <el-form-item label="审核备注" v-if="profile.review_note">
          <el-alert :title="profile.review_note" type="info" :closable="false" show-icon />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :loading="saving" @click="saveProfile">保存介绍</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { constructorContentApi } from '../../api'

const saving = ref(false)
const profile = reactive({
  company_name: '',
  brand_name: '',
  contact_name: '',
  contact_phone: '',
  description: '',
  status: 'pending',
  review_note: '',
})

async function loadProfile() {
  const data = await constructorContentApi.getProfile()
  Object.assign(profile, data || {})
}

async function saveProfile() {
  saving.value = true
  try {
    await constructorContentApi.updateProfile(profile)
    await loadProfile()
  } finally {
    saving.value = false
  }
}

onMounted(loadProfile)
</script>

<style scoped>
.constructor-profile {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.profile-form {
  max-width: 760px;
}
</style>
