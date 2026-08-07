<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 人脸认证核心服务
// +----------------------------------------------------------------------

namespace app\service;

use app\service\Channel\ChannelInterface;
use app\service\Channel\TencentHuiyanChannel;
use app\service\Channel\BaiduFaceChannel;
use app\service\Channel\LocalDemoChannel;
use think\facade\Db;
use think\facade\Log;

class FaceService
{
    /**
     * 通道实例缓存
     * @var array
     */
    protected array $channels = [];

    /**
     * 获取所有可用通道（按优先级排序）
     * @return array
     */
    public function getAvailableChannels(): array
    {
        try {
            $channels = Db::name('api_channels')
                ->where('enabled', 1)
                ->order('priority', 'asc')
                ->select()
                ->toArray();
            
            $result = [];
            foreach ($channels as $channel) {
                $instance = $this->getChannelInstance($channel['code'], json_decode($channel['config'], true) ?: []);
                if ($instance && $instance->isAvailable()) {
                    $result[] = [
                        'info' => $channel,
                        'instance' => $instance,
                    ];
                }
            }
            
            // 如果没有可用的第三方通道，添加本地演示通道
            if (empty($result)) {
                $localChannel = new LocalDemoChannel(['threshold' => 60]);
                $result[] = [
                    'info' => [
                        'id' => 0,
                        'name' => '自研算法(演示)',
                        'code' => 'local',
                        'type' => 'face',
                        'priority' => 999,
                    ],
                    'instance' => $localChannel,
                ];
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('获取通道列表失败: ' . $e->getMessage());
            // 出错时返回本地通道
            $localChannel = new LocalDemoChannel(['threshold' => 60]);
            return [
                [
                    'info' => ['id' => 0, 'name' => '自研算法(演示)', 'code' => 'local', 'type' => 'face'],
                    'instance' => $localChannel,
                ],
            ];
        }
    }

    /**
     * 获取指定通道实例
     * @param string $code 通道代码
     * @param array $config 配置
     * @return ChannelInterface|null
     */
    public function getChannelInstance(string $code, array $config = []): ?ChannelInterface
    {
        $cacheKey = $code . '_' . md5(json_encode($config));
        
        if (isset($this->channels[$cacheKey])) {
            return $this->channels[$cacheKey];
        }
        
        $instance = null;
        
        switch ($code) {
            case 'tencent':
                $instance = new TencentHuiyanChannel($config);
                break;
            case 'baidu':
                $instance = new BaiduFaceChannel($config);
                break;
            case 'local':
                $instance = new LocalDemoChannel($config);
                break;
            // 更多通道可在此扩展
        }
        
        if ($instance) {
            $this->channels[$cacheKey] = $instance;
        }
        
        return $instance;
    }

    /**
     * 初始化认证会话
     * @param array $params
     * @return array
     */
    public function initVerification(array $params): array
    {
        $tokenService = new TokenService();
        
        // 生成认证Token
        $token = $tokenService->generateToken([
            'user_id' => $params['user_id'] ?? '',
            'name' => $params['name'] ?? '',
            'id_card' => $params['id_card'] ?? '',
            'verify_type' => $params['verify_type'] ?? 'full',
            'return_url' => $params['return_url'] ?? '',
            'api_key_id' => $params['api_key_id'] ?? 0,
        ]);
        
        // 获取可用通道
        $channels = $this->getAvailableChannels();
        $primaryChannel = $channels[0]['instance'] ?? null;
        $channelCode = $channels[0]['info']['code'] ?? 'local';
        
        // 存储认证记录
        Db::name('verifications')->insert([
            'token' => $token['token'],
            'user_hash' => md5($params['user_id'] ?? ''),
            'channel' => $channelCode,
            'verify_type' => $params['verify_type'] ?? 'full',
            'status' => 0, // 待认证
            'callback_url' => $params['return_url'] ?? '',
            'request_ip' => sm_get_client_ip(),
            'expire_at' => $token['expire_at'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        return [
            'token' => $token['token'],
            'expire_time' => $token['expire_at'],
            'verify_url' => config('app.app_host', '') . '/verify?token=' . $token['token'],
            'channel' => $channelCode,
        ];
    }

    /**
     * 执行人脸检测（带自动降级）
     * @param string $imageBase64
     * @return array
     */
    public function faceDetect(string $imageBase64): array
    {
        $channels = $this->getAvailableChannels();
        
        foreach ($channels as $channel) {
            /** @var ChannelInterface $instance */
            $instance = $channel['instance'];
            $code = $channel['info']['code'];
            
            try {
                $result = $instance->faceDetect($imageBase64);
                
                if ($result['success']) {
                    $result['channel'] = $code;
                    $result['channel_name'] = $channel['info']['name'];
                    return $result;
                }
                
                Log::warning("通道 {$code} 人脸检测失败: " . ($result['error'] ?? '未知错误'));
            } catch (\Exception $e) {
                Log::error("通道 {$code} 人脸检测异常: " . $e->getMessage());
            }
        }
        
        return [
            'success' => false,
            'face_count' => 0,
            'faces' => [],
            'error' => '所有通道均不可用',
        ];
    }

    /**
     * 执行人脸比对（带自动降级）
     * @param string $image1
     * @param string $image2
     * @return array
     */
    public function faceCompare(string $image1, string $image2): array
    {
        $channels = $this->getAvailableChannels();
        
        foreach ($channels as $channel) {
            /** @var ChannelInterface $instance */
            $instance = $channel['instance'];
            $code = $channel['info']['code'];
            
            try {
                $result = $instance->faceCompare($image1, $image2);
                
                if ($result['success']) {
                    $result['channel'] = $code;
                    $result['channel_name'] = $channel['info']['name'];
                    return $result;
                }
                
                Log::warning("通道 {$code} 人脸比对失败: " . ($result['error'] ?? '未知错误'));
            } catch (\Exception $e) {
                Log::error("通道 {$code} 人脸比对异常: " . $e->getMessage());
            }
        }
        
        return [
            'success' => false,
            'score' => 0,
            'is_match' => false,
            'error' => '所有通道均不可用',
        ];
    }

    /**
     * 执行活体检测（带自动降级）
     * @param array $images
     * @param array $actions
     * @return array
     */
    public function livenessDetect(array $images, array $actions = []): array
    {
        $channels = $this->getAvailableChannels();
        
        foreach ($channels as $channel) {
            /** @var ChannelInterface $instance */
            $instance = $channel['instance'];
            $code = $channel['info']['code'];
            
            try {
                $result = $instance->livenessDetect($images, $actions);
                
                if ($result['success']) {
                    $result['channel'] = $code;
                    $result['channel_name'] = $channel['info']['name'];
                    return $result;
                }
                
                Log::warning("通道 {$code} 活体检测失败: " . ($result['error'] ?? '未知错误'));
            } catch (\Exception $e) {
                Log::error("通道 {$code} 活体检测异常: " . $e->getMessage());
            }
        }
        
        return [
            'success' => false,
            'passed' => false,
            'score' => 0,
            'error' => '所有通道均不可用',
        ];
    }

    /**
     * 完成认证
     * @param string $token
     * @param array $data
     * @return array
     */
    public function completeVerification(string $token, array $data): array
    {
        $verification = Db::name('verifications')->where('token', $token)->find();
        
        if (!$verification) {
            return [
                'success' => false,
                'message' => '认证记录不存在',
            ];
        }
        
        if ($verification['status'] != 0) {
            return [
                'success' => false,
                'message' => '认证已完成或已过期',
            ];
        }
        
        // 检查是否过期
        if (strtotime($verification['expire_at']) < time()) {
            Db::name('verifications')->where('token', $token)->update([
                'status' => 3, // 过期
                'fail_reason' => '认证超时',
                'completed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            
            return [
                'success' => false,
                'message' => '认证已过期，请重新发起',
                'status' => 'expired',
            ];
        }
        
        $status = $data['passed'] ? 1 : 2; // 1通过 2未通过
        $score = $data['score'] ?? 0;
        $livenessPassed = $data['liveness_passed'] ?? false;
        $faceMatchScore = $data['face_match_score'] ?? 0;
        
        // 更新认证记录
        Db::name('verifications')->where('token', $token)->update([
            'status' => $status,
            'score' => $score,
            'liveness_passed' => $livenessPassed ? 1 : 0,
            'face_match_score' => $faceMatchScore,
            'actions' => json_encode($data['actions'] ?? []),
            'fail_reason' => $data['fail_reason'] ?? '',
            'api_source' => $data['channel'] ?? '',
            'api_response' => json_encode($data['api_response'] ?? []),
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        // 保存人脸图片
        if (!empty($data['face_image'])) {
            $imagePath = $this->saveFaceImage($token, $data['face_image']);
            Db::name('verifications')->where('token', $token)->update([
                'face_image_path' => $imagePath,
            ]);
        }
        
        // 触发回调
        if (!empty($verification['callback_url'])) {
            $this->triggerCallback($token, $verification['callback_url']);
        }
        
        return [
            'success' => true,
            'status' => $status == 1 ? 'passed' : 'failed',
            'score' => $score,
            'message' => $status == 1 ? '认证通过' : '认证未通过',
        ];
    }

    /**
     * 保存人脸图片
     */
    protected function saveFaceImage(string $token, string $imageBase64): string
    {
        // 去掉base64前缀
        if (strpos($imageBase64, ',') !== false) {
            $imageBase64 = explode(',', $imageBase64)[1];
        }
        
        $imageData = base64_decode($imageBase64);
        if (!$imageData) {
            return '';
        }
        
        $dateDir = date('Ymd');
        $uploadDir = public_path() . '/uploads/faces/' . $dateDir;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = md5($token . '_' . time()) . '.jpg';
        $filepath = $uploadDir . '/' . $filename;
        
        file_put_contents($filepath, $imageData);
        
        return '/uploads/faces/' . $dateDir . '/' . $filename;
    }

    /**
     * 触发回调通知
     */
    protected function triggerCallback(string $token, string $callbackUrl): void
    {
        try {
            $verification = Db::name('verifications')->where('token', $token)->find();
            if (!$verification) return;
            
            $callbackSecret = sm_config('mofang.callback_secret', '');
            
            $params = [
                'token' => $token,
                'status' => $verification['status'] == 1 ? 'passed' : 'failed',
                'score' => $verification['score'],
                'verify_time' => $verification['completed_at'],
                'timestamp' => time(),
            ];
            
            $params['sign'] = sm_hmac_sign($params, $callbackSecret);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $callbackUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $success = $httpCode >= 200 && $httpCode < 300;
            
            Db::name('verifications')->where('token', $token)->update([
                'callback_status' => $success ? 1 : 2,
                'callback_time' => date('Y-m-d H:i:s'),
            ]);
            
            Log::info("认证回调完成: {$token}, HTTP: {$httpCode}");
        } catch (\Exception $e) {
            Log::error("认证回调失败: {$token}, " . $e->getMessage());
            
            Db::name('verifications')->where('token', $token)->update([
                'callback_status' => 2,
                'callback_time' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * 获取认证结果
     * @param string $token
     * @return array
     */
    public function getVerificationResult(string $token): array
    {
        $verification = Db::name('verifications')->where('token', $token)->find();
        
        if (!$verification) {
            return [
                'success' => false,
                'message' => '认证记录不存在',
            ];
        }
        
        $statusMap = [
            0 => 'pending',
            1 => 'passed',
            2 => 'failed',
            3 => 'expired',
            4 => 'error',
        ];
        
        return [
            'success' => true,
            'status' => $statusMap[$verification['status']] ?? 'unknown',
            'score' => (float)$verification['score'],
            'verify_time' => $verification['completed_at'],
            'liveness_passed' => (bool)$verification['liveness_passed'],
            'face_match_score' => (float)$verification['face_match_score'],
            'api_source' => $verification['channel'],
            'fail_reason' => $verification['fail_reason'],
        ];
    }

    /**
     * 生成随机活体动作序列
     * @param int $count 动作数量
     * @return array
     */
    public function generateLivenessActions(int $count = 3): array
    {
        $allActions = [
            ['code' => 'blink', 'name' => '眨眼', 'prompt' => '请眨眨眼'],
            ['code' => 'mouth_open', 'name' => '张嘴', 'prompt' => '请张大嘴巴'],
            ['code' => 'head_shake_left', 'name' => '向左摇头', 'prompt' => '请向左摇头'],
            ['code' => 'head_shake_right', 'name' => '向右摇头', 'prompt' => '请向右摇头'],
            ['code' => 'head_nod', 'name' => '点头', 'prompt' => '请点点头'],
        ];
        
        shuffle($allActions);
        
        return array_slice($allActions, 0, min($count, count($allActions)));
    }
}
