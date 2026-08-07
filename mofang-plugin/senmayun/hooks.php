<?php
/**
 * 森码云实人认证 - 钩子文件
 * 
 * 处理魔方财务的各种钩子事件
 */

if (!defined('ROOT')) {
    exit('Access Denied');
}

/**
 * 订单支付完成钩子
 * 支付完成后自动发起实人认证
 */
function hook_senmayun_order_paid($orderId) {
    $plugin = new Senmayun_FaceAuth();
    $config = $plugin->getConfig();
    
    // 检查是否开启自动认证
    if (!isset($config['auto_verify_on_paid']) || $config['auto_verify_on_paid'] != '1') {
        return;
    }
    
    $order = DB::find('order', ['id' => $orderId]);
    if (!$order) {
        return;
    }
    
    // 检查产品是否需要认证
    // 这里可以根据产品ID判断哪些产品需要认证
    $result = $plugin->createVerification($order['uid'], $orderId, $order['pid']);
    
    if ($result['status']) {
        // 保存token到session，用于跳转
        $_SESSION['senmayun_verify_token'] = $result['token'];
        $_SESSION['senmayun_verify_url'] = $result['verify_url'];
    }
}

/**
 * 用户注册钩子
 * 注册后提示进行实人认证
 */
function hook_senmayun_user_register($uid) {
    // 可以在这里添加注册后的认证提示逻辑
}

/**
 * 客户端导航钩子
 * 在用户中心添加认证入口
 */
function hook_senmayun_client_area_nav() {
    return [
        'senmayun' => [
            'name' => '实人认证',
            'icon' => 'fa-id-card',
            'url' => 'index.php?m=senmayun',
            'order' => 50
        ]
    ];
}
