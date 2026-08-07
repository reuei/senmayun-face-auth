<template>
  <div class="dashboard-page">
    <!-- 统计卡片 -->
    <div class="stats-grid">
      <div class="stat-card" v-for="stat in stats" :key="stat.label">
        <div class="stat-icon" :style="{ background: stat.color }">
          <span>{{ stat.icon }}</span>
        </div>
        <div class="stat-info">
          <div class="stat-value">{{ stat.value }}</div>
          <div class="stat-label">{{ stat.label }}</div>
          <div class="stat-change" :class="stat.trend">
            <span>{{ stat.trend === 'up' ? '↑' : '↓' }} {{ stat.change }}</span>
            <span class="change-period">较昨日</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 图表区域 -->
    <div class="charts-row">
      <div class="chart-card large">
        <div class="card-header">
          <h3>认证趋势</h3>
          <div class="chart-tabs">
            <button 
              v-for="tab in timeTabs" 
              :key="tab.value"
              class="tab-btn"
              :class="{ active: activeTab === tab.value }"
              @click="activeTab = tab.value"
            >
              {{ tab.label }}
            </button>
          </div>
        </div>
        <div class="chart-body">
          <div class="chart-placeholder">
            <div class="bar-chart">
              <div 
                v-for="(item, index) in chartData" 
                :key="index"
                class="bar-item"
              >
                <div class="bar" :style="{ height: item.value + '%' }"></div>
                <div class="bar-label">{{ item.label }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="chart-card">
        <div class="card-header">
          <h3>通道使用占比</h3>
        </div>
        <div class="chart-body">
          <div class="pie-chart">
            <div class="pie-visual">
              <div class="pie-ring"></div>
              <div class="pie-center">
                <div class="pie-total">5</div>
                <div class="pie-label">通道</div>
              </div>
            </div>
            <div class="pie-legend">
              <div class="legend-item" v-for="item in channelStats" :key="item.name">
                <span class="legend-dot" :style="{ background: item.color }"></span>
                <span class="legend-name">{{ item.name }}</span>
                <span class="legend-value">{{ item.value }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 最近记录和系统状态 -->
    <div class="bottom-row">
      <div class="table-card">
        <div class="card-header">
          <h3>最近认证记录</h3>
          <a href="/admin/verifications" class="view-all">查看全部 →</a>
        </div>
        <div class="table-body">
          <table class="data-table">
            <thead>
              <tr>
                <th>认证编号</th>
                <th>用户ID</th>
                <th>状态</th>
                <th>通道</th>
                <th>时间</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="record in recentRecords" :key="record.id">
                <td class="mono">{{ shortToken(record.token) }}</td>
                <td>{{ record.user_id }}</td>
                <td>
                  <span class="status-badge" :class="record.status">
                    {{ statusText(record.status) }}
                  </span>
                </td>
                <td>{{ record.channel }}</td>
                <td class="text-muted">{{ record.time }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="status-card">
        <div class="card-header">
          <h3>系统状态</h3>
        </div>
        <div class="status-body">
          <div class="status-item">
            <div class="status-info">
              <span class="status-label">服务状态</span>
              <span class="status-value online">
                <span class="status-dot"></span>
                运行正常
              </span>
            </div>
          </div>
          <div class="status-item">
            <div class="status-info">
              <span class="status-label">API通道</span>
              <span class="status-value">5/5 在线</span>
            </div>
          </div>
          <div class="status-item">
            <div class="status-info">
              <span class="status-label">平均响应</span>
              <span class="status-value">520ms</span>
            </div>
          </div>
          <div class="status-item">
            <div class="status-info">
              <span class="status-label">成功率</span>
              <span class="status-value success">98.5%</span>
            </div>
          </div>
          <div class="status-item">
            <div class="status-info">
              <span class="status-label">存储使用</span>
              <span class="status-value">2.3 GB / 10 GB</span>
            </div>
            <div class="progress-bar">
              <div class="progress-fill" style="width: 23%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const activeTab = ref('7d')

const timeTabs = [
  { label: '今日', value: 'today' },
  { label: '近7天', value: '7d' },
  { label: '近30天', value: '30d' }
]

const stats = [
  { icon: '📋', label: '今日认证', value: '1,234', change: '12.5%', trend: 'up', color: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' },
  { icon: '✅', label: '认证通过', value: '1,156', change: '8.3%', trend: 'up', color: 'linear-gradient(135deg, #10b981 0%, #059669 100%)' },
  { icon: '❌', label: '认证失败', value: '78', change: '2.1%', trend: 'down', color: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' },
  { icon: '⚡', label: '平均耗时', value: '520ms', change: '5.2%', trend: 'down', color: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' }
]

const chartData = [
  { label: '周一', value: 65 },
  { label: '周二', value: 78 },
  { label: '周三', value: 82 },
  { label: '周四', value: 71 },
  { label: '周五', value: 90 },
  { label: '周六', value: 55 },
  { label: '周日', value: 48 }
]

const channelStats = [
  { name: '腾讯云', value: 45, color: '#667eea' },
  { name: '百度AI', value: 25, color: '#10b981' },
  { name: 'Azure', value: 15, color: '#f59e0b' },
  { name: 'Face++', value: 10, color: '#ef4444' },
  { name: '自研', value: 5, color: '#8b5cf6' }
]

const recentRecords = [
  { id: 1, token: 'a1b2c3d4e5f6g7h8', user_id: 'user_001', status: 'passed', channel: '腾讯云', time: '2分钟前' },
  { id: 2, token: 'b2c3d4e5f6g7h8i9', user_id: 'user_002', status: 'passed', channel: '百度AI', time: '5分钟前' },
  { id: 3, token: 'c3d4e5f6g7h8i9j0', user_id: 'user_003', status: 'failed', channel: '腾讯云', time: '8分钟前' },
  { id: 4, token: 'd4e5f6g7h8i9j0k1', user_id: 'user_004', status: 'passed', channel: 'Azure', time: '12分钟前' },
  { id: 5, token: 'e5f6g7h8i9j0k1l2', user_id: 'user_005', status: 'pending', channel: '腾讯云', time: '15分钟前' }
]

function shortToken(token: string): string {
  return token.substring(0, 8) + '...'
}

function statusText(status: string): string {
  const map: Record<string, string> = {
    passed: '通过',
    failed: '失败',
    pending: '处理中'
  }
  return map[status] || status
}
</script>

<style scoped lang="scss">
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* 统计卡片 */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  gap: 16px;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  flex-shrink: 0;
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  line-height: 1.2;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

.stat-change {
  font-size: 12px;
  margin-top: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
  
  &.up {
    color: #10b981;
  }
  
  &.down {
    color: #ef4444;
  }
  
  .change-period {
    color: #9ca3af;
  }
}

/* 图表行 */
.charts-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.chart-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  
  &.large {
    /* 更大的卡片 */
  }
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

.chart-tabs {
  display: flex;
  gap: 4px;
  background: #f3f4f6;
  padding: 4px;
  border-radius: 8px;
}

.tab-btn {
  padding: 6px 14px;
  border: none;
  background: transparent;
  border-radius: 6px;
  font-size: 13px;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
  
  &.active {
    background: white;
    color: #1f2937;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }
}

.chart-body {
  padding: 24px;
}

/* 柱状图占位 */
.bar-chart {
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  height: 240px;
  padding: 0 10px;
}

.bar-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  flex: 1;
}

.bar {
  width: 32px;
  background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
  border-radius: 6px 6px 0 0;
  min-height: 10px;
  transition: height 0.3s;
}

.bar-label {
  font-size: 12px;
  color: #9ca3af;
}

/* 饼图 */
.pie-chart {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
}

.pie-visual {
  position: relative;
  width: 160px;
  height: 160px;
}

.pie-ring {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: conic-gradient(
    #667eea 0deg 162deg,
    #10b981 162deg 252deg,
    #f59e0b 252deg 306deg,
    #ef4444 306deg 342deg,
    #8b5cf6 342deg 360deg
  );
  position: relative;
  
  &::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 20px;
    right: 20px;
    bottom: 20px;
    background: white;
    border-radius: 50%;
  }
}

.pie-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.pie-total {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
}

.pie-label {
  font-size: 12px;
  color: #6b7280;
}

.pie-legend {
  width: 100%;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 0;
  font-size: 13px;
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.legend-name {
  flex: 1;
  color: #4b5563;
}

.legend-value {
  font-weight: 500;
  color: #1f2937;
}

/* 底部行 */
.bottom-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.table-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.view-all {
  font-size: 13px;
  color: #667eea;
  text-decoration: none;
  
  &:hover {
    text-decoration: underline;
  }
}

.table-body {
  padding: 0;
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
}

/* 系统状态卡片 */
.status-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.status-body {
  padding: 20px 24px;
}

.status-item {
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
  
  &:last-child {
    border-bottom: none;
  }
}

.status-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.status-label {
  font-size: 14px;
  color: #6b7280;
}

.status-value {
  font-size: 14px;
  font-weight: 500;
  color: #1f2937;
  
  &.online {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #16a34a;
    
    .status-dot {
      width: 8px;
      height: 8px;
      background: #22c55e;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }
  }
  
  &.success {
    color: #16a34a;
  }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.progress-bar {
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 3px;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .charts-row,
  .bottom-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
