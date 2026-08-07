<template>
  <div class="pricing-page">
    <nav class="navbar">
      <div class="nav-container">
        <div class="nav-logo">
          <span class="logo-text">森码云</span>
          <span class="logo-sub">实人认证</span>
        </div>
        <div class="nav-links">
          <a href="/" class="nav-link">首页</a>
          <a href="/pricing" class="nav-link active">定价</a>
          <a href="/docs" class="nav-link">API文档</a>
          <a href="/about" class="nav-link">关于</a>
        </div>
        <div class="nav-actions">
          <a href="/verify" class="btn btn-primary">开始认证</a>
        </div>
      </div>
    </nav>

    <section class="pricing-hero">
      <div class="hero-content">
        <span class="hero-tag">定价方案</span>
        <h1 class="hero-title">简单透明的定价</h1>
        <p class="hero-desc">按需选择，按量计费，无最低消费，无隐藏费用</p>
        
        <div class="billing-toggle">
          <span class="toggle-label">按量计费</span>
          <div class="toggle-switch" :class="{ on: isYearly }" @click="isYearly = !isYearly">
            <div class="toggle-thumb"></div>
          </div>
          <span class="toggle-label">包年包月</span>
          <span class="save-badge" v-if="isYearly">省20%</span>
        </div>
      </div>
    </section>

    <section class="pricing-cards">
      <div class="cards-container">
        <div class="pricing-card" v-for="plan in plans" :key="plan.name">
          <div class="card-header">
            <span class="plan-badge" v-if="plan.popular">最受欢迎</span>
            <h3 class="plan-name">{{ plan.name }}</h3>
            <p class="plan-desc">{{ plan.desc }}</p>
          </div>
          
          <div class="plan-price">
            <span class="price-currency">¥</span>
            <span class="price-value">{{ isYearly ? plan.yearlyPrice : plan.monthlyPrice }}</span>
            <span class="price-unit">/{{ isYearly ? '年' : '月' }}</span>
          </div>
          
          <div class="plan-volume">
            包含 <strong>{{ plan.volume }}</strong> 次认证
          </div>
          
          <ul class="plan-features">
            <li v-for="feature in plan.features" :key="feature">
              <span class="check-icon">✓</span>
              {{ feature }}
            </li>
          </ul>
          
          <a href="/verify" class="btn btn-block" :class="plan.popular ? 'btn-primary' : 'btn-outline'">
            {{ plan.cta }}
          </a>
        </div>
      </div>
    </section>

    <section class="enterprise-section">
      <div class="enterprise-card">
        <div class="enterprise-text">
          <h2>需要更大规模？</h2>
          <p>我们提供企业级定制方案，包括私有化部署、定制化开发、专属技术支持等服务。</p>
          <ul class="enterprise-features">
            <li>✓ 私有化部署</li>
            <li>✓ 定制化开发</li>
            <li>✓ 专属客户经理</li>
            <li>✓ 7x24小时技术支持</li>
            <li>✓ SLA服务保障</li>
            <li>✓ 数据安全合规</li>
          </ul>
        </div>
        <div class="enterprise-action">
          <a href="mailto:sales@builds.codes" class="btn btn-primary btn-lg">联系销售</a>
          <p class="contact-email">sales@builds.codes</p>
        </div>
      </div>
    </section>

    <section class="faq-section">
      <h2 class="section-title">常见问题</h2>
      <div class="faq-list">
        <div class="faq-item" v-for="(item, index) in faqs" :key="index" :class="{ open: openIndex === index }" @click="openIndex = openIndex === index ? -1 : index">
          <div class="faq-question">
            <span>{{ item.q }}</span>
            <span class="faq-icon">{{ openIndex === index ? '−' : '+' }}</span>
          </div>
          <div class="faq-answer" v-show="openIndex === index">
            {{ item.a }}
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
import { ref } from 'vue'

const isYearly = ref(false)
const openIndex = ref(-1)

const plans = [
  {
    name: '入门版',
    desc: '适合个人开发者和小型项目',
    monthlyPrice: '0',
    yearlyPrice: '0',
    volume: '100次/月',
    popular: false,
    cta: '免费开始',
    features: [
      '人脸检测',
      '基础人脸比对',
      '基础活体检测',
      '标准API接口',
      '社区支持'
    ]
  },
  {
    name: '专业版',
    desc: '适合大多数业务场景',
    monthlyPrice: '299',
    yearlyPrice: '2870',
    volume: '10,000次/月',
    popular: true,
    cta: '立即购买',
    features: [
      '包含入门版全部功能',
      '动作活体检测',
      '身份证OCR识别',
      '多通道冗余架构',
      '优先技术支持',
      '数据导出功能',
      '自定义回调地址'
    ]
  },
  {
    name: '企业版',
    desc: '适合大型企业和高要求场景',
    monthlyPrice: '999',
    yearlyPrice: '9590',
    volume: '100,000次/月',
    popular: false,
    cta: '联系销售',
    features: [
      '包含专业版全部功能',
      '无限API调用',
      '私有化部署选项',
      '定制化开发',
      '专属客户经理',
      '7x24小时支持',
      'SLA服务保障'
    ]
  }
]

const faqs = [
  {
    q: '超出套餐次数怎么计费？',
    a: '超出套餐部分按阶梯计费，具体价格请参考我们的详细定价表。企业版客户可以享受更优惠的阶梯价格。'
  },
  {
    q: '可以随时升级或降级套餐吗？',
    a: '可以的。升级套餐立即生效，降级套餐在下个计费周期生效。剩余次数会按比例折算。'
  },
  {
    q: '支持哪些支付方式？',
    a: '支持支付宝、微信支付、银行转账等多种支付方式。企业客户可以选择对公转账和合同付款。'
  },
  {
    q: '有没有免费试用？',
    a: '入门版完全免费，包含每月100次认证额度，足够用于测试和小型项目。专业版和企业版也提供7天免费试用。'
  },
  {
    q: '数据安全如何保障？',
    a: '我们采用银行级加密技术，所有人脸数据均采用AES-256加密存储，传输全程HTTPS加密。企业版还支持私有化部署，数据完全由您掌控。'
  }
]
</script>

<style scoped lang="scss">
.pricing-page {
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
    padding: 14px 32px;
    font-size: 16px;
  }
  
  &.btn-block {
    width: 100%;
  }
}

/* Hero */
.pricing-hero {
  padding: 120px 24px 60px;
  text-align: center;
  background: linear-gradient(180deg, #f0f4ff 0%, #ffffff 100%);
}

.hero-content {
  max-width: 600px;
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
}

.hero-desc {
  font-size: 18px;
  color: #6b7280;
  margin-bottom: 32px;
}

.billing-toggle {
  display: inline-flex;
  align-items: center;
  gap: 16px;
  padding: 8px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.toggle-label {
  font-size: 14px;
  color: #6b7280;
}

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
    background: #667eea;
    
    .toggle-thumb {
      left: 25px;
    }
  }
}

.save-badge {
  font-size: 12px;
  color: #10b981;
  background: #dcfce7;
  padding: 2px 8px;
  border-radius: 10px;
  font-weight: 500;
}

/* 定价卡片 */
.pricing-cards {
  padding: 40px 24px 80px;
}

.cards-container {
  max-width: 1000px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.pricing-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 20px;
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

.plan-badge {
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

.card-header {
  text-align: center;
  margin-bottom: 24px;
}

.plan-name {
  font-size: 22px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.plan-desc {
  font-size: 14px;
  color: #6b7280;
}

.plan-price {
  text-align: center;
  margin-bottom: 8px;
  
  .price-currency {
    font-size: 20px;
    color: #6b7280;
    vertical-align: top;
  }
  
  .price-value {
    font-size: 52px;
    font-weight: 800;
    color: #1f2937;
  }
  
  .price-unit {
    font-size: 16px;
    color: #6b7280;
  }
}

.plan-volume {
  text-align: center;
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 24px;
  
  strong {
    color: #1f2937;
  }
}

.plan-features {
  list-style: none;
  padding: 0;
  margin: 0 0 24px;
  
  li {
    padding: 8px 0;
    font-size: 14px;
    color: #4b5563;
    display: flex;
    align-items: center;
    gap: 10px;
    
    .check-icon {
      color: #10b981;
      font-weight: bold;
    }
  }
}

/* 企业版 */
.enterprise-section {
  padding: 0 24px 80px;
}

.enterprise-card {
  max-width: 1000px;
  margin: 0 auto;
  background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
  border-radius: 24px;
  padding: 48px;
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 48px;
  align-items: center;
  color: white;
}

.enterprise-text {
  h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 12px;
  }
  
  p {
    font-size: 16px;
    opacity: 0.8;
    margin-bottom: 20px;
    line-height: 1.6;
  }
}

.enterprise-features {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  
  li {
    font-size: 14px;
    opacity: 0.9;
  }
}

.enterprise-action {
  text-align: center;
  
  .contact-email {
    margin-top: 12px;
    font-size: 14px;
    opacity: 0.7;
  }
}

/* FAQ */
.faq-section {
  padding: 0 24px 80px;
  max-width: 800px;
  margin: 0 auto;
}

.section-title {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
  text-align: center;
  margin-bottom: 40px;
}

.faq-item {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  margin-bottom: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    border-color: #d1d5db;
  }
}

.faq-question {
  padding: 18px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  padding: 0 24px 18px;
  font-size: 14px;
  color: #6b7280;
  line-height: 1.8;
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
  
  .cards-container {
    grid-template-columns: 1fr;
  }
  
  .pricing-card:nth-child(2) {
    transform: none;
  }
  
  .enterprise-card {
    grid-template-columns: 1fr;
    padding: 32px;
  }
}
</style>
