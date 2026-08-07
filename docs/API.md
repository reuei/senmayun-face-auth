# API 文档

## 概述

森码云实人认证提供RESTful API，支持人脸检测、人脸比对、活体检测等功能。

### 基础信息
- **Base URL**: `https://face.builds.codes`
- **协议**: HTTPS
- **数据格式**: JSON
- **字符编码**: UTF-8

### 认证方式

所有API请求都需要在Header中携带认证信息：

| Header | 说明 |
|--------|------|
| X-API-Key | API Key |
| X-Signature | HMAC-SHA256签名 |
| X-Timestamp | Unix时间戳（秒） |

### 签名算法

```
signature = HMAC_SHA256(
  api_secret,
  method + "\n" + path + "\n" + timestamp + "\n" + body
)
```

- **method**: 请求方法（大写），如 GET、POST
- **path**: 请求路径，如 /api/v1/auth/init
- **timestamp**: Unix时间戳（秒）
- **body**: 请求体（GET请求为空字符串）

### 统一响应格式

```json
{
  "code": 200,
  "message": "success",
  "data": {}
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| code | int | 状态码，200表示成功 |
| message | string | 状态描述 |
| data | object | 响应数据 |

---

## 认证接口

### 1. 初始化认证

发起一次实人认证请求。

**请求地址**
```
POST /api/v1/auth/init
```

**请求参数**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| user_id | string | 是 | 用户唯一标识 |
| return_url | string | 否 | 认证完成后跳转的URL |
| notify_url | string | 否 | 认证结果回调URL |
| extra | string | 否 | 额外参数，回调时原样返回 |
| channel | string | 否 | 指定认证通道，不指定则自动选择 |

**响应示例**

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "token": "a1b2c3d4e5f6g7h8i9j0",
    "verify_url": "https://face.builds.codes/verify?token=a1b2c3d4e5f6g7h8i9j0",
    "expire_at": 1710508800,
    "expire_in": 300
  }
}
```

**响应字段说明**

| 字段 | 类型 | 说明 |
|------|------|------|
| token | string | 认证Token |
| verify_url | string | 认证页面URL |
| expire_at | int | 过期时间戳 |
| expire_in | int | 有效期（秒） |

---

### 2. 验证Token

验证认证Token的有效性。

**请求地址**
```
POST /api/v1/auth/verify-token
```

**请求参数**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| token | string | 是 | 认证Token |

**响应示例**

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "valid": true,
    "status": "pending",
    "expire_at": 1710508800
  }
}
```

---

### 3. 查询认证结果

根据Token查询认证结果。

**请求地址**
```
GET /api/v1/auth/result
```

**请求参数**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| token | string | 是 | 认证Token |

**响应示例**

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "token": "a1b2c3d4e5f6g7h8i9j0",
    "status": "passed",
    "score": 95.5,
    "liveness_passed": true,
    "channel": "tencent",
    "channel_name": "腾讯云慧眼",
    "verify_time": "2024-03-15 14:30:28",
    "duration": 520,
    "extra": ""
  }
}
```

**状态说明**

| 状态 | 说明 |
|------|------|
| pending | 待认证 |
| passed | 认证通过 |
| failed | 认证失败 |
| expired | 已过期 |

---

### 4. 回调通知

如果设置了notify_url，认证完成后系统会向该地址发送POST回调。

**回调方式**
```
POST {notify_url}
```

**回调参数**

```json
{
  "token": "a1b2c3d4e5f6g7h8i9j0",
  "status": "passed",
  "score": 95.5,
  "liveness_passed": true,
  "channel": "tencent",
  "verify_time": "2024-03-15 14:30:28",
  "extra": "your_extra_data"
}
```

**响应要求**

回调接收方需要返回字符串 `success` 表示已成功接收。

---

## 人脸接口

### 1. 人脸检测

检测图片中的人脸，返回人脸位置和关键点信息。

**请求地址**
```
POST /api/v1/face/detect
```

**请求参数**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| image | string | 是 | 图片Base64编码 |
| max_face_num | int | 否 | 最多检测人脸数，默认1 |

**响应示例**

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "face_num": 1,
    "faces": [
      {
        "face_rect": {
          "left": 100,
          "top": 80,
          "width": 120,
          "height": 150
        },
        "landmarks": [
          {"x": 120, "y": 120},
          {"x": 180, "y": 120}
        ],
        "attributes": {
          "gender": "male",
          "age": 25
        }
      }
    ],
    "channel": "tencent",
    "latency": 150
  }
}
```

---

### 2. 人脸比对

比对两张人脸图片的相似度。

**请求地址**
```
POST /api/v1/face/compare
```

**请求参数**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| image1 | string | 是 | 第一张图片Base64 |
| image2 | string | 是 | 第二张图片Base64 |

**响应示例**

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "score": 95.5,
    "is_match": true,
    "threshold": 80,
    "channel": "tencent",
    "latency": 200
  }
}
```

---

### 3. 活体检测

检测是否为真实活体人脸。

**请求地址**
```
POST /api/v1/face/liveness
```

**请求参数**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| images | array | 是 | 动作图片Base64数组 |
| actions | array | 是 | 动作序列 |

**响应示例**

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "passed": true,
    "score": 92.3,
    "actions_result": [
      {"action": "blink", "passed": true},
      {"action": "mouth", "passed": true}
    ],
    "channel": "tencent",
    "latency": 300
  }
}
```

---

## 管理接口

### 1. 管理员登录

**请求地址**
```
POST /api/admin/login
```

**请求参数**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名 |
| password | string | 是 | 密码 |

---

### 2. 获取仪表盘数据

**请求地址**
```
GET /api/admin/dashboard
```

---

### 3. 获取认证记录

**请求地址**
```
GET /api/admin/verifications
```

**请求参数**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码，默认1 |
| page_size | int | 否 | 每页数量，默认20 |
| status | string | 否 | 状态筛选 |
| channel | string | 否 | 通道筛选 |
| keyword | string | 否 | 关键词搜索 |

---

## 错误码

| 错误码 | 说明 |
|--------|------|
| 200 | 成功 |
| 400 | 请求参数错误 |
| 401 | 认证失败（API Key无效） |
| 403 | 权限不足 |
| 404 | 资源不存在 |
| 429 | 请求过于频繁（触发速率限制） |
| 500 | 服务器内部错误 |
| 1001 | Token无效 |
| 1002 | Token已过期 |
| 1003 | Token已使用 |
| 2001 | 未检测到人脸 |
| 2002 | 检测到多张人脸 |
| 2003 | 人脸质量不达标 |
| 2004 | 活体检测失败 |
| 3001 | API通道不可用 |
| 3002 | 所有通道均失败 |

---

## SDK

### PHP SDK

```bash
composer require senmayun/face-auth
```

### JavaScript SDK

```bash
npm install @senmayun/face-auth
```

### Python SDK

```bash
pip install senmayun-face-auth
```

---

## 附录

### 状态码列表

**认证状态**
- `pending`: 待认证
- `passed`: 认证通过
- `failed`: 认证失败
- `expired`: 已过期

**通道列表**
- `tencent`: 腾讯云慧眼
- `baidu`: 百度AI
- `azure`: Azure Face
- `facepp`: Face++
- `local`: 自研算法（演示）

**活体动作**
- `blink`: 眨眼
- `mouth`: 张嘴
- `head_turn`: 摇头
- `head_nod`: 点头

---

## 技术支持

- 文档：https://face.builds.codes/docs
- 邮箱：support@builds.codes
