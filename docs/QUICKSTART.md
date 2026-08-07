# 快速开始指南

## 5分钟快速接入

### 第一步：注册账号
1. 访问森码云实人认证系统
2. 注册管理员账号
3. 登录管理后台

### 第二步：配置API通道
1. 进入「API通道」页面
2. 选择要使用的人脸识别服务（推荐腾讯云慧眼）
3. 填写API密钥
4. 点击「测试连接」验证配置

### 第三步：创建API密钥
1. 进入「系统设置」→「API管理」
2. 点击「创建API Key」
3. 记录生成的 API Key 和 API Secret

### 第四步：接入API

#### PHP示例
```php
<?php

class SenmayunFaceAuth {
    private $apiUrl = 'https://face.builds.codes';
    private $apiKey = 'your_api_key';
    private $apiSecret = 'your_api_secret';
    
    // 发起认证
    public function initAuth($userId, $returnUrl = '') {
        $params = [
            'user_id' => $userId,
            'return_url' => $returnUrl,
            'notify_url' => 'https://your-domain.com/callback.php'
        ];
        
        return $this->request('/api/v1/auth/init', $params, 'POST');
    }
    
    // 查询结果
    public function getResult($token) {
        return $this->request('/api/v1/auth/result', ['token' => $token], 'GET');
    }
    
    // API请求
    private function request($path, $params, $method = 'GET') {
        $url = $this->apiUrl . $path;
        $timestamp = time();
        $body = $method === 'GET' ? '' : json_encode($params);
        
        // 生成签名
        $signString = $method . "\n" . $path . "\n" . $timestamp . "\n" . $body;
        $signature = hash_hmac('sha256', $signString, $this->apiSecret);
        
        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey,
            'X-Signature: ' . $signature,
            'X-Timestamp: ' . $timestamp
        ];
        
        $ch = curl_init();
        
        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}

// 使用示例
$auth = new SenmayunFaceAuth();

// 发起认证
$result = $auth->initAuth('user_001', 'https://your-domain.com/return.php');
if ($result['code'] == 200) {
    // 跳转到认证页面
    header('Location: ' . $result['data']['verify_url']);
}
```

#### JavaScript示例
```javascript
class SenmayunFaceAuth {
    constructor(apiKey, apiSecret) {
        this.apiUrl = 'https://face.builds.codes';
        this.apiKey = apiKey;
        this.apiSecret = apiSecret;
    }
    
    async initAuth(userId, returnUrl = '') {
        return this.request('/api/v1/auth/init', {
            user_id: userId,
            return_url: returnUrl
        }, 'POST');
    }
    
    async getResult(token) {
        return this.request('/api/v1/auth/result', { token }, 'GET');
    }
    
    async request(path, params, method = 'GET') {
        const timestamp = Math.floor(Date.now() / 1000);
        const body = method === 'GET' ? '' : JSON.stringify(params);
        
        // 注意：前端直接调用不推荐暴露apiSecret
        // 建议通过后端代理请求
        const signature = await this.hmacSha256(
            `${method}\n${path}\n${timestamp}\n${body}`,
            this.apiSecret
        );
        
        const headers = {
            'Content-Type': 'application/json',
            'X-API-Key': this.apiKey,
            'X-Signature': signature,
            'X-Timestamp': timestamp.toString()
        };
        
        let url = this.apiUrl + path;
        let options = { method, headers };
        
        if (method === 'GET') {
            url += '?' + new URLSearchParams(params);
        } else {
            options.body = body;
        }
        
        const response = await fetch(url, options);
        return await response.json();
    }
    
    async hmacSha256(message, secret) {
        const encoder = new TextEncoder();
        const keyData = encoder.encode(secret);
        const messageData = encoder.encode(message);
        
        const key = await crypto.subtle.importKey(
            'raw', keyData, { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']
        );
        
        const signature = await crypto.subtle.sign('HMAC', key, messageData);
        return Array.from(new Uint8Array(signature))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
    }
}
```

#### Python示例
```python
import requests
import hmac
import hashlib
import time
import json

class SenmayunFaceAuth:
    def __init__(self, api_key, api_secret):
        self.api_url = 'https://face.builds.codes'
        self.api_key = api_key
        self.api_secret = api_secret
    
    def init_auth(self, user_id, return_url=''):
        params = {
            'user_id': user_id,
            'return_url': return_url
        }
        return self._request('/api/v1/auth/init', params, 'POST')
    
    def get_result(self, token):
        return self._request('/api/v1/auth/result', {'token': token}, 'GET')
    
    def _request(self, path, params, method='GET'):
        url = self.api_url + path
        timestamp = int(time.time())
        body = '' if method == 'GET' else json.dumps(params)
        
        # 生成签名
        sign_string = f"{method}\n{path}\n{timestamp}\n{body}"
        signature = hmac.new(
            self.api_secret.encode(),
            sign_string.encode(),
            hashlib.sha256
        ).hexdigest()
        
        headers = {
            'Content-Type': 'application/json',
            'X-API-Key': self.api_key,
            'X-Signature': signature,
            'X-Timestamp': str(timestamp)
        }
        
        if method == 'GET':
            response = requests.get(url, params=params, headers=headers, timeout=30)
        else:
            response = requests.post(url, json=params, headers=headers, timeout=30)
        
        return response.json()

# 使用示例
auth = SenmayunFaceAuth('your_api_key', 'your_api_secret')
result = auth.init_auth('user_001', 'https://your-domain.com/return')
print(result)
```

### 第五步：处理回调

```php
<?php
// callback.php

// 获取回调数据
$data = json_decode(file_get_contents('php://input'), true);

// 验证签名（可选但推荐）
$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
$timestamp = $_SERVER['HTTP_X_TIMESTAMP'] ?? '';

// 处理认证结果
$token = $data['token'] ?? '';
$status = $data['status'] ?? ''; // passed / failed / expired
$score = $data['score'] ?? 0;

if ($status == 'passed') {
    // 认证通过，执行业务逻辑
    // 例如：更新用户状态、开通服务等
    echo 'success';
} else {
    // 认证失败
    echo 'success'; // 仍然返回success，表示已收到回调
}
```

## 前端集成方案

### 方案一：跳转式（推荐）
最简单的集成方式，用户跳转到认证页面，完成后跳转回来。

```javascript
// 1. 后端调用initAuth接口获取verify_url
// 2. 前端跳转到verify_url
window.location.href = verify_url;
```

### 方案二：弹窗式
使用iframe嵌入认证页面。

```html
<iframe 
  id="authFrame"
  src="https://face.builds.codes/verify?token=xxx"
  style="width: 100%; height: 600px; border: none;"
></iframe>

<script>
// 监听认证完成消息
window.addEventListener('message', function(e) {
  if (e.data.type === 'auth_complete') {
    console.log('认证结果:', e.data.result);
  }
});
</script>
```

### 方案三：SDK集成
使用前端SDK，完全自定义UI。

```javascript
import { FaceAuth } from '@senmayun/face-auth-sdk';

const auth = new FaceAuth({
  token: 'your_token'
});

// 启动摄像头
auth.startCamera('#video-container');

// 开始活体检测
auth.startLiveness(['blink', 'mouth', 'head_turn'])
  .then(result => {
    console.log('认证完成:', result);
  })
  .catch(error => {
    console.error('认证失败:', error);
  });
```

## 最佳实践

### 1. 安全建议
- **不要在前端暴露API Secret**，所有签名计算放在后端
- 验证回调签名，防止伪造请求
- 使用HTTPS传输
- 定期轮换API密钥

### 2. 用户体验
- 认证前提示用户准备好身份证
- 确保光线充足
- 提供重试机制
- 显示认证进度

### 3. 错误处理
- 网络错误：提示用户检查网络
- 摄像头权限：引导用户开启权限
- 认证失败：给出具体原因和重试选项
- 系统错误：提供客服联系方式

### 4. 性能优化
- 合理设置Token有效期
- 使用CDN加速静态资源
- 压缩图片后再上传
- 客户端预检测人脸质量

## 下一步

- 阅读完整的 [API文档](./API.md)
- 查看 [部署文档](./DEPLOYMENT.md)
- 了解 [魔方财务对接](./MOFANG.md)
- 探索 [管理后台使用指南](./ADMIN.md)

## 技术支持

如有问题，请联系：
- 邮箱：support@builds.codes
- 官网：https://face.builds.codes
