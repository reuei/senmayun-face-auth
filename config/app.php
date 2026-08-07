<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 应用配置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,

    // 系统名称
    'system_name'      => '森码云实人认证系统',
    'system_version'   => '1.0.0',
    'system_logo'      => '/assets/svg/logo.svg',

    // 人脸认证配置
    'face_auth' => [
        // 认证token有效期（秒）
        'token_ttl'      => 900,
        // 比对阈值（0-100）
        'match_threshold' => 80,
        // 活体动作数量
        'liveness_actions' => 3,
        // 每个动作超时时间（秒）
        'action_timeout'   => 15,
    ],
];
