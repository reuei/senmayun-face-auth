# 部署文档

## 系统要求

### 服务器要求
- PHP >= 8.1
- MySQL >= 5.7 或 MariaDB >= 10.3
- Apache / Nginx
- 可选：Redis（用于缓存和速率限制）

### PHP扩展要求
- pdo_mysql
- mbstring
- openssl
- json
- curl
- gd（用于自研人脸检测）
- fileinfo

## 部署方式

### 方式一：虚拟主机部署（推荐）

#### 1. 上传文件
```bash
# 将项目文件上传到网站根目录
# 注意：网站根目录必须指向 public/ 目录
```

#### 2. 目录结构
```
www/
├── app/              # 应用代码
├── config/           # 配置文件
├── database/         # 数据库脚本
├── public/           # 网站根目录（必须绑定到此目录）
│   ├── index.php     # 入口文件
│   ├── dist/         # 前端构建产物
│   └── .htaccess     # URL重写规则
├── resources/        # 前端源码
├── route/            # 路由定义
├── runtime/          # 运行时目录（需要写入权限）
├── vendor/           # Composer依赖
├── .env              # 环境配置
└── composer.json
```

#### 3. 设置目录权限
```bash
# 以下目录需要写入权限
chmod 755 runtime/
chmod 755 public/uploads/
```

#### 4. 创建数据库
- 在主机面板创建MySQL数据库
- 记录数据库名、用户名、密码

#### 5. 运行安装向导
1. 访问 `https://your-domain.com/install`
2. 按照提示完成环境检测
3. 填写数据库配置信息
4. 创建管理员账号
5. 配置API通道
6. 完成安装

### 方式二：VPS/云服务器部署

#### 1. 安装环境
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.1 php8.1-mysql php8.1-curl php8.1-gd php8.1-mbstring php8.1-xml mysql-server apache2

# CentOS/RHEL
sudo yum install php php-mysqlnd php-curl php-gd php-mbstring php-xml mariadb-server httpd
```

#### 2. 配置虚拟主机
```apache
<VirtualHost *:80>
    ServerName face.yourdomain.com
    DocumentRoot /var/www/senmayun-face-auth/public
    
    <Directory /var/www/senmayun-face-auth/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/face-auth-error.log
    CustomLog ${APACHE_LOG_DIR}/face-auth-access.log combined
</VirtualHost>
```

#### 3. 安装Composer依赖
```bash
cd /var/www/senmayun-face-auth
composer install --no-dev --optimize-autoloader
```

#### 4. 构建前端
```bash
cd resources
npm install
npm run build
```

#### 5. 设置权限
```bash
chown -R www-data:www-data /var/www/senmayun-face-auth
chmod -R 755 /var/www/senmayun-face-auth/runtime
chmod -R 755 /var/www/senmayun-face-auth/public/uploads
```

#### 6. 运行安装向导
访问 `https://your-domain.com/install` 完成安装

### 方式三：Docker部署

```dockerfile
FROM php:8.1-apache

# 安装扩展
RUN docker-php-ext-install pdo_mysql mbstring curl gd

# 启用重写
RUN a2enmod rewrite

# 复制文件
COPY . /var/www/html/

# 设置工作目录
WORKDIR /var/www/html

# 设置权限
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/runtime
```

```yaml
# docker-compose.yml
version: '3'
services:
  web:
    build: .
    ports:
      - "80:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
  
  mysql:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: face_auth
      MYSQL_USER: face_auth
      MYSQL_PASSWORD: face_auth
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

## Nginx配置

如果使用Nginx，配置如下：

```nginx
server {
    listen 80;
    server_name face.yourdomain.com;
    root /var/www/senmayun-face-auth/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 安全头
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    
    # 静态资源缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

## 配置HTTPS

### 使用Let's Encrypt
```bash
# 安装certbot
sudo apt install certbot python3-certbot-apache

# 获取证书
sudo certbot --apache -d face.yourdomain.com
```

### 强制HTTPS
在 `.htaccess` 中添加：
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 安全加固

### 1. 隐藏PHP版本
```php
// php.ini
expose_php = Off
```

### 2. 禁用危险函数
```php
// php.ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
```

### 3. 设置open_basedir
```php
// php.ini
open_basedir = /var/www/senmayun-face-auth/:/tmp/
```

### 4. 定期备份
```bash
# 数据库备份
mysqldump -u root -p face_auth > backup_$(date +%Y%m%d).sql

# 文件备份
tar -czf backup_files_$(date +%Y%m%d).tar.gz /var/www/senmayun-face-auth
```

## 性能优化

### 1. 启用OPcache
```php
// php.ini
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 60
```

### 2. 使用Redis缓存
在 `.env` 中配置：
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 3. 开启Gzip压缩
已在 `.htaccess` 中配置，确保 `mod_deflate` 已启用。

## 升级指南

### 版本升级步骤
1. 备份数据库和文件
2. 下载新版本
3. 替换文件（保留 `.env` 和 `public/uploads/`）
4. 运行数据库迁移
5. 清除缓存
6. 测试功能

### 注意事项
- 升级前务必备份
- 大版本升级建议在测试环境先验证
- 关注更新日志中的Breaking Changes

## 常见问题

### Q: 安装后访问显示500错误
A: 检查PHP版本是否满足要求，查看错误日志。

### Q: 图片无法上传
A: 检查 `public/uploads/` 目录权限。

### Q: API调用失败
A: 检查API通道配置是否正确，测试连接是否正常。

### Q: 页面样式错乱
A: 确认前端已构建，`public/dist/` 目录存在。

## 技术支持

- 文档：https://face.builds.codes/docs
- 邮箱：support@builds.codes
- 社区：https://github.com/senmayun/face-auth/issues
