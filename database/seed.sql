-- ============================================================
-- 森码云实人认证系统 - 初始数据
-- ============================================================

-- ============================================================
-- 初始管理员账号
-- 用户名: admin
-- 密码: admin123456 (需在安装时重新设置)
-- ============================================================
INSERT INTO `sm_admin` (`username`, `password`, `nickname`, `email`, `role`, `status`) VALUES
('admin', '$argon2id$v=19$m=65536,t=4,p=1$hash_placeholder', '超级管理员', 'admin@builds.codes', 1, 1);

-- ============================================================
-- 默认API通道配置
-- ============================================================
INSERT INTO `sm_api_channels` (`name`, `code`, `type`, `config`, `priority`, `weight`, `enabled`) VALUES
('腾讯云慧眼', 'tencent', 'both', '{"secret_id":"","secret_key":"","rule_id":""}', 1, 100, 0),
('百度AI人脸识别', 'baidu', 'both', '{"app_id":"","api_key":"","secret_key":""}', 2, 100, 0),
('Azure Face API', 'azure', 'face', '{"subscription_key":"","endpoint":""}', 3, 100, 0),
('Face++', 'facepp', 'both', '{"api_key":"","api_secret":"","api_url":"https://api-cn.faceplusplus.com"}', 4, 100, 0),
('自研算法(演示)', 'local', 'face', '{"threshold":80,"feature_dim":128}', 5, 100, 1);

-- ============================================================
-- 默认系统配置
-- ============================================================
INSERT INTO `sm_system_config` (`group`, `key`, `value`, `type`, `description`, `sort`) VALUES
-- 基础设置
('basic', 'site_name', '森码云实人认证系统', 'string', '网站名称', 1),
('basic', 'site_logo', '/assets/svg/logo.svg', 'string', '网站Logo', 2),
('basic', 'site_domain', 'face.builds.codes', 'string', '网站域名', 3),
('basic', 'site_description', '基于AI人脸识别技术的实人认证SaaS平台', 'string', '网站描述', 4),
('basic', 'site_keywords', '实人认证,人脸识别,活体检测,人脸比对', 'string', '网站关键词', 5),
('basic', 'icp_number', '', 'string', 'ICP备案号', 6),

-- 认证设置
('auth', 'match_threshold', '80', 'int', '人脸比对阈值(0-100)', 1),
('auth', 'token_ttl', '900', 'int', '认证Token有效期(秒)', 2),
('auth', 'liveness_actions', '3', 'int', '活体检测动作数量', 3),
('auth', 'action_timeout', '15', 'int', '每个动作超时时间(秒)', 4),
('auth', 'retry_limit', '3', 'int', '每天重试次数限制', 5),
('auth', 'face_image_retention', '60', 'int', '人脸图片保留天数(0永久)', 6),
('auth', 'default_channel', 'local', 'string', '默认认证通道', 7),

-- 安全设置
('security', 'api_rate_limit', '100', 'int', 'API每分钟限流', 1),
('security', 'login_rate_limit', '5', 'int', '登录每分钟限流', 2),
('security', 'ip_whitelist', '', 'string', 'IP白名单(逗号分隔)', 3),
('security', 'session_ttl', '7200', 'int', '会话有效期(秒)', 4),
('security', 'password_min_length', '8', 'int', '密码最小长度', 5),

-- 魔方财务设置
('mofang', 'enabled', '0', 'bool', '是否启用魔方财务对接', 1),
('mofang', 'api_url', '', 'string', '魔方财务API地址', 2),
('mofang', 'api_username', '', 'string', '魔方财务API用户名', 3),
('mofang', 'api_key', '', 'string', '魔方财务API密钥', 4),
('mofang', 'callback_secret', '', 'string', '回调签名密钥', 5),
('mofang', 'auto_verify', '1', 'bool', '认证通过自动更新用户状态', 6);

-- ============================================================
-- 安装锁
-- ============================================================
-- 安装完成后由安装程序写入
-- INSERT INTO `sm_install_lock` (`locked`, `version`, `install_ip`) VALUES (1, '1.0.0', '');
