<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 管理员认证中间件
// +----------------------------------------------------------------------

namespace app\middleware;

use think\Request;
use think\Response;

class AdminAuth
{
    public function handle(Request $request, \Closure $next)
    {
        $adminId = session('admin_id');
        $adminToken = session('admin_token');
        
        if (!$adminId || !$adminToken) {
            // 检查Header中的Token
            $headerToken = $request->header('X-Admin-Token', '');
            
            if (!$headerToken) {
                return json([
                    'code' => 401,
                    'message' => '未登录或登录已过期',
                    'data' => null,
                ]);
            }
            
            // 这里可以添加Header Token的验证逻辑
            // 简化版：直接从session获取
            if (!$adminId) {
                return json([
                    'code' => 401,
                    'message' => '未登录或登录已过期',
                    'data' => null,
                ]);
            }
        }
        
        return $next($request);
    }
}
