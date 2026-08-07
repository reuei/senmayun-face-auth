<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 安装检测中间件
// +----------------------------------------------------------------------

namespace app\middleware;

use think\Request;
use think\Response;

class InstallCheck
{
    public function handle(Request $request, \Closure $next)
    {
        // 安装相关路由不检测
        $path = $request->pathinfo();
        if (str_starts_with($path, 'install') || str_starts_with($path, 'api/install')) {
            return $next($request);
        }
        
        // 检测是否已安装
        if (!sm_is_installed()) {
            // API请求返回JSON
            if (str_starts_with($path, 'api/')) {
                return json([
                    'code' => 403,
                    'message' => '系统未安装，请先运行安装向导',
                    'data' => [
                        'install_url' => '/install',
                    ],
                ]);
            }
            
            // 页面请求重定向到安装页
            return redirect('/install');
        }
        
        return $next($request);
    }
}
