<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 人脸通道接口
// +----------------------------------------------------------------------

namespace app\service\Channel;

interface ChannelInterface
{
    /**
     * 获取通道代码
     * @return string
     */
    public function getCode(): string;

    /**
     * 获取通道名称
     * @return string
     */
    public function getName(): string;

    /**
     * 检测通道是否可用
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * 人脸检测
     * @param string $imageBase64 图片base64
     * @return array {
     *   @type bool $success 是否成功
     *   @type int $face_count 人脸数量
     *   @type array $faces 人脸信息数组
     *   @type string $error 错误信息
     * }
     */
    public function faceDetect(string $imageBase64): array;

    /**
     * 人脸比对 1:1
     * @param string $image1 图片1 base64
     * @param string $image2 图片2 base64
     * @return array {
     *   @type bool $success 是否成功
     *   @type float $score 相似度分数(0-100)
     *   @type bool $is_match 是否匹配
     *   @type string $error 错误信息
     * }
     */
    public function faceCompare(string $image1, string $image2): array;

    /**
     * 活体检测
     * @param array $images 图片帧数组(base64)
     * @param array $actions 动作序列
     * @return array {
     *   @type bool $success 是否成功
     *   @type bool $passed 是否通过
     *   @type float $score 活体分数
     *   @type string $error 错误信息
     * }
     */
    public function livenessDetect(array $images, array $actions = []): array;

    /**
     * 获取核身Token（用于H5跳转式核身）
     * @param array $params 参数
     * @return array {
     *   @type bool $success 是否成功
     *   @type string $token 核身Token
     *   @type string $url 跳转URL
     *   @type string $error 错误信息
     * }
     */
    public function getVerifyToken(array $params = []): array;

    /**
     * 查询核身结果
     * @param string $token 核身Token
     * @return array {
     *   @type bool $success 是否成功
     *   @type string $status 状态
     *   @type float $score 分数
     *   @type array $detail 详情
     *   @type string $error 错误信息
     * }
     */
    public function getVerifyResult(string $token): array;

    /**
     * 测试通道连接
     * @return array {
     *   @type bool $success 是否成功
     *   @type string $message 消息
     *   @type int $latency 延迟(ms)
     * }
     */
    public function testConnection(): array;
}
