<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 魔方财务对接服务
// +----------------------------------------------------------------------

namespace app\service;

use think\facade\Db;
use think\facade\Log;

class MofangFinanceService
{
    /**
     * 检查魔方财务对接是否启用
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)sm_config('mofang.enabled', false);
    }

    /**
     * 获取配置
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'enabled' => (bool)sm_config('mofang.enabled', false),
            'api_url' => sm_config('mofang.api_url', ''),
            'api_username' => sm_config('mofang.api_username', ''),
            'api_key' => sm_config('mofang.api_key', ''),
            'callback_secret' => sm_config('mofang.callback_secret', ''),
            'auto_verify' => (bool)sm_config('mofang.auto_verify', true),
        ];
    }

    /**
     * 保存配置
     * @param array $config
     * @return bool
     */
    public function saveConfig(array $config): bool
    {
        try {
            foreach ($config as $key => $value) {
                $type = is_bool($value) ? 'bool' : (is_numeric($value) ? 'int' : 'string');
                
                Db::name('system_config')
                    ->where('group', 'mofang')
                    ->where('key', $key)
                    ->update([
                        'value' => is_bool($value) ? ($value ? '1' : '0') : (string)$value,
                        'type' => $type,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }
            
            // 清除配置缓存
            if (function_exists('sm_config')) {
                // 触发重新加载
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('保存魔方财务配置失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 处理魔方财务发起的认证请求
     * @param array $params
     * @return array
     */
    public function createVerification(array $params): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => '魔方财务对接未启用',
            ];
        }
        
        // 验证签名
        $config = $this->getConfig();
        $sign = $params['sign'] ?? '';
        unset($params['sign']);
        
        if (!$this->verifySign($params, $sign, $config['api_key'])) {
            return [
                'success' => false,
                'message' => '签名验证失败',
            ];
        }
        
        $faceService = new FaceService();
        
        // 初始化认证
        $result = $faceService->initVerification([
            'user_id' => $params['user_id'] ?? '',
            'name' => $params['name'] ?? '',
            'id_card' => $params['id_card'] ?? '',
            'verify_type' => $params['verify_type'] ?? 'full',
            'return_url' => $params['return_url'] ?? '',
        ]);
        
        // 记录魔方财务订单
        Db::name('mofang_orders')->insert([
            'mofang_order_id' => $params['order_id'] ?? '',
            'mofang_user_id' => $params['user_id'] ?? '',
            'token' => $result['token'],
            'product_id' => $params['product_id'] ?? '',
            'product_name' => $params['product_name'] ?? '',
            'amount' => $params['amount'] ?? 0,
            'status' => 1, // 已支付待认证
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        return [
            'success' => true,
            'token' => $result['token'],
            'verify_url' => $result['verify_url'],
            'expire_time' => $result['expire_time'],
        ];
    }

    /**
     * 认证完成回调魔方财务
     * @param string $token
     * @param array $result
     * @return bool
     */
    public function notifyMofang(string $token, array $result): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }
        
        $config = $this->getConfig();
        
        if (empty($config['api_url'])) {
            return false;
        }
        
        try {
            // 查询订单
            $order = Db::name('mofang_orders')->where('token', $token)->find();
            if (!$order) {
                return false;
            }
            
            // 构造回调数据
            $callbackData = [
                'order_id' => $order['mofang_order_id'],
                'user_id' => $order['mofang_user_id'],
                'token' => $token,
                'status' => $result['status'] ?? 'failed',
                'score' => $result['score'] ?? 0,
                'verify_time' => date('Y-m-d H:i:s'),
                'timestamp' => time(),
            ];
            
            // 生成签名
            $callbackData['sign'] = $this->generateSign($callbackData, $config['api_key']);
            
            // 发送回调
            $url = rtrim($config['api_url'], '/') . '/plugin/senmayun/callback';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($callbackData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $success = $httpCode >= 200 && $httpCode < 300;
            
            // 更新订单状态
            if ($result['status'] === 'passed') {
                $orderStatus = 2; // 已认证
            } else {
                $orderStatus = 1; // 保持待认证状态
            }
            
            Db::name('mofang_orders')->where('token', $token)->update([
                'status' => $orderStatus,
                'callback_data' => json_encode([
                    'http_code' => $httpCode,
                    'response' => $response,
                    'success' => $success,
                ]),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            
            Log::info("魔方财务回调完成: {$token}, HTTP: {$httpCode}");
            
            return $success;
        } catch (\Exception $e) {
            Log::error('魔方财务回调失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 验证签名
     * @param array $params
     * @param string $sign
     * @param string $secret
     * @return bool
     */
    public function verifySign(array $params, string $sign, string $secret): bool
    {
        $expectedSign = $this->generateSign($params, $secret);
        return hash_equals($expectedSign, $sign);
    }

    /**
     * 生成签名
     * @param array $params
     * @param string $secret
     * @return string
     */
    public function generateSign(array $params, string $secret): string
    {
        // 按key排序
        ksort($params);
        
        $string = '';
        foreach ($params as $k => $v) {
            if ($k === 'sign' || $v === '' || is_null($v)) continue;
            if (is_array($v)) $v = json_encode($v, JSON_UNESCAPED_UNICODE);
            $string .= $k . '=' . $v . '&';
        }
        $string = rtrim($string, '&');
        
        return hash_hmac('sha256', $string, $secret);
    }

    /**
     * 查询认证状态（魔方财务主动查询）
     * @param string $token
     * @return array
     */
    public function queryStatus(string $token): array
    {
        $faceService = new FaceService();
        return $faceService->getVerificationResult($token);
    }

    /**
     * 获取插件下载包信息
     * @return array
     */
    public function getPluginInfo(): array
    {
        return [
            'name' => '森码云实人认证',
            'code' => 'senmayun',
            'version' => '1.0.0',
            'author' => '森码云',
            'description' => '魔方财务实人认证插件，对接森码云实人认证系统',
            'min_version' => '1.0.0',
            'type' => 'server',
        ];
    }
}
