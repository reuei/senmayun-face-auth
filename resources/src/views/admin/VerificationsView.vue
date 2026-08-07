<template>
  <div class="verifications-page">
    <!-- 筛选栏 -->
    <div class="filter-card">
      <div class="filter-row">
        <div class="filter-item">
          <label class="filter-label">状态</label>
          <select v-model="filters.status" class="filter-select">
            <option value="">全部状态</option>
            <option value="passed">通过</option>
            <option value="failed">失败</option>
            <option value="pending">处理中</option>
            <option value="expired">已过期</option>
          </select>
        </div>
        <div class="filter-item">
          <label class="filter-label">通道</label>
          <select v-model="filters.channel" class="filter-select">
            <option value="">全部通道</option>
            <option value="tencent">腾讯云慧眼</option>
            <option value="baidu">百度AI</option>
            <option value="azure">Azure Face</option>
            <option value="facepp">Face++</option>
            <option value="local">自研算法</option>
          </select>
        </div>
        <div class="filter-item">
          <label class="filter-label">日期范围</label>
          <input type="date" v-model="filters.startDate" class="filter-input" />
          <span class="filter-sep">至</span>
          <input type="date" v-model="filters.endDate" class="filter-input" />
        </div>
        <div class="filter-item">
          <label class="filter-label">搜索</label>
          <input 
            type="text" 
            v-model="filters.keyword" 
            class="filter-input"
            placeholder="认证编号/用户ID"
          />
        </div>
      </div>
      <div class="filter-actions">
        <button class="btn btn-primary" @click="handleSearch">
          🔍 查询
        </button>
        <button class="btn btn-outline" @click="resetFilters">
          ↺ 重置
        </button>
        <button class="btn btn-outline" @click="exportData">
          📥 导出
        </button>
      </div>
    </div>

    <!-- 统计概览 -->
    <div class="overview-cards">
      <div class="overview-card">
        <div class="overview-value">{{ stats.total }}</div>
        <div class="overview-label">总认证数</div>
      </div>
      <div class="overview-card success">
        <div class="overview-value">{{ stats.passed }}</div>
        <div class="overview-label">通过</div>
      </div>
      <div class="overview-card danger">
        <div class="overview-value">{{ stats.failed }}</div>
        <div class="overview-label">失败</div>
      </div>
      <div class="overview-card warning">
        <div class="overview-value">{{ stats.passRate }}%</div>
        <div class="overview-label">通过率</div>
      </div>
    </div>

    <!-- 数据表格 -->
    <div class="table-card">
      <div class="card-header">
        <h3>认证记录列表</h3>
        <span class="record-count">共 {{ total }} 条记录</span>
      </div>
      <div class="table-body">
        <table class="data-table">
          <thead>
            <tr>
              <th>认证编号</th>
              <th>用户ID</th>
              <th>状态</th>
              <th>认证通道</th>
              <th>相似度</th>
              <th>活体检测</th>
              <th>耗时</th>
              <th>认证时间</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="record in records" :key="record.id">
              <td class="mono">{{ record.token }}</td>
              <td>{{ record.user_id }}</td>
              <td>
                <span class="status-badge" :class="record.status">
                  {{ statusText(record.status) }}
                </span>
              </td>
              <td>{{ record.channel }}</td>
              <td :class="scoreClass(record.score)">{{ record.score }}%</td>
              <td>
                <span v-if="record.liveness_passed" class="text-success">通过</span>
                <span v-else-if="record.liveness_passed === false" class="text-danger">未通过</span>
                <span v-else class="text-muted">-</span>
              </td>
              <td>{{ record.duration }}ms</td>
              <td class="text-muted">{{ record.created_at }}</td>
              <td>
                <button class="action-btn" @click="viewDetail(record)">详情</button>
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

    <!-- 详情抽屉 -->
    <div class="drawer-mask" v-show="showDetail" @click="showDetail = false"></div>
    <div class="detail-drawer" :class="{ open: showDetail }">
      <div class="drawer-header">
        <h3>认证详情</h3>
        <button class="close-btn" @click="showDetail = false">✕</button>
      </div>
      <div class="drawer-body" v-if="currentRecord">
        <div class="detail-section">
          <h4>基本信息</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">认证编号</span>
              <span class="detail-value mono">{{ currentRecord.token }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">用户ID</span>
              <span class="detail-value">{{ currentRecord.user_id }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">认证状态</span>
              <span class="detail-value">
                <span class="status-badge" :class="currentRecord.status">
                  {{ statusText(currentRecord.status) }}
                </span>
              </span>
            </div>
            <div class="detail-item">
              <span class="detail-label">认证通道</span>
              <span class="detail-value">{{ currentRecord.channel }}</span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h4>认证结果</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">相似度</span>
              <span class="detail-value" :class="scoreClass(currentRecord.score)">
                {{ currentRecord.score }}%
              </span>
            </div>
            <div class="detail-item">
              <span class="detail-label">活体检测</span>
              <span class="detail-value">
                {{ currentRecord.liveness_passed ? '通过' : '未通过' }}
              </span>
            </div>
            <div class="detail-item">
              <span class="detail-label">认证耗时</span>
              <span class="detail-value">{{ currentRecord.duration }}ms</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">客户端IP</span>
              <span class="detail-value mono">{{ currentRecord.client_ip }}</span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h4>时间信息</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">创建时间</span>
              <span class="detail-value">{{ currentRecord.created_at }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">完成时间</span>
              <span class="detail-value">{{ currentRecord.completed_at }}</span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h4>人脸图片</h4>
          <div class="face-images">
            <div class="face-image-item">
              <div class="image-placeholder">📷</div>
              <span>采集照片</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const filters = ref({
  status: '',
  channel: '',
  startDate: '',
  endDate: '',
  keyword: ''
})

const currentPage = ref(1)
const total = ref(156)
const totalPages = computed(() => Math.ceil(total.value / 10))
const showDetail = ref(false)
const currentRecord = ref<any>(null)

const stats = ref({
  total: 156,
  passed: 142,
  failed: 12,
  passRate: 91.0
})

const records = ref([
  { id: 1, token: 'SM202403150001', user_id: 'user_001', status: 'passed', channel: '腾讯云慧眼', score: 98.5, liveness_passed: true, duration: 520, created_at: '2024-03-15 14:30:25', completed_at: '2024-03-15 14:30:28', client_ip: '192.168.1.1' },
  { id: 2, token: 'SM202403150002', user_id: 'user_002', status: 'passed', channel: '百度AI', score: 95.2, liveness_passed: true, duration: 680, created_at: '2024-03-15 14:25:10', completed_at: '2024-03-15 14:25:13', client_ip: '192.168.1.2' },
  { id: 3, token: 'SM202403150003', user_id: 'user_003', status: 'failed', channel: '腾讯云慧眼', score: 45.8, liveness_passed: true, duration: 450, created_at: '2024-03-15 14:20:33', completed_at: '2024-03-15 14:20:35', client_ip: '192.168.1.3' },
  { id: 4, token: 'SM202403150004', user_id: 'user_004', status: 'passed', channel: 'Azure Face', score: 92.1, liveness_passed: true, duration: 890, created_at: '2024-03-15 14:15:45', completed_at: '2024-03-15 14:15:49', client_ip: '192.168.1.4' },
  { id: 5, token: 'SM202403150005', user_id: 'user_005', status: 'pending', channel: '腾讯云慧眼', score: 0, liveness_passed: null, duration: 0, created_at: '2024-03-15 14:10:00', completed_at: '-', client_ip: '192.168.1.5' },
  { id: 6, token: 'SM202403150006', user_id: 'user_006', status: 'passed', channel: 'Face++', score: 96.7, liveness_passed: true, duration: 620, created_at: '2024-03-15 14:05:20', completed_at: '2024-03-15 14:05:23', client_ip: '192.168.1.6' },
  { id: 7, token: 'SM202403150007', user_id: 'user_007', status: 'expired', channel: '-', score: 0, liveness_passed: null, duration: 0, created_at: '2024-03-15 13:50:00', completed_at: '-', client_ip: '192.168.1.7' },
  { id: 8, token: 'SM202403150008', user_id: 'user_008', status: 'passed', channel: '百度AI', score: 94.3, liveness_passed: true, duration: 550, created_at: '2024-03-15 13:45:30', completed_at: '2024-03-15 13:45:33', client_ip: '192.168.1.8' },
  { id: 9, token: 'SM202403150009', user_id: 'user_009', status: 'failed', channel: '自研算法', score: 30.5, liveness_passed: false, duration: 320, created_at: '2024-03-15 13:40:15', completed_at: '2024-03-15 13:40:16', client_ip: '192.168.1.9' },
  { id: 10, token: 'SM202403150010', user_id: 'user_010', status: 'passed', channel: '腾讯云慧眼', score: 99.1, liveness_passed: true, duration: 480, created_at: '2024-03-15 13:35:00', completed_at: '2024-03-15 13:35:03', client_ip: '192.168.1.10' }
])

function statusText(status: string): string {
  const map: Record<string, string> = {
    passed: '通过',
    failed: '失败',
    pending: '处理中',
    expired: '已过期'
  }
  return map[status] || status
}

function scoreClass(score: number): string {
  if (score >= 90) return 'text-success'
  if (score >= 70) return 'text-warning'
  if (score > 0) return 'text-danger'
  return 'text-muted'
}

function handleSearch() {
  // 模拟搜索
  console.log('搜索:', filters.value)
}

function resetFilters() {
  filters.value = {
    status: '',
    channel: '',
    startDate: '',
    endDate: '',
    keyword: ''
  }
}

function exportData() {
  alert('导出功能开发中...')
}

function viewDetail(record: any) {
  currentRecord.value = record
  showDetail.value = true
}
</script>

<style scoped lang="scss">
.verifications-page {
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

/* 统计概览 */
.overview-cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.overview-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  
  &.success .overview-value { color: #10b981; }
  &.danger .overview-value { color: #ef4444; }
  &.warning .overview-value { color: #f59e0b; }
}

.overview-value {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 4px;
}

.overview-label {
  font-size: 13px;
  color: #6b7280;
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
  
  .text-success { color: #10b981; }
  .text-danger { color: #ef4444; }
  .text-warning { color: #f59e0b; }
  .text-muted { color: #9ca3af; }
}

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  
  &.passed {
    background: #dcfce7;
    color: #16a34a;
  }
  
  &.failed {
    background: #fee2e2;
    color: #dc2626;
  }
  
  &.pending {
    background: #fef3c7;
    color: #d97706;
  }
  
  &.expired {
    background: #f3f4f6;
    color: #6b7280;
  }
}

.action-btn {
  padding: 4px 12px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 6px;
  font-size: 13px;
  color: #374151;
  cursor: pointer;
  
  &:hover {
    border-color: #667eea;
    color: #667eea;
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

/* 详情抽屉 */
.drawer-mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 200;
}

.detail-drawer {
  position: fixed;
  top: 0;
  right: -500px;
  width: 500px;
  height: 100vh;
  background: white;
  box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
  z-index: 201;
  transition: right 0.3s;
  display: flex;
  flex-direction: column;
  
  &.open {
    right: 0;
  }
}

.drawer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  
  h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
  }
}

.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px 8px;
  
  &:hover {
    color: #374151;
  }
}

.drawer-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

.detail-section {
  margin-bottom: 28px;
  
  h4 {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f3f4f6;
  }
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.detail-item {
  .detail-label {
    display: block;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
  }
  
  .detail-value {
    font-size: 14px;
    color: #1f2937;
    font-weight: 500;
    
    &.mono {
      font-family: 'SF Mono', Monaco, monospace;
      font-size: 13px;
    }
    
    &.text-success { color: #10b981; }
  }
}

.face-images {
  display: flex;
  gap: 16px;
}

.face-image-item {
  text-align: center;
  
  .image-placeholder {
    width: 120px;
    height: 150px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    margin-bottom: 8px;
  }
  
  span {
    font-size: 13px;
    color: #6b7280;
  }
}

@media (max-width: 1024px) {
  .overview-cards {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
