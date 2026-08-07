<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 百度AI人脸识别通道
// +----------------------------------------------------------------------

namespace app\service\Channel;

use think\facade\Log;

class BaiduFaceChannel implements ChannelInterface
{
    protected string $code = 'baidu';
    protected string $name = '百度AI人脸识别';
    protected array $config = [];
    protected ?string $accessToken = null;

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
        return !empty($this->config['api_key']) 
            && !empty($this->config['secret_key']);
    }

    public function faceDetect(string $imageBase64): array
    {
        try {
            $startTime = microtime(true);
            
            $params = [
                'image' => $imageBase64,
                'image_type' => 'BASE64',
                'face_field' => 'age,beauty,expression,face_shape,gender,glasses,landmark,quality',
                'max_face_num' => 1,
                'face_type' => 'LIVE',
            ];
            
            $result = $this->request('detect', $params);
            
            if ($result['error_code'] !== 0) {
                return [
                    'success' => false,
                    'face_count' => 0,
                    'faces' => [],
                    'error' => $result['error_msg'] ?? '未知错误',
                ];
            }
            
            $faceNum = $result['result']['face_num'] ?? 0;
            $faces = [];
            
            if (!empty($result['result']['face_list'])) {
                foreach ($result['result']['face_list'] as $face) {
                    $location = $face['location'] ?? [];
                    $faces[] = [
                        'x' => $location['left'] ?? 0,
                        'y' => $location['top'] ?? 0,
                        'width' => $location['width'] ?? 0,
                        'height' => $location['height'] ?? 0,
                        'rotation' => $location['rotation'] ?? 0,
                        'age' => $face['age'] ?? 0,
                        'gender' => ($face['gender']['type'] ?? '') === 'male' ? 1 : 0,
                        'beauty' => $face['beauty'] ?? 0,
                        'quality' => $face['quality']['completeness'] ?? 0,
                        'blur' => $face['quality']['blur'] ?? 0,
                        'illumination' => $face['quality']['illumination'] ?? 0,
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
            Log::error('百度AI人脸检测失败: ' . $e->getMessage());
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
                [
                    'image' => $image1,
                    'image_type' => 'BASE64',
                    'face_type' => 'LIVE',
                    'quality_control' => 'NORMAL',
                    'liveness_control' => 'NONE',
                ],
                [
                    'image' => $image2,
                    'image_type' => 'BASE64',
                    'face_type' => 'LIVE',
                    'quality_control' => 'NORMAL',
                    'liveness_control' => 'NONE',
                ],
            ];
            
            $result = $this->request('match', $params);
            
            if ($result['error_code'] !== 0) {
                return [
                    'success' => false,
                    'score' => 0,
                    'is_match' => false,
                    'error' => $result['error_msg'] ?? '未知错误',
                ];
            }
            
            $score = $result['result']['score'] ?? 0;
            $threshold = $this->config['threshold'] ?? 80;
            
            return [
                'success' => true,
                'score' => round($score, 2),
                'is_match' => $score >= $threshold,
                'error' => '',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('百度AI人脸比对失败: ' . $e->getMessage());
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
            
            // 使用百度活体检测接口
            $params = [
                'image' => $images[0],
                'image_type' => 'BASE64',
            ];
            
            $result = $this->request('faceverify', $params);
            
            if ($result['error_code'] !== 0) {
                return [
                    'success' => false,
                    'passed' => false,
                    'score' => 0,
                    'error' => $result['error_msg'] ?? '未知错误',
                ];
            }
            
            $faceLiveness = $result['result']['face_liveness'] ?? 0;
            
            return [
                'success' => true,
                'passed' => $faceLiveness >= 0.7,
                'score' => round($faceLiveness * 100, 2),
                'error' => '',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('百度AI活体检测失败: ' . $e->getMessage());
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
        // 百度AI不支持H5跳转式核身，返回空
        return [
            'success' => false,
            'token' => '',
            'url' => '',
            'error' => '该通道不支持H5核身模式',
        ];
    }

    public function getVerifyResult(string $token): array
    {
        return [
            'success' => false,
            'status' => 'error',
            'score' => 0,
            'detail' => [],
            'error' => '该通道不支持此功能',
        ];
    }

    public function testConnection(): array
    {
        $startTime = microtime(true);
        
        try {
            if (!$this->isAvailable()) {
                return [
                    'success' => false,
                    'message' => '请先配置 API Key 和 Secret Key',
                    'latency' => 0,
                ];
            }
            
            // 测试获取access_token
            $token = $this->getAccessToken();
            $latency = round((microtime(true) - $startTime) * 1000);
            
            if ($token) {
                return [
                    'success' => true,
                    'message' => '连接正常',
                    'latency' => $latency,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '获取Access Token失败，请检查密钥配置',
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
     * 获取Access Token
     * @return string|null
     */
    protected function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        
        $apiKey = $this->config['api_key'] ?? '';
        $secretKey = $this->config['secret_key'] ?? '';
        
        if (empty($apiKey) || empty($secretKey)) {
            return null;
        }
        
        $url = 'https://aip.baidubce.com/oauth/2.0/token';
        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $apiKey,
            'client_secret' => $secretKey,
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['access_token'])) {
            $this->accessToken = $result['access_token'];
            return $this->accessToken;
        }
        
        return null;
    }

    /**
     * 调用百度AI API
     * @param string $method 方法名
     * @param array $params 参数
     * @return array
     */
    protected function request(string $method, array $params = []): array
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return ['error_code' => -1, 'error_msg' => '获取Access Token失败'];
        }
        
        $baseUrl = 'https://aip.baidubce.com/rest/2.0/face/v3';
        $url = "$baseUrl/$method?access_token=$accessToken";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['error_code' => -1, 'error_msg' => '网络错误: ' . $error];
        }
        
        $result = json_decode($response, true);
        
        if (!$result) {
            return ['error_code' => -1, 'error_msg' => '响应解析失败'];
        }
        
        return $result;
    }
}
