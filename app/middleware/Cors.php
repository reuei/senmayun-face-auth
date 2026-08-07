<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 跨域中间件
// +----------------------------------------------------------------------

namespace app\middleware;

use think\Request;
use think\Response;

class Cors
{
    public function handle(Request $request, \Closure $next)
    {
        // 处理OPTIONS预检请求
        if ($request->isOptions()) {
            return response()
                ->code(204)
                ->header([
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-API-Key, X-Signature, X-Timestamp, X-Admin-Token',
                    'Access-Control-Max-Age' => '86400',
                ]);
        }
        
        $response = $next($request);
        
        // 添加CORS头
        $response->header([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-API-Key, X-Signature, X-Timestamp, X-Admin-Token',
        ]);
        
        return $response;
    }
}
