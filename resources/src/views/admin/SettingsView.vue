<template>
  <div class="settings-page">
    <div class="settings-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.key"
        class="tab-btn"
        :class="{ active: activeTab === tab.key }"
        @click="activeTab = tab.key"
      >
        <span class="tab-icon">{{ tab.icon }}</span>
        {{ tab.label }}
      </button>
    </div>

    <!-- 基本设置 -->
    <div class="settings-card" v-show="activeTab === 'basic'">
      <div class="card-header">
        <h3>基本设置</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">站点名称</label>
          <input type="text" v-model="basic.site_name" class="form-input" />
        </div>

        <div class="form-group">
          <label class="form-label">站点描述</label>
          <textarea v-model="basic.site_desc" class="form-textarea" rows="3"></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">默认语言</label>
            <select v-model="basic.default_lang" class="form-select">
              <option value="zh-CN">简体中文</option>
              <option value="en">English</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">时区</label>
            <select v-model="basic.timezone" class="form-select">
              <option value="Asia/Shanghai">Asia/Shanghai (UTC+8)</option>
              <option value="UTC">UTC</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">ICP备案号</label>
          <input type="text" v-model="basic.icp" class="form-input" placeholder="如：京ICP备12345678号" />
        </div>
      </div>
    </div>

    <!-- 认证设置 -->
    <div class="settings-card" v-show="activeTab === 'auth'">
      <div class="card-header">
        <h3>认证设置</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">匹配阈值</label>
            <input type="number" v-model="auth.match_threshold" class="form-input" />
            <p class="form-hint">相似度高于此值视为匹配成功（0-100）</p>
          </div>
          <div class="form-group">
            <label class="form-label">Token有效期(秒)</label>
            <input type="number" v-model="auth.token_ttl" class="form-input" />
            <p class="form-hint">认证Token的有效时长</p>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">活体动作数量</label>
            <input type="number" v-model="auth.liveness_actions" class="form-input" />
            <p class="form-hint">每次活体检测需要完成的动作数</p>
          </div>
          <div class="form-group">
            <label class="form-label">动作超时(秒)</label>
            <input type="number" v-model="auth.action_timeout" class="form-input" />
            <p class="form-hint">单个动作的完成时限</p>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <input type="checkbox" v-model="auth.require_liveness" />
            强制活体检测
          </label>
          <p class="form-hint">开启后所有人脸比对都必须通过活体检测</p>
        </div>

        <div class="form-group">
          <label class="form-label">
            <input type="checkbox" v-model="auth.save_face_image" />
            保存人脸图片
          </label>
          <p class="form-hint">开启后会保存采集到的人脸图片（加密存储）</p>
        </div>

        <div class="form-group">
          <label class="form-label">图片保留天数</label>
          <input type="number" v-model="auth.image_retention_days" class="form-input" />
          <p class="form-hint">人脸图片自动删除的天数，0表示永久保留</p>
        </div>
      </div>
    </div>

    <!-- 安全设置 -->
    <div class="settings-card" v-show="activeTab === 'security'">
      <div class="card-header">
        <h3>安全设置</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">
            <input type="checkbox" v-model="security.rate_limit_enabled" />
            启用速率限制
          </label>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">每分钟请求上限</label>
            <input type="number" v-model="security.rate_limit_per_minute" class="form-input" :disabled="!security.rate_limit_enabled" />
          </div>
          <div class="form-group">
            <label class="form-label">每小时请求上限</label>
            <input type="number" v-model="security.rate_limit_per_hour" class="form-input" :disabled="!security.rate_limit_enabled" />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <input type="checkbox" v-model="security.ip_whitelist_enabled" />
            启用IP白名单
          </label>
          <p class="form-hint">只允许白名单内的IP访问API</p>
        </div>

        <div class="form-group" v-if="security.ip_whitelist_enabled">
          <label class="form-label">IP白名单</label>
          <textarea v-model="security.ip_whitelist" class="form-textarea" rows="5" placeholder="每行一个IP或IP段&#10;例如：&#10;192.168.1.1&#10;10.0.0.0/24"></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">
            <input type="checkbox" v-model="security.encrypt_data" />
            敏感数据加密存储
          </label>
          <p class="form-hint">使用AES-256加密存储身份证号等敏感信息</p>
        </div>

        <div class="form-group">
          <label class="form-label">
            <input type="checkbox" v-model="security.log_enabled" />
            记录操作日志
          </label>
        </div>
      </div>
    </div>

    <!-- 通知设置 -->
    <div class="settings-card" v-show="activeTab === 'notify'">
      <div class="card-header">
        <h3>通知设置</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">
            <input type="checkbox" v-model="notify.email_enabled" />
            邮件通知
          </label>
        </div>

        <div class="form-group" v-if="notify.email_enabled">
          <label class="form-label">SMTP服务器</label>
          <input type="text" v-model="notify.smtp_host" class="form-input" placeholder="smtp.example.com" />
        </div>

        <div class="form-row" v-if="notify.email_enabled">
          <div class="form-group">
            <label class="form-label">端口</label>
            <input type="number" v-model="notify.smtp_port" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">加密方式</label>
            <select v-model="notify.smtp_secure" class="form-select">
              <option value="ssl">SSL</option>
              <option value="tls">TLS</option>
              <option value="none">无</option>
            </select>
          </div>
        </div>

        <div class="form-group" v-if="notify.email_enabled">
          <label class="form-label">发件人邮箱</label>
          <input type="email" v-model="notify.smtp_user" class="form-input" />
        </div>

        <div class="form-group" v-if="notify.email_enabled">
          <label class="form-label">发件人密码</label>
          <input type="password" v-model="notify.smtp_pass" class="form-input" />
        </div>

        <div class="form-group">
          <label class="form-label">管理员通知邮箱</label>
          <input type="email" v-model="notify.admin_email" class="form-input" placeholder="多个邮箱用逗号分隔" />
          <p class="form-hint">异常告警、系统通知将发送到此邮箱</p>
        </div>
      </div>
    </div>

    <!-- 保存按钮 -->
    <div class="save-bar">
      <button class="btn btn-outline" @click="resetSettings">重置</button>
      <button class="btn btn-primary" @click="saveSettings">保存设置</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'

const activeTab = ref('basic')

const tabs = [
  { key: 'basic', label: '基本设置', icon: '⚙️' },
  { key: 'auth', label: '认证设置', icon: '🔐' },
  { key: 'security', label: '安全设置', icon: '🛡️' },
  { key: 'notify', label: '通知设置', icon: '🔔' }
]

const basic = reactive({
  site_name: '森码云实人认证',
  site_desc: '安全、可靠、易用的实人认证服务',
  default_lang: 'zh-CN',
  timezone: 'Asia/Shanghai',
  icp: ''
})

const auth = reactive({
  match_threshold: 80,
  token_ttl: 300,
  liveness_actions: 2,
  action_timeout: 15,
  require_liveness: true,
  save_face_image: true,
  image_retention_days: 90
})

const security = reactive({
  rate_limit_enabled: true,
  rate_limit_per_minute: 60,
  rate_limit_per_hour: 1000,
  ip_whitelist_enabled: false,
  ip_whitelist: '',
  encrypt_data: true,
  log_enabled: true
})

const notify = reactive({
  email_enabled: false,
  smtp_host: '',
  smtp_port: 465,
  smtp_secure: 'ssl',
  smtp_user: '',
  smtp_pass: '',
  admin_email: ''
})

function resetSettings() {
  if (confirm('确定要重置当前设置吗？')) {
    // 重置逻辑
  }
}

function saveSettings() {
  alert('设置保存成功！')
}
</script>

<style scoped lang="scss">
.settings-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* 标签页 */
.settings-tabs {
  display: flex;
  gap: 8px;
  background: white;
  padding: 8px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border: none;
  background: transparent;
  border-radius: 8px;
  font-size: 14px;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: #f9fafb;
  }
  
  &.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  
  .tab-icon {
    font-size: 18px;
  }
}

/* 设置卡片 */
.settings-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.card-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f3f4f6;
  
  h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
  }
}

.card-body {
  padding: 24px;
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

/* 表单 */
.form-group {
  margin-bottom: 20px;
  
  &:last-child {
    margin-bottom: 0;
  }
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
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

.form-textarea {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  font-size: 14px;
  box-sizing: border-box;
  resize: vertical;
  font-family: inherit;
  
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

@media (max-width: 640px) {
  .settings-tabs {
    flex-direction: column;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
