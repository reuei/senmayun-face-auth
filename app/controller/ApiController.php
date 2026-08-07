<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - API控制器
// +----------------------------------------------------------------------

namespace app\controller;

use app\service\FaceService;
use app\service\TokenService;
use think\facade\Db;
use think\facade\Log;
use think\Request;

class ApiController
{
    protected FaceService $faceService;
    protected TokenService $tokenService;

    public function __construct()
    {
        $this->faceService = new FaceService();
        $this->tokenService = new TokenService();
    }

    /**
     * 初始化认证会话
     */
    public function initAuth(Request $request)
    {
        $params = $request->post();
        
        // 验证必要参数
        if (empty($params['user_id'])) {
            return json([
                'code' => 400,
                'message' => 'user_id不能为空',
                'data' => null,
            ]);
        }
        
        // 速率限制
        $apiKey = $params['api_key'] ?? '';
        $rateLimit = $this->tokenService->checkRateLimit("api_init:{$apiKey}", 20, 60);
        if (!$rateLimit['allowed']) {
            return json([
                'code' => 429,
                'message' => '请求过于频繁，请稍后再试',
                'data' => [
                    'retry_after' => $rateLimit['reset'],
                ],
            ]);
        }
        
        try {
            $result = $this->faceService->initVerification([
                'user_id' => $params['user_id'],
                'name' => $params['name'] ?? '',
                'id_card' => $params['id_card'] ?? '',
                'return_url' => $params['return_url'] ?? '',
                'verify_type' => $params['verify_type'] ?? 'full',
            ]);
            
            // 记录API日志
            $this->logApiCall($request, 'auth/init', true);
            
            return json([
                'code' => 200,
                'message' => 'success',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('初始化认证失败: ' . $e->getMessage());
            
            return json([
                'code' => 500,
                'message' => '初始化认证失败: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * 验证Token有效性
     */
    public function verifyToken(Request $request)
    {
        $token = $request->post('token', '');
        
        if (empty($token)) {
            return json([
                'code' => 400,
                'message' => 'token不能为空',
                'data' => null,
            ]);
        }
        
        $result = $this->tokenService->verifyToken($token);
        
        return json([
            'code' => $result['valid'] ? 200 : 401,
            'message' => $result['message'],
            'data' => [
                'valid' => $result['valid'],
                'user_info' => $result['payload'] ?? [],
            ],
        ]);
    }

    /**
     * 查询认证结果
     */
    public function getResult(Request $request)
    {
        $token = $request->post('token', '');
        
        if (empty($token)) {
            return json([
                'code' => 400,
                'message' => 'token不能为空',
                'data' => null,
            ]);
        }
        
        $result = $this->faceService->getVerificationResult($token);
        
        if (!$result['success']) {
            return json([
                'code' => 404,
                'message' => $result['message'],
                'data' => null,
            ]);
        }
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * 认证回调（内部使用）
     */
    public function callback(Request $request)
    {
        $token = $request->post('token', '');
        $status = $request->post('status', '');
        
        if (empty($token) || empty($status)) {
            return json([
                'code' => 400,
                'message' => '参数不完整',
                'data' => null,
            ]);
        }
        
        // 处理回调逻辑
        return json([
            'code' => 200,
            'message' => '回调已接收',
            'data' => null,
        ]);
    }

    /**
     * 人脸检测
     */
    public function faceDetect(Request $request)
    {
        $image = $request->post('image', '');
        
        if (empty($image)) {
            return json([
                'code' => 400,
                'message' => '图片不能为空',
                'data' => null,
            ]);
        }
        
        // 去掉base64前缀
        if (strpos($image, ',') !== false) {
            $image = explode(',', $image)[1];
        }
        
        try {
            $result = $this->faceService->faceDetect($image);
            
            if (!$result['success']) {
                return json([
                    'code' => 500,
                    'message' => $result['error'] ?? '检测失败',
                    'data' => null,
                ]);
            }
            
            return json([
                'code' => 200,
                'message' => 'success',
                'data' => [
                    'face_count' => $result['face_count'],
                    'faces' => $result['faces'],
                    'channel' => $result['channel'] ?? '',
                    'channel_name' => $result['channel_name'] ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('人脸检测失败: ' . $e->getMessage());
            
            return json([
                'code' => 500,
                'message' => '检测失败: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * 人脸比对
     */
    public function faceCompare(Request $request)
    {
        $image1 = $request->post('image1', '');
        $image2 = $request->post('image2', '');
        
        if (empty($image1) || empty($image2)) {
            return json([
                'code' => 400,
                'message' => '两张图片都不能为空',
                'data' => null,
            ]);
        }
        
        // 去掉base64前缀
        if (strpos($image1, ',') !== false) {
            $image1 = explode(',', $image1)[1];
        }
        if (strpos($image2, ',') !== false) {
            $image2 = explode(',', $image2)[1];
        }
        
        try {
            $result = $this->faceService->faceCompare($image1, $image2);
            
            if (!$result['success']) {
                return json([
                    'code' => 500,
                    'message' => $result['error'] ?? '比对失败',
                    'data' => null,
                ]);
            }
            
            return json([
                'code' => 200,
                'message' => 'success',
                'data' => [
                    'score' => $result['score'],
                    'is_match' => $result['is_match'],
                    'channel' => $result['channel'] ?? '',
                    'channel_name' => $result['channel_name'] ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('人脸比对失败: ' . $e->getMessage());
            
            return json([
                'code' => 500,
                'message' => '比对失败: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * 活体检测
     */
    public function faceLiveness(Request $request)
    {
        $images = $request->post('images', []);
        $actions = $request->post('actions', []);
        
        if (empty($images)) {
            return json([
                'code' => 400,
                'message' => '图片不能为空',
                'data' => null,
            ]);
        }
        
        try {
            $result = $this->faceService->livenessDetect($images, $actions);
            
            if (!$result['success']) {
                return json([
                    'code' => 500,
                    'message' => $result['error'] ?? '检测失败',
                    'data' => null,
                ]);
            }
            
            return json([
                'code' => 200,
                'message' => 'success',
                'data' => [
                    'passed' => $result['passed'],
                    'score' => $result['score'],
                    'channel' => $result['channel'] ?? '',
                    'channel_name' => $result['channel_name'] ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('活体检测失败: ' . $e->getMessage());
            
            return json([
                'code' => 500,
                'message' => '检测失败: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * 提交认证结果（前端调用）
     */
    public function submitVerification(Request $request)
    {
        $token = $request->post('token', '');
        $passed = $request->post('passed', false);
        $score = $request->post('score', 0);
        $faceImage = $request->post('face_image', '');
        $actions = $request->post('actions', []);
        $livenessPassed = $request->post('liveness_passed', false);
        $faceMatchScore = $request->post('face_match_score', 0);
        
        if (empty($token)) {
            return json([
                'code' => 400,
                'message' => 'token不能为空',
                'data' => null,
            ]);
        }
        
        try {
            $result = $this->faceService->completeVerification($token, [
                'passed' => $passed,
                'score' => $score,
                'face_image' => $faceImage,
                'actions' => $actions,
                'liveness_passed' => $livenessPassed,
                'face_match_score' => $faceMatchScore,
                'channel' => $request->post('channel', ''),
                'fail_reason' => $request->post('fail_reason', ''),
            ]);
            
            if (!$result['success']) {
                return json([
                    'code' => 400,
                    'message' => $result['message'],
                    'data' => null,
                ]);
            }
            
            return json([
                'code' => 200,
                'message' => '提交成功',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('提交认证结果失败: ' . $e->getMessage());
            
            return json([
                'code' => 500,
                'message' => '提交失败: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * 获取活体动作序列
     */
    public function getLivenessActions(Request $request)
    {
        $token = $request->get('token', '');
        $count = $request->get('count', 3);
        
        $actions = $this->faceService->generateLivenessActions((int)$count);
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'actions' => $actions,
                'timeout' => sm_config('auth.action_timeout', 15),
            ],
        ]);
    }

    /**
     * 记录API调用日志
     */
    protected function logApiCall(Request $request, string $endpoint, bool $success = true): void
    {
        try {
            $params = $request->post();
            // 脱敏处理
            unset($params['api_secret'], $params['image'], $params['image1'], $params['image2']);
            
            Db::name('api_logs')->insert([
                'api_key' => $params['api_key'] ?? '',
                'endpoint' => $endpoint,
                'method' => $request->method(),
                'request_ip' => sm_get_client_ip(),
                'user_agent' => $request->header('user-agent', ''),
                'request_params' => json_encode($params),
                'status' => $success ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            Log::error('记录API日志失败: ' . $e->getMessage());
        }
    }
}
