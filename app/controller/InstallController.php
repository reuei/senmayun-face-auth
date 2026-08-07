<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 安装向导控制器
// +----------------------------------------------------------------------

namespace app\controller;

use think\facade\Db;
use think\facade\Log;
use think\Request;

class InstallController
{
    /**
     * 检查是否已安装
     */
    public function check()
    {
        $installed = sm_is_installed();
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'installed' => $installed,
            ],
        ]);
    }

    /**
     * 环境检测
     */
    public function environment()
    {
        $checks = [
            'php_version' => [
                'name' => 'PHP版本',
                'required' => '>= 8.1',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, '8.1.0', '>='),
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL扩展',
                'required' => '已安装',
                'current' => extension_loaded('pdo_mysql') ? '已安装' : '未安装',
                'passed' => extension_loaded('pdo_mysql'),
            ],
            'gd' => [
                'name' => 'GD扩展',
                'required' => '已安装',
                'current' => extension_loaded('gd') ? '已安装' : '未安装',
                'passed' => extension_loaded('gd'),
            ],
            'curl' => [
                'name' => 'cURL扩展',
                'required' => '已安装',
                'current' => extension_loaded('curl') ? '已安装' : '未安装',
                'passed' => extension_loaded('curl'),
            ],
            'openssl' => [
                'name' => 'OpenSSL扩展',
                'required' => '已安装',
                'current' => extension_loaded('openssl') ? '已安装' : '未安装',
                'passed' => extension_loaded('openssl'),
            ],
            'mbstring' => [
                'name' => 'Mbstring扩展',
                'required' => '已安装',
                'current' => extension_loaded('mbstring') ? '已安装' : '未安装',
                'passed' => extension_loaded('mbstring'),
            ],
            'fileinfo' => [
                'name' => 'Fileinfo扩展',
                'required' => '已安装',
                'current' => extension_loaded('fileinfo') ? '已安装' : '未安装',
                'passed' => extension_loaded('fileinfo'),
            ],
            'public_writable' => [
                'name' => 'public目录可写',
                'required' => '可写',
                'current' => is_writable(public_path()) ? '可写' : '不可写',
                'passed' => is_writable(public_path()),
            ],
            'runtime_writable' => [
                'name' => 'runtime目录可写',
                'required' => '可写',
                'current' => is_writable(runtime_path()) ? '可写' : '不可写',
                'passed' => is_writable(runtime_path()),
            ],
        ];
        
        $allPassed = true;
        foreach ($checks as $check) {
            if (!$check['passed']) {
                $allPassed = false;
                break;
            }
        }
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'checks' => $checks,
                'all_passed' => $allPassed,
            ],
        ]);
    }

    /**
     * 测试数据库连接
     */
    public function testDatabase(Request $request)
    {
        $host = $request->post('host', '127.0.0.1');
        $port = $request->post('port', 3306);
        $database = $request->post('database', '');
        $username = $request->post('username', '');
        $password = $request->post('password', '');
        $prefix = $request->post('prefix', 'sm_');
        
        if (empty($database) || empty($username)) {
            return json([
                'code' => 400,
                'message' => '数据库名和用户名不能为空',
                'data' => null,
            ]);
        }
        
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // 检查MySQL版本
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            
            return json([
                'code' => 200,
                'message' => '连接成功',
                'data' => [
                    'success' => true,
                    'version' => $version,
                ],
            ]);
        } catch (\PDOException $e) {
            return json([
                'code' => 200,
                'message' => '连接失败',
                'data' => [
                    'success' => false,
                    'error' => $e->getMessage(),
                ],
            ]);
        }
    }

    /**
     * 安装数据库
     */
    public function installDatabase(Request $request)
    {
        if (sm_is_installed()) {
            return json([
                'code' => 400,
                'message' => '系统已安装，请勿重复安装',
                'data' => null,
            ]);
        }
        
        $host = $request->post('host', '127.0.0.1');
        $port = $request->post('port', 3306);
        $database = $request->post('database', '');
        $username = $request->post('username', '');
        $password = $request->post('password', '');
        $prefix = $request->post('prefix', 'sm_');
        
        $adminUsername = $request->post('admin_username', 'admin');
        $adminPassword = $request->post('admin_password', '');
        $adminEmail = $request->post('admin_email', '');
        
        if (empty($database) || empty($username)) {
            return json([
                'code' => 400,
                'message' => '数据库名和用户名不能为空',
                'data' => null,
            ]);
        }
        
        if (empty($adminPassword) || strlen($adminPassword) < 8) {
            return json([
                'code' => 400,
                'message' => '管理员密码不能少于8位',
                'data' => null,
            ]);
        }
        
        try {
            // 1. 测试连接
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // 2. 读取SQL文件
            $sqlFile = root_path() . 'database/install.sql';
            if (!is_file($sqlFile)) {
                return json([
                    'code' => 500,
                    'message' => 'SQL文件不存在',
                    'data' => null,
                ]);
            }
            
            $sql = file_get_contents($sqlFile);
            
            // 替换表前缀
            $sql = str_replace('sm_', $prefix, $sql);
            
            // 执行SQL
            $statements = $this->splitSql($sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // 3. 更新.env文件
            $envFile = root_path() . '.env';
            $envContent = file_get_contents($envFile);
            
            $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $host, $envContent);
            $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $port, $envContent);
            $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $database, $envContent);
            $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $username, $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . $password, $envContent);
            $envContent = preg_replace('/DB_PREFIX=.*/', 'DB_PREFIX=' . $prefix, $envContent);
            
            // 生成JWT密钥
            $jwtSecret = bin2hex(random_bytes(32));
            $envContent = preg_replace('/JWT_SECRET=.*/', 'JWT_SECRET=' . $jwtSecret, $envContent);
            
            file_put_contents($envFile, $envContent);
            
            // 4. 设置管理员密码
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
            $pdo->exec("UPDATE `{$prefix}admin` SET `password` = '{$hashedPassword}', `username` = '{$adminUsername}', `email` = '{$adminEmail}' WHERE `id` = 1");
            
            // 5. 写入安装锁
            $installIp = sm_get_client_ip();
            $pdo->exec("INSERT INTO `{$prefix}install_lock` (`locked`, `version`, `install_ip`, `created_at`) VALUES (1, '1.0.0', '{$installIp}', NOW())");
            
            // 6. 创建install.lock文件
            file_put_contents(public_path() . '/install.lock', json_encode([
                'version' => '1.0.0',
                'install_time' => date('Y-m-d H:i:s'),
                'install_ip' => $installIp,
            ]));
            
            return json([
                'code' => 200,
                'message' => '安装成功',
                'data' => [
                    'success' => true,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('安装数据库失败: ' . $e->getMessage());
            
            return json([
                'code' => 500,
                'message' => '安装失败: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * 分割SQL语句
     */
    protected function splitSql(string $sql): array
    {
        $statements = [];
        $current = '';
        $inComment = false;
        $inString = false;
        $stringChar = '';
        
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // 跳过注释
            if (str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                continue;
            }
            
            if (str_starts_with($trimmed, '/*')) {
                $inComment = true;
                continue;
            }
            
            if ($inComment) {
                if (str_ends_with($trimmed, '*/')) {
                    $inComment = false;
                }
                continue;
            }
            
            $current .= $line . "\n";
            
            // 检查语句结束
            if (str_ends_with(rtrim($line), ';')) {
                $statements[] = $current;
                $current = '';
            }
        }
        
        if (!empty(trim($current))) {
            $statements[] = $current;
        }
        
        return $statements;
    }
}
