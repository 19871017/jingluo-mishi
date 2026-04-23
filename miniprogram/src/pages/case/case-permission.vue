<template>
  <scroll-view scroll-y class="permission-page">
    <view class="permission-shell">
      <view class="hero card">
        <text class="hero-title">施工案例权限申请</text>
        <text class="hero-desc">品牌方和施工方可提交申请，经后台审核通过后，获得上传和管理施工案例的权限。</text>
      </view>

      <view v-if="record" class="status-card card">
        <text class="status-title">当前申请状态：{{ statusText(record.status) }}</text>
        <text class="status-desc">当前角色：{{ roleText(currentRole) }}</text>
        <text v-if="record.brand_name" class="status-desc">品牌归属：{{ record.brand_name }}</text>
        <text class="status-desc">申请身份：{{ record.role_type === 'brand' ? '品牌方' : '施工方' }}</text>
        <text v-if="record.review_note" class="status-note">审核备注：{{ record.review_note }}</text>
      </view>

      <view class="form-card card">
        <picker mode="selector" :range="roleOptions" @change="onRoleChange">
          <view class="field picker-field">申请身份：{{ roleLabel }}</view>
        </picker>
        <input v-model="form.brand_name" class="field" placeholder="所属品牌名称（品牌方必填，施工方可填写服务品牌）" />
        <input v-model="form.company_name" class="field" placeholder="公司/团队名称" />
        <input v-model="form.contact_name" class="field" placeholder="联系人姓名" />
        <input v-model="form.contact_phone" class="field" placeholder="联系电话" type="number" />
        <textarea v-model="form.reason" class="field area" placeholder="请输入申请说明，描述你的角色与业务场景" maxlength="1000" />
        <button class="primary-btn" @click="submit">提交申请</button>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { api } from '../../services/api'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const roleOptions = ['品牌方', '施工方']
const roleLabel = ref('品牌方')
const record = ref(null)
const currentRole = ref('member')
const form = reactive({
  role_type: 'brand',
  brand_name: '',
  company_name: '',
  contact_name: '',
  contact_phone: '',
  reason: '',
})

function onRoleChange(event) {
  const index = Number(event.detail.value)
  roleLabel.value = roleOptions[index]
  form.role_type = index === 0 ? 'brand' : 'constructor'
}

async function loadRecord() {
  if (!auth.isLoggedIn) return
  const data = await api.getConstructionCasePermission()
  record.value = data.record || null
  currentRole.value = data.current_role || auth.profile?.role || 'member'
}

function statusText(status) {
  if (status === 'approved') return '已通过'
  if (status === 'rejected') return '已驳回'
  return '待审核'
}

function roleText(role) {
  if (role === 'brand') return '品牌方'
  if (role === 'constructor') return '施工方'
  if (role === 'admin') return '管理员'
  return '普通用户'
}

async function submit() {
  if (!auth.isLoggedIn) {
    uni.showToast({ title: '请先登录后提交申请', icon: 'none' })
    return
  }
  if (!form.brand_name || !form.company_name || !form.contact_name || !form.contact_phone || !form.reason) {
    uni.showToast({ title: '请先填写完整申请信息', icon: 'none' })
    return
  }
  await api.createConstructionCasePermission({ ...form })
  uni.showToast({ title: '申请已提交', icon: 'success' })
  loadRecord()
}

onMounted(loadRecord)
</script>

<style scoped>
.permission-page { height:100vh; background: radial-gradient(circle at top right, rgba(255,181,72,.12), transparent 20%), linear-gradient(180deg,#fff7ef 0%,#f6f7fb 24%,#f6f7fb 100%); }
.permission-shell { padding:24rpx; }
.hero,.status-card,.form-card { padding:24rpx; }
.status-card,.form-card { margin-top:20rpx; }
.hero-title,.status-title { display:block; font-size:34rpx; font-weight:800; color:#1f2937; }
.hero-desc,.status-desc,.status-note { display:block; margin-top:10rpx; font-size:22rpx; line-height:1.7; color:#9ca3af; }
.status-note { color:#f97316; }
.form-card { display:flex; flex-direction:column; gap:18rpx; }
.field { width:100%; min-height:84rpx; padding:20rpx; border-radius:16rpx; background:#f9fafb; font-size:24rpx; }
.picker-field { display:flex; align-items:center; }
.area { height:220rpx; }
</style>
