<template>
  <div class="about-page">
    <nav class="navbar">
      <div class="nav-container">
        <div class="nav-logo">
          <span class="logo-text">森码云</span>
          <span class="logo-sub">实人认证</span>
        </div>
        <div class="nav-links">
          <a href="/" class="nav-link">首页</a>
          <a href="/pricing" class="nav-link">定价</a>
          <a href="/docs" class="nav-link">API文档</a>
          <a href="/about" class="nav-link active">关于</a>
        </div>
        <div class="nav-actions">
          <a href="/verify" class="btn btn-primary">开始认证</a>
        </div>
      </div>
    </nav>

    <section class="about-hero">
      <div class="hero-content">
        <span class="hero-tag">关于我们</span>
        <h1 class="hero-title">让身份验证更简单、更安全</h1>
        <p class="hero-desc">
          森码云致力于为企业提供安全、可靠、易用的实人认证解决方案，
          帮助企业快速构建身份核验能力，降低运营风险。
        </p>
      </div>
    </section>

    <section class="mission-section">
      <div class="section-container">
        <div class="mission-grid">
          <div class="mission-card">
            <div class="mission-icon">🎯</div>
            <h3>我们的使命</h3>
            <p>用技术让身份验证变得简单、安全、可信，助力每一家企业数字化转型。</p>
          </div>
          <div class="mission-card">
            <div class="mission-icon">💡</div>
            <h3>我们的愿景</h3>
            <p>成为最值得信赖的实人认证服务商，让每个人都能安全地享受数字服务。</p>
          </div>
          <div class="mission-card">
            <div class="mission-icon">❤️</div>
            <h3>我们的价值观</h3>
            <p>安全第一、用户至上、持续创新、开放合作。</p>
          </div>
        </div>
      </div>
    </section>

    <section class="features-section">
      <div class="section-container">
        <div class="section-header">
          <span class="section-tag">为什么选择我们</span>
          <h2 class="section-title">专业、可靠、安全</h2>
        </div>
        
        <div class="features-grid">
          <div class="feature-item" v-for="feature in features" :key="feature.title">
            <div class="feature-icon" :style="{ background: feature.color }">
              <span>{{ feature.icon }}</span>
            </div>
            <h3 class="feature-title">{{ feature.title }}</h3>
            <p class="feature-desc">{{ feature.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="stats-section">
      <div class="section-container">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-number">99.99%</div>
            <div class="stat-label">服务可用性</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">99.8%</div>
            <div class="stat-label">识别准确率</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">500ms</div>
            <div class="stat-label">平均响应时间</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">5+</div>
            <div class="stat-label">认证通道</div>
          </div>
        </div>
      </div>
    </section>

    <section class="contact-section">
      <div class="section-container">
        <div class="contact-card">
          <div class="contact-text">
            <h2>联系我们</h2>
            <p>有任何问题或合作意向，欢迎随时联系我们</p>
          </div>
          <div class="contact-info">
            <div class="contact-item">
              <span class="contact-icon">📧</span>
              <div>
                <div class="contact-label">商务合作</div>
                <div class="contact-value">sales@builds.codes</div>
              </div>
            </div>
            <div class="contact-item">
              <span class="contact-icon">🔧</span>
              <div>
                <div class="contact-label">技术支持</div>
                <div class="contact-value">support@builds.codes</div>
              </div>
            </div>
            <div class="contact-item">
              <span class="contact-icon">🌐</span>
              <div>
                <div class="contact-label">官方网站</div>
                <div class="contact-value">face.builds.codes</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div class="footer-bottom">
        <p>© 2026 森码云. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
const features = [
  {
    icon: '🔒',
    title: '安全可靠',
    desc: '银行级加密技术，数据全程加密存储传输，符合等保2.0三级要求。',
    color: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
  },
  {
    icon: '⚡',
    title: '高性能',
    desc: '毫秒级响应速度，支持高并发访问，多通道负载均衡保障稳定。',
    color: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
  },
  {
    icon: '🔌',
    title: '易集成',
    desc: '标准RESTful API，完善的SDK和文档，最快10分钟完成接入。',
    color: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'
  },
  {
    icon: '💰',
    title: '低成本',
    desc: '按需付费，无最低消费，免费额度充足，大幅降低运营成本。',
    color: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'
  },
  {
    icon: '📊',
    title: '可视化',
    desc: '完善的数据统计和分析功能，实时监控认证情况，数据一目了然。',
    color: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
  },
  {
    icon: '🛡️',
    title: '合规性',
    desc: '严格遵守数据安全法规，支持数据本地化，满足各行业合规要求。',
    color: 'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)'
  }
]
</script>

<style scoped lang="scss">
.about-page {
  min-height: 100vh;
  background: #fff;
}

.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.nav-logo {
  display: flex;
  align-items: center;
  gap: 8px;
  
  .logo-text {
    font-size: 20px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  
  .logo-sub {
    font-size: 12px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 4px;
  }
}

.nav-links {
  display: flex;
  gap: 32px;
  
  .nav-link {
    color: #4b5563;
    font-size: 14px;
    text-decoration: none;
    transition: color 0.2s;
    
    &:hover, &.active {
      color: #667eea;
    }
  }
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  
  &.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    
    &:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
  }
}

/* Hero */
.about-hero {
  padding: 120px 24px 80px;
  text-align: center;
  background: linear-gradient(180deg, #f0f4ff 0%, #ffffff 100%);
}

.hero-content {
  max-width: 700px;
  margin: 0 auto;
}

.hero-tag {
  display: inline-block;
  padding: 6px 16px;
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 16px;
}

.hero-title {
  font-size: 42px;
  font-weight: 800;
  color: #1f2937;
  margin-bottom: 16px;
  line-height: 1.3;
}

.hero-desc {
  font-size: 18px;
  color: #6b7280;
  line-height: 1.8;
}

/* 通用section */
.section-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

.section-header {
  text-align: center;
  margin-bottom: 48px;
}

.section-tag {
  display: inline-block;
  padding: 6px 16px;
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 12px;
}

.section-title {
  font-size: 36px;
  font-weight: 700;
  color: #1f2937;
}

/* 使命 */
.mission-section {
  padding: 80px 0;
}

.mission-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.mission-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 32px;
  text-align: center;
  transition: all 0.3s;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
  }
}

.mission-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.mission-card h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 12px;
}

.mission-card p {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.6;
}

/* 特性 */
.features-section {
  padding: 80px 0;
  background: #f9fafb;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.feature-item {
  background: white;
  border-radius: 16px;
  padding: 32px;
  transition: all 0.3s;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
  }
}

.feature-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  margin-bottom: 20px;
}

.feature-title {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 12px;
}

.feature-desc {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.6;
}

/* 数据统计 */
.stats-section {
  padding: 80px 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  text-align: center;
}

.stat-item {
  color: white;
  
  .stat-number {
    font-size: 42px;
    font-weight: 800;
    margin-bottom: 8px;
  }
  
  .stat-label {
    font-size: 14px;
    opacity: 0.8;
  }
}

/* 联系我们 */
.contact-section {
  padding: 80px 0;
}

.contact-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 24px;
  padding: 48px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.contact-text {
  h2 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 12px;
  }
  
  p {
    font-size: 16px;
    color: #6b7280;
    line-height: 1.6;
  }
}

.contact-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 12px;
}

.contact-icon {
  font-size: 28px;
}

.contact-label {
  font-size: 13px;
  color: #6b7280;
  margin-bottom: 2px;
}

.contact-value {
  font-size: 15px;
  font-weight: 500;
  color: #1f2937;
}

.footer {
  background: #f9fafb;
  padding: 24px;
  border-top: 1px solid #e5e7eb;
}

.footer-bottom {
  max-width: 1200px;
  margin: 0 auto;
  text-align: center;
  font-size: 14px;
  color: #6b7280;
}

@media (max-width: 768px) {
  .nav-links {
    display: none;
  }
  
  .hero-title {
    font-size: 32px;
  }
  
  .mission-grid,
  .features-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .contact-card {
    grid-template-columns: 1fr;
    padding: 32px;
  }
}
</style>
