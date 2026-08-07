<template>
  <div class="home-page">
    <!-- 导航栏 -->
    <nav class="navbar">
      <div class="nav-container">
        <div class="nav-logo">
          <img src="/assets/svg/logo.svg" alt="森码云" class="logo-img" @error="handleLogoError" />
          <span class="logo-text">森码云</span>
          <span class="logo-sub">实人认证</span>
        </div>
        <div class="nav-links">
          <a href="#features" class="nav-link">功能特性</a>
          <a href="#process" class="nav-link">认证流程</a>
          <a href="#pricing" class="nav-link">定价方案</a>
          <a href="#faq" class="nav-link">常见问题</a>
          <a href="/docs" class="nav-link">API文档</a>
        </div>
        <div class="nav-actions">
          <a href="/admin/login" class="btn btn-ghost">管理后台</a>
          <a href="/verify" class="btn btn-primary">开始认证</a>
        </div>
      </div>
    </nav>

    <!-- Hero区域 -->
    <section class="hero">
      <div class="hero-bg">
        <div class="hero-gradient"></div>
        <div class="hero-pattern"></div>
      </div>
      <div class="hero-content">
        <div class="hero-text">
          <div class="hero-badge">
            <span class="badge-dot"></span>
            <span>多通道冗余 · 99.99% 可用性</span>
          </div>
          <h1 class="hero-title">
            安全可靠的<br />
            <span class="text-gradient">实人认证</span>解决方案
          </h1>
          <p class="hero-desc">
            基于AI人脸识别技术，提供活体检测、人脸比对、身份证核验等全流程实人认证服务。
            多通道冗余架构，保障业务稳定运行，适用于金融、社交、电商等多种场景。
          </p>
          <div class="hero-actions">
            <a href="/verify" class="btn btn-primary btn-lg">
              立即体验
              <i class="icon-arrow-right"></i>
            </a>
            <a href="#features" class="btn btn-outline btn-lg">
              了解更多
            </a>
          </div>
          <div class="hero-stats">
            <div class="stat-item">
              <div class="stat-number">99.8%</div>
              <div class="stat-label">识别准确率</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
              <div class="stat-number">500ms</div>
              <div class="stat-label">平均响应</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
              <div class="stat-number">5+</div>
              <div class="stat-label">认证通道</div>
            </div>
          </div>
        </div>
        <div class="hero-visual">
          <div class="hero-card">
            <div class="card-header">
              <div class="card-dots">
                <span class="dot red"></span>
                <span class="dot yellow"></span>
                <span class="dot green"></span>
              </div>
              <span class="card-title">实人认证</span>
            </div>
            <div class="card-body">
              <div class="face-scan">
                <div class="face-placeholder">
                  <div class="face-icon">👤</div>
                  <div class="scan-line"></div>
                </div>
                <div class="scan-status">
                  <div class="status-dot scanning"></div>
                  <span>正在检测人脸...</span>
                </div>
              </div>
              <div class="result-preview">
                <div class="result-row">
                  <span class="result-label">活体检测</span>
                  <span class="result-value success">通过 ✓</span>
                </div>
                <div class="result-row">
                  <span class="result-label">人脸比对</span>
                  <span class="result-value success">98.5%</span>
                </div>
                <div class="result-row">
                  <span class="result-label">认证结果</span>
                  <span class="result-value success">已通过</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 功能特性 -->
    <section id="features" class="features">
      <div class="section-header">
        <span class="section-tag">核心功能</span>
        <h2 class="section-title">全方位实人认证能力</h2>
        <p class="section-desc">覆盖从人脸采集到结果核验的全流程，满足各类业务场景需求</p>
      </div>
      <div class="features-grid">
        <div class="feature-card" v-for="feature in features" :key="feature.title">
          <div class="feature-icon" :style="{ background: feature.bgColor }">
            <span>{{ feature.icon }}</span>
          </div>
          <h3 class="feature-title">{{ feature.title }}</h3>
          <p class="feature-desc">{{ feature.desc }}</p>
          <ul class="feature-list">
            <li v-for="item in feature.items" :key="item">{{ item }}</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- 认证流程 -->
    <section id="process" class="process">
      <div class="section-header">
        <span class="section-tag">使用流程</span>
        <h2 class="section-title">三步完成实人认证</h2>
        <p class="section-desc">简单快捷的认证流程，用户无需下载APP，H5页面即可完成</p>
      </div>
      <div class="process-steps">
        <div class="step-item" v-for="(step, index) in steps" :key="index">
          <div class="step-number">{{ index + 1 }}</div>
          <div class="step-content">
            <h3 class="step-title">{{ step.title }}</h3>
            <p class="step-desc">{{ step.desc }}</p>
          </div>
          <div class="step-arrow" v-if="index < steps.length - 1">→</div>
        </div>
      </div>
    </section>

    <!-- 多通道架构 -->
    <section class="channels">
      <div class="channels-content">
        <div class="channels-text">
          <span class="section-tag">多通道冗余</span>
          <h2 class="section-title">多通道架构，保障99.99%可用性</h2>
          <p class="section-desc">
            同时接入多家主流人脸识别服务商，任一通道故障自动切换，
            确保您的业务不受单一服务商影响。
          </p>
          <div class="channel-list">
            <div class="channel-item" v-for="channel in channels" :key="channel.name">
              <div class="channel-icon">{{ channel.icon }}</div>
              <div class="channel-info">
                <div class="channel-name">{{ channel.name }}</div>
                <div class="channel-desc">{{ channel.desc }}</div>
              </div>
              <div class="channel-status online">
                <span class="status-dot"></span>
                在线
              </div>
            </div>
          </div>
        </div>
        <div class="channels-visual">
          <div class="channel-diagram">
            <div class="diagram-top">
              <div class="diagram-node primary">您的业务系统</div>
            </div>
            <div class="diagram-arrows">
              <div class="arrow-line"></div>
              <div class="arrow-line"></div>
              <div class="arrow-line"></div>
              <div class="arrow-line"></div>
              <div class="arrow-line"></div>
            </div>
            <div class="diagram-middle">
              <div class="diagram-node gateway">
                <span>森码云</span>
                <span class="node-sub">智能路由网关</span>
              </div>
            </div>
            <div class="diagram-arrows">
              <div class="arrow-line"></div>
              <div class="arrow-line"></div>
              <div class="arrow-line"></div>
              <div class="arrow-line"></div>
              <div class="arrow-line"></div>
            </div>
            <div class="diagram-bottom">
              <div class="diagram-node channel">腾讯云慧眼</div>
              <div class="diagram-node channel">百度AI</div>
              <div class="diagram-node channel">Azure</div>
              <div class="diagram-node channel">Face++</div>
              <div class="diagram-node channel">自研算法</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 定价方案 -->
    <section id="pricing" class="pricing">
      <div class="section-header">
        <span class="section-tag">定价方案</span>
        <h2 class="section-title">灵活透明的定价</h2>
        <p class="section-desc">按需选择，按量计费，无最低消费，无隐藏费用</p>
      </div>
      <div class="pricing-grid">
        <div class="pricing-card" v-for="plan in pricingPlans" :key="plan.name">
          <div class="pricing-header">
            <span class="pricing-badge" v-if="plan.popular">最受欢迎</span>
            <h3 class="pricing-name">{{ plan.name }}</h3>
            <p class="pricing-desc">{{ plan.desc }}</p>
          </div>
          <div class="pricing-price">
            <span class="price-unit">¥</span>
            <span class="price-value">{{ plan.price }}</span>
            <span class="price-unit">/次</span>
          </div>
          <ul class="pricing-features">
            <li v-for="feature in plan.features" :key="feature">
              <span class="check-icon">✓</span>
              {{ feature }}
            </li>
          </ul>
          <a href="/verify" class="btn btn-primary btn-block" :class="{ 'btn-outline': !plan.popular }">
            {{ plan.cta }}
          </a>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="faq">
      <div class="section-header">
        <span class="section-tag">常见问题</span>
        <h2 class="section-title">您可能想了解的问题</h2>
      </div>
      <div class="faq-list">
        <div class="faq-item" v-for="(item, index) in faqs" :key="index" :class="{ active: activeFaq === index }" @click="toggleFaq(index)">
          <div class="faq-question">
            <span>{{ item.q }}</span>
            <span class="faq-icon">{{ activeFaq === index ? '−' : '+' }}</span>
          </div>
          <div class="faq-answer" v-show="activeFaq === index">
            {{ item.a }}
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta">
      <div class="cta-content">
        <h2>准备好开始了吗？</h2>
        <p>立即体验森码云实人认证服务，让您的业务更安全、更高效</p>
        <div class="cta-actions">
          <a href="/verify" class="btn btn-primary btn-lg">免费体验</a>
          <a href="/docs" class="btn btn-outline btn-lg btn-white">查看文档</a>
        </div>
      </div>
    </section>

    <!-- 页脚 -->
    <footer class="footer">
      <div class="footer-content">
        <div class="footer-col">
          <div class="footer-logo">
            <span class="logo-text">森码云</span>
            <span class="logo-sub">实人认证</span>
          </div>
          <p class="footer-desc">
            基于AI人脸识别技术的SaaS化实人认证平台，为您的业务保驾护航。
          </p>
        </div>
        <div class="footer-col">
          <h4>产品</h4>
          <ul>
            <li><a href="#features">功能特性</a></li>
            <li><a href="#pricing">定价方案</a></li>
            <li><a href="/docs">API文档</a></li>
            <li><a href="#process">使用流程</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>支持</h4>
          <ul>
            <li><a href="#faq">常见问题</a></li>
            <li><a href="/about">关于我们</a></li>
            <li><a href="mailto:support@builds.codes">联系我们</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>联系</h4>
          <ul>
            <li>📧 support@builds.codes</li>
            <li>🌐 face.builds.codes</li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© 2026 森码云. All rights reserved.</p>
        <p>
          <a href="#">隐私政策</a>
          <span class="dot">·</span>
          <a href="#">服务条款</a>
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const activeFaq = ref<number | null>(null)

const features = [
  {
    icon: '🔍',
    title: '活体检测',
    desc: '多种活体检测方式，有效防御照片、视频、3D面具等攻击',
    bgColor: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    items: ['动作活体检测', '静默活体检测', '防3D面具攻击', '防屏幕翻拍']
  },
  {
    icon: '👥',
    title: '人脸比对',
    desc: '1:1人脸比对，准确率高达99.8%，支持多种比对场景',
    bgColor: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    items: ['身份证照片比对', '两张人脸比对', '相似度评分', '阈值可配置']
  },
  {
    icon: '🛡️',
    title: '安全可靠',
    desc: '金融级安全保障，数据加密存储传输，符合等保要求',
    bgColor: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    items: ['HTTPS加密传输', 'AES-256存储加密', 'HMAC签名验证', '数据脱敏处理']
  },
  {
    icon: '⚡',
    title: '高性能',
    desc: '毫秒级响应速度，支持高并发，保障业务稳定运行',
    bgColor: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
    items: ['平均500ms响应', '支持高并发', '多通道负载均衡', '自动降级容错']
  },
  {
    icon: '🔌',
    title: '易于集成',
    desc: '标准RESTful API，完善的SDK和文档，快速接入',
    bgColor: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    items: ['RESTful API', 'H5页面跳转', 'SDK支持', '详细文档']
  },
  {
    icon: '📊',
    title: '数据统计',
    desc: '完善的数据统计和分析功能，实时监控认证情况',
    bgColor: 'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)',
    items: ['实时数据看板', '认证记录查询', '通道使用统计', '数据导出']
  }
]

const steps = [
  {
    title: '发起认证',
    desc: '通过API或后台发起认证请求，获取认证链接和Token'
  },
  {
    title: '人脸采集',
    desc: '用户跳转至认证页面，完成人脸采集和活体检测'
  },
  {
    title: '获取结果',
    desc: '认证完成后通过回调或主动查询获取认证结果'
  }
]

const channels = [
  { icon: '☁️', name: '腾讯云慧眼', desc: '腾讯云官方人脸核身服务，H5跳转式' },
  { icon: '🔵', name: '百度AI', desc: '百度智能云人脸识别，动作活体检测' },
  { icon: '🟦', name: 'Azure Face', desc: '微软Azure人脸识别，海外节点可用' },
  { icon: '🔴', name: 'Face++', desc: '旷视科技人脸识别，金融级准确率' },
  { icon: '💻', name: '自研算法', desc: '本地演示通道，无需第三方密钥' }
]

const pricingPlans = [
  {
    name: '基础版',
    desc: '适合小型项目和测试使用',
    price: '0.5',
    popular: false,
    cta: '开始使用',
    features: [
      '人脸检测',
      '人脸比对',
      '基础活体检测',
      '标准API接口',
      '邮件支持'
    ]
  },
  {
    name: '专业版',
    desc: '适合大多数业务场景',
    price: '0.8',
    popular: true,
    cta: '立即体验',
    features: [
      '包含基础版全部功能',
      '动作活体检测',
      '身份证OCR',
      '多通道冗余',
      '优先技术支持',
      '数据导出'
    ]
  },
  {
    name: '企业版',
    desc: '适合大型企业定制需求',
    price: '定制',
    popular: false,
    cta: '联系销售',
    features: [
      '包含专业版全部功能',
      '私有化部署',
      '定制化开发',
      '专属客户经理',
      '7x24小时支持',
      'SLA保障'
    ]
  }
]

const faqs = [
  {
    q: '实人认证的准确率有多高？',
    a: '我们的人脸比对准确率高达99.8%，活体检测通过率超过98%。同时支持多通道冗余架构，确保在各种场景下都能提供稳定可靠的认证服务。'
  },
  {
    q: '如何接入实人认证服务？',
    a: '您可以通过两种方式接入：1) API接口方式，适合需要深度集成的场景；2) H5跳转方式，用户跳转到我们的认证页面完成认证。详细接入方式请参考API文档。'
  },
  {
    q: '人脸数据安全吗？',
    a: '我们非常重视数据安全。所有人脸数据均采用AES-256加密存储，传输全程HTTPS加密，符合等保2.0三级要求。您也可以选择私有化部署方案，数据完全由您掌控。'
  },
  {
    q: '支持哪些人脸识别服务商？',
    a: '目前支持腾讯云慧眼、百度AI、Azure Face、Face++等主流服务商，同时提供自研算法作为演示和备用通道。我们会持续接入更多服务商。'
  },
  {
    q: '如何计费？有最低消费吗？',
    a: '采用按量计费模式，按实际认证次数收费，没有最低消费和隐藏费用。企业版客户可以选择包年包月或定制计费方案。'
  }
]

function toggleFaq(index: number) {
  activeFaq.value = activeFaq.value === index ? null : index
}

function handleLogoError(e: Event) {
  const target = e.target as HTMLImageElement
  target.style.display = 'none'
}
</script>

<style scoped lang="scss">
.home-page {
  min-height: 100vh;
  background: #fff;
}

/* 导航栏 */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: rgba(255, 255, 255, 0.8);
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
  
  .logo-img {
    height: 32px;
    width: auto;
  }
  
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
    
    &:hover {
      color: #667eea;
    }
  }
}

.nav-actions {
  display: flex;
  gap: 12px;
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
  
  &.btn-ghost {
    background: transparent;
    color: #4b5563;
    
    &:hover {
      background: #f3f4f6;
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
    padding: 12px 28px;
    font-size: 16px;
  }
  
  &.btn-block {
    width: 100%;
  }
  
  &.btn-white {
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
    
    &:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: white;
    }
  }
}

/* Hero区域 */
.hero {
  position: relative;
  padding: 120px 24px 80px;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 0;
}

.hero-gradient {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 600px;
  background: linear-gradient(180deg, #f0f4ff 0%, #ffffff 100%);
}

.hero-pattern {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    radial-gradient(circle at 20% 30%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  background: rgba(102, 126, 234, 0.1);
  border-radius: 20px;
  font-size: 14px;
  color: #667eea;
  margin-bottom: 24px;
  
  .badge-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
  }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.hero-title {
  font-size: 48px;
  font-weight: 800;
  line-height: 1.2;
  color: #1f2937;
  margin-bottom: 24px;
  
  .text-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
}

.hero-desc {
  font-size: 18px;
  line-height: 1.8;
  color: #6b7280;
  margin-bottom: 32px;
}

.hero-actions {
  display: flex;
  gap: 16px;
  margin-bottom: 48px;
}

.hero-stats {
  display: flex;
  align-items: center;
  gap: 32px;
}

.stat-item {
  .stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
  }
  
  .stat-label {
    font-size: 14px;
    color: #6b7280;
    margin-top: 4px;
  }
}

.stat-divider {
  width: 1px;
  height: 40px;
  background: #e5e7eb;
}

/* Hero视觉卡片 */
.hero-visual {
  display: flex;
  justify-content: center;
}

.hero-card {
  width: 100%;
  max-width: 400px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  transform: perspective(1000px) rotateY(-5deg);
  transition: transform 0.3s;
  
  &:hover {
    transform: perspective(1000px) rotateY(0deg);
  }
}

.card-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  
  .card-dots {
    display: flex;
    gap: 6px;
    
    .dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      
      &.red { background: #ef4444; }
      &.yellow { background: #f59e0b; }
      &.green { background: #10b981; }
    }
  }
  
  .card-title {
    margin-left: 12px;
    font-size: 14px;
    color: #6b7280;
  }
}

.card-body {
  padding: 24px;
}

.face-scan {
  text-align: center;
  margin-bottom: 24px;
}

.face-placeholder {
  width: 200px;
  height: 200px;
  margin: 0 auto 16px;
  background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  
  .face-icon {
    font-size: 80px;
  }
  
  .scan-line {
    position: absolute;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, #667eea, transparent);
    animation: scan 2s linear infinite;
  }
}

@keyframes scan {
  0% { top: 0; }
  100% { top: 100%; }
}

.scan-status {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  background: #f0fdf4;
  border-radius: 20px;
  font-size: 14px;
  color: #16a34a;
  
  .status-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    
    &.scanning {
      animation: pulse 1s infinite;
    }
  }
}

.result-preview {
  background: #f9fafb;
  border-radius: 12px;
  padding: 16px;
}

.result-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  
  &:not(:last-child) {
    border-bottom: 1px solid #e5e7eb;
  }
  
  .result-label {
    font-size: 14px;
    color: #6b7280;
  }
  
  .result-value {
    font-size: 14px;
    font-weight: 600;
    
    &.success {
      color: #16a34a;
    }
  }
}

/* 通用section样式 */
.section-header {
  text-align: center;
  margin-bottom: 60px;
}

.section-tag {
  display: inline-block;
  padding: 6px 16px;
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 16px;
}

.section-title {
  font-size: 36px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 16px;
}

.section-desc {
  font-size: 18px;
  color: #6b7280;
  max-width: 600px;
  margin: 0 auto;
}

/* 功能特性 */
.features {
  padding: 80px 24px;
  background: #f9fafb;
}

.features-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.feature-card {
  background: white;
  border-radius: 16px;
  padding: 32px;
  transition: all 0.3s;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
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
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 12px;
}

.feature-desc {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 16px;
}

.feature-list {
  list-style: none;
  padding: 0;
  margin: 0;
  
  li {
    font-size: 14px;
    color: #4b5563;
    padding: 4px 0;
    padding-left: 20px;
    position: relative;
    
    &::before {
      content: '✓';
      position: absolute;
      left: 0;
      color: #10b981;
      font-weight: bold;
    }
  }
}

/* 流程 */
.process {
  padding: 80px 24px;
}

.process-steps {
  max-width: 900px;
  margin: 0 auto;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  gap: 24px;
}

.step-item {
  flex: 1;
  text-align: center;
  position: relative;
}

.step-number {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: 700;
}

.step-title {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.step-desc {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.6;
}

.step-arrow {
  position: absolute;
  right: -20px;
  top: 20px;
  font-size: 24px;
  color: #d1d5db;
}

/* 多通道架构 */
.channels {
  padding: 80px 24px;
  background: #f9fafb;
}

.channels-content {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}

.channels-text .section-title {
  text-align: left;
}

.channels-text .section-desc {
  text-align: left;
  margin: 0 0 32px;
}

.channel-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.channel-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: white;
  border-radius: 12px;
  transition: all 0.2s;
  
  &:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }
}

.channel-icon {
  font-size: 28px;
}

.channel-info {
  flex: 1;
  
  .channel-name {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
  }
  
  .channel-desc {
    font-size: 13px;
    color: #6b7280;
    margin-top: 2px;
  }
}

.channel-status {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  padding: 4px 12px;
  border-radius: 12px;
  
  &.online {
    background: #dcfce7;
    color: #16a34a;
    
    .status-dot {
      width: 6px;
      height: 6px;
      background: #22c55e;
      border-radius: 50%;
    }
  }
}

/* 通道架构图 */
.channels-visual {
  display: flex;
  justify-content: center;
}

.channel-diagram {
  width: 100%;
  max-width: 400px;
}

.diagram-top,
.diagram-middle,
.diagram-bottom {
  display: flex;
  justify-content: center;
  padding: 16px 0;
}

.diagram-bottom {
  flex-wrap: wrap;
  gap: 8px;
  justify-content: center;
}

.diagram-node {
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  text-align: center;
  
  &.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 16px;
    padding: 16px 32px;
  }
  
  &.gateway {
    background: #1f2937;
    color: white;
    flex-direction: column;
    display: flex;
    
    .node-sub {
      font-size: 12px;
      opacity: 0.7;
      margin-top: 4px;
    }
  }
  
  &.channel {
    background: white;
    border: 1px solid #e5e7eb;
    color: #4b5563;
    font-size: 12px;
    padding: 8px 16px;
  }
}

.diagram-arrows {
  display: flex;
  justify-content: center;
  gap: 40px;
  padding: 8px 0;
  
  .arrow-line {
    width: 2px;
    height: 24px;
    background: linear-gradient(180deg, #d1d5db, #9ca3af);
    position: relative;
    
    &::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 50%;
      transform: translateX(-50%);
      border-left: 4px solid transparent;
      border-right: 4px solid transparent;
      border-top: 6px solid #9ca3af;
    }
  }
}

/* 定价 */
.pricing {
  padding: 80px 24px;
}

.pricing-grid {
  max-width: 1000px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.pricing-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 32px;
  position: relative;
  transition: all 0.3s;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
  }
  
  &:nth-child(2) {
    border-color: #667eea;
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.2);
    transform: scale(1.05);
    
    &:hover {
      transform: scale(1.05) translateY(-4px);
    }
  }
}

.pricing-badge {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  padding: 4px 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-size: 12px;
  font-weight: 500;
  border-radius: 12px;
}

.pricing-header {
  text-align: center;
  margin-bottom: 24px;
}

.pricing-name {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.pricing-desc {
  font-size: 14px;
  color: #6b7280;
}

.pricing-price {
  text-align: center;
  margin-bottom: 24px;
  
  .price-unit {
    font-size: 18px;
    color: #6b7280;
    vertical-align: top;
  }
  
  .price-value {
    font-size: 48px;
    font-weight: 700;
    color: #1f2937;
  }
}

.pricing-features {
  list-style: none;
  padding: 0;
  margin: 0 0 24px;
  
  li {
    padding: 8px 0;
    font-size: 14px;
    color: #4b5563;
    display: flex;
    align-items: center;
    gap: 8px;
    
    .check-icon {
      color: #10b981;
      font-weight: bold;
    }
  }
}

/* FAQ */
.faq {
  padding: 80px 24px;
  background: #f9fafb;
}

.faq-list {
  max-width: 800px;
  margin: 0 auto;
}

.faq-item {
  background: white;
  border-radius: 12px;
  margin-bottom: 12px;
  overflow: hidden;
  transition: all 0.2s;
  
  &.active {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }
}

.faq-question {
  padding: 20px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  color: #1f2937;
  
  .faq-icon {
    font-size: 20px;
    color: #667eea;
    font-weight: 300;
  }
}

.faq-answer {
  padding: 0 24px 20px;
  font-size: 14px;
  color: #6b7280;
  line-height: 1.8;
}

/* CTA */
.cta {
  padding: 80px 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.cta-content {
  max-width: 600px;
  margin: 0 auto;
  text-align: center;
  color: white;
  
  h2 {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 16px;
  }
  
  p {
    font-size: 18px;
    opacity: 0.9;
    margin-bottom: 32px;
  }
}

.cta-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
}

/* 页脚 */
.footer {
  background: #1f2937;
  color: #9ca3af;
  padding: 60px 24px 24px;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 48px;
  margin-bottom: 48px;
}

.footer-logo {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  
  .logo-text {
    font-size: 20px;
    font-weight: 700;
    color: white;
  }
  
  .logo-sub {
    font-size: 12px;
    color: #9ca3af;
    background: #374151;
    padding: 2px 8px;
    border-radius: 4px;
  }
}

.footer-desc {
  font-size: 14px;
  line-height: 1.6;
}

.footer-col {
  h4 {
    color: white;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 16px;
  }
  
  ul {
    list-style: none;
    padding: 0;
    margin: 0;
    
    li {
      padding: 6px 0;
      font-size: 14px;
      
      a {
        color: #9ca3af;
        text-decoration: none;
        transition: color 0.2s;
        
        &:hover {
          color: white;
        }
      }
    }
  }
}

.footer-bottom {
  max-width: 1200px;
  margin: 0 auto;
  padding-top: 24px;
  border-top: 1px solid #374151;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  
  .dot {
    margin: 0 8px;
  }
  
  a {
    color: #9ca3af;
    text-decoration: none;
    
    &:hover {
      color: white;
    }
  }
}

/* 响应式 */
@media (max-width: 768px) {
  .hero-content {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .hero-title {
    font-size: 32px;
  }
  
  .hero-actions {
    justify-content: center;
  }
  
  .hero-stats {
    justify-content: center;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
  }
  
  .process-steps {
    flex-direction: column;
  }
  
  .step-arrow {
    display: none;
  }
  
  .channels-content {
    grid-template-columns: 1fr;
  }
  
  .pricing-grid {
    grid-template-columns: 1fr;
  }
  
  .pricing-card:nth-child(2) {
    transform: none;
  }
  
  .footer-content {
    grid-template-columns: 1fr 1fr;
  }
  
  .nav-links {
    display: none;
  }
}
</style>
