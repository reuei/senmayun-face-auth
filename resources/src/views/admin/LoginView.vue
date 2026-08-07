<template>
  <div class="login-page">
    <div class="login-bg">
      <div class="bg-gradient"></div>
      <div class="bg-pattern"></div>
    </div>

    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="brand">
            <span class="brand-name">森码云</span>
            <span class="brand-sub">管理后台</span>
          </div>
          <h1 class="login-title">欢迎回来</h1>
          <p class="login-desc">请登录您的管理员账号</p>
        </div>

        <form class="login-form" @submit.prevent="handleLogin">
          <div class="form-group">
            <label class="form-label">用户名</label>
            <div class="input-wrapper">
              <span class="input-icon">👤</span>
              <input 
                type="text" 
                v-model="form.username" 
                class="form-input"
                placeholder="请输入用户名"
                autocomplete="username"
              />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">密码</label>
            <div class="input-wrapper">
              <span class="input-icon">🔒</span>
              <input 
                :type="showPassword ? 'text' : 'password'" 
                v-model="form.password" 
                class="form-input"
                placeholder="请输入密码"
                autocomplete="current-password"
              />
              <button 
                type="button" 
                class="password-toggle"
                @click="showPassword = !showPassword"
              >
                {{ showPassword ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <div class="form-options">
            <label class="checkbox">
              <input type="checkbox" v-model="form.remember" />
              <span class="checkbox-custom"></span>
              <span>记住我</span>
            </label>
            <a href="#" class="forgot-link">忘记密码？</a>
          </div>

          <button 
            type="submit" 
            class="btn btn-primary btn-block btn-lg"
            :disabled="loading"
          >
            <span v-if="loading" class="loading-dots">
              <span></span><span></span><span></span>
            </span>
            <span v-else>登 录</span>
          </button>
        </form>

        <div class="login-footer">
          <p>🔒 您的数据受到银行级加密保护</p>
        </div>
      </div>

      <div class="login-side">
        <div class="side-content">
          <div class="side-icon">🛡️</div>
          <h2>安全的管理后台</h2>
          <ul class="side-features">
            <li>✓ 实时数据监控</li>
            <li>✓ 多通道管理</li>
            <li>✓ 认证记录查询</li>
            <li>✓ 系统配置管理</li>
            <li>✓ 操作日志审计</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const form = ref({
  username: '',
  password: '',
  remember: false
})

const showPassword = ref(false)
const loading = ref(false)

async function handleLogin() {
  if (!form.value.username || !form.value.password) {
    alert('请输入用户名和密码')
    return
  }
  
  loading.value = true
  
  try {
    const res = await axios.post('/api/admin/login', {
      username: form.value.username,
      password: form.value.password
    })
    
    if (res.data.code === 200) {
      // 登录成功
      router.push('/admin')
    } else {
      alert(res.data.message || '登录失败')
    }
  } catch (e: any) {
    alert(e.response?.data?.message || '登录失败，请重试')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped lang="scss">
.login-page {
  min-height: 100vh;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.login-bg {
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
    radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
}

.login-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 900px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
  margin: 24px;
}

/* 登录卡片 */
.login-card {
  background: white;
  padding: 48px 40px;
}

.login-header {
  text-align: center;
  margin-bottom: 32px;
}

.brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 24px;
  
  .brand-name {
    font-size: 22px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  
  .brand-sub {
    font-size: 12px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 4px;
  }
}

.login-title {
  font-size: 26px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.login-desc {
  font-size: 14px;
  color: #6b7280;
}

/* 表单 */
.login-form {
  margin-bottom: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  font-size: 16px;
  opacity: 0.5;
}

.form-input {
  width: 100%;
  padding: 12px 14px 12px 42px;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  font-size: 15px;
  transition: all 0.2s;
  box-sizing: border-box;
  
  &:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }
}

.password-toggle {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  font-size: 16px;
  cursor: pointer;
  padding: 4px;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.checkbox {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
  color: #4b5563;
  
  input {
    display: none;
    
    &:checked + .checkbox-custom {
      background: #667eea;
      border-color: #667eea;
      
      &::after {
        opacity: 1;
      }
    }
  }
}

.checkbox-custom {
  width: 18px;
  height: 18px;
  border: 2px solid #d1d5db;
  border-radius: 4px;
  position: relative;
  transition: all 0.2s;
  
  &::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.2s;
  }
}

.forgot-link {
  font-size: 14px;
  color: #667eea;
  text-decoration: none;
  
  &:hover {
    text-decoration: underline;
  }
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 24px;
  border-radius: 10px;
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
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }
  
  &.btn-block {
    width: 100%;
  }
  
  &.btn-lg {
    padding: 14px 24px;
    font-size: 16px;
  }
}

.loading-dots {
  display: flex;
  gap: 4px;
  
  span {
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    animation: bounce 1.4s infinite ease-in-out both;
    
    &:nth-child(1) { animation-delay: -0.32s; }
    &:nth-child(2) { animation-delay: -0.16s; }
  }
}

@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1);
  }
}

.login-footer {
  text-align: center;
  font-size: 12px;
  color: #9ca3af;
}

/* 侧边 */
.login-side {
  background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px;
}

.side-content {
  text-align: center;
  color: white;
}

.side-icon {
  font-size: 80px;
  margin-bottom: 24px;
}

.side-content h2 {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 24px;
}

.side-features {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
  display: inline-block;
  
  li {
    padding: 8px 0;
    font-size: 15px;
    opacity: 0.9;
  }
}

@media (max-width: 768px) {
  .login-container {
    grid-template-columns: 1fr;
  }
  
  .login-side {
    display: none;
  }
  
  .login-card {
    padding: 32px 24px;
  }
}
</style>
