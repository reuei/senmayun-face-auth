# 森码云实人认证系统

> SaaS 化人脸核身 / 实人认证平台，支持活体检测、人脸比对、多通道冗余，可对接魔方财务系统。

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-777BB4.svg)](https://www.php.net/)
[![Vue Version](https://img.shields.io/badge/Vue-3.x-4FC08D.svg)](https://vuejs.org/)

## 项目简介

森码云实人认证系统是一套完整的 SaaS 化实人认证解决方案，提供从人脸采集、活体检测到人脸比对的全流程服务。系统采用多通道冗余架构，同时接入腾讯云慧眼、百度AI、Azure Face、Face++ 等主流人脸识别服务，任一通道故障自动切换，保障 99.99% 可用性。

## 功能特性

### 🔐 核心功能
- **活体检测**：动作活体 + 静默活体双引擎，有效防御照片、视频、3D面具攻击
- **人脸比对**：1:1 人脸比对准确率 99.8%，支持与身份证照片、自传照片比对
- **人脸检测**：毫秒级人脸检测，支持多人脸、遮挡、侧脸等复杂场景
- **身份证 OCR**：自动识别身份证信息，减少用户输入

### 🛡️ 多通道架构
- 腾讯云慧眼（主通道，H5 人脸核身）
- 百度 AI 人脸识别（备用通道）
- Azure Face API（海外通道）
- Face++ 旷视（备选通道）
- 自研算法通道（演示 / 离线可用）

### 🔌 开放能力
- RESTful API 接口，HMAC-SHA256 签名验证
- 长 Token 机制，单次有效 + 15 分钟过期 + 使用即焚
- 异步回调 + 轮询查询双模式
- 速率限制、IP 白名单

### 🏢 魔方财务对接
- 标准 Server 模块插件，即插即用
- 实人认证商品一键创建
- 认证通过自动更新用户实名状态
- 完整的订单与回调记录

### 🎨 管理后台
- 数据仪表盘（今日认证数、通过率、趋势图）
- 认证记录查询与人工复核
- API 通道管理（动态启停、优先级配置、负载均衡）
- 魔方财务对接配置
- 系统设置与操作日志

## 技术架构

```
┌─────────────────────────────────────────────────────────┐
│                        前端层                            │
│  Vue 3 + TypeScript + Element Plus + TailwindCSS        │
│  face-api.js (浏览器端人脸识别)                           │
└───────────────────────────┬─────────────────────────────┘
                            │
┌───────────────────────────┼─────────────────────────────┐
│                        网关层                            │
│  Nginx / Apache + .htaccess + 安全头 + 静态资源缓存       │
└───────────────────────────┬─────────────────────────────┘
                            │
┌───────────────────────────┼─────────────────────────────┐
│                       应用层                             │
│  ThinkPHP 8 + PHP 8.1+                                   │
│  ┌─────────────────────────────────────────────────┐    │
│  │  控制器层  │  服务层  │  模型层  │  中间件       │    │
│  └─────────────────────────────────────────────────┘    │
└───────────────────────────┬─────────────────────────────┘
                            │
┌───────────────────────────┼─────────────────────────────┐
│                    服务通道层                             │
│  ┌──────────┐ ┌────────┐ ┌───────┐ ┌────────┐ ┌─────┐ │
│  │ 腾讯慧眼 │ │ 百度AI │ │ Azure │ │ Face++ │ │自研 │ │
│  └──────────┘ └────────┘ └───────┘ └────────┘ └─────┘ │
└───────────────────────────┬─────────────────────────────┘
                            │
┌───────────────────────────┼─────────────────────────────┐
│                       数据层                             │
│  MySQL 5.7+ + Redis (可选) + 文件存储                    │
└─────────────────────────────────────────────────────────┘
```

## 目录结构

```
senmayun-face-auth/
├── app/                      # ThinkPHP 应用目录
│   ├── controller/           # 控制器
│   ├── model/                # 模型
│   ├── service/              # 服务层
│   │   └── Channel/          # 多通道适配器
│   └── middleware/           # 中间件
├── config/                   # 配置文件
├── public/                   # Web 入口目录
│   ├── index.php             # 后端入口
│   ├── dist/                 # Vue 打包产物
│   └── assets/               # 静态资源
├── resources/                # Vue 前端源码
│   └── src/
│       ├── views/            # 页面
│       ├── components/       # 组件
│       ├── api/              # API 封装
│       └── router/           # 路由
├── mofang-plugin/            # 魔方财务插件
├── database/                 # SQL 脚本
├── route/                    # 路由定义
├── runtime/                  # 运行时目录
└── vendor/                   # Composer 依赖
```

## 快速开始

### 环境要求

- PHP >= 8.1
- MySQL >= 5.7
- Nginx / Apache
- Composer
- Node.js >= 16 (前端开发)

### 部署步骤

#### 1. 下载源码

```bash
git clone https://github.com/senmayun/face-auth.git
cd face-auth
```

#### 2. 安装后端依赖

```bash
composer install --no-dev
```

#### 3. 配置环境变量

```bash
cp .env.example .env
# 编辑 .env 文件，配置数据库等信息
```

#### 4. 构建前端

```bash
cd resources
npm install
npm run build
```

#### 5. 配置 Web 服务器

**Nginx 配置示例：**

```nginx
server {
    listen 80;
    server_name face.builds.codes;
    root /path/to/senmayun-face-auth/public;
    index index.html index.php;

    location / {
        try_files $uri $uri/ /dist/index.html;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(ht|env) {
        deny all;
    }
}
```

**Apache：** 直接使用 `public/.htaccess` 即可。

#### 6. 运行安装向导

访问 `https://your-domain/install`，按照向导完成安装。

### 开发模式

```bash
# 后端启动
php think run

# 前端启动
cd resources
npm run dev
```

## 魔方财务对接

### 安装插件

1. 将 `mofang-plugin/senmayun/` 目录复制到魔方财务的 `public/plugins/server/` 目录下
2. 在魔方财务后台「插件管理」中启用插件
3. 配置 API 地址、API Key 等信息

### 使用流程

1. 用户在魔方财务前台购买「实人认证」商品
2. 支付完成后点击「去认证」
3. 跳转到森码云实人认证页面完成认证
4. 认证结果自动回调更新用户实名状态

## API 文档

### 认证流程

```
1. 调用 /api/v1/auth/init 获取认证 Token 和跳转 URL
2. 用户跳转到认证页面完成人脸采集和活体检测
3. 认证完成后系统回调通知，或主动调用 /api/v1/auth/result 查询结果
```

### 主要接口

| 接口 | 方法 | 说明 |
|------|------|------|
| `/api/v1/auth/init` | POST | 初始化认证会话 |
| `/api/v1/auth/verify-token` | POST | 验证 Token 有效性 |
| `/api/v1/auth/result` | POST | 查询认证结果 |
| `/api/v1/face/detect` | POST | 人脸检测 |
| `/api/v1/face/compare` | POST | 人脸比对 |

### 签名方式

所有 API 请求需在 Header 中携带：

- `X-API-Key`: API Key
- `X-Signature`: HMAC-SHA256 签名
- `X-Timestamp`: 当前时间戳

签名生成方式：将所有请求参数按 key 排序后拼接，使用 API Secret 进行 HMAC-SHA256 签名。

## 安全说明

- 所有用户输入经验证器过滤
- API 接口 HMAC-SHA256 签名校验
- 长 Token 单次有效 + 15 分钟过期
- 人脸图像传输全程 HTTPS，存储加密
- 防重放攻击：nonce + timestamp
- SQL 注入、XSS、CSRF 防护
- 速率限制：每 IP 每分钟最多 10 次认证请求
- 敏感配置写入环境变量，不硬编码

## 许可证

本项目采用 [Apache License 2.0](LICENSE) 开源协议。

## 技术支持

- 文档：[https://face.builds.codes/docs](https://face.builds.codes/docs)
- 邮箱：support@builds.codes

---

**注意**：本系统涉及人脸识别等敏感功能，请确保遵守当地法律法规，获取用户明确授权后再进行人脸采集和处理。
