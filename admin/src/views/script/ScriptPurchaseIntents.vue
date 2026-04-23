<template>
  <div class="script-purchase-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <div>
            <div class="header-title">剧本购买意向</div>
            <div class="header-desc">查看客户对剧本的授权购买意向，便于管理员后续联系跟进。</div>
          </div>
        </div>
      </template>

      <el-table :data="list" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="script_name" label="剧本名称" min-width="180" />
        <el-table-column prop="brand_name" label="所属品牌" min-width="140" />
        <el-table-column prop="city" label="意向授权城市" min-width="140" />
        <el-table-column prop="contact_name" label="联系人姓名" min-width="120" />
        <el-table-column prop="contact_phone" label="联系电话" min-width="160" />
        <el-table-column prop="created_at" label="提交时间" min-width="180" />
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { scriptPurchaseIntentApi } from '../../api'

const list = ref([])

async function fetchList() {
  const data = await scriptPurchaseIntentApi.list()
  list.value = data.list || []
}

onMounted(fetchList)
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
</style>
