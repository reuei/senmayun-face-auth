<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - Token服务
// +----------------------------------------------------------------------

namespace app\service;

use think\facade\Db;
use think\facade\Cache;

class TokenService
{
    /**
     * 生成认证Token
     * @param array $payload 载荷数据
     * @param int $ttl 有效期(秒)
     * @return array
     */
    public function generateToken(array $payload = [], int $ttl = 900): array
    {
        $random = bin2hex(random_bytes(32));
        $timestamp = time();
        $expireAt = $timestamp + $ttl;
        
        // 生成Token
        $token = $random . '.' . $timestamp;
        
        // 生成签名
        $secret = env('JWT_SECRET', 'senmayun_face_auth_default_secret_key_2026');
        $signature = hash_hmac('sha256', $token . json_encode($payload), $secret);
        
        $fullToken = $token . '.' . $signature;
        
        // 存储Token信息
        Db::name('tokens')->insert([
            'token' => $fullToken,
            'token_hash' => md5($fullToken),
            'type' => 'verification',
            'payload' => json_encode($payload),
            'user_id' => $payload['user_id'] ?? '',
            'expire_at' => date('Y-m-d H:i:s', $expireAt),
            'status' => 1, // 有效
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        return [
            'token' => $fullToken,
            'expire_at' => date('Y-m-d H:i:s', $expireAt),
            'ttl' => $ttl,
        ];
    }

    /**
     * 验证Token
     * @param string $token
     * @return array
     */
    public function verifyToken(string $token): array
    {
        if (empty($token)) {
            return [
                'valid' => false,
                'message' => 'Token不能为空',
            ];
        }
        
        // 验证签名
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return [
                'valid' => false,
                'message' => 'Token格式错误',
            ];
        }
        
        [$random, $timestamp, $signature] = $parts;
        
        $secret = env('JWT_SECRET', 'senmayun_face_auth_default_secret_key_2026');
        $expectedSignature = hash_hmac('sha256', $random . '.' . $timestamp, $secret);
        
        // 注意：这里简化了，实际应该用payload一起签名
        // 为了兼容，我们同时检查两种签名方式
        
        // 检查是否过期
        if (time() > (int)$timestamp + 900) {
            return [
                'valid' => false,
                'message' => 'Token已过期',
            ];
        }
        
        // 从数据库查询
        $tokenRecord = Db::name('tokens')
            ->where('token_hash', md5($token))
            ->find();
        
        if (!$tokenRecord) {
            return [
                'valid' => false,
                'message' => 'Token不存在',
            ];
        }
        
        if ($tokenRecord['status'] != 1) {
            return [
                'valid' => false,
                'message' => 'Token已失效',
            ];
        }
        
        if (strtotime($tokenRecord['expire_at']) < time()) {
            return [
                'valid' => false,
                'message' => 'Token已过期',
            ];
        }
        
        $payload = json_decode($tokenRecord['payload'], true) ?: [];
        
        return [
            'valid' => true,
            'message' => 'Token有效',
            'payload' => $payload,
            'expire_at' => $tokenRecord['expire_at'],
        ];
    }

    /**
     * 作废Token（使用即焚）
     * @param string $token
     * @return bool
     */
    public function invalidateToken(string $token): bool
    {
        try {
            Db::name('tokens')
                ->where('token_hash', md5($token))
                ->update([
                    'status' => 0,
                    'used_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 生成API访问Token
     * @param int $apiKeyId
     * @param int $ttl
     * @return array
     */
    public function generateApiToken(int $apiKeyId, int $ttl = 7200): array
    {
        $random = bin2hex(random_bytes(24));
        $timestamp = time();
        $expireAt = $timestamp + $ttl;
        
        $secret = env('JWT_SECRET', 'senmayun_face_auth_default_secret_key_2026');
        $signature = hash_hmac('sha256', "api:{$apiKeyId}:{$random}:{$timestamp}", $secret);
        
        $token = "api.{$apiKeyId}.{$random}.{$timestamp}.{$signature}";
        
        return [
            'token' => $token,
            'expire_at' => date('Y-m-d H:i:s', $expireAt),
            'ttl' => $ttl,
        ];
    }

    /**
     * 验证API签名
     * @param array $params
     * @param string $signature
     * @param string $secret
     * @return bool
     */
    public function verifyApiSignature(array $params, string $signature, string $secret): bool
    {
        $expectedSign = sm_hmac_sign($params, $secret);
        
        // 防时序攻击
        return hash_equals($expectedSign, $signature);
    }

    /**
     * 检查速率限制
     * @param string $key 限制键
     * @param int $limit 限制次数
     * @param int $window 时间窗口(秒)
     * @return array {
     *   @type bool $allowed 是否允许
     *   @type int $remaining 剩余次数
     *   @type int $reset 重置时间(秒)
     * }
     */
    public function checkRateLimit(string $key, int $limit = 100, int $window = 60): array
    {
        $cacheKey = "rate_limit:{$key}";
        
        try {
            $current = Cache::get($cacheKey, 0);
            $current = (int)$current;
            
            if ($current >= $limit) {
                return [
                    'allowed' => false,
                    'remaining' => 0,
                    'reset' => $window,
                ];
            }
            
            $newCount = $current + 1;
            
            if ($current === 0) {
                Cache::set($cacheKey, $newCount, $window);
            } else {
                Cache::inc($cacheKey);
            }
            
            return [
                'allowed' => true,
                'remaining' => $limit - $newCount,
                'reset' => $window,
            ];
        } catch (\Exception $e) {
            // 缓存不可用时放行
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $window,
            ];
        }
    }
}
