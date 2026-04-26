import { createRouter, createWebHistory } from 'vue-router'
import { useStore } from '../store'

const routes = [
  {
    path: '/index.html',
    redirect: '/',
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/login/Login.vue'),
    meta: { title: '管理员登录' },
  },
  {
    path: '/brand-login',
    name: 'BrandLogin',
    component: () => import('../views/login/Login.vue'),
    meta: { title: '品牌方登录' },
  },
  {
    path: '/constructor-login',
    name: 'ConstructorLogin',
    component: () => import('../views/login/Login.vue'),
    meta: { title: '施工方登录' },
  },
  {
    path: '/',
    component: () => import('../views/layout/Layout.vue'),
    redirect: '/brand-profile',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/home/Dashboard.vue'),
        meta: { title: '首页概览' },
      },
      {
        path: 'category',
        name: 'Category',
        component: () => import('../views/category/Category.vue'),
        meta: { title: '剧本类目管理' },
      },
      {
        path: 'feature-tag',
        name: 'FeatureTag',
        component: () => import('../views/category/FeatureTag.vue'),
        meta: { title: '特色标签管理' },
      },
      {
        path: 'brand',
        name: 'Brand',
        component: () => import('../views/brand/Brand.vue'),
        meta: { title: '品牌管理' },
      },
      {
        path: 'brand-profile',
        name: 'BrandProfile',
        component: () => import('../views/brand/BrandProfile.vue'),
        meta: { title: '品牌方个人设置' },
      },
      {
        path: 'script',
        name: 'Script',
        component: () => import('../views/script/Script.vue'),
        meta: { title: '剧本管理' },
      },
      {
        path: 'script-purchase-intents',
        name: 'ScriptPurchaseIntents',
        component: () => import('../views/script/ScriptPurchaseIntents.vue'),
        meta: { title: '剧本购买意向' },
      },
      {
        path: 'home-content',
        name: 'HomeContent',
        component: () => import('../views/home/HomeContent.vue'),
        meta: { title: '首页内容管理' },
      },
      {
        path: 'popup-ad',
        name: 'PopupAd',
        component: () => import('../views/home/PopupAd.vue'),
        meta: { title: '弹出广告管理' },
      },
      {
        path: 'construction-permission',
        name: 'ConstructionPermission',
        component: () => import('../views/construction/ConstructionPermission.vue'),
        meta: { title: '入驻审核' },
      },
      {
        path: 'construction-case',
        name: 'ConstructionCase',
        component: () => import('../views/construction/ConstructionCase.vue'),
        meta: { title: '案例管理' },
      },
      {
        path: 'construction-profile',
        name: 'ConstructorProfile',
        component: () => import('../views/construction/ConstructorProfile.vue'),
        meta: { title: '施工方介绍' },
      },
      {
        path: 'construction-case-manage',
        name: 'ConstructorCaseManage',
        component: () => import('../views/construction/ConstructorCaseManage.vue'),
        meta: { title: '施工案例维护' },
      },
      {
        path: 'community',
        name: 'Community',
        component: () => import('../views/community/CommunityManage.vue'),
        meta: { title: '社区管理' },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const store = useStore()
  const isAuthPage = ['/login', '/brand-login', '/constructor-login'].includes(to.path)

  if (!isAuthPage && !store.token) {
    next('/login')
    return
  }

  if (store.token && isAuthPage) {
    next('/')
    return
  }

  if (store.isBrandAdmin && to.path === '/dashboard') {
    next('/brand-profile')
    return
  }

  next()
})

export default router
