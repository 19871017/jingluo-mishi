<template>
  <div class="login-container">
    <el-card class="login-card">
      <template #header>
        <div class="card-header">
          <span>{{ portalTitle }}</span>
          <small class="card-subtitle">{{ portalHint }}</small>
        </div>
      </template>

      <el-form ref="formRef" :model="form" :rules="rules">
        <el-form-item prop="username">
          <el-input v-model="form.username" placeholder="请输入账号" prefix-icon="User" />
        </el-form-item>
        <el-form-item prop="password">
          <el-input
            v-model="form.password"
            type="password"
            placeholder="请输入密码"
            prefix-icon="Lock"
            show-password
            @keyup.enter="handleLogin"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" style="width: 100%" :loading="loading" @click="handleLogin">
            {{ submitText }}
          </el-button>
        </el-form-item>
      </el-form>

      <div class="switch-links">
        <router-link to="/login">管理员登录</router-link>
        <router-link to="/brand-login">品牌方登录</router-link>
        <router-link to="/constructor-login">施工方登录</router-link>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from '../../store'
import { adminApi, brandPortalApi, constructorPortalApi } from '../../api'

const route = useRoute()
const router = useRouter()
const store = useStore()
const formRef = ref(null)
const loading = ref(false)

const portalType = computed(() => {
  if (route.path === '/brand-login') return 'brand'
  if (route.path === '/constructor-login') return 'constructor'
  return 'admin'
})

const portalTitle = computed(() => {
  if (portalType.value === 'brand') return '品牌方后台'
  if (portalType.value === 'constructor') return '施工方后台'
  return '平台管理后台'
})

const portalHint = computed(() => {
  if (portalType.value === 'brand') return '品牌方登录后可维护品牌剧本、图片、视频与授权资料。'
  if (portalType.value === 'constructor') return '施工方登录后可维护公司介绍、案例图片与施工视频。'
  return '管理员用于审核、运营和全局内容管理。'
})

const submitText = computed(() => {
  if (portalType.value === 'brand') return '进入品牌方后台'
  if (portalType.value === 'constructor') return '进入施工方后台'
  return '登录管理后台'
})

const form = reactive({
  username: '',
  password: '',
})

const rules = {
  username: [{ required: true, message: '请输入账号', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
}

async function handleLogin() {
  await formRef.value.validate()
  loading.value = true

  try {
    const data = portalType.value === 'brand'
      ? await brandPortalApi.login(form)
      : portalType.value === 'constructor'
        ? await constructorPortalApi.login(form)
        : await adminApi.login(form)

    if (data?.token) {
      store.login(data)
      router.push('/')
    }
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #ffe2b8 0%, #ff9b4a 100%);
}

.login-card {
  width: 440px;
}

.card-header {
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-align: center;
}

.card-header span {
  font-size: 22px;
  font-weight: 700;
}

.card-subtitle {
  color: #909399;
  font-size: 13px;
}

.switch-links {
  display: flex;
  justify-content: center;
  gap: 18px;
  font-size: 14px;
}
</style>
