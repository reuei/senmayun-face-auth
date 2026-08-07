<template>
  <div class="result-page">
    <div class="result-bg" :class="statusClass"></div>
    
    <div class="result-container">
      <div class="result-card">
        <!-- 成功状态 -->
        <template v-if="status === 'passed'">
          <div class="result-icon success">
            <div class="icon-circle">
              <span class="check-mark">✓</span>
            </div>
            <div class="icon-ring"></div>
            <div class="icon-ring delay"></div>
          </div>
          <h1 class="result-title success">认证通过</h1>
          <p class="result-desc">您的实人认证已成功完成</p>
        </template>

        <!-- 失败状态 -->
        <template v-else-if="status === 'failed'">
          <div class="result-icon failed">
            <div class="icon-circle">
              <span class="cross-mark">✕</span>
            </div>
          </div>
          <h1 class="result-title failed">认证未通过</h1>
          <p class="result-desc">{{ failReason || '很抱歉，您的认证未通过，请重试' }}</p>
        </template>

        <!-- 过期状态 -->
        <template v-else-if="status === 'expired'">
          <div class="result-icon expired">
            <div class="icon-circle">
              <span class="clock-icon">⏰</span>
            </div>
          </div>
          <h1 class="result-title expired">认证已过期</h1>
          <p class="result-desc">认证链接已过期，请重新发起认证</p>
        </template>

        <!-- 加载中 -->
        <template v-else>
          <div class="result-icon loading">
            <div class="loading-spinner"></div>
          </div>
          <h1 class="result-title">正在查询结果...</h1>
          <p class="result-desc">请稍候</p>
        </template>

        <!-- 认证详情 -->
        <div class="result-details" v-if="status === 'passed' || status === 'failed'">
          <div class="detail-row">
            <span class="detail-label">认证编号</span>
            <span class="detail-value">{{ shortToken }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">认证时间</span>
            <span class="detail-value">{{ verifyTime }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">认证通道</span>
            <span class="detail-value">{{ channelName }}</span>
          </div>
          <div class="detail-row" v-if="score > 0">
            <span class="detail-label">相似度</span>
            <span class="detail-value" :class="scoreClass">{{ score }}%</span>
          </div>
          <div class="detail-row" v-if="livenessPassed !== null">
            <span class="detail-label">活体检测</span>
            <span class="detail-value" :class="livenessPassed ? 'success' : 'failed'">
              {{ livenessPassed ? '通过' : '未通过' }}
            </span>
          </div>
        </div>

        <!-- 操作按钮 -->
        <div class="result-actions">
          <template v-if="status === 'passed'">
            <button class="btn btn-primary btn-block" @click="goBack">
              返回应用
            </button>
            <button class="btn btn-outline btn-block" @click="downloadReport">
              下载认证凭证
            </button>
          </template>
          
          <template v-else-if="status === 'failed'">
            <button class="btn btn-primary btn-block" @click="retry">
              重新认证
            </button>
            <button class="btn btn-outline btn-block" @click="contactSupport">
              联系客服
            </button>
          </template>
          
          <template v-else-if="status === 'expired'">
            <button class="btn btn-primary btn-block" @click="goHome">
              返回首页
            </button>
          </template>
        </div>

        <!-- 安全提示 -->
        <div class="security-tip">
          <span class="tip-icon">🔒</span>
          <span>您的人脸数据已加密存储，仅用于本次认证</span>
        </div>
      </div>

      <!-- 底部信息 -->
      <div class="result-footer">
        <p>森码云实人认证系统</p>
        <p class="footer-links">
          <a href="#">隐私政策</a>
          <span>·</span>
          <a href="#">服务条款</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getAuthResult } from '../api/auth'

const route = useRoute()
const router = useRouter()

const status = ref('loading') // loading, passed, failed, expired
const score = ref(0)
const livenessPassed = ref<boolean | null>(null)
const failReason = ref('')
const verifyTime = ref('')
const channelName = ref('')
const token = ref('')

const statusClass = computed(() => {
  return {
    'status-success': status.value === 'passed',
    'status-failed': status.value === 'failed',
    'status-expired': status.value === 'expired'
  }
})

const shortToken = computed(() => {
  if (!token.value) return '-'
  return token.value.substring(0, 8) + '...' + token.value.substring(token.value.length - 6)
})

const scoreClass = computed(() => {
  if (score.value >= 90) return 'success'
  if (score.value >= 70) return 'warning'
  return 'failed'
})

async function loadResult() {
  const tokenParam = route.query.token as string
  if (!tokenParam) {
    status.value = 'expired'
    return
  }
  
  token.value = tokenParam
  
  try {
    const result = await getAuthResult(tokenParam)
    
    if (result.data) {
      status.value = result.data.status || 'failed'
      score.value = result.data.score || 0
      livenessPassed.value = result.data.liveness_passed ?? null
      failReason.value = result.data.fail_reason || ''
      verifyTime.value = result.data.verify_time || ''
      channelName.value = getChannelName(result.data.api_source)
      
      if (status.value === 'pending') {
        // 还在处理中，3秒后重试
        setTimeout(loadResult, 3000)
      }
    } else {
      status.value = 'failed'
      failReason.value = result.message || '查询失败'
    }
  } catch (e) {
    status.value = 'failed'
    failReason.value = '网络错误，请稍后重试'
  }
}

function getChannelName(code: string): string {
  const map: Record<string, string> = {
    'tencent': '腾讯云慧眼',
    'baidu': '百度AI',
    'azure': 'Azure Face',
    'facepp': 'Face++',
    'local': '自研算法'
  }
  return map[code] || code || '未知通道'
}

function goBack() {
  // 返回来源页面
  const returnUrl = route.query.return_url as string
  if (returnUrl) {
    window.location.href = returnUrl
  } else {
    router.push('/')
  }
}

function retry() {
  router.push('/verify')
}

function goHome() {
  router.push('/')
}

function downloadReport() {
  // 生成简单的认证凭证
  const reportContent = `
森码云实人认证凭证
==================

认证编号：${token.value}
认证时间：${verifyTime.value}
认证结果：认证通过
认证通道：${channelName.value}
相似度：${score.value}%

本凭证由森码云实人认证系统生成，仅供参考。
  `.trim()
  
  const blob = new Blob([reportContent], { type: 'text/plain;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `认证凭证_${Date.now()}.txt`
  a.click()
  URL.revokeObjectURL(url)
}

function contactSupport() {
  window.location.href = 'mailto:support@builds.codes'
}

onMounted(() => {
  loadResult()
})
</script>

<style scoped lang="scss">
.result-page {
  min-height: 100vh;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.result-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 0;
  transition: background 0.5s;
  
  &.status-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }
  
  &.status-failed {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  }
  
  &.status-expired {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  }
}

.result-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 420px;
}

.result-card {
  background: white;
  border-radius: 24px;
  padding: 40px 32px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  text-align: center;
}

/* 结果图标 */
.result-icon {
  margin-bottom: 24px;
  position: relative;
  display: inline-block;
  
  .icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 2;
    margin: 0 auto;
  }
  
  &.success .icon-circle {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    animation: bounce-in 0.5s ease-out;
  }
  
  &.failed .icon-circle {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    animation: shake 0.5s ease-out;
  }
  
  &.expired .icon-circle {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  }
  
  .check-mark {
    color: white;
    font-size: 40px;
    font-weight: bold;
    animation: check-draw 0.5s ease-out 0.2s both;
  }
  
  .cross-mark {
    color: white;
    font-size: 36px;
    font-weight: bold;
  }
  
  .clock-icon {
    font-size: 36px;
  }
  
  .icon-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100px;
    height: 100px;
    border: 3px solid rgba(16, 185, 129, 0.4);
    border-radius: 50%;
    animation: ring-pulse 2s ease-out infinite;
    
    &.delay {
      animation-delay: 0.5s;
    }
  }
  
  .loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #e5e7eb;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
  }
}

@keyframes bounce-in {
  0% { transform: scale(0); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}

@keyframes check-draw {
  0% { transform: scale(0); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-10px); }
  75% { transform: translateX(10px); }
}

@keyframes ring-pulse {
  0% { transform: translate(-50%, -50%) scale(0.8); opacity: 1; }
  100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* 结果标题 */
.result-title {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 8px;
  
  &.success {
    color: #059669;
  }
  
  &.failed {
    color: #dc2626;
  }
  
  &.expired {
    color: #d97706;
  }
}

.result-desc {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 24px;
}

/* 详情 */
.result-details {
  background: #f9fafb;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 24px;
  text-align: left;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  
  &:not(:last-child) {
    border-bottom: 1px solid #e5e7eb;
  }
  
  .detail-label {
    font-size: 14px;
    color: #6b7280;
  }
  
  .detail-value {
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
    
    &.success {
      color: #059669;
    }
    
    &.failed {
      color: #dc2626;
    }
    
    &.warning {
      color: #d97706;
    }
  }
}

/* 按钮 */
.result-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 14px 24px;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  
  &.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    
    &:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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
  
  &.btn-block {
    width: 100%;
  }
}

/* 安全提示 */
.security-tip {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 12px;
  color: #9ca3af;
  
  .tip-icon {
    font-size: 14px;
  }
}

/* 底部 */
.result-footer {
  text-align: center;
  margin-top: 24px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 12px;
  
  p {
    margin: 4px 0;
  }
  
  .footer-links {
    a {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      
      &:hover {
        color: white;
      }
    }
    
    span {
      margin: 0 8px;
      opacity: 0.5;
    }
  }
}
</style>
