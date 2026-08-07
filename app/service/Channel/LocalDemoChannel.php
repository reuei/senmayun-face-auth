<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 自研/本地演示通道
// +----------------------------------------------------------------------

namespace app\service\Channel;

use think\facade\Log;

/**
 * 本地演示通道
 * 用于无第三方密钥时的演示与测试
 * 基于PHP GD库实现基础人脸检测和比对
 * 注意：此通道仅用于演示，不保证准确率，生产环境请使用第三方API
 */
class LocalDemoChannel implements ChannelInterface
{
    protected string $code = 'local';
    protected string $name = '自研算法(演示)';
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
        // 本地通道始终可用（演示模式）
        return extension_loaded('gd');
    }

    public function faceDetect(string $imageBase64): array
    {
        try {
            $startTime = microtime(true);
            
            // 解码图片
            $imageData = base64_decode($imageBase64);
            if (!$imageData) {
                return [
                    'success' => false,
                    'face_count' => 0,
                    'faces' => [],
                    'error' => '图片解码失败',
                ];
            }
            
            // 创建图片资源
            $image = @imagecreatefromstring($imageData);
            if (!$image) {
                return [
                    'success' => false,
                    'face_count' => 0,
                    'faces' => [],
                    'error' => '图片格式不支持',
                ];
            }
            
            $width = imagesx($image);
            $height = imagesy($image);
            
            // 简化版人脸检测：基于肤色区域检测
            $faceRegion = $this->detectSkinRegion($image, $width, $height);
            
            imagedestroy($image);
            
            $faces = [];
            if ($faceRegion) {
                $faces[] = [
                    'x' => $faceRegion['x'],
                    'y' => $faceRegion['y'],
                    'width' => $faceRegion['width'],
                    'height' => $faceRegion['height'],
                    'confidence' => $faceRegion['confidence'],
                    'quality' => 85.0, // 演示用
                ];
            }
            
            return [
                'success' => true,
                'face_count' => count($faces),
                'faces' => $faces,
                'error' => '',
                'note' => '演示模式，检测结果仅供参考',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('本地人脸检测失败: ' . $e->getMessage());
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
            
            // 简化版人脸比对：基于图像直方图相似度
            $similarity = $this->calculateImageSimilarity($image1, $image2);
            
            $threshold = $this->config['threshold'] ?? 60;
            
            return [
                'success' => true,
                'score' => round($similarity, 2),
                'is_match' => $similarity >= $threshold,
                'error' => '',
                'note' => '演示模式，比对结果仅供参考',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('本地人脸比对失败: ' . $e->getMessage());
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
            
            // 简化版活体检测：检查多帧之间的差异
            $score = 0;
            
            if (count($images) >= 2) {
                // 计算帧间差异
                $totalDiff = 0;
                for ($i = 1; $i < count($images); $i++) {
                    $diff = $this->calculateImageDiff($images[$i - 1], $images[$i]);
                    $totalDiff += $diff;
                }
                $avgDiff = $totalDiff / (count($images) - 1);
                
                // 帧间有适度差异说明是真人（照片不会有微变化）
                // 差异太小可能是照片，差异太大可能是视频攻击
                if ($avgDiff > 2 && $avgDiff < 30) {
                    $score = 75 + min($avgDiff, 15);
                } else {
                    $score = 50;
                }
            } else {
                // 单张图片给基础分
                $score = 60;
            }
            
            // 如果有动作序列，增加分数
            if (!empty($actions)) {
                $score = min($score + 10, 95);
            }
            
            return [
                'success' => true,
                'passed' => $score >= 70,
                'score' => round($score, 2),
                'error' => '',
                'note' => '演示模式，活体检测结果仅供参考',
                'latency' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('本地活体检测失败: ' . $e->getMessage());
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
        // 本地通道生成模拟token
        $token = 'local_' . bin2hex(random_bytes(16));
        
        return [
            'success' => true,
            'token' => $token,
            'url' => '',
            'error' => '',
            'note' => '演示模式',
        ];
    }

    public function getVerifyResult(string $token): array
    {
        // 本地通道直接返回演示结果
        return [
            'success' => true,
            'status' => 'passed',
            'score' => 85.5,
            'detail' => [
                'note' => '演示模式结果',
            ],
            'error' => '',
        ];
    }

    public function testConnection(): array
    {
        $startTime = microtime(true);
        
        if (!extension_loaded('gd')) {
            return [
                'success' => false,
                'message' => 'GD扩展未安装',
                'latency' => 0,
            ];
        }
        
        return [
            'success' => true,
            'message' => '本地演示通道正常（GD库已加载）',
            'latency' => round((microtime(true) - $startTime) * 1000),
        ];
    }

    /**
     * 检测肤色区域（简化版人脸检测）
     */
    protected function detectSkinRegion($image, int $width, int $height): ?array
    {
        $skinPixels = [];
        $sampleStep = max(1, min($width, $height) / 100);
        
        for ($y = 0; $y < $height; $y += $sampleStep) {
            for ($x = 0; $x < $width; $x += $sampleStep) {
                $rgb = imagecolorat($image, (int)$x, (int)$y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                
                // 简单肤色检测（YCrCb色彩空间）
                if ($this->isSkinColor($r, $g, $b)) {
                    $skinPixels[] = ['x' => $x, 'y' => $y];
                }
            }
        }
        
        if (count($skinPixels) < 10) {
            return null;
        }
        
        // 计算边界框
        $minX = PHP_INT_MAX;
        $maxX = 0;
        $minY = PHP_INT_MAX;
        $maxY = 0;
        
        foreach ($skinPixels as $p) {
            $minX = min($minX, $p['x']);
            $maxX = max($maxX, $p['x']);
            $minY = min($minY, $p['y']);
            $maxY = max($maxY, $p['y']);
        }
        
        $faceWidth = $maxX - $minX;
        $faceHeight = $maxY - $minY;
        
        // 验证人脸比例（宽高比约0.7-0.9）
        if ($faceWidth <= 0 || $faceHeight <= 0) {
            return null;
        }
        
        $ratio = $faceWidth / $faceHeight;
        if ($ratio < 0.5 || $ratio > 1.5) {
            return null;
        }
        
        // 人脸面积占比
        $faceArea = $faceWidth * $faceHeight;
        $imageArea = $width * $height;
        $areaRatio = $faceArea / $imageArea;
        
        if ($areaRatio < 0.05 || $areaRatio > 0.8) {
            return null;
        }
        
        $confidence = min(90, 60 + count($skinPixels) / 5);
        
        return [
            'x' => (int)$minX,
            'y' => (int)$minY,
            'width' => (int)$faceWidth,
            'height' => (int)$faceHeight,
            'confidence' => round($confidence, 2),
        ];
    }

    /**
     * 判断是否为肤色
     */
    protected function isSkinColor(int $r, int $g, int $b): bool
    {
        // 简单肤色判断规则
        if ($r < 95 || $g < 40 || $b < 20) {
            return false;
        }
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        
        if ($max - $min < 15) {
            return false;
        }
        
        if ($r <= $g || $r <= $b) {
            return false;
        }
        
        // YCrCb 近似判断
        $cr = 128 + (112 * $r - 93 * $g - 19 * $b) / 200;
        $cb = 128 + (-37 * $r - 74 * $g + 112 * $b) / 200;
        
        return $cr > 133 && $cr < 173 && $cb > 77 && $cb < 127;
    }

    /**
     * 计算两张图片的相似度（基于直方图）
     */
    protected function calculateImageSimilarity(string $image1Base64, string $image2Base64): float
    {
        $img1 = @imagecreatefromstring(base64_decode($image1Base64));
        $img2 = @imagecreatefromstring(base64_decode($image2Base64));
        
        if (!$img1 || !$img2) {
            return 0;
        }
        
        // 缩放到统一尺寸
        $size = 64;
        $tmp1 = imagecreatetruecolor($size, $size);
        $tmp2 = imagecreatetruecolor($size, $size);
        imagecopyresampled($tmp1, $img1, 0, 0, 0, 0, $size, $size, imagesx($img1), imagesy($img1));
        imagecopyresampled($tmp2, $img2, 0, 0, 0, 0, $size, $size, imagesx($img2), imagesy($img2));
        
        // 计算灰度直方图
        $hist1 = $this->calculateGrayHistogram($tmp1, $size);
        $hist2 = $this->calculateGrayHistogram($tmp2, $size);
        
        imagedestroy($img1);
        imagedestroy($img2);
        imagedestroy($tmp1);
        imagedestroy($tmp2);
        
        // 计算直方图交集
        $intersection = 0;
        $total = 0;
        for ($i = 0; $i < 256; $i++) {
            $intersection += min($hist1[$i], $hist2[$i]);
            $total += max($hist1[$i], $hist2[$i]);
        }
        
        $similarity = $total > 0 ? ($intersection / $total) * 100 : 0;
        
        return $similarity;
    }

    /**
     * 计算灰度直方图
     */
    protected function calculateGrayHistogram($image, int $size): array
    {
        $hist = array_fill(0, 256, 0);
        
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $gray = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);
                $hist[$gray]++;
            }
        }
        
        return $hist;
    }

    /**
     * 计算两张图片的差异度
     */
    protected function calculateImageDiff(string $image1Base64, string $image2Base64): float
    {
        $img1 = @imagecreatefromstring(base64_decode($image1Base64));
        $img2 = @imagecreatefromstring(base64_decode($image2Base64));
        
        if (!$img1 || !$img2) {
            return 100;
        }
        
        $size = 32;
        $tmp1 = imagecreatetruecolor($size, $size);
        $tmp2 = imagecreatetruecolor($size, $size);
        imagecopyresampled($tmp1, $img1, 0, 0, 0, 0, $size, $size, imagesx($img1), imagesy($img1));
        imagecopyresampled($tmp2, $img2, 0, 0, 0, 0, $size, $size, imagesx($img2), imagesy($img2));
        
        $totalDiff = 0;
        $pixelCount = $size * $size;
        
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $rgb1 = imagecolorat($tmp1, $x, $y);
                $rgb2 = imagecolorat($tmp2, $x, $y);
                
                $r1 = ($rgb1 >> 16) & 0xFF;
                $g1 = ($rgb1 >> 8) & 0xFF;
                $b1 = $rgb1 & 0xFF;
                
                $r2 = ($rgb2 >> 16) & 0xFF;
                $g2 = ($rgb2 >> 8) & 0xFF;
                $b2 = $rgb2 & 0xFF;
                
                $diff = abs($r1 - $r2) + abs($g1 - $g2) + abs($b1 - $b2);
                $totalDiff += $diff / 3;
            }
        }
        
        imagedestroy($img1);
        imagedestroy($img2);
        imagedestroy($tmp1);
        imagedestroy($tmp2);
        
        return $totalDiff / $pixelCount;
    }
}
