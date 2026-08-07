<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 腾讯云慧眼通道
// +----------------------------------------------------------------------

namespace app\service\Channel;

use think\facade\Log;

class TencentHuiyanChannel implements ChannelInterface
{
    protected string $code = 'tencent';
    protected string $name = '腾讯云慧眼';
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isAvailable(): bool
    {
        return !empty($this->config['secret_id']) 
            && !empty($this->config['secret_key']);
    }

    public function faceDetect(string $imageBase64): array
    {
        try {
            $startTime = microtime(true);
            
            $params = [
                'Image' => $imageBase64,
                'MaxFaceNum' => 1,
                'MinFaceSize' => 40,
                'NeedFaceAttributes' => 1,
                'NeedQualityDetection' => 1,
            ];
            
            $result = $this->request('DetectFace', $params);
            
            if ($result['code'] !== 0) {
                return [
                    'success' => false,
                    'face_count' => 0,
                    'faces' => [],
                    'error' => $result['message'] ?? '未知错误',
                ];
            }
            
            $faceNum = $result['data']['FaceNum'] ?? 0;
            $faces = [];
            
            if (!empty($result['data']['FaceInfos'])) {
                foreach ($result['data']['FaceInfos'] as $face) {
                    $faces[] = [
                        'x' => $face['X'] ?? 0,
                        'y' => $face['Y'] ?? 0,
                        'width' => $face['Width'] ?? 0,
                        'height' => $face['Height'] ?? 0,
                        'gender' => $face['FaceAttributesInfo']['Gender'] ?? 0,
                        'age' => $face['FaceAttributesInfo']['Age'] ?? 0,
                        'quality' => $face['QualityInfo']['Score'] ?? 0,
                    ];
                }
            }
            
            return [
                'success' => true,
                'face_count' => $faceNum,
                'faces' => $faces,
                'error' => '',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('腾讯云慧眼人脸检测失败: ' . $e->getMessage());
            return [
                'success' => false,
                'face_count' => 0,
                'faces' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    public function faceCompare(string $image1, string $image2): array
    {
        try {
            $startTime = microtime(true);
            
            $params = [
                'ImageA' => $image1,
                'ImageB' => $image2,
                'QualityControl' => 'NORMAL',
                'NeedRotateDetection' => 1,
            ];
            
            $result = $this->request('CompareFace', $params);
            
            if ($result['code'] !== 0) {
                return [
                    'success' => false,
                    'score' => 0,
                    'is_match' => false,
                    'error' => $result['message'] ?? '未知错误',
                ];
            }
            
            $score = $result['data']['Score'] ?? 0;
            $threshold = $this->config['threshold'] ?? 80;
            
            return [
                'success' => true,
                'score' => round($score, 2),
                'is_match' => $score >= $threshold,
                'error' => '',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('腾讯云慧眼人脸比对失败: ' . $e->getMessage());
            return [
                'success' => false,
                'score' => 0,
                'is_match' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function livenessDetect(array $images, array $actions = []): array
    {
        try {
            $startTime = microtime(true);
            
            if (empty($images)) {
                return [
                    'success' => false,
                    'passed' => false,
                    'score' => 0,
                    'error' => '图片不能为空',
                ];
            }
            
            // 使用动作活体接口
            $params = [
                'LivenessType' => 'ACTION',
                'ValidateData' => $images[0], // 最佳帧
                'Optional' => json_encode([
                    'actions' => $actions,
                ]),
            ];
            
            $result = $this->request('LivenessCompare', $params);
            
            if ($result['code'] !== 0) {
                return [
                    'success' => false,
                    'passed' => false,
                    'score' => 0,
                    'error' => $result['message'] ?? '未知错误',
                ];
            }
            
            $score = $result['data']['Score'] ?? 0;
            
            return [
                'success' => true,
                'passed' => $score >= 70,
                'score' => round($score, 2),
                'error' => '',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('腾讯云慧眼活体检测失败: ' . $e->getMessage());
            return [
                'success' => false,
                'passed' => false,
                'score' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getVerifyToken(array $params = []): array
    {
        try {
            $startTime = microtime(true);
            
            $ruleId = $this->config['rule_id'] ?? '';
            
            $reqParams = [
                'RuleId' => $ruleId,
                'SceneInfo' => $params['scene_info'] ?? 'default',
                'OrderNo' => $params['order_no'] ?? '',
                'Name' => $params['name'] ?? '',
                'IdCard' => $params['id_card'] ?? '',
                'RedirectUrl' => $params['redirect_url'] ?? '',
            ];
            
            $result = $this->request('ApplyWebVerificationToken', $reqParams);
            
            if ($result['code'] !== 0) {
                return [
                    'success' => false,
                    'token' => '',
                    'url' => '',
                    'error' => $result['message'] ?? '未知错误',
                ];
            }
            
            return [
                'success' => true,
                'token' => $result['data']['BizToken'] ?? '',
                'url' => $result['data']['VerificationUrl'] ?? '',
                'error' => '',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('腾讯云慧眼获取Token失败: ' . $e->getMessage());
            return [
                'success' => false,
                'token' => '',
                'url' => '',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getVerifyResult(string $token): array
    {
        try {
            $startTime = microtime(true);
            
            $params = [
                'BizToken' => $token,
            ];
            
            $result = $this->request('GetWebVerificationResult', $params);
            
            if ($result['code'] !== 0) {
                return [
                    'success' => false,
                    'status' => 'error',
                    'score' => 0,
                    'detail' => [],
                    'error' => $result['message'] ?? '未知错误',
                ];
            }
            
            $resultCode = $result['data']['ResultCode'] ?? -1;
            $status = 'pending';
            
            if ($resultCode === 0) {
                $status = 'passed';
            } elseif ($resultCode > 0) {
                $status = 'failed';
            }
            
            return [
                'success' => true,
                'status' => $status,
                'score' => $result['data']['Similarity'] ?? 0,
                'detail' => [
                    'result_code' => $resultCode,
                    'description' => $result['data']['Description'] ?? '',
                    'best_frame' => $result['data']['BestFrameUrl'] ?? '',
                ],
                'error' => '',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('腾讯云慧眼查询结果失败: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'error',
                'score' => 0,
                'detail' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    public function testConnection(): array
    {
        $startTime = microtime(true);
        
        try {
            if (!$this->isAvailable()) {
                return [
                    'success' => false,
                    'message' => '请先配置 SecretId 和 SecretKey',
                    'latency' => 0,
                ];
            }
            
            // 调用一个简单接口测试
            $result = $this->request('GetActionSequence', [
                'ActionType' => 1,
            ]);
            
            $latency = round((microtime(true) - $startTime) * 1000);
            
            if ($result['code'] === 0) {
                return [
                    'success' => true,
                    'message' => '连接正常',
                    'latency' => $latency,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? '连接失败',
                    'latency' => $latency,
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'latency' => 0,
            ];
        }
    }

    /**
     * 调用腾讯云API
     * @param string $action 接口名
     * @param array $params 参数
     * @return array
     */
    protected function request(string $action, array $params = []): array
    {
        $secretId = $this->config['secret_id'] ?? '';
        $secretKey = $this->config['secret_key'] ?? '';
        
        if (empty($secretId) || empty($secretKey)) {
            return ['code' => -1, 'message' => '未配置密钥'];
        }
        
        $service = 'faceid';
        $host = 'faceid.tencentcloudapi.com';
        $version = '2018-03-01';
        $region = 'ap-beijing';
        $timestamp = time();
        $date = gmdate('Y-m-d', $timestamp);
        
        // 步骤1: 拼接规范请求串
        $payload = json_encode($params, JSON_UNESCAPED_UNICODE);
        $hashedRequestPayload = hash('SHA256', $payload);
        
        $httpRequestMethod = 'POST';
        $canonicalUri = '/';
        $canonicalQueryString = '';
        $canonicalHeaders = "content-type:application/json\n"
            . "host:$host\n";
        $signedHeaders = 'content-type;host';
        
        $canonicalRequest = $httpRequestMethod . "\n"
            . $canonicalUri . "\n"
            . $canonicalQueryString . "\n"
            . $canonicalHeaders . "\n"
            . $signedHeaders . "\n"
            . $hashedRequestPayload;
        
        // 步骤2: 拼接待签名字符串
        $algorithm = 'TC3-HMAC-SHA256';
        $credentialScope = "$date/$service/tc3_request";
        $hashedCanonicalRequest = hash('SHA256', $canonicalRequest);
        
        $stringToSign = $algorithm . "\n"
            . $timestamp . "\n"
            . $credentialScope . "\n"
            . $hashedCanonicalRequest;
        
        // 步骤3: 计算签名
        $secretDate = hash_hmac('SHA256', $date, "TC3$secretKey", true);
        $secretService = hash_hmac('SHA256', $service, $secretDate, true);
        $secretSigning = hash_hmac('SHA256', 'tc3_request', $secretService, true);
        $signature = hash_hmac('SHA256', $stringToSign, $secretSigning);
        
        // 步骤4: 拼接Authorization
        $authorization = $algorithm . ' '
            . "Credential=$secretId/$credentialScope, "
            . "SignedHeaders=$signedHeaders, "
            . "Signature=$signature";
        
        // 发送请求
        $url = "https://$host/";
        $headers = [
            "Authorization: $authorization",
            "Content-Type: application/json",
            "Host: $host",
            "X-TC-Action: $action",
            "X-TC-Timestamp: $timestamp",
            "X-TC-Version: $version",
            "X-TC-Region: $region",
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['code' => -1, 'message' => '网络错误: ' . $error];
        }
        
        $result = json_decode($response, true);
        
        if (!$result) {
            return ['code' => -1, 'message' => '响应解析失败'];
        }
        
        if (isset($result['Response']['Error'])) {
            return [
                'code' => -1,
                'message' => $result['Response']['Error']['Message'] ?? '未知错误',
            ];
        }
        
        return [
            'code' => 0,
            'message' => 'success',
            'data' => $result['Response'] ?? [],
        ];
    }
}
