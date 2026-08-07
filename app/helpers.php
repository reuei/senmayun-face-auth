<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 辅助函数
// +----------------------------------------------------------------------

use think\facade\Config;

if (!function_exists('sm_config')) {
    /**
     * 获取系统配置
     * @param string $key 配置键 (group.key)
     * @param mixed $default 默认值
     * @return mixed
     */
    function sm_config(string $key, mixed $default = null): mixed
    {
        static $configs = null;
        
        if ($configs === null) {
            try {
                $rows = \think\facade\Db::name('system_config')->select();
                $configs = [];
                foreach ($rows as $row) {
                    $g = $row['group'];
                    $k = $row['key'];
                    $v = $row['value'];
                    if ($row['type'] === 'json') {
                        $v = json_decode($v, true);
                    } elseif ($row['type'] === 'int') {
                        $v = (int)$v;
                    } elseif ($row['type'] === 'bool') {
                        $v = (bool)$v;
                    }
                    $configs["{$g}.{$k}"] = $v;
                }
            } catch (\Exception $e) {
                $configs = [];
            }
        }
        
        return $configs[$key] ?? $default;
    }
}

if (!function_exists('sm_generate_token')) {
    /**
     * 生成认证Token
     * @param int $length
     * @return string
     */
    function sm_generate_token(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('sm_generate_api_key')) {
    /**
     * 生成API Key
     * @return string
     */
    function sm_generate_api_key(): string
    {
        return 'sk_' . bin2hex(random_bytes(16));
    }
}

if (!function_exists('sm_generate_api_secret')) {
    /**
     * 生成API Secret
     * @return string
     */
    function sm_generate_api_secret(): string
    {
        return bin2hex(random_bytes(32));
    }
}

if (!function_exists('sm_hmac_sign')) {
    /**
     * HMAC-SHA256签名
     * @param array $data
     * @param string $secret
     * @return string
     */
    function sm_hmac_sign(array $data, string $secret): string
    {
        ksort($data);
        $string = '';
        foreach ($data as $k => $v) {
            if ($k === 'sign' || $v === '' || is_null($v)) continue;
            if (is_array($v)) $v = json_encode($v, JSON_UNESCAPED_UNICODE);
            $string .= $k . '=' . $v . '&';
        }
        $string = rtrim($string, '&');
        return hash_hmac('sha256', $string, $secret);
    }
}

if (!function_exists('sm_mask_id_card')) {
    /**
     * 身份证号脱敏
     * @param string $idCard
     * @return string
     */
    function sm_mask_id_card(string $idCard): string
    {
        if (strlen($idCard) !== 18) return $idCard;
        return substr($idCard, 0, 6) . '********' . substr($idCard, -4);
    }
}

if (!function_exists('sm_mask_phone')) {
    /**
     * 手机号脱敏
     * @param string $phone
     * @return string
     */
    function sm_mask_phone(string $phone): string
    {
        if (strlen($phone) !== 11) return $phone;
        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }
}

if (!function_exists('sm_mask_name')) {
    /**
     * 姓名脱敏
     * @param string $name
     * @return string
     */
    function sm_mask_name(string $name): string
    {
        $len = mb_strlen($name);
        if ($len <= 1) return $name;
        if ($len === 2) return mb_substr($name, 0, 1) . '*';
        return mb_substr($name, 0, 1) . str_repeat('*', $len - 2) . mb_substr($name, -1);
    }
}

if (!function_exists('sm_encrypt')) {
    /**
     * 对称加密 (AES-256-CBC)
     * @param string $data
     * @param string $key
     * @return string
     */
    function sm_encrypt(string $data, string $key = ''): string
    {
        if (!$key) {
            $key = env('JWT_SECRET', 'senmayun_face_auth_default_secret_key_2026');
        }
        $key = substr(hash('sha256', $key), 0, 32);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }
}

if (!function_exists('sm_decrypt')) {
    /**
     * 对称解密
     * @param string $data
     * @param string $key
     * @return string|false
     */
    function sm_decrypt(string $data, string $key = ''): string|false
    {
        if (!$key) {
            $key = env('JWT_SECRET', 'senmayun_face_auth_default_secret_key_2026');
        }
        $key = substr(hash('sha256', $key), 0, 32);
        $data = base64_decode($data);
        if ($data === false || strlen($data) < 16) return false;
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}

if (!function_exists('sm_is_installed')) {
    /**
     * 检测系统是否已安装
     * @return bool
     */
    function sm_is_installed(): bool
    {
        $lockFile = public_path() . '/install.lock';
        if (is_file($lockFile)) {
            return true;
        }
        // 也检查数据库中的安装锁表
        try {
            $exists = \think\facade\Db::query("SHOW TABLES LIKE 'sm_install_lock'");
            if (!empty($exists)) {
                $lock = \think\facade\Db::name('install_lock')->where('locked', 1)->find();
                return !empty($lock);
            }
        } catch (\Exception $e) {
            // 数据库连接失败，视为未安装
        }
        return false;
    }
}

if (!function_exists('sm_format_bytes')) {
    /**
     * 格式化字节数
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    function sm_format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('sm_get_client_ip')) {
    /**
     * 获取客户端真实IP
     * @return string
     */
    function sm_get_client_ip(): string
    {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        ];
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ip = explode(',', $_SERVER[$header])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
}
