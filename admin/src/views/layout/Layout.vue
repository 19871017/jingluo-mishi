<template>
  <el-container class="layout-container">
    <el-aside width="220px">
      <div class="logo">{{ portalName }}</div>
      <el-menu
        :default-active="$route.path"
        router
        background-color="#304156"
        text-color="#bfcbd9"
        active-text-color="#409EFF"
      >
        <el-menu-item v-if="!store.isBrandAdmin" index="/dashboard">
          <span>首页概览</span>
        </el-menu-item>

        <template v-if="store.isBrandAdmin">
          <el-menu-item index="/brand-profile">
            <span>个人设置</span>
          </el-menu-item>
          <el-menu-item index="/script">
            <span>我的剧本</span>
          </el-menu-item>
        </template>

        <template v-else-if="store.isConstructorAdmin">
          <el-menu-item index="/construction-profile">
            <span>施工方介绍</span>
          </el-menu-item>
          <el-menu-item index="/construction-case-manage">
            <span>施工案例维护</span>
          </el-menu-item>
        </template>

        <template v-else>
          <el-menu-item index="/brand">
            <span>品牌管理</span>
          </el-menu-item>
          <el-menu-item index="/construction-permission">
            <span>入驻审核</span>
          </el-menu-item>
          <el-menu-item index="/construction-case">
            <span>案例管理</span>
          </el-menu-item>
          <el-menu-item index="/category">
            <span>剧本类目管理</span>
          </el-menu-item>
          <el-menu-item index="/feature-tag">
            <span>特色标签管理</span>
          </el-menu-item>
          <el-menu-item index="/script">
            <span>剧本管理</span>
          </el-menu-item>
          <el-menu-item index="/script-purchase-intents">
            <span>剧本购买意向</span>
          </el-menu-item>
          <el-menu-item index="/popup-ad">
            <span>弹出广告管理</span>
          </el-menu-item>
          <el-menu-item index="/community">
            <span>社区管理</span>
          </el-menu-item>
        </template>
      </el-menu>
    </el-aside>

    <el-container>
      <el-header>
        <div class="header-right">
          <div class="user-meta">
            <span class="username">{{ store.adminInfo?.username }}</span>
            <span class="sub-name">{{ subName }}</span>
          </div>
          <el-button type="danger" size="small" @click="handleLogout">退出登录</el-button>
        </div>
      </el-header>

      <el-main>
        <el-alert
          v-if="pageGuide"
          class="page-guide"
          type="info"
          :closable="false"
          show-icon
          :title="pageGuide.title"
          :description="pageGuide.description"
        />
        <router-view />
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from '../../store'

const store = useStore()
const router = useRouter()
const route = useRoute()

const portalName = computed(() => {
  if (store.isBrandAdmin) return '品牌方后台'
  if (store.isConstructorAdmin) return '施工方后台'
  return '平台管理后台'
})

const subName = computed(() => {
  if (store.isBrandAdmin) return store.adminInfo?.brand_name || '未绑定品牌'
  if (store.isConstructorAdmin) return store.adminInfo?.company_name || '未填写施工方名称'
  return '平台管理员'
})

const guideMap = {
  '/dashboard': {
    title: '功能说明',
    description: '这里展示当前账号的核心数据概览。管理员看全站概况，品牌方看剧本数据，施工方看案例数据。',
  },
  '/brand-profile': {
    title: '功能说明',
    description: '这里用于品牌方维护自己的品牌名称、Logo、背景图和品牌介绍，前端品牌页会读取这些信息。',
  },
  '/category': {
    title: '功能说明',
    description: '这里用于维护剧本类目。新增或修改后，前端“剧本”页和筛选项会同步使用这些类目。',
  },
  '/brand': {
    title: '功能说明',
    description: '这里用于管理品牌资料、品牌后台账号和品牌状态。品牌页前端展示会读取这里的数据。',
  },
  '/feature-tag': {
    title: '功能说明',
    description: '这里用于维护剧本的特色标签。新增、删除或排序后，后台剧本管理和前端筛选会自动同步。',
  },
  '/script': {
    title: '功能说明',
    description: '这里用于新增、编辑、审核剧本。品牌方在这里维护自己的剧本，管理员在这里做全局审核。',
  },
  '/script-purchase-intents': {
    title: '功能说明',
    description: '这里用于查看剧本购买意向线索。仅平台管理员可见，品牌方不可查看买家联系方式。',
  },
  '/construction-permission': {
    title: '功能说明',
    description: '这里用于审核品牌方和施工方的入驻申请。施工方审核通过后会自动生成施工方后台账号。',
  },
  '/construction-case': {
    title: '功能说明',
    description: '这里用于管理施工案例、设置轮播置顶，并控制前端案例展示。',
  },
  '/construction-profile': {
    title: '功能说明',
    description: '这里用于施工方维护自己的介绍、联系电话和服务品牌。前端施工方详情页会展示这些信息。',
  },
  '/construction-case-manage': {
    title: '功能说明',
    description: '这里用于施工方上传、编辑施工案例图片和视频。提交后进入审核，审核通过后前端可见。',
  },
  '/community': {
    title: '功能说明',
    description: '这里用于管理BBS帖子和评论，支持查看详情、删除操作，维护社区内容质量。',
  },
  '/popup-ad': {
    title: '功能说明',
    description: '这里用于管理小程序弹出广告。可以上传广告图片并绑定剧本，前端小程序会显示这些广告。',
  },
}

const pageGuide = computed(() => guideMap[route.path] || null)

function handleLogout() {
  const nextLoginPath = store.isBrandAdmin
    ? '/brand-login'
    : (store.isConstructorAdmin ? '/constructor-login' : '/login')
  store.logout()
  router.push(nextLoginPath)
}
</script>

<style scoped>
.layout-container {
  height: 100%;
}

.el-aside {
  background-color: #304156;
}

.logo {
  height: 60px;
  line-height: 60px;
  text-align: center;
  color: #fff;
  font-size: 18px;
  font-weight: 700;
}

.el-header {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.username {
  color: #303133;
  font-weight: 600;
}

.sub-name {
  color: #909399;
  font-size: 12px;
}

.page-guide {
  margin-bottom: 16px;
}
</style>
