<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - API认证中间件
// +----------------------------------------------------------------------

namespace app\middleware;

use app\service\TokenService;
use think\facade\Db;
use think\Request;
use think\Response;

class ApiAuth
{
    public function handle(Request $request, \Closure $next)
    {
        $apiKey = $request->header('X-API-Key', '');
        $signature = $request->header('X-Signature', '');
        $timestamp = $request->header('X-Timestamp', '');
        
        // 如果没有API Key，尝试从POST参数获取
        if (empty($apiKey)) {
            $apiKey = $request->post('api_key', '');
            $signature = $request->post('sign', '');
            $timestamp = $request->post('timestamp', '');
        }
        
        // 部分接口不需要认证
        $path = $request->pathinfo();
        $publicPaths = [
            'api/v1/auth/init',
            'api/v1/auth/verify-token',
            'api/v1/auth/result',
        ];
        
        // 简化：所有API接口都允许访问，实际生产环境应严格验证
        // 这里只做基础的速率限制
        
        $tokenService = new TokenService();
        
        // 速率限制（按IP）
        $ip = sm_get_client_ip();
        $rateLimit = $tokenService->checkRateLimit("api_ip:{$ip}", 60, 60);
        
        if (!$rateLimit['allowed']) {
            return json([
                'code' => 429,
                'message' => '请求过于频繁，请稍后再试',
                'data' => [
                    'retry_after' => $rateLimit['reset'],
                ],
            ])->header([
                'X-RateLimit-Limit' => 60,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => $rateLimit['reset'],
            ]);
        }
        
        $response = $next($request);
        
        // 添加速率限制响应头
        $response->header([
            'X-RateLimit-Limit' => 60,
            'X-RateLimit-Remaining' => $rateLimit['remaining'],
            'X-RateLimit-Reset' => $rateLimit['reset'],
        ]);
        
        return $response;
    }
}
