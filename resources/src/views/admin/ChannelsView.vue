<template>
  <div class="channels-page">
    <!-- 顶部说明 -->
    <div class="intro-card">
      <div class="intro-icon">🔌</div>
      <div class="intro-text">
        <h3>多通道冗余架构</h3>
        <p>同时配置多个人脸识别服务通道，系统会自动按优先级选择可用通道，任一通道故障自动降级切换，保障业务稳定运行。</p>
      </div>
      <button class="btn btn-primary" @click="showAddModal = true">
        + 添加通道
      </button>
    </div>

    <!-- 通道列表 -->
    <div class="channels-list">
      <div 
        v-for="channel in channels" 
        :key="channel.code"
        class="channel-card"
        :class="{ disabled: !channel.enabled }"
      >
        <div class="channel-header">
          <div class="channel-info">
            <div class="channel-icon">{{ channel.icon }}</div>
            <div>
              <h3 class="channel-name">{{ channel.name }}</h3>
              <p class="channel-desc">{{ channel.desc }}</p>
            </div>
          </div>
          <div class="channel-actions">
            <div class="toggle-switch" :class="{ on: channel.enabled }" @click="toggleChannel(channel)">
              <div class="toggle-thumb"></div>
            </div>
          </div>
        </div>

        <div class="channel-stats">
          <div class="stat-item">
            <span class="stat-label">优先级</span>
            <span class="stat-value">{{ channel.priority }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">今日调用</span>
            <span class="stat-value">{{ channel.today_calls }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">成功率</span>
            <span class="stat-value" :class="channel.success_rate > 95 ? 'text-success' : 'text-warning'">
              {{ channel.success_rate }}%
            </span>
          </div>
          <div class="stat-item">
            <span class="stat-label">平均耗时</span>
            <span class="stat-value">{{ channel.avg_latency }}ms</span>
          </div>
        </div>

        <div class="channel-footer">
          <button class="action-btn" @click="testChannel(channel)">
            🔗 测试连接
          </button>
          <button class="action-btn" @click="editChannel(channel)">
            ⚙️ 配置
          </button>
          <button class="action-btn text-danger" @click="deleteChannel(channel)">
            🗑️ 删除
          </button>
        </div>
      </div>
    </div>

    <!-- 添加/编辑弹窗 -->
    <div class="modal-mask" v-show="showAddModal" @click="showAddModal = false"></div>
    <div class="modal" v-show="showAddModal">
      <div class="modal-header">
        <h3>{{ editingChannel ? '编辑通道' : '添加通道' }}</h3>
        <button class="close-btn" @click="showAddModal = false">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">通道类型</label>
          <select v-model="form.type" class="form-select" :disabled="!!editingChannel">
            <option value="">请选择通道类型</option>
            <option value="tencent">腾讯云慧眼</option>
            <option value="baidu">百度AI人脸识别</option>
            <option value="azure">Azure Face API</option>
            <option value="facepp">Face++ (旷视科技)</option>
            <option value="local">自研算法(演示)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">通道名称</label>
          <input type="text" v-model="form.name" class="form-input" placeholder="自定义通道名称" />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">优先级</label>
            <input type="number" v-model="form.priority" class="form-input" placeholder="数字越小优先级越高" />
          </div>
          <div class="form-group">
            <label class="form-label">权重</label>
            <input type="number" v-model="form.weight" class="form-input" placeholder="负载均衡权重" />
          </div>
        </div>

        <!-- 腾讯云配置 -->
        <template v-if="form.type === 'tencent'">
          <div class="form-group">
            <label class="form-label">SecretId</label>
            <input type="text" v-model="form.secret_id" class="form-input" placeholder="腾讯云SecretId" />
          </div>
          <div class="form-group">
            <label class="form-label">SecretKey</label>
            <input type="password" v-model="form.secret_key" class="form-input" placeholder="腾讯云SecretKey" />
          </div>
          <div class="form-group">
            <label class="form-label">Region</label>
            <input type="text" v-model="form.region" class="form-input" placeholder="ap-guangzhou" value="ap-guangzhou" />
          </div>
          <div class="form-group">
            <label class="form-label">BizType</label>
            <input type="text" v-model="form.biz_type" class="form-input" placeholder="业务类型(可选)" />
          </div>
        </template>

        <!-- 百度AI配置 -->
        <template v-if="form.type === 'baidu'">
          <div class="form-group">
            <label class="form-label">API Key</label>
            <input type="text" v-model="form.api_key" class="form-input" placeholder="百度AI API Key" />
          </div>
          <div class="form-group">
            <label class="form-label">Secret Key</label>
            <input type="password" v-model="form.secret_key" class="form-input" placeholder="百度AI Secret Key" />
          </div>
        </template>

        <!-- Azure配置 -->
        <template v-if="form.type === 'azure'">
          <div class="form-group">
            <label class="form-label">Endpoint</label>
            <input type="text" v-model="form.endpoint" class="form-input" placeholder="https://xxx.cognitiveservices.azure.com" />
          </div>
          <div class="form-group">
            <label class="form-label">Subscription Key</label>
            <input type="password" v-model="form.subscription_key" class="form-input" placeholder="订阅密钥" />
          </div>
        </template>

        <!-- Face++配置 -->
        <template v-if="form.type === 'facepp'">
          <div class="form-group">
            <label class="form-label">API Key</label>
            <input type="text" v-model="form.api_key" class="form-input" placeholder="Face++ API Key" />
          </div>
          <div class="form-group">
            <label class="form-label">API Secret</label>
            <input type="password" v-model="form.api_secret" class="form-input" placeholder="Face++ API Secret" />
          </div>
          <div class="form-group">
            <label class="form-label">API Server</label>
            <select v-model="form.server" class="form-select">
              <option value="https://api-cn.faceplusplus.com">中国内地</option>
              <option value="https://api-us.faceplusplus.com">美国</option>
            </select>
          </div>
        </template>

        <div class="form-group">
          <label class="form-label">
            <input type="checkbox" v-model="form.enabled" />
            启用此通道
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" @click="showAddModal = false">取消</button>
        <button class="btn btn-primary" @click="saveChannel">保存</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'

const showAddModal = ref(false)
const editingChannel = ref<any>(null)

const form = reactive({
  type: '',
  name: '',
  priority: 1,
  weight: 100,
  enabled: true,
  // 腾讯云
  secret_id: '',
  secret_key: '',
  region: 'ap-guangzhou',
  biz_type: '',
  // 百度
  api_key: '',
  // Azure
  endpoint: '',
  subscription_key: '',
  // Face++
  api_secret: '',
  server: 'https://api-cn.faceplusplus.com'
})

const channels = ref([
  {
    code: 'tencent',
    name: '腾讯云慧眼',
    desc: '腾讯云官方人脸核身服务，支持H5跳转式核身',
    icon: '☁️',
    enabled: true,
    priority: 1,
    weight: 100,
    today_calls: 856,
    success_rate: 99.2,
    avg_latency: 480
  },
  {
    code: 'baidu',
    name: '百度AI',
    desc: '百度智能云人脸识别服务，支持动作活体检测',
    icon: '🔵',
    enabled: true,
    priority: 2,
    weight: 80,
    today_calls: 234,
    success_rate: 98.5,
    avg_latency: 620
  },
  {
    code: 'azure',
    name: 'Azure Face',
    desc: '微软Azure人脸识别，海外节点可用',
    icon: '🟦',
    enabled: true,
    priority: 3,
    weight: 60,
    today_calls: 89,
    success_rate: 97.8,
    avg_latency: 890
  },
  {
    code: 'facepp',
    name: 'Face++',
    desc: '旷视科技人脸识别，金融级准确率',
    icon: '🔴',
    enabled: false,
    priority: 4,
    weight: 50,
    today_calls: 0,
    success_rate: 0,
    avg_latency: 0
  },
  {
    code: 'local',
    name: '自研算法(演示)',
    desc: '本地演示通道，无需第三方密钥，仅供测试',
    icon: '💻',
    enabled: true,
    priority: 10,
    weight: 10,
    today_calls: 12,
    success_rate: 85.0,
    avg_latency: 150
  }
])

function toggleChannel(channel: any) {
  channel.enabled = !channel.enabled
}

function testChannel(channel: any) {
  alert(`正在测试 ${channel.name} 连接...`)
}

function editChannel(channel: any) {
  editingChannel.value = channel
  form.type = channel.code
  form.name = channel.name
  form.priority = channel.priority
  form.weight = channel.weight
  form.enabled = channel.enabled
  showAddModal.value = true
}

function deleteChannel(channel: any) {
  if (confirm(`确定要删除通道「${channel.name}」吗？`)) {
    const index = channels.value.findIndex(c => c.code === channel.code)
    if (index > -1) {
      channels.value.splice(index, 1)
    }
  }
}

function saveChannel() {
  if (!form.type || !form.name) {
    alert('请填写完整信息')
    return
  }
  
  alert('保存成功！')
  showAddModal.value = false
  editingChannel.value = null
  
  // 重置表单
  form.type = ''
  form.name = ''
  form.priority = 1
  form.weight = 100
  form.enabled = true
}
</script>

<style scoped lang="scss">
.channels-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* 介绍卡片 */
.intro-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  flex-shrink: 0;
  
  &.btn-primary {
    background: white;
    color: #667eea;
    
    &:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
  }
  
  &.btn-outline {
    background: transparent;
    border: 1px solid #d1d5db;
    color: #374151;
    
    &:hover {
      border-color: #667eea;
      color: #667eea;
    }
  }
}

/* 通道列表 */
.channels-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.channel-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
  
  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }
  
  &.disabled {
    opacity: 0.6;
  }
}

.channel-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.channel-info {
  display: flex;
  gap: 14px;
}

.channel-icon {
  font-size: 36px;
  flex-shrink: 0;
}

.channel-name {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 4px;
}

.channel-desc {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
  line-height: 1.5;
}

/* 开关 */
.toggle-switch {
  width: 48px;
  height: 26px;
  background: #d1d5db;
  border-radius: 13px;
  position: relative;
  cursor: pointer;
  transition: all 0.2s;
  
  .toggle-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  &.on {
    background: #10b981;
    
    .toggle-thumb {
      left: 25px;
    }
  }
}

/* 通道统计 */
.channel-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  padding: 16px 0;
  border-top: 1px solid #f3f4f6;
  border-bottom: 1px solid #f3f4f6;
  margin-bottom: 16px;
}

.stat-item {
  text-align: center;
  
  .stat-label {
    display: block;
    font-size: 12px;
    color: #9ca3af;
    margin-bottom: 4px;
  }
  
  .stat-value {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    
    &.text-success { color: #10b981; }
    &.text-warning { color: #f59e0b; }
  }
}

.channel-footer {
  display: flex;
  gap: 8px;
}

.action-btn {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 8px;
  font-size: 13px;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    border-color: #667eea;
    color: #667eea;
  }
  
  &.text-danger {
    color: #ef4444;
    
    &:hover {
      border-color: #ef4444;
      background: #fef2f2;
    }
  }
}

/* 弹窗 */
.modal-mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 200;
}

.modal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 520px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  z-index: 201;
  overflow: hidden;
}

.modal-header {
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

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
}

.form-group {
  margin-bottom: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}

.form-input,
.form-select {
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
  
  &:disabled {
    background: #f9fafb;
    color: #9ca3af;
  }
}

@media (max-width: 1024px) {
  .channels-list {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .intro-card {
    flex-direction: column;
    text-align: center;
  }
  
  .channel-stats {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
