# 森码云实人认证系统

> SaaS 化人脸核身 / 实人认证平台，支持活体检测、人脸比对、多通道冗余，可对接魔方财务系统。

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-777BB4.svg)](https://www.php.net/)
[![Vue Version](https://img.shields.io/badge/Vue-3.x-4FC08D.svg)](https://vuejs.org/)

## 项目简介

森码云实人认证系统是一套完整的实人认证解决方案，基于纯PHP + Vue3 CDN架构开发，**无需编译，上传到虚拟主机即可运行**。支持多通道人脸识别、活体检测、魔方财务系统对接等功能。

## ✨ 特性

- 🚀 **零编译部署** - 纯PHP + CDN前端，上传即用
- 🎯 **多通道冗余** - 腾讯云、百度AI、Azure、Face++、自研算法
- 🔐 **活体检测** - 动作活体、炫彩活体、近红外活体
- 📊 **可视化管理** - 完整的后台管理系统
- 💰 **魔方财务对接** - 无缝对接魔方财务系统
- 🔒 **安全可靠** - 数据加密、速率限制、IP白名单
- 📱 **响应式设计** - 完美适配PC和移动端

## 📁 目录结构

```
senmayun-face-auth/
├── public/                  # 网站根目录（需绑定到此处）
│   ├── index.html          # 前台首页（CDN版本）
│   ├── admin.html          # 管理后台（CDN版本）
│   ├── install.html        # 安装向导
│   ├── docs.html           # API文档
│   ├── api.php             # API入口（纯PHP）
│   ├── .htaccess           # Apache重写规则
│   └── uploads/            # 上传文件目录
├── app/                     # 应用代码
│   ├── controller/         # 控制器
│   ├── service/            # 服务层
│   ├── middleware/         # 中间件
│   └── helpers.php         # 辅助函数
├── config/                  # 配置文件
│   ├── app.php             # 应用配置
│   └── database.php        # 数据库配置（安装后生成）
├── database/                # 数据库
│   ├── install.sql         # 安装SQL
│   └── install.lock        # 安装锁（安装后生成）
├── mofang-plugin/           # 魔方财务插件
│   └── senmayun/           # 插件目录
└── docs/                    # 文档
```

## 🚀 快速开始

### 环境要求

- PHP >= 7.4
- MySQL >= 5.7
- PDO PHP Extension
- GD PHP Extension
- OpenSSL PHP Extension

### 安装步骤

1. **上传文件**
   - 将所有文件上传到您的网站根目录
   - 确保网站根目录绑定到 `public/` 目录

2. **运行安装向导**
   - 访问 `https://your-domain.com/install.html`
   - 按照提示完成环境检测、数据库配置、管理员设置

3. **完成安装**
   - 安装完成后，删除 `install.html` 文件
   - 删除 `database/install.sql` 文件（可选，建议删除）

4. **开始使用**
   - 前台：`https://your-domain.com/`
   - 后台：`https://your-domain.com/admin.html`
   - 文档：`https://your-domain.com/docs.html`

## 🎯 功能模块

### 前台功能
- 🏠 首页展示
- 👤 实人认证页面
- ✅ 认证结果页面
- 📄 API文档

### 后台功能
- 📊 仪表盘（数据统计、趋势图）
- 📋 认证记录管理
- 🔌 API通道管理
- 💰 魔方财务配置
- ⚙️ 系统设置
- 📝 操作日志

### 认证通道
- 腾讯云慧眼
- 百度AI人脸识别
- Azure Face API
- Face++ (旷视科技)
- 自研本地演示算法

## 🔌 API接口

### 基础信息
- Base URL: `https://your-domain.com/api/v1`
- 数据格式: JSON
- 认证方式: API Key

### 主要接口

| 接口 | 方法 | 说明 |
|------|------|------|
| `/auth/init` | POST | 初始化认证 |
| `/auth/verify` | POST | 提交认证 |
| `/auth/result` | GET | 查询结果 |

详细API文档请访问 `https://your-domain.com/docs.html`

## 💰 魔方财务对接

系统内置魔方财务对接模块，支持：
- 用户购买后自动发起认证
- 认证通过自动开通服务
- 产品映射配置
- 订单同步

**插件安装：**
1. 将 `mofang-plugin/senmayun/` 目录复制到魔方财务插件目录
2. 在魔方财务后台启用插件
3. 配置API地址和密钥

## 🛠️ 技术栈

### 前端
- Vue 3 (CDN)
- Element Plus (CDN)
- TailwindCSS (CDN)
- ECharts (CDN)
- Font Awesome (CDN)

### 后端
- PHP 7.4+
- PDO (MySQL)
- 纯PHP实现，无需框架

## 📝 开发说明

### 前端开发
所有前端页面均为纯HTML + CDN引入，无需编译：
- `index.html` - 前台页面
- `admin.html` - 后台管理
- `install.html` - 安装向导
- `docs.html` - API文档

直接修改对应HTML文件即可。

### 后端开发
后端采用纯PHP实现，入口文件为 `public/api.php`：
- 路由处理在 `api.php` 中
- 业务逻辑在 `app/service/` 目录
- 数据操作使用PDO

## 🔒 安全建议

1. 安装完成后删除安装文件
2. 修改默认管理员密码
3. 启用HTTPS
4. 配置IP白名单
5. 定期备份数据库
6. 开启操作日志

## 📄 许可证

Apache License 2.0

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📧 联系方式

- 官网：face.builds.codes
- 邮箱：support@builds.codes

---

**如果本项目对您有帮助，请给个 Star ⭐ 支持一下！**
