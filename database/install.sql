-- ============================================================
-- 森码云实人认证系统 - 数据库安装脚本
-- 数据库: MySQL 5.7+
-- 字符集: utf8mb4
-- 创建日期: 2026-08-07
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. 管理员表
-- ============================================================
DROP TABLE IF EXISTS `sm_admin`;
CREATE TABLE `sm_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码(Argon2ID哈希)',
  `nickname` varchar(50) DEFAULT '' COMMENT '昵称',
  `email` varchar(100) DEFAULT '' COMMENT '邮箱',
  `avatar` varchar(255) DEFAULT '' COMMENT '头像',
  `role` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '角色:1超级管理员 2普通管理员',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1正常 0禁用',
  `last_login_ip` varchar(50) DEFAULT '' COMMENT '最后登录IP',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `login_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- ============================================================
-- 2. 用户表（认证用户，脱敏存储）
-- ============================================================
DROP TABLE IF EXISTS `sm_users`;
CREATE TABLE `sm_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_hash` varchar(64) NOT NULL COMMENT '用户唯一标识哈希',
  `name_encrypted` varchar(255) DEFAULT '' COMMENT '姓名(加密存储)',
  `id_card_encrypted` varchar(255) DEFAULT '' COMMENT '身份证号(加密存储)',
  `id_card_masked` varchar(32) DEFAULT '' COMMENT '身份证号(脱敏显示)',
  `phone_encrypted` varchar(255) DEFAULT '' COMMENT '手机号(加密存储)',
  `phone_masked` varchar(20) DEFAULT '' COMMENT '手机号(脱敏显示)',
  `face_feature` text COMMENT '人脸特征向量(JSON)',
  `face_image_path` varchar(255) DEFAULT '' COMMENT '人脸图片路径',
  `verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已认证:1是 0否',
  `verify_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '认证次数',
  `last_verify_time` datetime DEFAULT NULL COMMENT '最后认证时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_hash` (`user_hash`),
  KEY `idx_verified` (`verified`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- ============================================================
-- 3. 认证记录表
-- ============================================================
DROP TABLE IF EXISTS `sm_verifications`;
CREATE TABLE `sm_verifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `token` varchar(128) NOT NULL COMMENT '认证Token',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '用户ID',
  `user_hash` varchar(64) DEFAULT '' COMMENT '用户标识哈希',
  `channel` varchar(50) NOT NULL DEFAULT 'local' COMMENT '认证通道:tencent/baidu/azure/facepp/local',
  `verify_type` varchar(30) NOT NULL DEFAULT 'full' COMMENT '认证类型:liveness/face_compare/full',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态:0待认证 1通过 2未通过 3过期 4异常',
  `score` decimal(5,2) DEFAULT '0.00' COMMENT '认证分数(0-100)',
  `liveness_passed` tinyint(1) DEFAULT '0' COMMENT '活体检测是否通过',
  `face_match_score` decimal(5,2) DEFAULT '0.00' COMMENT '人脸比对分数',
  `actions` json DEFAULT NULL COMMENT '活体动作序列',
  `face_image_path` varchar(255) DEFAULT '' COMMENT '采集的人脸图片',
  `id_card_image_front` varchar(255) DEFAULT '' COMMENT '身份证正面照',
  `id_card_image_back` varchar(255) DEFAULT '' COMMENT '身份证背面照',
  `fail_reason` varchar(255) DEFAULT '' COMMENT '失败原因',
  `request_ip` varchar(50) DEFAULT '' COMMENT '请求IP',
  `user_agent` varchar(500) DEFAULT '' COMMENT 'User-Agent',
  `device_info` json DEFAULT NULL COMMENT '设备信息',
  `api_request_id` varchar(100) DEFAULT '' COMMENT '第三方API请求ID',
  `api_response` json DEFAULT NULL COMMENT '第三方API原始响应',
  `callback_url` varchar(500) DEFAULT '' COMMENT '回调URL',
  `callback_status` tinyint(1) DEFAULT '0' COMMENT '回调状态:0未回调 1成功 2失败',
  `callback_time` datetime DEFAULT NULL COMMENT '回调时间',
  `started_at` datetime DEFAULT NULL COMMENT '开始时间',
  `completed_at` datetime DEFAULT NULL COMMENT '完成时间',
  `expire_at` datetime NOT NULL COMMENT '过期时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_user_hash` (`user_hash`),
  KEY `idx_status` (`status`),
  KEY `idx_channel` (`channel`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_expire_at` (`expire_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='认证记录表';

-- ============================================================
-- 4. API密钥表
-- ============================================================
DROP TABLE IF EXISTS `sm_api_keys`;
CREATE TABLE `sm_api_keys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '密钥ID',
  `name` varchar(100) NOT NULL COMMENT '密钥名称',
  `api_key` varchar(64) NOT NULL COMMENT 'API Key',
  `api_secret` varchar(128) NOT NULL COMMENT 'API Secret',
  `user_id` int(11) unsigned DEFAULT NULL COMMENT '所属用户ID(预留)',
  `permissions` json DEFAULT NULL COMMENT '权限列表',
  `rate_limit` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '每分钟限流',
  `call_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '调用次数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1启用 0禁用',
  `expire_at` datetime DEFAULT NULL COMMENT '过期时间',
  `last_used_at` datetime DEFAULT NULL COMMENT '最后使用时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_api_key` (`api_key`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API密钥表';

-- ============================================================
-- 5. API通道配置表
-- ============================================================
DROP TABLE IF EXISTS `sm_api_channels`;
CREATE TABLE `sm_api_channels` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '通道ID',
  `name` varchar(100) NOT NULL COMMENT '通道名称',
  `code` varchar(50) NOT NULL COMMENT '通道代码:tencent/baidu/azure/facepp/local',
  `type` varchar(30) NOT NULL DEFAULT 'face' COMMENT '通道类型:face/idcard/both',
  `config` json DEFAULT NULL COMMENT '通道配置(密钥等)',
  `priority` int(11) NOT NULL DEFAULT '100' COMMENT '优先级(数字越小越优先)',
  `weight` int(11) NOT NULL DEFAULT '100' COMMENT '权重(负载均衡用)',
  `enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `daily_limit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '每日调用上限(0不限)',
  `today_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '今日调用次数',
  `success_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '成功次数',
  `fail_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `last_test_time` datetime DEFAULT NULL COMMENT '最后测试时间',
  `last_test_result` tinyint(1) DEFAULT NULL COMMENT '最后测试结果',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API通道配置表';

-- ============================================================
-- 6. API调用日志表
-- ============================================================
DROP TABLE IF EXISTS `sm_api_logs`;
CREATE TABLE `sm_api_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `api_key_id` int(11) unsigned DEFAULT NULL COMMENT 'API密钥ID',
  `api_key` varchar(64) DEFAULT '' COMMENT 'API Key',
  `channel` varchar(50) DEFAULT '' COMMENT '使用的通道',
  `endpoint` varchar(100) NOT NULL COMMENT '接口路径',
  `method` varchar(10) NOT NULL DEFAULT 'POST' COMMENT '请求方法',
  `request_ip` varchar(50) DEFAULT '' COMMENT '请求IP',
  `user_agent` varchar(500) DEFAULT '' COMMENT 'User-Agent',
  `request_params` json DEFAULT NULL COMMENT '请求参数(脱敏)',
  `response_code` int(11) DEFAULT NULL COMMENT '响应状态码',
  `response_data` json DEFAULT NULL COMMENT '响应数据(摘要)',
  `cost_time` int(11) unsigned DEFAULT '0' COMMENT '耗时(毫秒)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1成功 0失败',
  `error_message` varchar(500) DEFAULT '' COMMENT '错误信息',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_api_key` (`api_key`),
  KEY `idx_channel` (`channel`),
  KEY `idx_endpoint` (`endpoint`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API调用日志表';

-- ============================================================
-- 7. 系统配置表
-- ============================================================
DROP TABLE IF EXISTS `sm_system_config`;
CREATE TABLE `sm_system_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `group` varchar(50) NOT NULL DEFAULT 'basic' COMMENT '配置分组',
  `key` varchar(100) NOT NULL COMMENT '配置键',
  `value` text COMMENT '配置值',
  `type` varchar(20) NOT NULL DEFAULT 'string' COMMENT '值类型:string/int/bool/json',
  `description` varchar(255) DEFAULT '' COMMENT '配置说明',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_key` (`group`, `key`),
  KEY `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- ============================================================
-- 8. 操作日志表
-- ============================================================
DROP TABLE IF EXISTS `sm_operation_logs`;
CREATE TABLE `sm_operation_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `admin_id` int(11) unsigned DEFAULT NULL COMMENT '管理员ID',
  `admin_name` varchar(50) DEFAULT '' COMMENT '管理员名称',
  `module` varchar(50) NOT NULL COMMENT '模块',
  `action` varchar(50) NOT NULL COMMENT '操作',
  `target` varchar(255) DEFAULT '' COMMENT '操作对象',
  `old_value` json DEFAULT NULL COMMENT '变更前值',
  `new_value` json DEFAULT NULL COMMENT '变更后值',
  `ip` varchar(50) DEFAULT '' COMMENT '操作IP',
  `user_agent` varchar(500) DEFAULT '' COMMENT 'User-Agent',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';

-- ============================================================
-- 9. 魔方财务订单表
-- ============================================================
DROP TABLE IF EXISTS `sm_mofang_orders`;
CREATE TABLE `sm_mofang_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `mofang_order_id` varchar(100) NOT NULL COMMENT '魔方财务订单ID',
  `mofang_user_id` varchar(100) DEFAULT '' COMMENT '魔方财务用户ID',
  `verification_id` bigint(20) unsigned DEFAULT NULL COMMENT '认证记录ID',
  `token` varchar(128) DEFAULT '' COMMENT '认证Token',
  `product_id` varchar(100) DEFAULT '' COMMENT '商品ID',
  `product_name` varchar(255) DEFAULT '' COMMENT '商品名称',
  `amount` decimal(10,2) DEFAULT '0.00' COMMENT '订单金额',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态:0待支付 1已支付 2已认证 3已退款',
  `callback_data` json DEFAULT NULL COMMENT '回调数据',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mofang_order_id` (`mofang_order_id`),
  KEY `idx_verification_id` (`verification_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='魔方财务订单表';

-- ============================================================
-- 10. 安装锁表
-- ============================================================
DROP TABLE IF EXISTS `sm_install_lock`;
CREATE TABLE `sm_install_lock` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `locked` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否锁定',
  `version` varchar(20) NOT NULL COMMENT '安装版本',
  `install_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '安装时间',
  `install_ip` varchar(50) DEFAULT '' COMMENT '安装IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='安装锁表';

SET FOREIGN_KEY_CHECKS = 1;
