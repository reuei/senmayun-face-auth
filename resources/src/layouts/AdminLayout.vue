<template>
  <div class="admin-layout">
    <!-- 侧边栏 -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="brand">
          <span class="brand-icon">🛡️</span>
          <span class="brand-text" v-show="!sidebarCollapsed">森码云</span>
        </div>
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
          {{ sidebarCollapsed ? '→' : '←' }}
        </button>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section">
          <span class="section-title" v-show="!sidebarCollapsed">主菜单</span>
          <router-link to="/admin" class="nav-item" exact>
            <span class="nav-icon">📊</span>
            <span class="nav-text" v-show="!sidebarCollapsed">仪表盘</span>
          </router-link>
          <router-link to="/admin/verifications" class="nav-item">
            <span class="nav-icon">📋</span>
            <span class="nav-text" v-show="!sidebarCollapsed">认证记录</span>
          </router-link>
        </div>

        <div class="nav-section">
          <span class="section-title" v-show="!sidebarCollapsed">系统管理</span>
          <router-link to="/admin/channels" class="nav-item">
            <span class="nav-icon">🔌</span>
            <span class="nav-text" v-show="!sidebarCollapsed">API通道</span>
          </router-link>
          <router-link to="/admin/mofang" class="nav-item">
            <span class="nav-icon">💰</span>
            <span class="nav-text" v-show="!sidebarCollapsed">魔方财务</span>
          </router-link>
          <router-link to="/admin/settings" class="nav-item">
            <span class="nav-icon">⚙️</span>
            <span class="nav-text" v-show="!sidebarCollapsed">系统设置</span>
          </router-link>
        </div>

        <div class="nav-section">
          <span class="section-title" v-show="!sidebarCollapsed">其他</span>
          <router-link to="/admin/logs" class="nav-item">
            <span class="nav-icon">📝</span>
            <span class="nav-text" v-show="!sidebarCollapsed">操作日志</span>
          </router-link>
        </div>
      </nav>

      <div class="sidebar-footer">
        <a href="/" class="nav-item">
          <span class="nav-icon">🏠</span>
          <span class="nav-text" v-show="!sidebarCollapsed">返回首页</span>
        </a>
      </div>
    </aside>

    <!-- 主内容区 -->
    <div class="main-wrapper">
      <!-- 顶部栏 -->
      <header class="topbar">
        <div class="topbar-left">
          <h1 class="page-title">{{ pageTitle }}</h1>
        </div>
        <div class="topbar-right">
          <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="搜索..." />
          </div>
          <button class="icon-btn" title="通知">
            🔔
            <span class="badge">3</span>
          </button>
          <div class="user-menu" @click="showUserMenu = !showUserMenu">
            <div class="user-avatar">A</div>
            <span class="user-name" v-show="!sidebarCollapsed">admin</span>
            <span class="arrow">▼</span>
          </div>
          <div class="user-dropdown" v-show="showUserMenu">
            <a href="#" class="dropdown-item">
              <span>👤</span> 个人资料
            </a>
            <a href="/admin/settings" class="dropdown-item">
              <span>⚙️</span> 系统设置
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item danger" @click.prevent="handleLogout">
              <span>🚪</span> 退出登录
            </a>
          </div>
        </div>
      </header>

      <!-- 内容区 -->
      <main class="main-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const sidebarCollapsed = ref(false)
const showUserMenu = ref(false)

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    '/admin': '仪表盘',
    '/admin/verifications': '认证记录',
    '/admin/channels': 'API通道管理',
    '/admin/mofang': '魔方财务配置',
    '/admin/settings': '系统设置',
    '/admin/logs': '操作日志'
  }
  return titles[route.path] || '管理后台'
})

function handleLogout() {
  if (confirm('确定要退出登录吗？')) {
    router.push('/admin/login')
  }
}
</script>

<style scoped lang="scss">
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f3f4f6;
}

/* 侧边栏 */
.sidebar {
  width: 240px;
  background: #1f2937;
  color: white;
  display: flex;
  flex-direction: column;
  transition: width 0.3s;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 100;
  
  &.collapsed {
    width: 64px;
  }
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  border-bottom: 1px solid #374151;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  
  .brand-icon {
    font-size: 24px;
  }
  
  .brand-text {
    font-size: 18px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
}

.collapse-btn {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 14px;
  padding: 4px 8px;
  border-radius: 4px;
  
  &:hover {
    background: #374151;
    color: white;
  }
}

.sidebar-nav {
  flex: 1;
  padding: 12px 8px;
  overflow-y: auto;
}

.nav-section {
  margin-bottom: 24px;
}

.section-title {
  display: block;
  padding: 8px 12px;
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 8px;
  color: #d1d5db;
  text-decoration: none;
  font-size: 14px;
  transition: all 0.2s;
  margin-bottom: 2px;
  
  &:hover {
    background: #374151;
    color: white;
  }
  
  &.router-link-active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  
  .nav-icon {
    font-size: 18px;
    width: 24px;
    text-align: center;
  }
}

.sidebar-footer {
  padding: 12px 8px;
  border-top: 1px solid #374151;
}

/* 主内容区 */
.main-wrapper {
  flex: 1;
  margin-left: 240px;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.3s;
}

.sidebar.collapsed + .main-wrapper {
  margin-left: 64px;
}

/* 顶部栏 */
.topbar {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 0 24px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 50;
}

.page-title {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-box {
  position: relative;
  
  .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    opacity: 0.5;
  }
  
  input {
    padding: 8px 12px 8px 36px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    width: 200px;
    transition: all 0.2s;
    
    &:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
  }
}

.icon-btn {
  position: relative;
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  
  &:hover {
    background: #f3f4f6;
  }
  
  .badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 5px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
  }
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 8px;
  cursor: pointer;
  position: relative;
  
  &:hover {
    background: #f3f4f6;
  }
}

.user-avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
}

.user-name {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.arrow {
  font-size: 10px;
  color: #9ca3af;
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  min-width: 180px;
  z-index: 100;
  overflow: hidden;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  color: #374151;
  text-decoration: none;
  font-size: 14px;
  transition: all 0.2s;
  
  &:hover {
    background: #f9fafb;
  }
  
  &.danger {
    color: #ef4444;
    
    &:hover {
      background: #fef2f2;
    }
  }
}

.dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 4px 0;
}

/* 主内容 */
.main-content {
  flex: 1;
  padding: 24px;
}

@media (max-width: 768px) {
  .sidebar {
    width: 64px;
    
    .brand-text,
    .nav-text,
    .section-title {
      display: none;
    }
  }
  
  .main-wrapper {
    margin-left: 64px;
  }
  
  .search-box {
    display: none;
  }
  
  .user-name {
    display: none;
  }
}
</style>
