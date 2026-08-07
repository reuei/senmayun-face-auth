<template>
  <div class="docs-page">
    <div class="docs-container">
      <!-- 侧边栏 -->
      <aside class="docs-sidebar">
        <div class="sidebar-header">
          <div class="brand">
            <span class="brand-name">森码云</span>
            <span class="brand-sub">API文档</span>
          </div>
        </div>
        <nav class="sidebar-nav">
          <div class="nav-group">
            <div class="group-title">快速开始</div>
            <a href="#intro" class="nav-link active">简介</a>
            <a href="#quickstart" class="nav-link">快速接入</a>
            <a href="#auth" class="nav-link">认证方式</a>
          </div>
          <div class="nav-group">
            <div class="group-title">API接口</div>
            <a href="#init" class="nav-link">初始化认证</a>
            <a href="#verify" class="nav-link">验证Token</a>
            <a href="#result" class="nav-link">查询结果</a>
            <a href="#callback" class="nav-link">回调通知</a>
          </div>
          <div class="nav-group">
            <div class="group-title">人脸接口</div>
            <a href="#detect" class="nav-link">人脸检测</a>
            <a href="#compare" class="nav-link">人脸比对</a>
            <a href="#liveness" class="nav-link">活体检测</a>
          </div>
          <div class="nav-group">
            <div class="group-title">其他</div>
            <a href="#error" class="nav-link">错误码</a>
            <a href="#sdk" class="nav-link">SDK下载</a>
          </div>
        </nav>
      </aside>

      <!-- 主内容 -->
      <main class="docs-main">
        <header class="docs-header">
          <div class="header-left">
            <a href="/" class="back-link">← 返回首页</a>
          </div>
          <div class="header-right">
            <a href="/verify" class="btn btn-primary">开始使用</a>
          </div>
        </header>

        <article class="docs-content">
          <!-- 简介 -->
          <section id="intro" class="doc-section">
            <h1>森码云实人认证 API</h1>
            <p class="doc-lead">
              森码云实人认证提供简单易用的RESTful API，帮助您快速集成本地人脸检测、活体检测和身份核验功能。
            </p>
            <div class="info-cards">
              <div class="info-card">
                <div class="card-icon">📚</div>
                <div class="card-text">
                  <h4>版本</h4>
                  <p>v1.0</p>
                </div>
              </div>
              <div class="info-card">
                <div class="card-icon">🔐</div>
                <div class="card-text">
                  <h4>认证方式</h4>
                  <p>API Key + HMAC签名</p>
                </div>
              </div>
              <div class="info-card">
                <div class="card-icon">⚡</div>
                <div class="card-text">
                  <h4>响应格式</h4>
                  <p>JSON</p>
                </div>
              </div>
            </div>
          </section>

          <!-- 快速接入 -->
          <section id="quickstart" class="doc-section">
            <h2>快速接入</h2>
            <p>只需三步，即可完成实人认证接入：</p>
            <ol class="step-list">
              <li>
                <strong>获取API密钥</strong>
                <p>在管理后台创建API Key和Secret</p>
              </li>
              <li>
                <strong>发起认证</strong>
                <p>调用初始化接口，获取认证链接和Token</p>
              </li>
              <li>
                <strong>获取结果</strong>
                <p>通过回调或主动查询获取认证结果</p>
              </li>
            </ol>
          </section>

          <!-- 认证方式 -->
          <section id="auth" class="doc-section">
            <h2>认证方式</h2>
            <p>所有API请求都需要在Header中携带以下信息：</p>
            <div class="code-block">
              <pre><code>X-API-Key: your_api_key
X-Signature: hmac_sha256_signature
X-Timestamp: 1678888888</code></pre>
            </div>
            <h3>签名算法</h3>
            <p>使用HMAC-SHA256算法，签名内容为：</p>
            <div class="code-block">
              <pre><code>signature = HMAC_SHA256(
  api_secret,
  method + "\n" + path + "\n" + timestamp + "\n" + body
)</code></pre>
            </div>
          </section>

          <!-- 初始化认证 -->
          <section id="init" class="doc-section">
            <div class="api-method">
              <span class="method-badge post">POST</span>
              <code class="api-path">/api/v1/auth/init</code>
            </div>
            <h2>初始化认证</h2>
            <p>发起一次实人认证请求，获取认证链接和Token。</p>
            
            <h3>请求参数</h3>
            <table class="param-table">
              <thead>
                <tr>
                  <th>参数名</th>
                  <th>类型</th>
                  <th>必填</th>
                  <th>说明</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>user_id</td>
                  <td>string</td>
                  <td>是</td>
                  <td>用户唯一标识</td>
                </tr>
                <tr>
                  <td>return_url</td>
                  <td>string</td>
                  <td>否</td>
                  <td>认证完成后跳转的URL</td>
                </tr>
                <tr>
                  <td>notify_url</td>
                  <td>string</td>
                  <td>否</td>
                  <td>认证结果回调URL</td>
                </tr>
                <tr>
                  <td>extra</td>
                  <td>string</td>
                  <td>否</td>
                  <td>额外参数，回调时原样返回</td>
                </tr>
              </tbody>
            </table>

            <h3>响应示例</h3>
            <div class="code-block">
              <pre><code>{
  "code": 200,
  "message": "success",
  "data": {
    "token": "a1b2c3d4e5f6...",
    "verify_url": "https://face.builds.codes/verify?token=xxx",
    "expire_at": 1678889888
  }
}</code></pre>
            </div>
          </section>

          <!-- 验证Token -->
          <section id="verify" class="doc-section">
            <div class="api-method">
              <span class="method-badge post">POST</span>
              <code class="api-path">/api/v1/auth/verify-token</code>
            </div>
            <h2>验证Token</h2>
            <p>验证认证Token的有效性。</p>
          </section>

          <!-- 查询结果 -->
          <section id="result" class="doc-section">
            <div class="api-method">
              <span class="method-badge get">GET</span>
              <code class="api-path">/api/v1/auth/result</code>
            </div>
            <h2>查询认证结果</h2>
            <p>根据Token查询认证结果。</p>
          </section>

          <!-- 回调通知 -->
          <section id="callback" class="doc-section">
            <h2>回调通知</h2>
            <p>如果设置了notify_url，认证完成后系统会向该地址发送POST回调。</p>
            <div class="code-block">
              <pre><code>{
  "token": "a1b2c3d4e5f6...",
  "status": "passed",
  "score": 95.5,
  "liveness_passed": true,
  "verify_time": "2023-03-15 10:30:00",
  "extra": "your_extra_data"
}</code></pre>
            </div>
          </section>

          <!-- 人脸检测 -->
          <section id="detect" class="doc-section">
            <div class="api-method">
              <span class="method-badge post">POST</span>
              <code class="api-path">/api/v1/face/detect</code>
            </div>
            <h2>人脸检测</h2>
            <p>检测图片中的人脸，返回人脸位置和关键点信息。</p>
          </section>

          <!-- 人脸比对 -->
          <section id="compare" class="doc-section">
            <div class="api-method">
              <span class="method-badge post">POST</span>
              <code class="api-path">/api/v1/face/compare</code>
            </div>
            <h2>人脸比对</h2>
            <p>比对两张人脸图片的相似度。</p>
          </section>

          <!-- 活体检测 -->
          <section id="liveness" class="doc-section">
            <div class="api-method">
              <span class="method-badge post">POST</span>
              <code class="api-path">/api/v1/face/liveness</code>
            </div>
            <h2>活体检测</h2>
            <p>检测是否为真实活体人脸。</p>
          </section>

          <!-- 错误码 -->
          <section id="error" class="doc-section">
            <h2>错误码</h2>
            <table class="param-table">
              <thead>
                <tr>
                  <th>错误码</th>
                  <th>说明</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>200</td><td>成功</td></tr>
                <tr><td>400</td><td>请求参数错误</td></tr>
                <tr><td>401</td><td>认证失败</td></tr>
                <tr><td>403</td><td>权限不足</td></tr>
                <tr><td>404</td><td>资源不存在</td></tr>
                <tr><td>429</td><td>请求过于频繁</td></tr>
                <tr><td>500</td><td>服务器内部错误</td></tr>
              </tbody>
            </table>
          </section>

          <!-- SDK下载 -->
          <section id="sdk" class="doc-section">
            <h2>SDK下载</h2>
            <p>我们提供多种语言的SDK，帮助您快速集成：</p>
            <div class="sdk-grid">
              <div class="sdk-card">
                <div class="sdk-icon">🐘</div>
                <h4>PHP SDK</h4>
                <p>Composer安装</p>
                <code>composer require senmayun/face-auth</code>
              </div>
              <div class="sdk-card">
                <div class="sdk-icon">📦</div>
                <h4>JavaScript SDK</h4>
                <p>NPM安装</p>
                <code>npm install @senmayun/face-auth</code>
              </div>
              <div class="sdk-card">
                <div class="sdk-icon">🐍</div>
                <h4>Python SDK</h4>
                <p>Pip安装</p>
                <code>pip install senmayun-face-auth</code>
              </div>
            </div>
          </section>
        </article>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
// 文档页
</script>

<style scoped lang="scss">
.docs-page {
  min-height: 100vh;
  background: #fff;
}

.docs-container {
  display: flex;
  min-height: 100vh;
}

/* 侧边栏 */
.docs-sidebar {
  width: 260px;
  background: #fafafa;
  border-right: 1px solid #e5e7eb;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  overflow-y: auto;
}

.sidebar-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
  
  .brand-name {
    font-size: 18px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  
  .brand-sub {
    font-size: 11px;
    color: #6b7280;
    background: #e5e7eb;
    padding: 2px 6px;
    border-radius: 4px;
  }
}

.sidebar-nav {
  padding: 16px 0;
}

.nav-group {
  margin-bottom: 24px;
}

.group-title {
  padding: 8px 24px;
  font-size: 12px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.nav-link {
  display: block;
  padding: 8px 24px;
  font-size: 14px;
  color: #4b5563;
  text-decoration: none;
  transition: all 0.2s;
  
  &:hover, &.active {
    color: #667eea;
    background: rgba(102, 126, 234, 0.05);
    border-left: 3px solid #667eea;
    padding-left: 21px;
  }
}

/* 主内容 */
.docs-main {
  flex: 1;
  margin-left: 260px;
}

.docs-header {
  position: sticky;
  top: 0;
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 16px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 10;
}

.back-link {
  color: #6b7280;
  text-decoration: none;
  font-size: 14px;
  
  &:hover {
    color: #667eea;
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

/* 文档内容 */
.docs-content {
  max-width: 800px;
  padding: 40px;
}

.doc-section {
  margin-bottom: 48px;
  padding-bottom: 48px;
  border-bottom: 1px solid #f3f4f6;
  
  &:last-child {
    border-bottom: none;
  }
}

.doc-section h1 {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 16px;
}

.doc-section h2 {
  font-size: 24px;
  font-weight: 600;
  color: #1f2937;
  margin: 32px 0 16px;
}

.doc-section h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 24px 0 12px;
}

.doc-section p {
  font-size: 15px;
  line-height: 1.8;
  color: #4b5563;
  margin-bottom: 16px;
}

.doc-lead {
  font-size: 18px !important;
  color: #6b7280 !important;
  line-height: 1.6 !important;
}

/* 信息卡片 */
.info-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin: 24px 0;
}

.info-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  gap: 12px;
  align-items: center;
}

.card-icon {
  font-size: 28px;
}

.card-text h4 {
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
  margin: 0 0 4px;
}

.card-text p {
  font-size: 15px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

/* 步骤列表 */
.step-list {
  list-style: none;
  padding: 0;
  counter-reset: step;
  
  li {
    position: relative;
    padding: 16px 16px 16px 56px;
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 12px;
    counter-increment: step;
    
    &::before {
      content: counter(step);
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      width: 28px;
      height: 28px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 600;
    }
    
    strong {
      display: block;
      font-size: 15px;
      color: #1f2937;
      margin-bottom: 4px;
    }
    
    p {
      margin: 0;
      font-size: 14px;
      color: #6b7280;
    }
  }
}

/* API方法 */
.api-method {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.method-badge {
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  color: white;
  
  &.post { background: #10b981; }
  &.get { background: #3b82f6; }
  &.put { background: #f59e0b; }
  &.delete { background: #ef4444; }
}

.api-path {
  font-size: 15px;
  color: #1f2937;
  background: #f3f4f6;
  padding: 6px 12px;
  border-radius: 6px;
  font-family: 'SF Mono', Monaco, monospace;
}

/* 代码块 */
.code-block {
  background: #1f2937;
  border-radius: 12px;
  padding: 20px;
  margin: 16px 0;
  overflow-x: auto;
  
  pre {
    margin: 0;
  }
  
  code {
    color: #e5e7eb;
    font-size: 13px;
    line-height: 1.6;
    font-family: 'SF Mono', Monaco, monospace;
  }
}

/* 参数表格 */
.param-table {
  width: 100%;
  border-collapse: collapse;
  margin: 16px 0;
  
  th, td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
  }
  
  th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
  }
  
  td {
    color: #4b5563;
  }
  
  code {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
  }
}

/* SDK卡片 */
.sdk-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin: 24px 0;
}

.sdk-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  
  .sdk-icon {
    font-size: 40px;
    margin-bottom: 12px;
  }
  
  h4 {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 8px;
  }
  
  p {
    font-size: 13px;
    color: #6b7280;
    margin: 0 0 12px;
  }
  
  code {
    display: block;
    background: #1f2937;
    color: #e5e7eb;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-family: 'SF Mono', Monaco, monospace;
  }
}

@media (max-width: 768px) {
  .docs-sidebar {
    display: none;
  }
  
  .docs-main {
    margin-left: 0;
  }
  
  .info-cards,
  .sdk-grid {
    grid-template-columns: 1fr;
  }
}
</style>
