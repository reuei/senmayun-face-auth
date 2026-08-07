<?php
/**
 * 森码云实人认证 - 页面文件
 * 
 * 处理前端页面展示
 */

if (!defined('ROOT')) {
    exit('Access Denied');
}

/**
 * 认证页面
 */
function senmayun_verifyPage() {
    $uid = $_SESSION['uid'] ?? 0;
    
    if (!$uid) {
        header('Location: index.php?m=user&a=login');
        exit;
    }
    
    $plugin = new Senmayun_FaceAuth();
    $config = $plugin->getConfig();
    
    // 检查是否已认证
    $authStatus = $plugin->getUserAuthStatus($uid);
    
    // 处理发起认证请求
    if ($_POST && isset($_POST['action']) && $_POST['action'] == 'verify') {
        $result = $plugin->createVerification($uid);
        
        if ($result['status']) {
            header('Location: ' . $result['verify_url']);
            exit;
        }
        
        $error = $result['msg'];
    }
    
    // 输出页面
    $title = '实人认证';
    $content = '<div class="senmayun-verify-page">';
    
    if ($authStatus['verified']) {
        $content .= '<div class="alert alert-success">
            <h4><i class="fa fa-check-circle"></i> 您已完成实人认证</h4>
            <p>认证时间：' . $authStatus['verify_time'] . '</p>
            <p>相似度：' . $authStatus['score'] . '%</p>
        </div>';
    } else {
        if (isset($error)) {
            $content .= '<div class="alert alert-danger">' . $error . '</div>';
        }
        
        $content .= '<div class="panel panel-default">
            <div class="panel-heading">实人认证</div>
            <div class="panel-body">
                <p>为保障您的账户安全，部分服务需要完成实人认证后才能使用。</p>
                <ul>
                    <li>✓ 全程加密，保护您的隐私</li>
                    <li>✓ 仅需30秒即可完成</li>
                    <li>✓ 支持多种认证方式</li>
                </ul>
                <form method="post" style="margin-top: 20px;">
                    <input type="hidden" name="action" value="verify">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fa fa-id-card"></i> 开始认证
                    </button>
                </form>
            </div>
        </div>';
    }
    
    $content .= '</div>';
    
    // 使用魔方财务的模板输出
    if (function_exists('view')) {
        view($title, $content);
    } else {
        echo $content;
    }
}

/**
 * 回调页面（用户跳转回来）
 */
function senmayun_callbackPage() {
    $token = $_GET['token'] ?? '';
    $orderId = $_GET['order_id'] ?? 0;
    
    if (!$token) {
        header('Location: index.php');
        exit;
    }
    
    $plugin = new Senmayun_FaceAuth();
    $result = $plugin->queryStatus($token);
    
    $title = '认证结果';
    $content = '<div class="senmayun-result-page">';
    
    if (isset($result['status']) && $result['status'] == 'passed') {
        $content .= '<div class="alert alert-success">
            <h4><i class="fa fa-check-circle"></i> 认证成功</h4>
            <p>恭喜您，实人认证已通过！</p>
            <p>相似度：' . ($result['score'] ?? 0) . '%</p>
        </div>';
        
        if ($orderId) {
            $content .= '<p><a href="index.php?m=order&a=detail&id=' . $orderId . '" class="btn btn-primary">查看订单</a></p>';
        } else {
            $content .= '<p><a href="index.php?m=user" class="btn btn-primary">返回用户中心</a></p>';
        }
    } elseif (isset($result['status']) && $result['status'] == 'failed') {
        $content .= '<div class="alert alert-danger">
            <h4><i class="fa fa-times-circle"></i> 认证失败</h4>
            <p>很抱歉，实人认证未通过，请重新尝试。</p>
            <p><a href="index.php?m=senmayun" class="btn btn-primary">重新认证</a></p>
        </div>';
    } else {
        $content .= '<div class="alert alert-warning">
            <h4><i class="fa fa-clock-o"></i> 认证处理中</h4>
            <p>认证结果正在处理，请稍后查看。</p>
            <p><a href="index.php?m=senmayun" class="btn btn-primary">查看状态</a></p>
        </div>';
    }
    
    $content .= '</div>';
    
    if (function_exists('view')) {
        view($title, $content);
    } else {
        echo $content;
    }
}

/**
 * 通知页面（服务器回调）
 */
function senmayun_notifyPage() {
    $plugin = new Senmayun_FaceAuth();
    
    // 获取POST数据
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    // 处理回调
    $result = $plugin->handleCallback($data);
    
    if ($result) {
        echo 'success';
    } else {
        echo 'fail';
    }
    
    exit;
}
