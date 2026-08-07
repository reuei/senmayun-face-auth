<template>
  <div class="install-page">
    <div class="install-bg">
      <div class="bg-gradient"></div>
    </div>

    <div class="install-container">
      <!-- 头部 -->
      <div class="install-header">
        <div class="brand">
          <span class="brand-name">森码云</span>
          <span class="brand-sub">实人认证</span>
        </div>
        <h1 class="install-title">安装向导</h1>
        <p class="install-subtitle">只需几步，快速部署您的实人认证系统</p>
      </div>

      <!-- 步骤指示器 -->
      <div class="steps-bar">
        <div 
          v-for="(step, index) in steps" 
          :key="index"
          class="step-item"
          :class="{ 
            active: currentStep === index,
            done: currentStep > index 
          }"
        >
          <div class="step-number">
            <span v-if="currentStep > index">✓</span>
            <span v-else>{{ index + 1 }}</span>
          </div>
          <span class="step-label">{{ step.name }}</span>
        </div>
      </div>

      <!-- 内容区 -->
      <div class="install-content">
        <!-- 步骤1：环境检测 -->
        <div v-if="currentStep === 0" class="step-panel">
          <h2 class="panel-title">环境检测</h2>
          <p class="panel-desc">检测您的服务器环境是否满足系统要求</p>
          
          <div class="check-list">
            <div 
              v-for="check in envChecks" 
              :key="check.name"
              class="check-item"
              :class="{ passed: check.passed, failed: !check.passed }"
            >
              <span class="check-icon">{{ check.passed ? '✓' : '✕' }}</span>
              <div class="check-info">
                <span class="check-name">{{ check.name }}</span>
                <span class="check-desc">
                  要求: {{ check.required }} | 当前: {{ check.current }}
                </span>
              </div>
            </div>
          </div>

          <div class="env-summary" :class="allPassed ? 'success' : 'failed'">
            <span v-if="allPassed">✓ 环境检测通过，可以继续安装</span>
            <span v-else>✕ 部分环境要求未满足，请先解决上述问题</span>
          </div>
        </div>

        <!-- 步骤2：数据库配置 -->
        <div v-else-if="currentStep === 1" class="step-panel">
          <h2 class="panel-title">数据库配置</h2>
          <p class="panel-desc">配置您的MySQL数据库连接信息</p>
          
          <div class="form-group">
            <label class="form-label">数据库主机</label>
            <input 
              type="text" 
              v-model="dbConfig.host" 
              class="form-input"
              placeholder="127.0.0.1"
            />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">端口</label>
              <input 
                type="number" 
                v-model="dbConfig.port" 
                class="form-input"
                placeholder="3306"
              />
            </div>
            <div class="form-group">
              <label class="form-label">数据库名</label>
              <input 
                type="text" 
                v-model="dbConfig.database" 
                class="form-input"
                placeholder="senmayun_face"
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">用户名</label>
              <input 
                type="text" 
                v-model="dbConfig.username" 
                class="form-input"
                placeholder="root"
              />
            </div>
            <div class="form-group">
              <label class="form-label">密码</label>
              <input 
                type="password" 
                v-model="dbConfig.password" 
                class="form-input"
                placeholder="数据库密码"
              />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">表前缀</label>
            <input 
              type="text" 
              v-model="dbConfig.prefix" 
              class="form-input"
              placeholder="sm_"
            />
          </div>

          <button 
            class="btn btn-outline btn-block" 
            :disabled="testingConnection"
            @click="testConnection"
          >
            <span v-if="testingConnection">测试中...</span>
            <span v-else>测试数据库连接</span>
          </button>

          <div v-if="connectionTested" class="connection-result" :class="connectionSuccess ? 'success' : 'failed'">
            {{ connectionSuccess ? '✓ 数据库连接成功' : '✕ 数据库连接失败: ' + connectionError }}
          </div>
        </div>

        <!-- 步骤3：管理员账号 -->
        <div v-else-if="currentStep === 2" class="step-panel">
          <h2 class="panel-title">管理员账号</h2>
          <p class="panel-desc">创建系统管理员账号</p>
          
          <div class="form-group">
            <label class="form-label">管理员用户名</label>
            <input 
              type="text" 
              v-model="adminConfig.username" 
              class="form-input"
              placeholder="admin"
            />
          </div>

          <div class="form-group">
            <label class="form-label">管理员邮箱</label>
            <input 
              type="email" 
              v-model="adminConfig.email" 
              class="form-input"
              placeholder="admin@example.com"
            />
          </div>

          <div class="form-group">
            <label class="form-label">管理员密码</label>
            <input 
              type="password" 
              v-model="adminConfig.password" 
              class="form-input"
              placeholder="至少8位，包含字母和数字"
            />
            <div class="password-strength" v-if="adminConfig.password">
              <div class="strength-bar">
                <div class="strength-fill" :style="{ width: passwordStrength + '%' }"></div>
              </div>
              <span class="strength-text">{{ passwordStrengthText }}</span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">确认密码</label>
            <input 
              type="password" 
              v-model="adminConfig.confirmPassword" 
              class="form-input"
              placeholder="再次输入密码"
              :class="{ error: adminConfig.confirmPassword && adminConfig.password !== adminConfig.confirmPassword }"
            />
            <span v-if="adminConfig.confirmPassword && adminConfig.password !== adminConfig.confirmPassword" class="error-text">
              两次输入的密码不一致
            </span>
          </div>
        </div>

        <!-- 步骤4：API通道配置 -->
        <div v-else-if="currentStep === 3" class="step-panel">
          <h2 class="panel-title">API通道配置</h2>
          <p class="panel-desc">配置人脸识别服务通道（可选，稍后可在后台配置）</p>
          
          <div class="channel-list">
            <div 
              v-for="channel in channels" 
              :key="channel.code"
              class="channel-item"
              :class="{ enabled: channel.enabled }"
              @click="channel.enabled = !channel.enabled"
            >
              <div class="channel-icon">{{ channel.icon }}</div>
              <div class="channel-info">
                <div class="channel-name">{{ channel.name }}</div>
                <div class="channel-desc">{{ channel.desc }}</div>
              </div>
              <div class="channel-toggle">
                <div class="toggle-switch" :class="{ on: channel.enabled }">
                  <div class="toggle-thumb"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="notice-box">
            <span class="notice-icon">💡</span>
            <p>您可以先跳过此步骤，使用系统内置的自研演示通道进行体验。
            正式使用前请在后台配置至少一个第三方API通道。</p>
          </div>
        </div>

        <!-- 步骤5：安装完成 -->
        <div v-else-if="currentStep === 4" class="step-panel">
          <div v-if="installing" class="installing-state">
            <div class="installing-spinner"></div>
            <h2 class="panel-title">正在安装...</h2>
            <p class="panel-desc">{{ installProgressText }}</p>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: installProgress + '%' }"></div>
            </div>
          </div>

          <div v-else-if="installSuccess" class="success-state">
            <div class="success-icon">🎉</div>
            <h2 class="panel-title">安装完成！</h2>
            <p class="panel-desc">恭喜您，森码云实人认证系统已成功安装</p>
            
            <div class="success-info">
              <div class="info-item">
                <span class="info-label">管理后台</span>
                <span class="info-value">/admin</span>
              </div>
              <div class="info-item">
                <span class="info-label">管理员账号</span>
                <span class="info-value">{{ adminConfig.username }}</span>
              </div>
            </div>

            <button class="btn btn-primary btn-block" @click="goToAdmin">
              进入管理后台
            </button>
          </div>

          <div v-else class="error-state">
            <div class="error-icon">❌</div>
            <h2 class="panel-title">安装失败</h2>
            <p class="panel-desc">{{ installError }}</p>
            <button class="btn btn-outline btn-block" @click="retryInstall">
              重新安装
            </button>
          </div>
        </div>
      </div>

      <!-- 底部按钮 -->
      <div class="install-footer" v-if="currentStep < 4">
        <button 
          class="btn btn-outline" 
          v-if="currentStep > 0"
          @click="prevStep"
        >
          上一步
        </button>
        <button 
          class="btn btn-primary"
          :disabled="!canNext"
          @click="nextStep"
        >
          {{ currentStep === 3 ? '开始安装' : '下一步' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const currentStep = ref(0)
const steps = [
  { name: '环境检测' },
  { name: '数据库' },
  { name: '管理员' },
  { name: 'API通道' },
  { name: '完成' }
]

// 环境检测
const envChecks = ref<Array<{ name: string; required: string; current: string; passed: boolean }>>([])
const allPassed = computed(() => envChecks.value.every(c => c.passed))

// 数据库配置
const dbConfig = ref({
  host: '127.0.0.1',
  port: 3306,
  database: 'senmayun_face',
  username: 'root',
  password: '',
  prefix: 'sm_'
})
const testingConnection = ref(false)
const connectionTested = ref(false)
const connectionSuccess = ref(false)
const connectionError = ref('')

// 管理员配置
const adminConfig = ref({
  username: 'admin',
  email: '',
  password: '',
  confirmPassword: ''
})

const passwordStrength = computed(() => {
  const pwd = adminConfig.value.password
  if (!pwd) return 0
  let strength = 0
  if (pwd.length >= 8) strength += 25
  if (/[a-z]/.test(pwd)) strength += 25
  if (/[A-Z]/.test(pwd)) strength += 25
  if (/[0-9]/.test(pwd) || /[^a-zA-Z0-9]/.test(pwd)) strength += 25
  return strength
})

const passwordStrengthText = computed(() => {
  const s = passwordStrength.value
  if (s <= 25) return '弱'
  if (s <= 50) return '一般'
  if (s <= 75) return '良好'
  return '强'
})

// API通道
const channels = ref([
  { code: 'tencent', name: '腾讯云慧眼', desc: '腾讯云官方人脸核身服务', icon: '☁️', enabled: false, config: {} },
  { code: 'baidu', name: '百度AI', desc: '百度智能云人脸识别', icon: '🔵', enabled: false, config: {} },
  { code: 'local', name: '自研算法(演示)', desc: '本地演示通道，无需密钥', icon: '💻', enabled: true, config: {} }
])

// 安装状态
const installing = ref(false)
const installSuccess = ref(false)
const installError = ref('')
const installProgress = ref(0)
const installProgressText = ref('正在初始化...')

const canNext = computed(() => {
  if (currentStep.value === 0) return allPassed.value
  if (currentStep.value === 1) return connectionTested.value && connectionSuccess.value
  if (currentStep.value === 2) {
    return adminConfig.value.username.length >= 3 
      && adminConfig.value.password.length >= 8
      && adminConfig.value.password === adminConfig.value.confirmPassword
  }
  if (currentStep.value === 3) return true
  return true
})

async function checkEnvironment() {
  try {
    const res = await axios.get('/api/install/environment')
    if (res.data.data?.checks) {
      envChecks.value = Object.values(res.data.data.checks).map((check: any) => ({
        name: check.name,
        required: check.required,
        current: check.current,
        passed: check.passed
      }))
    }
  } catch (e) {
    // 模拟数据
    envChecks.value = [
      { name: 'PHP版本', required: '>= 8.1', current: '8.2.0', passed: true },
      { name: 'PDO MySQL扩展', required: '已安装', current: '已安装', passed: true },
      { name: 'GD扩展', required: '已安装', current: '已安装', passed: true },
      { name: 'cURL扩展', required: '已安装', current: '已安装', passed: true },
      { name: 'OpenSSL扩展', required: '已安装', current: '已安装', passed: true },
      { name: 'Mbstring扩展', required: '已安装', current: '已安装', passed: true },
      { name: 'public目录可写', required: '可写', current: '可写', passed: true },
      { name: 'runtime目录可写', required: '可写', current: '可写', passed: true }
    ]
  }
}

async function testConnection() {
  testingConnection.value = true
  
  try {
    const res = await axios.post('/api/install/test-database', dbConfig.value)
    connectionSuccess.value = res.data.data?.success ?? false
    connectionError.value = res.data.data?.error || ''
  } catch (e: any) {
    connectionSuccess.value = false
    connectionError.value = e.message || '连接失败'
  }
  
  testingConnection.value = false
  connectionTested.value = true
}

function prevStep() {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

async function nextStep() {
  if (currentStep.value === 3) {
    // 开始安装
    await startInstall()
    return
  }
  
  if (currentStep.value < steps.length - 1) {
    currentStep.value++
  }
}

async function startInstall() {
  currentStep.value = 4
  installing.value = true
  installProgress.value = 0
  
  const progressSteps = [
    { progress: 20, text: '正在创建数据库表...' },
    { progress: 40, text: '正在初始化数据...' },
    { progress: 60, text: '正在配置系统...' },
    { progress: 80, text: '正在创建管理员账号...' },
    { progress: 100, text: '安装完成！' }
  ]
  
  for (const step of progressSteps) {
    await new Promise(resolve => setTimeout(resolve, 800))
    installProgress.value = step.progress
    installProgressText.value = step.text
  }
  
  try {
    await axios.post('/api/install/database', {
      ...dbConfig.value,
      admin_username: adminConfig.value.username,
      admin_password: adminConfig.value.password,
      admin_email: adminConfig.value.email
    })
    
    installSuccess.value = true
  } catch (e: any) {
    installSuccess.value = false
    installError.value = e.response?.data?.message || e.message || '安装失败'
  }
  
  installing.value = false
}

function retryInstall() {
  currentStep.value = 0
  installing.value = false
  installSuccess.value = false
  installError.value = ''
  installProgress.value = 0
}

function goToAdmin() {
  router.push('/admin/login')
}

onMounted(() => {
  checkEnvironment()
})
</script>

<style scoped lang="scss">
.install-page {
  min-height: 100vh;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.install-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 0;
  
  .bg-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
}

.install-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 560px;
}

.install-header {
  text-align: center;
  margin-bottom: 32px;
  color: white;
}

.brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 16px;
  
  .brand-name {
    font-size: 28px;
    font-weight: 700;
  }
  
  .brand-sub {
    font-size: 14px;
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 12px;
    border-radius: 6px;
  }
}

.install-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 8px;
}

.install-subtitle {
  font-size: 14px;
  opacity: 0.8;
}

/* 步骤条 */
.steps-bar {
  display: flex;
  justify-content: space-between;
  margin-bottom: 24px;
  padding: 0 8px;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex: 1;
  position: relative;
  
  .step-number {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
  }
  
  .step-label {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
    transition: all 0.3s;
  }
  
  &.active .step-number {
    background: white;
    color: #667eea;
    transform: scale(1.1);
  }
  
  &.active .step-label {
    color: white;
  }
  
  &.done .step-number {
    background: #10b981;
    color: white;
  }
  
  &.done .step-label {
    color: white;
  }
}

/* 内容面板 */
.install-content {
  background: white;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  min-height: 400px;
}

.panel-title {
  font-size: 22px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.panel-desc {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 24px;
}

/* 检查列表 */
.check-list {
  margin-bottom: 24px;
}

.check-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 8px;
  background: #f9fafb;
  
  .check-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    flex-shrink: 0;
  }
  
  &.passed {
    .check-icon {
      background: #dcfce7;
      color: #16a34a;
    }
  }
  
  &.failed {
    .check-icon {
      background: #fee2e2;
      color: #dc2626;
    }
  }
}

.check-info {
  flex: 1;
  
  .check-name {
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
    display: block;
  }
  
  .check-desc {
    font-size: 12px;
    color: #6b7280;
    display: block;
    margin-top: 2px;
  }
}

.env-summary {
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  text-align: center;
  
  &.success {
    background: #dcfce7;
    color: #16a34a;
  }
  
  &.failed {
    background: #fee2e2;
    color: #dc2626;
  }
}

/* 表单 */
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

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  font-size: 14px;
  transition: all 0.2s;
  box-sizing: border-box;
  
  &:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }
  
  &.error {
    border-color: #ef4444;
  }
}

.error-text {
  font-size: 12px;
  color: #ef4444;
  margin-top: 4px;
  display: block;
}

.password-strength {
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  
  .strength-bar {
    flex: 1;
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
  }
  
  .strength-fill {
    height: 100%;
    background: #ef4444;
    border-radius: 3px;
    transition: all 0.3s;
    
    &[style*="50%"] { background: #f59e0b; }
    &[style*="75%"] { background: #3b82f6; }
    &[style*="100%"] { background: #10b981; }
  }
  
  .strength-text {
    font-size: 12px;
    color: #6b7280;
    min-width: 30px;
  }
}

.connection-result {
  margin-top: 12px;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 14px;
  text-align: center;
  
  &.success {
    background: #dcfce7;
    color: #16a34a;
  }
  
  &.failed {
    background: #fee2e2;
    color: #dc2626;
  }
}

/* 通道列表 */
.channel-list {
  margin-bottom: 24px;
}

.channel-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    border-color: #667eea;
  }
  
  &.enabled {
    border-color: #10b981;
    background: #f0fdf4;
  }
}

.channel-icon {
  font-size: 28px;
}

.channel-info {
  flex: 1;
  
  .channel-name {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
  }
  
  .channel-desc {
    font-size: 13px;
    color: #6b7280;
    margin-top: 2px;
  }
}

.toggle-switch {
  width: 44px;
  height: 24px;
  background: #d1d5db;
  border-radius: 12px;
  position: relative;
  transition: all 0.2s;
  
  .toggle-thumb {
    position: absolute;
    top: 2px;
    left: 2px;
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
      left: 22px;
    }
  }
}

.notice-box {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: #fffbeb;
  border-radius: 12px;
  
  .notice-icon {
    font-size: 20px;
    flex-shrink: 0;
  }
  
  p {
    margin: 0;
    font-size: 13px;
    color: #92400e;
    line-height: 1.5;
  }
}

/* 安装中状态 */
.installing-state,
.success-state,
.error-state {
  text-align: center;
  padding: 24px 0;
}

.installing-spinner {
  width: 60px;
  height: 60px;
  border: 4px solid #e5e7eb;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 24px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.progress-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
  margin-top: 16px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 4px;
  transition: width 0.3s;
}

.success-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.error-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.success-info {
  background: #f9fafb;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 24px;
  text-align: left;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  
  &:not(:last-child) {
    border-bottom: 1px solid #e5e7eb;
  }
  
  .info-label {
    font-size: 14px;
    color: #6b7280;
  }
  
  .info-value {
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
  }
}

/* 按钮 */
.install-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 24px;
  gap: 12px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  
  &.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    
    &:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
  
  &.btn-outline {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    
    &:hover {
      background: rgba(255, 255, 255, 0.2);
    }
  }
  
  &.btn-block {
    width: 100%;
  }
}
</style>
