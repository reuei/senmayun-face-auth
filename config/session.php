<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - Session配置
// +----------------------------------------------------------------------

return [
    // session name
    'name'           => 'SENMAYUN_SESSION',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => 'file',
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间
    'expire'         => 7200,
    // 前缀
    'prefix'         => 'sm_',
    // 是否自动开启session
    'auto_start'     => true,
    // 是否使用cookie
    'use_cookies'    => true,
    // 是否仅通过cookie传递sessionid
    'use_only_cookies' => true,
    // cookie设置
    'cookie' => [
        'httponly' => true,
        'secure'   => false,
        'samesite' => 'Lax',
    ],
];
