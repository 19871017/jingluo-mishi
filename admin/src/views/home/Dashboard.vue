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

    <template v-else>
      <el-row :gutter="20">
        <el-col :span="6">
          <el-card>
            <template #header>剧本总数</template>
            <div class="stat-value">{{ stats.total_scripts || stats.scripts || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card>
            <template #header>品牌总数</template>
            <div class="stat-value">{{ stats.total_brands || stats.brands || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card>
            <template #header>用户总数</template>
            <div class="stat-value">{{ stats.total_users || stats.users || 0 }}</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card>
            <template #header>市场信息总数</template>
            <div class="stat-value">{{ stats.total_market_listings || stats.market_listings || 0 }}</div>
          </el-card>
        </el-col>
      </el-row>

      <el-row :gutter="20" style="margin-top: 20px">
        <el-col :span="12">
          <el-card>
            <template #header>待审核提醒</template>
            <div class="pending-list">
              <div class="pending-item">待审核剧本：{{ stats.pending_scripts || 0 }}</div>
              <div class="pending-item">待审核品牌：{{ stats.pending_brands || 0 }}</div>
              <div class="pending-item">待审核市场信息：{{ stats.pending_market_listings || 0 }}</div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </template>
  </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue'
import { adminApi } from '../../api'
import { useStore } from '../../store'

const store = useStore()
const stats = reactive({})

async function fetchStats() {
  try {
    const data = await adminApi.getStats()
    Object.assign(stats, data)
  } catch (error) {
    console.error(error)
  }
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

.pending-item {
  font-size: 16px;
  color: #666;
}
</style>
