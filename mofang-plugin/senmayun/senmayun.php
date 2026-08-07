<?php
/**
 * 森码云实人认证 - 魔方财务插件
 * 
 * 插件名称: 森码云实人认证
 * 插件标识: senmayun
 * 版本: 1.0.0
 * 作者: 森码云
 * 描述: 与森码云实人认证系统对接，为魔方财务提供实人认证功能
 * 依赖: 魔方财务系统 >= 3.0
 */

if (!defined('ROOT')) {
    exit('Access Denied');
}

class Senmayun_FaceAuth {
    
    /**
     * 插件信息
     */
    public $info = [
        'name'        => '森码云实人认证',
        'code'        => 'senmayun',
        'version'     => '1.0.0',
        'author'      => '森码云',
        'description' => '与森码云实人认证系统对接，提供实人认证服务',
        'config'      => []
    ];
    
    /**
     * 配置项
     */
    private $config = [];
    
    /**
     * API地址
     */
    private $apiUrl = '';
    
    /**
     * 构造函数
     */
    public function __construct() {
        $this->config = $this->getConfig();
        $this->apiUrl = isset($this->config['api_url']) ? rtrim($this->config['api_url'], '/') : '';
    }
    
    /**
     * 插件安装
     */
    public function install() {
        // 创建认证记录表
        $sql = "CREATE TABLE IF NOT EXISTS `senmayun_face_auth` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `uid` int(11) NOT NULL COMMENT '用户ID',
            `order_id` int(11) DEFAULT NULL COMMENT '订单ID',
            `product_id` int(11) DEFAULT NULL COMMENT '产品ID',
            `token` varchar(100) DEFAULT NULL COMMENT '认证Token',
            `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态:0待认证,1通过,2失败,3过期',
            `score` decimal(5,2) DEFAULT NULL COMMENT '相似度',
            `liveness_passed` tinyint(1) DEFAULT NULL COMMENT '活体检测',
            `verify_time` datetime DEFAULT NULL COMMENT '认证时间',
            `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `uid` (`uid`),
            KEY `token` (`token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        DB::query($sql);
        
        return true;
    }
    
    /**
     * 插件卸载
     */
    public function uninstall() {
        // 可选：删除表
        // DB::query("DROP TABLE IF EXISTS `senmayun_face_auth`");
        return true;
    }
    
    /**
     * 插件启用
     */
    public function enable() {
        return true;
    }
    
    /**
     * 插件禁用
     */
    public function disable() {
        return true;
    }
    
    /**
     * 获取配置
     */
    public function getConfig() {
        $config = [];
        $result = DB::query("SELECT * FROM `mod_config` WHERE `mod` = 'senmayun'");
        while ($row = DB::fetch($result)) {
            $config[$row['k']] = $row['v'];
        }
        return $config;
    }
    
    /**
     * 保存配置
     */
    public function saveConfig($data) {
        foreach ($data as $k => $v) {
            $exists = DB::find('mod_config', ['mod' => 'senmayun', 'k' => $k]);
            if ($exists) {
                DB::update('mod_config', ['v' => $v], ['mod' => 'senmayun', 'k' => $k]);
            } else {
                DB::insert('mod_config', ['mod' => 'senmayun', 'k' => $k, 'v' => $v]);
            }
        }
        return true;
    }
    
    /**
     * 发起认证
     */
    public function createVerification($uid, $orderId = 0, $productId = 0) {
        if (empty($this->apiUrl)) {
            return ['status' => false, 'msg' => '森码云API地址未配置'];
        }
        
        $user = DB::find('users', ['id' => $uid]);
        if (!$user) {
            return ['status' => false, 'msg' => '用户不存在'];
        }
        
        // 生成认证请求
        $params = [
            'user_id' => $uid,
            'order_id' => $orderId,
            'product_id' => $productId,
            'return_url' => $this->getReturnUrl($orderId),
            'notify_url' => $this->getNotifyUrl(),
            'extra' => json_encode([
                'uid' => $uid,
                'order_id' => $orderId,
                'product_id' => $productId
            ])
        ];
        
        $result = $this->apiRequest('/api/v1/auth/init', $params, 'POST');
        
        if ($result && $result['code'] == 200) {
            $token = $result['data']['token'];
            $verifyUrl = $result['data']['verify_url'];
            
            // 保存记录
            DB::insert('senmayun_face_auth', [
                'uid' => $uid,
                'order_id' => $orderId,
                'product_id' => $productId,
                'token' => $token,
                'status' => 0,
                'create_time' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'status' => true,
                'token' => $token,
                'verify_url' => $verifyUrl
            ];
        }
        
        return ['status' => false, 'msg' => $result['message'] ?? '认证请求失败'];
    }
    
    /**
     * 查询认证状态
     */
    public function queryStatus($token) {
        if (empty($this->apiUrl)) {
            return ['status' => false, 'msg' => 'API地址未配置'];
        }
        
        $result = $this->apiRequest('/api/v1/auth/result', ['token' => $token], 'GET');
        
        if ($result && $result['code'] == 200) {
            return $result['data'];
        }
        
        return ['status' => false, 'msg' => $result['message'] ?? '查询失败'];
    }
    
    /**
     * 回调处理
     */
    public function handleCallback($data) {
        $token = $data['token'] ?? '';
        $status = $data['status'] ?? '';
        $score = $data['score'] ?? 0;
        $livenessPassed = $data['liveness_passed'] ?? false;
        
        if (empty($token)) {
            return false;
        }
        
        $record = DB::find('senmayun_face_auth', ['token' => $token]);
        if (!$record) {
            return false;
        }
        
        // 更新状态
        $statusMap = [
            'passed' => 1,
            'failed' => 2,
            'expired' => 3
        ];
        
        $newStatus = $statusMap[$status] ?? 0;
        
        DB::update('senmayun_face_auth', [
            'status' => $newStatus,
            'score' => $score,
            'liveness_passed' => $livenessPassed ? 1 : 0,
            'verify_time' => date('Y-m-d H:i:s')
        ], ['id' => $record['id']]);
        
        // 认证通过，开通服务
        if ($newStatus == 1 && $record['order_id'] > 0) {
            $this->activateService($record['order_id']);
        }
        
        return true;
    }
    
    /**
     * 开通服务
     */
    private function activateService($orderId) {
        $order = DB::find('order', ['id' => $orderId]);
        if (!$order) {
            return false;
        }
        
        // 调用魔方财务的开通服务方法
        if (class_exists('Order')) {
            $orderObj = new Order();
            $orderObj->activate($orderId);
        }
        
        return true;
    }
    
    /**
     * 获取返回地址
     */
    private function getReturnUrl($orderId = 0) {
        $url = $this->getSiteUrl() . '/index.php?m=senmayun&a=callback';
        if ($orderId) {
            $url .= '&order_id=' . $orderId;
        }
        return $url;
    }
    
    /**
     * 获取通知地址
     */
    private function getNotifyUrl() {
        return $this->getSiteUrl() . '/index.php?m=senmayun&a=notify';
    }
    
    /**
     * 获取站点URL
     */
    private function getSiteUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host;
    }
    
    /**
     * API请求
     */
    private function apiRequest($path, $params = [], $method = 'GET') {
        $url = $this->apiUrl . $path;
        
        $apiKey = $this->config['api_key'] ?? '';
        $apiSecret = $this->config['api_secret'] ?? '';
        
        $timestamp = time();
        $body = $method === 'GET' ? '' : json_encode($params);
        
        // 生成签名
        $signString = $method . "\n" . $path . "\n" . $timestamp . "\n" . $body;
        $signature = hash_hmac('sha256', $signString, $apiSecret);
        
        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $apiKey,
            'X-Signature: ' . $signature,
            'X-Timestamp: ' . $timestamp
        ];
        
        $ch = curl_init();
        
        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode != 200) {
            return ['code' => $httpCode, 'message' => 'HTTP请求失败'];
        }
        
        return json_decode($response, true);
    }
    
    /**
     * 获取用户认证状态
     */
    public function getUserAuthStatus($uid) {
        $record = DB::find('senmayun_face_auth', ['uid' => $uid, 'status' => 1]);
        if ($record) {
            return [
                'verified' => true,
                'verify_time' => $record['verify_time'],
                'score' => $record['score']
            ];
        }
        return ['verified' => false];
    }
    
    /**
     * 配置页面
     */
    public function configPage() {
        $config = $this->config;
        
        $html = '<div class="panel panel-default">
            <div class="panel-heading">森码云实人认证配置</div>
            <div class="panel-body">
                <form action="" method="post">
                    <div class="form-group">
                        <label>API地址</label>
                        <input type="text" name="api_url" class="form-control" value="' . ($config['api_url'] ?? '') . '" placeholder="https://face.builds.codes">
                        <span class="help-block">森码云实人认证系统的访问地址</span>
                    </div>
                    <div class="form-group">
                        <label>API Key</label>
                        <input type="text" name="api_key" class="form-control" value="' . ($config['api_key'] ?? '') . '">
                    </div>
                    <div class="form-group">
                        <label>API Secret</label>
                        <input type="password" name="api_secret" class="form-control" value="' . ($config['api_secret'] ?? '') . '">
                    </div>
                    <div class="form-group">
                        <label>回调地址</label>
                        <input type="text" class="form-control" value="' . $this->getNotifyUrl() . '" readonly>
                        <span class="help-block">请将此地址填入森码云管理后台的回调地址配置中</span>
                    </div>
                    <button type="submit" class="btn btn-primary">保存配置</button>
                </form>
            </div>
        </div>';
        
        return $html;
    }
}
