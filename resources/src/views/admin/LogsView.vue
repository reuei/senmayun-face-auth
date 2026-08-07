<template>
  <div class="logs-page">
    <!-- 筛选栏 -->
    <div class="filter-card">
      <div class="filter-row">
        <div class="filter-item">
          <label class="filter-label">操作类型</label>
          <select v-model="filters.type" class="filter-select">
            <option value="">全部类型</option>
            <option value="login">登录</option>
            <option value="config">配置修改</option>
            <option value="channel">通道管理</option>
            <option value="delete">删除操作</option>
            <option value="other">其他</option>
          </select>
        </div>
        <div class="filter-item">
          <label class="filter-label">操作人</label>
          <input 
            type="text" 
            v-model="filters.username" 
            class="filter-input"
            placeholder="用户名"
          />
        </div>
        <div class="filter-item">
          <label class="filter-label">日期</label>
          <input type="date" v-model="filters.startDate" class="filter-input" />
          <span class="filter-sep">至</span>
          <input type="date" v-model="filters.endDate" class="filter-input" />
        </div>
      </div>
      <div class="filter-actions">
        <button class="btn btn-primary" @click="handleSearch">🔍 查询</button>
        <button class="btn btn-outline" @click="resetFilters">↺ 重置</button>
      </div>
    </div>

    <!-- 日志列表 -->
    <div class="table-card">
      <div class="card-header">
        <h3>操作日志</h3>
        <span class="record-count">共 {{ total }} 条记录</span>
      </div>
      <div class="table-body">
        <table class="data-table">
          <thead>
            <tr>
              <th>时间</th>
              <th>操作人</th>
              <th>操作类型</th>
              <th>操作描述</th>
              <th>IP地址</th>
              <th>状态</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id">
              <td class="text-muted">{{ log.created_at }}</td>
              <td>{{ log.username }}</td>
              <td>
                <span class="type-badge" :class="log.type">
                  {{ typeText(log.type) }}
                </span>
              </td>
              <td>{{ log.action }}</td>
              <td class="mono">{{ log.ip }}</td>
              <td>
                <span class="status-badge" :class="log.status">
                  {{ log.status === 'success' ? '成功' : '失败' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- 分页 -->
      <div class="pagination">
        <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
          上一页
        </button>
        <span class="page-info">第 {{ currentPage }} / {{ totalPages }} 页</span>
        <button class="page-btn" :disabled="currentPage >= totalPages" @click="currentPage++">
          下一页
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const filters = ref({
  type: '',
  username: '',
  startDate: '',
  endDate: ''
})

const currentPage = ref(1)
const total = ref(328)
const totalPages = computed(() => Math.ceil(total.value / 20))

const logs = ref([
  { id: 1, username: 'admin', type: 'login', action: '管理员登录', ip: '192.168.1.100', status: 'success', created_at: '2024-03-15 14:30:25' },
  { id: 2, username: 'admin', type: 'config', action: '修改系统设置-认证阈值', ip: '192.168.1.100', status: 'success', created_at: '2024-03-15 14:25:10' },
  { id: 3, username: 'admin', type: 'channel', action: '启用百度AI通道', ip: '192.168.1.100', status: 'success', created_at: '2024-03-15 14:20:33' },
  { id: 4, username: 'admin', type: 'channel', action: '测试腾讯云通道连接', ip: '192.168.1.100', status: 'success', created_at: '2024-03-15 14:15:45' },
  { id: 5, username: 'admin', type: 'config', action: '保存魔方财务配置', ip: '192.168.1.100', status: 'success', created_at: '2024-03-15 14:10:00' },
  { id: 6, username: 'admin', type: 'delete', action: '删除认证记录 SM202403140099', ip: '192.168.1.100', status: 'success', created_at: '2024-03-15 13:50:20' },
  { id: 7, username: 'admin', type: 'login', action: '管理员登录', ip: '192.168.1.100', status: 'success', created_at: '2024-03-15 09:00:00' },
  { id: 8, username: 'admin', type: 'login', action: '管理员登录', ip: '10.0.0.5', status: 'failed', created_at: '2024-03-14 22:30:15' },
  { id: 9, username: 'admin', type: 'config', action: '修改管理员密码', ip: '192.168.1.100', status: 'success', created_at: '2024-03-14 16:45:30' },
  { id: 10, username: 'admin', type: 'other', action: '导出认证记录', ip: '192.168.1.100', status: 'success', created_at: '2024-03-14 15:20:00' },
  { id: 11, username: 'admin', type: 'channel', action: '添加Face++通道', ip: '192.168.1.100', status: 'success', created_at: '2024-03-14 14:10:25' },
  { id: 12, username: 'admin', type: 'channel', action: '调整通道优先级', ip: '192.168.1.100', status: 'success', created_at: '2024-03-14 13:55:40' },
  { id: 13, username: 'admin', type: 'config', action: '保存安全设置', ip: '192.168.1.100', status: 'success', created_at: '2024-03-14 11:30:00' },
  { id: 14, username: 'admin', type: 'login', action: '管理员登录', ip: '192.168.1.100', status: 'success', created_at: '2024-03-14 09:05:12' },
  { id: 15, username: 'admin', type: 'other', action: '清理过期数据', ip: '192.168.1.100', status: 'success', created_at: '2024-03-13 23:00:00' },
  { id: 16, username: 'admin', type: 'config', action: '修改通知设置', ip: '192.168.1.100', status: 'success', created_at: '2024-03-13 16:20:45' },
  { id: 17, username: 'admin', type: 'channel', action: '禁用Azure通道', ip: '192.168.1.100', status: 'success', created_at: '2024-03-13 14:15:30' },
  { id: 18, username: 'admin', type: 'delete', action: '删除API密钥 key_xxxxx', ip: '192.168.1.100', status: 'success', created_at: '2024-03-13 11:40:20' },
  { id: 19, username: 'admin', type: 'other', action: '创建API密钥', ip: '192.168.1.100', status: 'success', created_at: '2024-03-13 11:35:00' },
  { id: 20, username: 'admin', type: 'login', action: '管理员登录', ip: '192.168.1.100', status: 'success', created_at: '2024-03-13 09:00:00' }
])

function typeText(type: string): string {
  const map: Record<string, string> = {
    login: '登录',
    config: '配置',
    channel: '通道',
    delete: '删除',
    other: '其他'
  }
  return map[type] || type
}

function handleSearch() {
  console.log('搜索:', filters.value)
}

function resetFilters() {
  filters.value = {
    type: '',
    username: '',
    startDate: '',
    endDate: ''
  }
}
</script>

<style scoped lang="scss">
.logs-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* 筛选栏 */
.filter-card {
  background: white;
  border-radius: 16px;
  padding: 20px 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.filter-row {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 16px;
}

.filter-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-label {
  font-size: 14px;
  color: #6b7280;
  white-space: nowrap;
}

.filter-select,
.filter-input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  min-width: 120px;
  
  &:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }
}

.filter-sep {
  color: #9ca3af;
  font-size: 14px;
}

.filter-actions {
  display: flex;
  gap: 12px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  
  &.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    
    &:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
  }
  
  &.btn-outline {
    background: white;
    border: 1px solid #d1d5db;
    color: #374151;
    
    &:hover {
      border-color: #667eea;
      color: #667eea;
    }
  }
}

/* 表格卡片 */
.table-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #f3f4f6;
  
  h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
  }
}

.record-count {
  font-size: 14px;
  color: #6b7280;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  
  th, td {
    padding: 14px 20px;
    text-align: left;
    font-size: 14px;
  }
  
  th {
    background: #f9fafb;
    font-weight: 600;
    color: #6b7280;
    font-size: 13px;
    border-bottom: 1px solid #e5e7eb;
  }
  
  td {
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
  }
  
  tr:last-child td {
    border-bottom: none;
  }
  
  tr:hover td {
    background: #f9fafb;
  }
  
  .mono {
    font-family: 'SF Mono', Monaco, monospace;
    font-size: 13px;
  }
  
  .text-muted {
    color: #9ca3af;
  }
}

.type-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  
  &.login {
    background: #dbeafe;
    color: #2563eb;
  }
  
  &.config {
    background: #fef3c7;
    color: #d97706;
  }
  
  &.channel {
    background: #e0e7ff;
    color: #4f46e5;
  }
  
  &.delete {
    background: #fee2e2;
    color: #dc2626;
  }
  
  &.other {
    background: #f3f4f6;
    color: #6b7280;
  }
}

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  
  &.success {
    background: #dcfce7;
    color: #16a34a;
  }
  
  &.failed {
    background: #fee2e2;
    color: #dc2626;
  }
}

/* 分页 */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  padding: 20px;
  border-top: 1px solid #f3f4f6;
}

.page-btn {
  padding: 8px 16px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 8px;
  font-size: 14px;
  color: #374151;
  cursor: pointer;
  
  &:hover:not(:disabled) {
    border-color: #667eea;
    color: #667eea;
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.page-info {
  font-size: 14px;
  color: #6b7280;
}
</style>
