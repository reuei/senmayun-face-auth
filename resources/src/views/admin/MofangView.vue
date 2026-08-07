<template>
  <div class="mofang-page">
    <!-- 介绍 -->
    <div class="intro-card">
      <div class="intro-icon">💰</div>
      <div class="intro-text">
        <h3>魔方财务对接</h3>
        <p>与魔方财务系统无缝对接，用户在购买实人认证产品后自动发起认证，认证完成后自动开通服务。</p>
      </div>
      <div class="intro-status" :class="{ enabled: config.enabled }">
        <span class="status-dot"></span>
        {{ config.enabled ? '已启用' : '未启用' }}
      </div>
    </div>

    <div class="content-grid">
      <!-- 基础配置 -->
      <div class="config-card">
        <div class="card-header">
          <h3>基础配置</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">
              <input type="checkbox" v-model="config.enabled" />
              启用魔方财务对接
            </label>
          </div>

          <div class="form-group">
            <label class="form-label">魔方财务地址</label>
            <input 
              type="text" 
              v-model="config.api_url" 
              class="form-input"
              placeholder="https://your-domain.com"
            />
            <p class="form-hint">魔方财务系统的访问地址，不带末尾斜杠</p>
          </div>

          <div class="form-group">
            <label class="form-label">AppKey</label>
            <input 
              type="text" 
              v-model="config.app_key" 
              class="form-input"
              placeholder="魔方财务AppKey"
            />
          </div>

          <div class="form-group">
            <label class="form-label">AppSecret</label>
            <input 
              type="password" 
              v-model="config.app_secret" 
              class="form-input"
              placeholder="魔方财务AppSecret"
            />
          </div>

          <div class="form-group">
            <label class="form-label">回调地址</label>
            <div class="input-with-copy">
              <input 
                type="text" 
                :value="callbackUrl" 
                class="form-input"
                readonly
              />
              <button class="copy-btn" @click="copyCallbackUrl">复制</button>
            </div>
            <p class="form-hint">将此地址填入魔方财务的回调配置中</p>
          </div>
        </div>
      </div>

      <!-- 产品映射 -->
      <div class="config-card">
        <div class="card-header">
          <h3>产品映射</h3>
          <button class="btn btn-primary btn-sm" @click="addMapping">+ 添加</button>
        </div>
        <div class="card-body">
          <div class="mapping-list">
            <div v-for="(item, index) in mappings" :key="index" class="mapping-item">
              <div class="mapping-info">
                <span class="mapping-label">产品ID</span>
                <input type="text" v-model="item.product_id" class="mapping-input" placeholder="产品ID" />
              </div>
              <div class="mapping-arrow">→</div>
              <div class="mapping-info">
                <span class="mapping-label">认证类型</span>
                <select v-model="item.auth_type" class="mapping-input">
                  <option value="face">人脸比对</option>
                  <option value="liveness">活体检测</option>
                  <option value="full">完整核身</option>
                </select>
              </div>
              <div class="mapping-info">
                <span class="mapping-label">次数</span>
                <input type="number" v-model="item.times" class="mapping-input small" placeholder="次数" />
              </div>
              <button class="delete-btn" @click="removeMapping(index)">✕</button>
            </div>
          </div>
          <p class="empty-tip" v-if="mappings.length === 0">暂无产品映射，点击上方按钮添加</p>
        </div>
      </div>

      <!-- 订单列表 -->
      <div class="config-card full-width">
        <div class="card-header">
          <h3>最近订单</h3>
          <a href="#" class="view-all">查看全部 →</a>
        </div>
        <div class="card-body">
          <table class="data-table">
            <thead>
              <tr>
                <th>订单号</th>
                <th>用户</th>
                <th>产品</th>
                <th>金额</th>
                <th>状态</th>
                <th>认证状态</th>
                <th>时间</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in orders" :key="order.id">
                <td class="mono">{{ order.order_no }}</td>
                <td>{{ order.username }}</td>
                <td>{{ order.product_name }}</td>
                <td>¥{{ order.amount }}</td>
                <td>
                  <span class="status-badge" :class="order.status">
                    {{ orderStatusText(order.status) }}
                  </span>
                </td>
                <td>
                  <span class="status-badge" :class="order.auth_status">
                    {{ authStatusText(order.auth_status) }}
                  </span>
                </td>
                <td class="text-muted">{{ order.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 保存按钮 -->
    <div class="save-bar">
      <button class="btn btn-outline" @click="testConnection">测试连接</button>
      <button class="btn btn-primary" @click="saveConfig">保存配置</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'

const config = reactive({
  enabled: true,
  api_url: 'https://billing.example.com',
  app_key: 'mf_app_xxxxxx',
  app_secret: 'mf_secret_xxxxxx'
})

const callbackUrl = computed(() => {
  return window.location.origin + '/mofang/callback'
})

const mappings = ref([
  { product_id: '1001', auth_type: 'full', times: 100 },
  { product_id: '1002', auth_type: 'face', times: 500 },
  { product_id: '1003', auth_type: 'liveness', times: 1000 }
])

const orders = ref([
  { id: 1, order_no: 'MF202403150001', username: 'user001', product_name: '实人认证-100次', amount: '29.90', status: 'paid', auth_status: 'completed', created_at: '2024-03-15 14:30:00' },
  { id: 2, order_no: 'MF202403150002', username: 'user002', product_name: '实人认证-500次', amount: '99.00', status: 'paid', auth_status: 'pending', created_at: '2024-03-15 13:20:00' },
  { id: 3, order_no: 'MF202403150003', username: 'user003', product_name: '活体检测-1000次', amount: '199.00', status: 'pending', auth_status: 'none', created_at: '2024-03-15 12:10:00' }
])

function copyCallbackUrl() {
  navigator.clipboard.writeText(callbackUrl.value)
  alert('已复制到剪贴板')
}

function addMapping() {
  mappings.value.push({ product_id: '', auth_type: 'face', times: 100 })
}

function removeMapping(index: number) {
  mappings.value.splice(index, 1)
}

function orderStatusText(status: string): string {
  const map: Record<string, string> = {
    pending: '待支付',
    paid: '已支付',
    refunded: '已退款',
    cancelled: '已取消'
  }
  return map[status] || status
}

function authStatusText(status: string): string {
  const map: Record<string, string> = {
    none: '未开始',
    pending: '进行中',
    completed: '已完成',
    failed: '失败'
  }
  return map[status] || status
}

function testConnection() {
  alert('正在测试连接...')
}

function saveConfig() {
  alert('配置保存成功！')
}
</script>

<style scoped lang="scss">
.mofang-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* 介绍卡片 */
.intro-card {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  color: white;
}

.intro-icon {
  font-size: 48px;
  flex-shrink: 0;
}

.intro-text {
  flex: 1;
  
  h3 {
    font-size: 20px;
    font-weight: 600;
    margin: 0 0 8px;
  }
  
  p {
    font-size: 14px;
    opacity: 0.9;
    margin: 0;
    line-height: 1.6;
  }
}

.intro-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  
  .status-dot {
    width: 8px;
    height: 8px;
    background: #fca5a5;
    border-radius: 50%;
  }
  
  &.enabled .status-dot {
    background: #86efac;
    animation: pulse 2s infinite;
  }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* 内容网格 */
.content-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.config-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  
  &.full-width {
    grid-column: 1 / -1;
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

.view-all {
  font-size: 13px;
  color: #667eea;
  text-decoration: none;
  
  &:hover {
    text-decoration: underline;
  }
}

.card-body {
  padding: 24px;
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
  
  &.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
  }
}

/* 表单 */
.form-group {
  margin-bottom: 20px;
  
  &:last-child {
    margin-bottom: 0;
  }
}

.form-label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
  
  input[type="checkbox"] {
    margin-right: 8px;
  }
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  font-size: 14px;
  box-sizing: border-box;
  
  &:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }
}

.form-hint {
  font-size: 12px;
  color: #9ca3af;
  margin-top: 6px;
}

.input-with-copy {
  display: flex;
  gap: 8px;
  
  .form-input {
    flex: 1;
    background: #f9fafb;
  }
}

.copy-btn {
  padding: 10px 16px;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 13px;
  color: #4b5563;
  cursor: pointer;
  white-space: nowrap;
  
  &:hover {
    background: #e5e7eb;
  }
}

/* 产品映射 */
.mapping-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.mapping-item {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 10px;
}

.mapping-info {
  flex: 1;
  
  .mapping-label {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 6px;
  }
  
  .mapping-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 13px;
    box-sizing: border-box;
    
    &.small {
      width: 80px;
    }
    
    &:focus {
      outline: none;
      border-color: #667eea;
    }
  }
}

.mapping-arrow {
  padding-bottom: 10px;
  color: #9ca3af;
  font-size: 18px;
}

.delete-btn {
  width: 28px;
  height: 28px;
  background: #fee2e2;
  border: none;
  border-radius: 6px;
  color: #ef4444;
  cursor: pointer;
  font-size: 12px;
  margin-bottom: 2px;
  
  &:hover {
    background: #fecaca;
  }
}

.empty-tip {
  text-align: center;
  color: #9ca3af;
  font-size: 14px;
  padding: 20px 0;
}

/* 数据表格 */
.data-table {
  width: 100%;
  border-collapse: collapse;
  
  th, td {
    padding: 12px 16px;
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
  
  &.paid, &.completed {
    background: #dcfce7;
    color: #16a34a;
  }
  
  &.pending {
    background: #fef3c7;
    color: #d97706;
  }
  
  &.failed, &.refunded, &.cancelled {
    background: #fee2e2;
    color: #dc2626;
  }
  
  &.none {
    background: #f3f4f6;
    color: #6b7280;
  }
}

/* 保存栏 */
.save-bar {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}
</style>
