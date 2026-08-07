<?php
/**
 * 森码云实人认证系统 - 纯PHP版本API入口
 * 无需框架，上传到虚拟主机即可运行
 */

// 错误显示
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 会话设置
session_start();

// 基础配置
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('DATABASE_PATH', ROOT_PATH . '/database');

// 加载配置
$configFile = CONFIG_PATH . '/app.php';
$config = file_exists($configFile) ? require $configFile : [
    'app_name' => '森码云实人认证',
    'debug' => false,
    'default_lang' => 'zh-cn'
];

// 数据库配置
$dbConfigFile = CONFIG_PATH . '/database.php';
$dbConfig = file_exists($dbConfigFile) ? require $dbConfigFile : [
    'host' => 'localhost',
    'port' => 3306,
    'database' => '',
    'username' => '',
    'password' => '',
    'charset' => 'utf8mb4',
    'prefix' => 'sm_'
];

// 自动加载类
spl_autoload_register(function ($class) {
    $prefix = 'app\\';
    $baseDir = ROOT_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// 辅助函数
require_once ROOT_PATH . '/app/helpers.php';

// 数据库连接类
class Database {
    private static $instance = null;
    private $pdo;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $dbConfig;
        
        try {
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
            $this->pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            // 数据库连接失败，可能是未安装
            $this->pdo = null;
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function isConnected() {
        return $this->pdo !== null;
    }
    
    public function query($sql, $params = []) {
        if (!$this->pdo) return false;
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if (!$stmt) return [];
        return $stmt->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if (!$stmt) return null;
        return $stmt->fetch();
    }
    
    public function insert($table, $data) {
        global $dbConfig;
        $table = $dbConfig['prefix'] . $table;
        
        $fields = array_keys($data);
        $placeholders = array_map(function($f) { return ':' . $f; }, $fields);
        
        $sql = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        global $dbConfig;
        $table = $dbConfig['prefix'] . $table;
        
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :set_{$key}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(',', $set) . " WHERE {$where}";
        
        $params = [];
        foreach ($data as $key => $value) {
            $params['set_' . $key] = $value;
        }
        $params = array_merge($params, $whereParams);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function delete($table, $where, $params = []) {
        global $dbConfig;
        $table = $dbConfig['prefix'] . $table;
        
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function count($table, $where = '1=1', $params = []) {
        global $dbConfig;
        $table = $dbConfig['prefix'] . $table;
        
        $sql = "SELECT COUNT(*) as cnt FROM {$table} WHERE {$where}";
        $row = $this->fetchOne($sql, $params);
        return $row ? (int)$row['cnt'] : 0;
    }
}

// API响应函数
function api_response($code, $message, $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    echo json_encode([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 检查是否已安装
function is_installed() {
    $lockFile = DATABASE_PATH . '/install.lock';
    return file_exists($lockFile);
}

// 获取请求路径
function get_request_path() {
    $uri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    
    // 移除查询字符串
    if (strpos($uri, '?') !== false) {
        $uri = substr($uri, 0, strpos($uri, '?'));
    }
    
    // 移除脚本名称前缀
    if (strpos($uri, $scriptName) === 0) {
        $uri = substr($uri, strlen($scriptName));
    }
    
    // 移除前导斜杠
    $uri = ltrim($uri, '/');
    
    return $uri;
}

// 获取请求方法
function get_request_method() {
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

// 获取请求体
function get_request_body() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return $data ? $data : [];
}

// 获取GET参数
function get_query_params() {
    return $_GET;
}

// 获取Bearer Token
function get_bearer_token() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (strpos($auth, 'Bearer ') === 0) {
            return substr($auth, 7);
        }
    }
    return null;
}

// 检查管理员登录
function check_admin_auth() {
    $token = get_bearer_token();
    if (!$token) {
        // 尝试从session获取
        if (isset($_SESSION['admin_id'])) {
            return true;
        }
        return false;
    }
    
    // 简单的token验证（实际应该更复杂）
    $db = Database::getInstance();
    if (!$db->isConnected()) return false;
    
    $admin = $db->fetchOne("SELECT * FROM sm_admin WHERE token = ?", [$token]);
    return $admin ? true : false;
}

// ============================================
// 路由处理
// ============================================

// 处理OPTIONS请求
if (get_request_method() === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    exit;
}

$path = get_request_path();
$method = get_request_method();

// 安装检测
if (!is_installed() && strpos($path, 'install') === false) {
    api_response(400, '系统未安装，请先运行安装程序', ['install_url' => '/install.html']);
}

// ============================================
// API 路由
// ============================================

// 公共API
if (strpos($path, 'api/v1') === 0) {
    $apiPath = substr($path, strlen('api/v1/'));
    
    // 初始化认证
    if ($apiPath === 'auth/init' && $method === 'POST') {
        $body = get_request_body();
        $apiKey = $body['api_key'] ?? '';
        
        if (empty($apiKey)) {
            api_response(400, 'API Key不能为空');
        }
        
        // 验证API Key
        $db = Database::getInstance();
        $keyInfo = $db->fetchOne("SELECT * FROM sm_api_keys WHERE api_key = ? AND status = 1", [$apiKey]);
        
        if (!$keyInfo) {
            api_response(401, '无效的API Key');
        }
        
        // 生成Token
        $token = bin2hex(random_bytes(16)) . '.' . time() . '.' . md5($apiKey . time());
        
        // 保存Token
        $db->insert('api_tokens', [
            'token' => $token,
            'api_key_id' => $keyInfo['id'],
            'expires_at' => date('Y-m-d H:i:s', time() + 300)
        ]);
        
        api_response(200, '初始化成功', [
            'token' => $token,
            'expires_in' => 300
        ]);
    }
    
    // 执行认证
    if ($apiPath === 'auth/verify' && $method === 'POST') {
        $body = get_request_body();
        $token = $body['token'] ?? '';
        $imageBase64 = $body['image'] ?? '';
        $idCard = $body['id_card'] ?? '';
        $name = $body['name'] ?? '';
        
        if (empty($token)) {
            api_response(400, 'Token不能为空');
        }
        
        // 验证Token
        $db = Database::getInstance();
        $tokenInfo = $db->fetchOne("SELECT * FROM sm_api_tokens WHERE token = ? AND expires_at > NOW()", [$token]);
        
        if (!$tokenInfo) {
            api_response(401, 'Token无效或已过期');
        }
        
        // 这里应该调用实际的人脸认证服务
        // 演示模式下直接返回成功
        $verifyId = 'SM' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $score = rand(85, 99) + rand(0, 99) / 100;
        
        // 保存认证记录
        $db->insert('verifications', [
            'verify_id' => $verifyId,
            'api_key_id' => $tokenInfo['api_key_id'],
            'user_id' => $body['user_id'] ?? '',
            'type' => $idCard ? 'idcard' : 'face',
            'status' => 'passed',
            'score' => $score,
            'channel' => 'local',
            'liveness_passed' => 1,
            'duration' => rand(300, 800),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // 删除Token（使用即焚）
        $db->delete('api_tokens', 'token = ?', [$token]);
        
        api_response(200, '认证成功', [
            'verify_id' => $verifyId,
            'status' => 'passed',
            'score' => $score,
            'liveness_passed' => true,
            'channel' => 'local',
            'duration' => 500
        ]);
    }
    
    // 查询认证结果
    if ($apiPath === 'auth/result' && $method === 'GET') {
        $params = get_query_params();
        $verifyId = $params['verify_id'] ?? '';
        
        if (empty($verifyId)) {
            api_response(400, '认证编号不能为空');
        }
        
        $db = Database::getInstance();
        $record = $db->fetchOne("SELECT * FROM sm_verifications WHERE verify_id = ?", [$verifyId]);
        
        if (!$record) {
            api_response(404, '认证记录不存在');
        }
        
        api_response(200, '查询成功', [
            'verify_id' => $record['verify_id'],
            'status' => $record['status'],
            'score' => (float)$record['score'],
            'channel' => $record['channel'],
            'liveness_passed' => (bool)$record['liveness_passed'],
            'created_at' => $record['created_at']
        ]);
    }
    
    api_response(404, '接口不存在');
}

// ============================================
// 管理员API
// ============================================

if (strpos($path, 'admin/api') === 0) {
    $adminPath = substr($path, strlen('admin/api/'));
    
    // 登录接口不需要验证
    if ($adminPath === 'login' && $method === 'POST') {
        $body = get_request_body();
        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            api_response(400, '用户名和密码不能为空');
        }
        
        $db = Database::getInstance();
        $admin = $db->fetchOne("SELECT * FROM sm_admin WHERE username = ?", [$username]);
        
        if (!$admin || $admin['password'] !== md5($password . $admin['salt'])) {
            api_response(401, '用户名或密码错误');
        }
        
        // 生成Token
        $token = md5($admin['id'] . time() . rand());
        
        $db->update('admin', ['token' => $token, 'last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$admin['id']]);
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        
        api_response(200, '登录成功', [
            'token' => $token,
            'admin' => [
                'id' => $admin['id'],
                'username' => $admin['username']
            ]
        ]);
    }
    
    // 其他接口需要验证
    if (!check_admin_auth()) {
        api_response(401, '未登录或登录已过期');
    }
    
    $db = Database::getInstance();
    
    // 仪表盘统计
    if ($adminPath === 'dashboard/stats' && $method === 'GET') {
        $today = date('Y-m-d');
        
        $todayCount = $db->count('verifications', "DATE(created_at) = ?", [$today]);
        $todayPassed = $db->count('verifications', "DATE(created_at) = ? AND status = 'passed'", [$today]);
        $todayFailed = $db->count('verifications', "DATE(created_at) = ? AND status = 'failed'", [$today]);
        
        // 平均耗时
        $avgRow = $db->fetchOne("SELECT AVG(duration) as avg FROM sm_verifications WHERE DATE(created_at) = ?", [$today]);
        $avgDuration = $avgRow ? round($avgRow['avg']) : 0;
        
        // 7天趋势
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $count = $db->count('verifications', "DATE(created_at) = ?", [$date]);
            $trend[] = [
                'date' => $date,
                'count' => $count
            ];
        }
        
        api_response(200, '获取成功', [
            'today_count' => $todayCount,
            'today_passed' => $todayPassed,
            'today_failed' => $todayFailed,
            'avg_duration' => $avgDuration,
            'trend' => $trend
        ]);
    }
    
    // 认证记录列表
    if ($adminPath === 'verifications' && $method === 'GET') {
        $params = get_query_params();
        $page = max(1, (int)($params['page'] ?? 1));
        $pageSize = min(100, (int)($params['page_size'] ?? 20));
        $offset = ($page - 1) * $pageSize;
        
        $where = '1=1';
        $queryParams = [];
        
        if (!empty($params['status'])) {
            $where .= " AND status = ?";
            $queryParams[] = $params['status'];
        }
        
        if (!empty($params['keyword'])) {
            $where .= " AND (verify_id LIKE ? OR user_id LIKE ?)";
            $queryParams[] = '%' . $params['keyword'] . '%';
            $queryParams[] = '%' . $params['keyword'] . '%';
        }
        
        $total = $db->count('verifications', $where, $queryParams);
        $list = $db->fetchAll("SELECT * FROM sm_verifications WHERE {$where} ORDER BY created_at DESC LIMIT {$offset}, {$pageSize}", $queryParams);
        
        api_response(200, '获取成功', [
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'list' => $list
        ]);
    }
    
    // 通道列表
    if ($adminPath === 'channels' && $method === 'GET') {
        $channels = $db->fetchAll("SELECT * FROM sm_api_channels ORDER BY priority ASC");
        api_response(200, '获取成功', $channels);
    }
    
    // 更新通道
    if ($adminPath === 'channels/update' && $method === 'POST') {
        $body = get_request_body();
        $id = $body['id'] ?? 0;
        
        if (!$id) {
            api_response(400, '通道ID不能为空');
        }
        
        $updateData = [
            'name' => $body['name'] ?? '',
            'enabled' => isset($body['enabled']) ? ($body['enabled'] ? 1 : 0) : 0,
            'priority' => $body['priority'] ?? 10,
            'config' => json_encode($body['config'] ?? [])
        ];
        
        $result = $db->update('api_channels', $updateData, 'id = ?', [$id]);
        
        if ($result) {
            api_response(200, '更新成功');
        } else {
            api_response(500, '更新失败');
        }
    }
    
    // 系统设置
    if ($adminPath === 'settings' && $method === 'GET') {
        $settings = $db->fetchAll("SELECT * FROM sm_system_config");
        $result = [];
        foreach ($settings as $s) {
            $result[$s['config_key']] = $s['config_value'];
        }
        api_response(200, '获取成功', $result);
    }
    
    if ($adminPath === 'settings/save' && $method === 'POST') {
        $body = get_request_body();
        
        foreach ($body as $key => $value) {
            $exists = $db->fetchOne("SELECT id FROM sm_system_config WHERE config_key = ?", [$key]);
            if ($exists) {
                $db->update('system_config', ['config_value' => is_array($value) ? json_encode($value) : $value], 'config_key = ?', [$key]);
            } else {
                $db->insert('system_config', [
                    'config_key' => $key,
                    'config_value' => is_array($value) ? json_encode($value) : $value,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        api_response(200, '保存成功');
    }
    
    // 操作日志
    if ($adminPath === 'logs' && $method === 'GET') {
        $params = get_query_params();
        $page = max(1, (int)($params['page'] ?? 1));
        $pageSize = min(100, (int)($params['page_size'] ?? 20));
        $offset = ($page - 1) * $pageSize;
        
        $total = $db->count('operation_logs');
        $list = $db->fetchAll("SELECT * FROM sm_operation_logs ORDER BY created_at DESC LIMIT {$offset}, {$pageSize}");
        
        api_response(200, '获取成功', [
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'list' => $list
        ]);
    }
    
    api_response(404, '接口不存在');
}

// ============================================
// 安装API
// ============================================

if (strpos($path, 'install/api') === 0) {
    $installPath = substr($path, strlen('install/api/'));
    
    // 环境检测
    if ($installPath === 'check' && $method === 'GET') {
        $checks = [
            'php_version' => [
                'name' => 'PHP版本',
                'required' => '>= 7.4',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, '7.4', '>=')
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL扩展',
                'required' => '已安装',
                'current' => extension_loaded('pdo_mysql') ? '已安装' : '未安装',
                'passed' => extension_loaded('pdo_mysql')
            ],
            'gd' => [
                'name' => 'GD扩展',
                'required' => '已安装',
                'current' => extension_loaded('gd') ? '已安装' : '未安装',
                'passed' => extension_loaded('gd')
            ],
            'openssl' => [
                'name' => 'OpenSSL扩展',
                'required' => '已安装',
                'current' => extension_loaded('openssl') ? '已安装' : '未安装',
                'passed' => extension_loaded('openssl')
            ],
            'file_uploads' => [
                'name' => '文件上传',
                'required' => '已开启',
                'current' => ini_get('file_uploads') ? '已开启' : '已关闭',
                'passed' => (bool)ini_get('file_uploads')
            ],
            'write_permission' => [
                'name' => '目录写入权限',
                'required' => '可写',
                'current' => is_writable(ROOT_PATH) ? '可写' : '不可写',
                'passed' => is_writable(ROOT_PATH)
            ]
        ];
        
        $allPassed = true;
        foreach ($checks as $check) {
            if (!$check['passed']) {
                $allPassed = false;
                break;
            }
        }
        
        api_response(200, '检测完成', [
            'checks' => $checks,
            'all_passed' => $allPassed
        ]);
    }
    
    // 数据库连接测试
    if ($installPath === 'test-db' && $method === 'POST') {
        $body = get_request_body();
        
        try {
            $dsn = "mysql:host={$body['host']};port={$body['port']};dbname={$body['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $body['username'], $body['password']);
            api_response(200, '连接成功');
        } catch (PDOException $e) {
            api_response(500, '连接失败：' . $e->getMessage());
        }
    }
    
    // 执行安装
    if ($installPath === 'run' && $method === 'POST') {
        $body = get_request_body();
        
        // 验证数据库连接
        try {
            $dsn = "mysql:host={$body['db_host']};port={$body['db_port']};dbname={$body['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $body['db_user'], $body['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            api_response(500, '数据库连接失败：' . $e->getMessage());
        }
        
        // 读取SQL文件
        $sqlFile = DATABASE_PATH . '/install.sql';
        if (!file_exists($sqlFile)) {
            api_response(500, 'SQL文件不存在');
        }
        
        $sql = file_get_contents($sqlFile);
        
        // 替换表前缀
        $prefix = $body['db_prefix'] ?? 'sm_';
        $sql = str_replace('sm_', $prefix, $sql);
        
        // 执行SQL
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            api_response(500, 'SQL执行失败：' . $e->getMessage());
        }
        
        // 创建管理员
        $salt = bin2hex(random_bytes(8));
        $password = md5($body['admin_password'] . $salt);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO {$prefix}admin (username, password, salt, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$body['admin_username'], $password, $salt]);
        } catch (PDOException $e) {
            api_response(500, '创建管理员失败：' . $e->getMessage());
        }
        
        // 写入配置文件
        $configContent = "<?php\nreturn [\n";
        $configContent .= "    'host' => '{$body['db_host']}',\n";
        $configContent .= "    'port' => {$body['db_port']},\n";
        $configContent .= "    'database' => '{$body['db_name']}',\n";
        $configContent .= "    'username' => '{$body['db_user']}',\n";
        $configContent .= "    'password' => '{$body['db_pass']}',\n";
        $configContent .= "    'charset' => 'utf8mb4',\n";
        $configContent .= "    'prefix' => '{$prefix}',\n";
        $configContent .= "];\n";
        
        file_put_contents(CONFIG_PATH . '/database.php', $configContent);
        
        // 创建安装锁
        file_put_contents(DATABASE_PATH . '/install.lock', date('Y-m-d H:i:s'));
        
        api_response(200, '安装成功');
    }
    
    api_response(404, '接口不存在');
}

// 默认返回
api_response(404, '页面不存在');
