<template>
  <div class="dashboard">
    <template v-if="store.isConstructorAdmin">
      <el-row :gutter="20">
        <el-col :span="8">
          <el-card>
            <template #header>施工案例总数</template>
            <div class="stat-value">{{ stats.construction_cases || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card>
            <template #header>待审核案例</template>
            <div class="stat-value">{{ stats.pending_cases || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card>
            <template #header>已通过案例</template>
            <div class="stat-value">{{ stats.approved_cases || 0 }}</div>
          </el-card>
        </el-col>
      </el-row>

      <el-row :gutter="20" style="margin-top: 20px">
        <el-col :span="24">
          <el-card>
            <template #header>施工方信息</template>
            <div class="pending-list">
              <div class="pending-item">施工方名称：{{ stats.company_name || '未填写' }}</div>
              <div class="pending-item">关联品牌：{{ stats.brand_name || '未填写' }}</div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </template>

    <template v-else-if="store.isBrandAdmin">
      <el-row :gutter="20">
        <el-col :span="8">
          <el-card>
            <template #header>我的剧本总数</template>
            <div class="stat-value">{{ stats.scripts || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card>
            <template #header>待审核剧本</template>
            <div class="stat-value">{{ stats.pending_scripts || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card>
            <template #header>已通过剧本</template>
            <div class="stat-value">{{ stats.approved_scripts || 0 }}</div>
          </el-card>
        </el-col>
      </el-row>
    </template>

    <template v-else>
      <el-row :gutter="20">
        <el-col :span="4">
          <el-card>
            <template #header>剧本总数</template>
            <div class="stat-value">{{ stats.total_scripts || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card>
            <template #header>品牌总数</template>
            <div class="stat-value">{{ stats.total_brands || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card>
            <template #header>施工方总数</template>
            <div class="stat-value">{{ stats.total_constructors || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card>
            <template #header>用户总数</template>
            <div class="stat-value">{{ stats.total_users || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card>
            <template #header>社区帖子总数</template>
            <div class="stat-value">{{ stats.total_community_posts || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card>
            <template #header>社区评论总数</template>
            <div class="stat-value">{{ stats.total_community_comments || 0 }}</div>
          </el-card>
        </el-col>
      </el-row>

      <el-row :gutter="20" style="margin-top: 20px">
        <el-col :span="24">
          <el-card>
            <template #header>待审核提醒</template>
            <div class="pending-grid">
              <div class="pending-item action" @click="go('/script')">待审核剧本：{{ stats.pending_scripts || 0 }}</div>
              <div class="pending-item action" @click="go('/brand')">待审核品牌：{{ stats.pending_brands || 0 }}</div>
              <div class="pending-item action" @click="go('/construction-permission')">品牌方入驻待审核：{{ stats.pending_brand_permissions || 0 }}</div>
              <div class="pending-item action" @click="go('/construction-permission')">施工方入驻待审核：{{ stats.pending_constructor_permissions || 0 }}</div>
              <div class="pending-item action" @click="go('/construction-case')">待审核案例：{{ stats.pending_construction_cases || 0 }}</div>
              <div class="pending-item action" @click="go('/community')">待审核市场信息：{{ stats.pending_market_listings || 0 }}</div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </template>
  </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { adminApi } from '../../api'
import { useStore } from '../../store'

const store = useStore()
const router = useRouter()
const stats = reactive({})

async function fetchStats() {
  try {
    const data = await adminApi.getStats()
    Object.assign(stats, data)
  } catch (error) {
    console.error(error)
  }
}

function go(path) {
  router.push(path)
}

onMounted(fetchStats)
</script>

<style scoped>
.stat-value {
  font-size: 32px;
  font-weight: bold;
  text-align: center;
  color: #409eff;
}

.pending-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.pending-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.pending-item {
  font-size: 16px;
  color: #666;
}

.pending-item.action {
  padding: 14px 16px;
  border-radius: 10px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  color: #334155;
  cursor: pointer;
  transition: all .2s ease;
}

.pending-item.action:hover {
  border-color: #93c5fd;
  background: #eff6ff;
  color: #2563eb;
}
</style>
