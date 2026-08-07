import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'Home',
    component: () => import('@/views/HomeView.vue'),
    meta: { title: '森码云实人认证 - 首页' },
  },
  {
    path: '/verify',
    name: 'Verify',
    component: () => import('@/views/VerifyView.vue'),
    meta: { title: '实人认证', fullscreen: true },
  },
  {
    path: '/result',
    name: 'Result',
    component: () => import('@/views/ResultView.vue'),
    meta: { title: '认证结果' },
  },
  {
    path: '/pricing',
    name: 'Pricing',
    component: () => import('@/views/PricingView.vue'),
    meta: { title: '价格方案' },
  },
  {
    path: '/about',
    name: 'About',
    component: () => import('@/views/AboutView.vue'),
    meta: { title: '关于我们' },
  },
  {
    path: '/docs',
    name: 'Docs',
    component: () => import('@/views/DocsView.vue'),
    meta: { title: 'API文档' },
  },
  {
    path: '/install',
    name: 'Install',
    component: () => import('@/views/InstallView.vue'),
    meta: { title: '系统安装' },
  },
  {
    path: '/admin',
    name: 'Admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    redirect: '/admin/dashboard',
    meta: { title: '管理后台', requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/admin/DashboardView.vue'),
        meta: { title: '仪表盘' },
      },
      {
        path: 'verifications',
        name: 'Verifications',
        component: () => import('@/views/admin/VerificationsView.vue'),
        meta: { title: '认证记录' },
      },
      {
        path: 'channels',
        name: 'Channels',
        component: () => import('@/views/admin/ChannelsView.vue'),
        meta: { title: 'API通道管理' },
      },
      {
        path: 'mofang',
        name: 'MofangConfig',
        component: () => import('@/views/admin/MofangView.vue'),
        meta: { title: '魔方财务配置' },
      },
      {
        path: 'settings',
        name: 'Settings',
        component: () => import('@/views/admin/SettingsView.vue'),
        meta: { title: '系统设置' },
      },
      {
        path: 'logs',
        name: 'OperationLogs',
        component: () => import('@/views/admin/LogsView.vue'),
        meta: { title: '操作日志' },
      },
    ],
  },
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: () => import('@/views/admin/LoginView.vue'),
    meta: { title: '管理员登录' },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/NotFoundView.vue'),
    meta: { title: '页面未找到' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  },
})

router.beforeEach((to, from, next) => {
  document.title = (to.meta.title as string) || '森码云实人认证系统'
  next()
})

export default router
