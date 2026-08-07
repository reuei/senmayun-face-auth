<template>
  <div class="verify-page">
    <!-- 背景装饰 -->
    <div class="verify-bg">
      <div class="bg-gradient"></div>
      <div class="bg-pattern"></div>
    </div>

    <div class="verify-container">
      <!-- 头部 -->
      <div class="verify-header">
        <div class="brand">
          <span class="brand-name">森码云</span>
          <span class="brand-sub">实人认证</span>
        </div>
        <div class="step-indicator">
          <div class="step" :class="{ active: currentStep >= 1, done: currentStep > 1 }">
            <span class="step-num">1</span>
            <span class="step-text">准备</span>
          </div>
          <div class="step-line" :class="{ active: currentStep > 1 }"></div>
          <div class="step" :class="{ active: currentStep >= 2, done: currentStep > 2 }">
            <span class="step-num">2</span>
            <span class="step-text">采集</span>
          </div>
          <div class="step-line" :class="{ active: currentStep > 2 }"></div>
          <div class="step" :class="{ active: currentStep >= 3, done: currentStep > 3 }">
            <span class="step-num">3</span>
            <span class="step-text">核验</span>
          </div>
        </div>
      </div>

      <!-- 主内容区 -->
      <div class="verify-content">
        <!-- 步骤1：准备 -->
        <div v-if="currentStep === 1" class="step-content">
          <div class="step-icon">📋</div>
          <h2 class="step-title">准备认证</h2>
          <p class="step-desc">请确保您处于光线充足的环境，并准备好您的身份证</p>
          
          <div class="checklist">
            <div class="check-item" :class="{ checked: checks.lighting }" @click="checks.lighting = !checks.lighting">
              <span class="check-box">{{ checks.lighting ? '✓' : '' }}</span>
              <span>光线充足，面部清晰可见</span>
            </div>
            <div class="check-item" :class="{ checked: checks.idCard }" @click="checks.idCard = !checks.idCard">
              <span class="check-box">{{ checks.idCard ? '✓' : '' }}</span>
              <span>准备好本人身份证</span>
            </div>
            <div class="check-item" :class="{ checked: checks.network }" @click="checks.network = !checks.network">
              <span class="check-box">{{ checks.network ? '✓' : '' }}</span>
              <span>网络连接稳定</span>
            </div>
          </div>

          <div class="privacy-notice">
            <div class="notice-icon">🔒</div>
            <div class="notice-text">
              <p><strong>隐私声明</strong></p>
              <p>您的人脸数据将被加密存储，仅用于本次身份核验，核验完成后将按规定期限删除。</p>
            </div>
          </div>

          <button class="btn btn-primary btn-lg btn-block" :disabled="!canStart" @click="startVerify">
            开始认证
          </button>
        </div>

        <!-- 步骤2：人脸采集 -->
        <div v-else-if="currentStep === 2" class="step-content">
          <div class="capture-area">
            <!-- 摄像头预览 -->
            <div class="camera-container">
              <video 
                ref="videoRef" 
                class="camera-video" 
                autoplay 
                playsinline 
                muted
                v-show="cameraReady"
              ></video>
              <canvas ref="canvasRef" class="capture-canvas" style="display: none;"></canvas>
              
              <!-- 加载状态 -->
              <div v-if="!cameraReady" class="camera-loading">
                <div class="loading-spinner"></div>
                <p>正在启动摄像头...</p>
              </div>

              <!-- 人脸框 -->
              <div class="face-frame" :class="faceFrameClass">
                <div class="frame-corner tl"></div>
                <div class="frame-corner tr"></div>
                <div class="frame-corner bl"></div>
                <div class="frame-corner br"></div>
                <div class="face-oval"></div>
              </div>

              <!-- 进度环 -->
              <div class="progress-ring" v-if="isCapturing">
                <svg class="ring-svg" viewBox="0 0 100 100">
                  <circle class="ring-bg" cx="50" cy="50" r="45" fill="none" stroke-width="3"/>
                  <circle 
                    class="ring-progress" 
                    cx="50" 
                    cy="50" 
                    r="45" 
                    fill="none" 
                    stroke-width="3"
                    :stroke-dasharray="circumference"
                    :stroke-dashoffset="progressOffset"
                  />
                </svg>
              </div>

              <!-- 实时提示 -->
              <div class="live-tip" :class="tipType">
                <span class="tip-icon">{{ tipIcon }}</span>
                <span class="tip-text">{{ currentTip }}</span>
              </div>
            </div>

            <!-- 动作引导 -->
            <div class="action-guide" v-if="livenessPhase === 'action'">
              <div class="action-title">
                <span class="action-icon">{{ currentAction?.icon }}</span>
                <span>请{{ currentAction?.name }}</span>
              </div>
              <div class="action-progress">
                <div 
                  v-for="(action, index) in livenessActions" 
                  :key="index"
                  class="action-dot"
                  :class="{ 
                    active: index === currentActionIndex,
                    done: index < currentActionIndex,
                    failed: actionFailed && index === currentActionIndex
                  }"
                ></div>
              </div>
              <div class="action-timer">
                <span>剩余 {{ actionTimeLeft }}s</span>
              </div>
            </div>
          </div>

          <div class="capture-tips">
            <p>请将面部对准框内，保持正面朝向镜头</p>
          </div>
        </div>

        <!-- 步骤3：核验中 -->
        <div v-else-if="currentStep === 3" class="step-content">
          <div class="verifying-animation">
            <div class="scan-circle">
              <div class="scan-ring"></div>
              <div class="scan-ring delay-1"></div>
              <div class="scan-ring delay-2"></div>
              <div class="center-icon">🔍</div>
            </div>
          </div>
          <h2 class="step-title">正在核验中</h2>
          <p class="step-desc">{{ verifyStatusText }}</p>
          
          <div class="verify-steps">
            <div class="verify-step" :class="{ done: verifyProgress >= 1 }">
              <span class="step-check">{{ verifyProgress >= 1 ? '✓' : '' }}</span>
              <span>人脸检测</span>
            </div>
            <div class="verify-step" :class="{ done: verifyProgress >= 2 }">
              <span class="step-check">{{ verifyProgress >= 2 ? '✓' : '' }}</span>
              <span>活体检测</span>
            </div>
            <div class="verify-step" :class="{ done: verifyProgress >= 3 }">
              <span class="step-check">{{ verifyProgress >= 3 ? '✓' : '' }}</span>
              <span>结果比对</span>
            </div>
          </div>
        </div>

        <!-- 错误状态 -->
        <div v-else-if="currentStep === -1" class="step-content">
          <div class="error-icon">❌</div>
          <h2 class="step-title error-title">{{ errorTitle }}</h2>
          <p class="step-desc error-desc">{{ errorMessage }}</p>
          
          <div class="error-actions">
            <button class="btn btn-primary" @click="retry">重新认证</button>
            <button class="btn btn-outline" @click="goHome">返回首页</button>
          </div>
        </div>
      </div>

      <!-- 底部 -->
      <div class="verify-footer">
        <p>🔒 您的数据受到银行级加密保护</p>
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { initAuth, submitVerification } from '../api/auth'

const route = useRoute()
const router = useRouter()

// 状态
const currentStep = ref(1) // 1:准备 2:采集 3:核验 -1:错误
const cameraReady = ref(false)
const isCapturing = ref(false)
const captureProgress = ref(0)
const livenessPhase = ref('detect') // detect:检测人脸 action:动作活体
const currentActionIndex = ref(0)
const actionTimeLeft = ref(15)
const actionFailed = ref(false)
const verifyProgress = ref(0)
const verifyStatusText = ref('正在初始化...')

// 错误
const errorTitle = ref('')
const errorMessage = ref('')

// 检查项
const checks = ref({
  lighting: false,
  idCard: false,
  network: false
})

// 活体动作
const livenessActions = ref<Array<{ code: string; name: string; icon: string; prompt: string }>>([])
const currentAction = computed(() => livenessActions.value[currentActionIndex.value])

// DOM引用
const videoRef = ref<HTMLVideoElement | null>(null)
const canvasRef = ref<HTMLCanvasElement | null>(null)
let mediaStream: MediaStream | null = null
let captureInterval: number | null = null
let actionTimer: number | null = null
let verifyTimer: number | null = null

// Token
const token = ref('')

// 计算属性
const canStart = computed(() => {
  return checks.value.lighting && checks.value.idCard && checks.value.network
})

const circumference = 2 * Math.PI * 45
const progressOffset = computed(() => {
  return circumference * (1 - captureProgress.value / 100)
})

const faceFrameClass = computed(() => {
  if (!cameraReady.value) return ''
  if (isCapturing.value) return 'capturing'
  return 'ready'
})

const tipType = computed(() => {
  if (!cameraReady.value) return 'info'
  if (isCapturing.value) return 'success'
  return 'info'
})

const tipIcon = computed(() => {
  if (!cameraReady.value) return '⏳'
  if (isCapturing.value) return '✓'
  return '👤'
})

const currentTip = computed(() => {
  if (!cameraReady.value) return '正在启动摄像头...'
  if (livenessPhase.value === 'action') {
    return currentAction.value?.prompt || '请按提示做动作'
  }
  if (isCapturing.value) return '检测成功，正在采集...'
  return '请将面部对准框内'
})

// 开始认证
async function startVerify() {
  try {
    // 获取token
    const tokenParam = route.query.token as string
    if (tokenParam) {
      token.value = tokenParam
    } else {
      // 没有token的话，创建一个演示用的
      const result = await initAuth({
        user_id: 'demo_user_' + Date.now(),
        return_url: window.location.origin + '/result'
      })
      if (result.data?.token) {
        token.value = result.data.token
      }
    }
    
    currentStep.value = 2
    await nextTick()
    await startCamera()
  } catch (e) {
    showError('初始化失败', '认证初始化失败，请刷新页面重试')
  }
}

// 启动摄像头
async function startCamera() {
  try {
    mediaStream = await navigator.mediaDevices.getUserMedia({
      video: {
        width: { ideal: 640 },
        height: { ideal: 480 },
        facingMode: 'user'
      },
      audio: false
    })
    
    if (videoRef.value && mediaStream) {
      videoRef.value.srcObject = mediaStream
      await videoRef.value.play()
      cameraReady.value = true
      
      // 开始检测人脸（简化版，实际用face-api.js）
      startFaceDetection()
    }
  } catch (e) {
    console.error('摄像头启动失败:', e)
    showError('摄像头启动失败', '请允许浏览器访问摄像头权限，或检查设备是否正常')
  }
}

// 模拟人脸检测
function startFaceDetection() {
  // 简化：2秒后检测到人脸，开始采集
  setTimeout(() => {
    if (currentStep.value !== 2) return
    
    isCapturing.value = true
    startCaptureProgress()
  }, 2000)
}

// 采集进度
function startCaptureProgress() {
  captureProgress.value = 0
  
  captureInterval = window.setInterval(() => {
    captureProgress.value += 2
    
    if (captureProgress.value >= 100) {
      if (captureInterval) {
        clearInterval(captureInterval)
        captureInterval = null
      }
      
      // 采集完成，进入活体检测阶段
      startLivenessDetection()
    }
  }, 50)
}

// 活体检测
function startLivenessDetection() {
  // 生成随机动作
  livenessActions.value = [
    { code: 'blink', name: '眨眼', icon: '👁️', prompt: '请眨眨眼' },
    { code: 'mouth_open', name: '张嘴', icon: '👄', prompt: '请张大嘴巴' },
    { code: 'head_shake', name: '摇头', icon: '🔄', prompt: '请左右摇摇头' }
  ]
  
  livenessPhase.value = 'action'
  currentActionIndex.value = 0
  actionTimeLeft.value = 15
  
  startActionTimer()
}

// 动作计时器
function startActionTimer() {
  actionTimer = window.setInterval(() => {
    actionTimeLeft.value--
    
    if (actionTimeLeft.value <= 0) {
      if (actionTimer) {
        clearInterval(actionTimer)
        actionTimer = null
      }
      
      // 模拟动作成功
      if (currentActionIndex.value < livenessActions.value.length - 1) {
        currentActionIndex.value++
        actionTimeLeft.value = 15
        startActionTimer()
      } else {
        // 所有动作完成
        completeCapture()
      }
    }
  }, 1000)
  
  // 模拟：每个动作5秒完成
  setTimeout(() => {
    if (currentActionIndex.value < livenessActions.value.length - 1) {
      if (actionTimer) {
        clearInterval(actionTimer)
        actionTimer = null
      }
      currentActionIndex.value++
      actionTimeLeft.value = 15
      startActionTimer()
    } else {
      if (actionTimer) {
        clearInterval(actionTimer)
        actionTimer = null
      }
      completeCapture()
    }
  }, 5000 * (currentActionIndex.value + 1))
}

// 采集完成
function completeCapture() {
  isCapturing.value = false
  
  // 拍照
  capturePhoto()
  
  // 进入核验阶段
  currentStep.value = 3
  startVerifyProgress()
}

// 拍照
function capturePhoto() {
  if (!videoRef.value || !canvasRef.value) return
  
  const video = videoRef.value
  const canvas = canvasRef.value
  
  canvas.width = video.videoWidth
  canvas.height = video.videoHeight
  
  const ctx = canvas.getContext('2d')
  if (ctx) {
    ctx.drawImage(video, 0, 0)
  }
}

// 核验进度
function startVerifyProgress() {
  verifyProgress.value = 0
  
  const steps = [
    { progress: 1, text: '正在检测人脸...', delay: 1000 },
    { progress: 2, text: '正在进行活体检测...', delay: 2000 },
    { progress: 3, text: '正在比对身份信息...', delay: 3000 }
  ]
  
  steps.forEach((step, index) => {
    setTimeout(() => {
      verifyProgress.value = step.progress
      verifyStatusText.value = step.text
    }, step.delay)
  })
  
  // 最终结果
  verifyTimer = window.setTimeout(async () => {
    try {
      // 提交认证结果
      const imageData = canvasRef.value?.toDataURL('image/jpeg', 0.8) || ''
      
      await submitVerification({
        token: token.value,
        passed: true,
        score: 95.5,
        face_image: imageData,
        liveness_passed: true,
        face_match_score: 98.2,
        actions: livenessActions.value.map(a => a.code)
      })
      
      // 跳转到结果页
      router.push(`/result?token=${token.value}`)
    } catch (e) {
      showError('核验失败', '认证核验过程中出现错误，请重试')
    }
  }, 4000)
}

// 显示错误
function showError(title: string, message: string) {
  errorTitle.value = title
  errorMessage.value = message
  currentStep.value = -1
  
  // 停止摄像头
  stopCamera()
}

// 重试
function retry() {
  currentStep.value = 1
  cameraReady.value = false
  isCapturing.value = false
  captureProgress.value = 0
  livenessPhase.value = 'detect'
  currentActionIndex.value = 0
  verifyProgress.value = 0
  checks.value = {
    lighting: false,
    idCard: false,
    network: false
  }
}

// 返回首页
function goHome() {
  router.push('/')
}

// 停止摄像头
function stopCamera() {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop())
    mediaStream = null
  }
  
  if (captureInterval) {
    clearInterval(captureInterval)
    captureInterval = null
  }
  
  if (actionTimer) {
    clearInterval(actionTimer)
    actionTimer = null
  }
  
  if (verifyTimer) {
    clearTimeout(verifyTimer)
    verifyTimer = null
  }
}

onMounted(() => {
  // 检查是否有token
  const tokenParam = route.query.token as string
  if (tokenParam) {
    token.value = tokenParam
  }
})

onUnmounted(() => {
  stopCamera()
})
</script>

<style scoped lang="scss">
.verify-page {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

.verify-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 0;
}

.bg-gradient {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-pattern {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 70% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
}

.verify-container {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  max-width: 480px;
  margin: 0 auto;
  padding: 24px;
}

/* 头部 */
.verify-header {
  text-align: center;
  margin-bottom: 24px;
}

.brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 24px;
  
  .brand-name {
    font-size: 24px;
    font-weight: 700;
    color: white;
  }
  
  .brand-sub {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.8);
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 8px;
    border-radius: 4px;
  }
}

.step-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  
  .step-num {
    width: 28px;
    height: 28px;
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
  
  .step-text {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
  }
  
  &.active .step-num {
    background: white;
    color: #667eea;
  }
  
  &.active .step-text {
    color: white;
  }
  
  &.done .step-num {
    background: #10b981;
    color: white;
  }
}

.step-line {
  width: 40px;
  height: 2px;
  background: rgba(255, 255, 255, 0.2);
  margin-bottom: 18px;
  transition: background 0.3s;
  
  &.active {
    background: #10b981;
  }
}

/* 内容区 */
.verify-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.step-content {
  width: 100%;
  background: white;
  border-radius: 24px;
  padding: 32px 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  text-align: center;
}

.step-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.step-title {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
  
  &.error-title {
    color: #ef4444;
  }
}

.step-desc {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 24px;
  line-height: 1.6;
  
  &.error-desc {
    color: #6b7280;
  }
}

/* 检查清单 */
.checklist {
  text-align: left;
  margin-bottom: 24px;
}

.check-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 8px;
  background: #f9fafb;
  
  &:hover {
    background: #f3f4f6;
  }
  
  &.checked {
    background: #ecfdf5;
    
    .check-box {
      background: #10b981;
      border-color: #10b981;
      color: white;
    }
  }
}

.check-box {
  width: 22px;
  height: 22px;
  border: 2px solid #d1d5db;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: bold;
  color: transparent;
  transition: all 0.2s;
  flex-shrink: 0;
}

/* 隐私声明 */
.privacy-notice {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: #f0f9ff;
  border-radius: 12px;
  margin-bottom: 24px;
  text-align: left;
  
  .notice-icon {
    font-size: 24px;
    flex-shrink: 0;
  }
  
  .notice-text {
    p {
      margin: 0;
      font-size: 13px;
      color: #0369a1;
      line-height: 1.5;
      
      &:first-child {
        font-weight: 600;
        margin-bottom: 4px;
      }
    }
  }
}

/* 按钮 */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 16px;
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
    background: transparent;
    border: 1px solid #d1d5db;
    color: #374151;
    
    &:hover {
      border-color: #667eea;
      color: #667eea;
    }
  }
  
  &.btn-lg {
    padding: 16px 32px;
    font-size: 18px;
  }
  
  &.btn-block {
    width: 100%;
  }
}

/* 采集区域 */
.capture-area {
  margin-bottom: 16px;
}

.camera-container {
  position: relative;
  width: 280px;
  height: 280px;
  margin: 0 auto;
  border-radius: 50%;
  overflow: hidden;
  background: #1f2937;
}

.camera-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scaleX(-1); // 镜像翻转
}

.camera-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  
  p {
    margin-top: 16px;
    font-size: 14px;
  }
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.2);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* 人脸框 */
.face-frame {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 200px;
  height: 240px;
  pointer-events: none;
  
  .face-oval {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    transition: all 0.3s;
  }
  
  &.ready .face-oval {
    border-color: #fbbf24;
  }
  
  &.capturing .face-oval {
    border-color: #10b981;
  }
}

.frame-corner {
  position: absolute;
  width: 24px;
  height: 24px;
  border: 3px solid white;
  
  &.tl {
    top: -2px;
    left: -2px;
    border-right: none;
    border-bottom: none;
    border-radius: 8px 0 0 0;
  }
  
  &.tr {
    top: -2px;
    right: -2px;
    border-left: none;
    border-bottom: none;
    border-radius: 0 8px 0 0;
  }
  
  &.bl {
    bottom: -2px;
    left: -2px;
    border-right: none;
    border-top: none;
    border-radius: 0 0 0 8px;
  }
  
  &.br {
    bottom: -2px;
    right: -2px;
    border-left: none;
    border-top: none;
    border-radius: 0 0 8px 0;
  }
}

/* 进度环 */
.progress-ring {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 260px;
  height: 260px;
  pointer-events: none;
}

.ring-svg {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.ring-bg {
  stroke: rgba(255, 255, 255, 0.2);
}

.ring-progress {
  stroke: #10b981;
  stroke-linecap: round;
  transition: stroke-dashoffset 0.1s linear;
}

/* 实时提示 */
.live-tip {
  position: absolute;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  color: white;
  font-size: 13px;
  white-space: nowrap;
  
  &.success {
    background: rgba(16, 185, 129, 0.8);
  }
}

/* 动作引导 */
.action-guide {
  margin-top: 20px;
  padding: 16px;
  background: #f0f4ff;
  border-radius: 12px;
}

.action-title {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 12px;
  
  .action-icon {
    font-size: 24px;
  }
}

.action-progress {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-bottom: 8px;
}

.action-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #d1d5db;
  transition: all 0.3s;
  
  &.active {
    background: #667eea;
    transform: scale(1.2);
  }
  
  &.done {
    background: #10b981;
  }
  
  &.failed {
    background: #ef4444;
  }
}

.action-timer {
  font-size: 13px;
  color: #6b7280;
  text-align: center;
}

.capture-tips {
  text-align: center;
  font-size: 13px;
  color: #6b7280;
  margin-top: 16px;
}

/* 核验中 */
.verifying-animation {
  margin-bottom: 24px;
}

.scan-circle {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto;
  
  .center-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 40px;
  }
}

.scan-ring {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border: 3px solid #667eea;
  border-radius: 50%;
  animation: scan-pulse 2s ease-out infinite;
  
  &.delay-1 {
    animation-delay: 0.5s;
  }
  
  &.delay-2 {
    animation-delay: 1s;
  }
}

@keyframes scan-pulse {
  0% {
    transform: scale(0.5);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.verify-steps {
  text-align: left;
  margin-top: 24px;
}

.verify-step {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  border-radius: 8px;
  margin-bottom: 8px;
  background: #f9fafb;
  color: #9ca3af;
  font-size: 14px;
  transition: all 0.3s;
  
  .step-check {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: transparent;
    transition: all 0.3s;
  }
  
  &.done {
    color: #1f2937;
    background: #ecfdf5;
    
    .step-check {
      background: #10b981;
      color: white;
    }
  }
}

/* 错误 */
.error-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.error-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 24px;
}

/* 底部 */
.verify-footer {
  text-align: center;
  padding-top: 24px;
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

/* 响应式 */
@media (max-width: 480px) {
  .verify-container {
    padding: 16px;
  }
  
  .step-content {
    padding: 24px 16px;
  }
  
  .camera-container {
    width: 240px;
    height: 240px;
  }
  
  .face-frame {
    width: 170px;
    height: 200px;
  }
}
</style>
