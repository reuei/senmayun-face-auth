<template>
  <div class="not-found-page">
    <div class="nf-bg"></div>
    <div class="nf-container">
      <div class="nf-content">
        <div class="nf-code">404</div>
        <h1 class="nf-title">页面未找到</h1>
        <p class="nf-desc">抱歉，您访问的页面不存在或已被移除</p>
        <div class="nf-actions">
          <a href="/" class="btn btn-primary">返回首页</a>
          <a href="/verify" class="btn btn-outline">开始认证</a>
        </div>
      </div>
      <div class="nf-illustration">
        <div class="illustration-box">
          <div class="floating-icon">🔍</div>
          <div class="floating-icon delay-1">📄</div>
          <div class="floating-icon delay-2">❓</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// 404页面
</script>

<style scoped lang="scss">
.not-found-page {
  min-height: 100vh;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.nf-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.nf-container {
  position: relative;
  z-index: 1;
  max-width: 900px;
  width: 100%;
  padding: 24px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: center;
}

.nf-content {
  color: white;
}

.nf-code {
  font-size: 120px;
  font-weight: 900;
  line-height: 1;
  margin-bottom: 16px;
  background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.6) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.nf-title {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 12px;
}

.nf-desc {
  font-size: 16px;
  opacity: 0.8;
  margin-bottom: 32px;
  line-height: 1.6;
}

.nf-actions {
  display: flex;
  gap: 12px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 24px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  
  &.btn-primary {
    background: white;
    color: #667eea;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
  }
  
  &.btn-outline {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.5);
    color: white;
    
    &:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: white;
    }
  }
}

/* 插画 */
.nf-illustration {
  display: flex;
  justify-content: center;
}

.illustration-box {
  position: relative;
  width: 300px;
  height: 300px;
}

.floating-icon {
  position: absolute;
  font-size: 64px;
  animation: float 3s ease-in-out infinite;
  
  &:nth-child(1) {
    top: 20%;
    left: 20%;
  }
  
  &.delay-1 {
    top: 50%;
    right: 15%;
    animation-delay: 0.5s;
    font-size: 56px;
  }
  
  &.delay-2 {
    bottom: 20%;
    left: 30%;
    animation-delay: 1s;
    font-size: 48px;
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(5deg);
  }
}

@media (max-width: 768px) {
  .nf-container {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .nf-code {
    font-size: 80px;
  }
  
  .nf-actions {
    justify-content: center;
  }
  
  .nf-illustration {
    display: none;
  }
}
</style>
