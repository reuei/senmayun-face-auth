<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 首页控制器
// +----------------------------------------------------------------------

namespace app\controller;

use think\facade\View;

class HomeController
{
    /**
     * 首页
     */
    public function index()
    {
        // 检查是否已安装
        if (!sm_is_installed()) {
            return redirect('/install');
        }
        
        // 返回前端SPA
        return $this->renderSpa();
    }

    /**
     * 认证页
     */
    public function verify()
    {
        if (!sm_is_installed()) {
            return redirect('/install');
        }
        
        return $this->renderSpa();
    }

    /**
     * 结果页
     */
    public function result()
    {
        if (!sm_is_installed()) {
            return redirect('/install');
        }
        
        return $this->renderSpa();
    }

    /**
     * 后台入口
     */
    public function admin()
    {
        if (!sm_is_installed()) {
            return redirect('/install');
        }
        
        return $this->renderSpa();
    }

    /**
     * 安装页
     */
    public function install()
    {
        if (sm_is_installed()) {
            return redirect('/');
        }
        
        return $this->renderSpa();
    }

    /**
     * 定价页
     */
    public function pricing()
    {
        return $this->renderSpa();
    }

    /**
     * 关于页
     */
    public function about()
    {
        return $this->renderSpa();
    }

    /**
     * 文档页
     */
    public function docs()
    {
        return $this->renderSpa();
    }

    /**
     * 渲染前端SPA
     */
    protected function renderSpa()
    {
        $indexFile = public_path() . '/dist/index.html';
        
        if (is_file($indexFile)) {
            return file_get_contents($indexFile);
        }
        
        // 如果前端未构建，返回提示
        return response()->code(200)->contentType('text/html')->content(
            '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>森码云实人认证系统</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            background: white;
            padding: 48px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 480px;
        }
        h1 { color: #1f2937; margin-bottom: 16px; }
        p { color: #6b7280; line-height: 1.6; }
        code {
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>森码云实人认证系统</h1>
        <p>前端资源未构建，请执行以下命令构建前端：</p>
        <p><code>cd resources && npm install && npm run build</code></p>
    </div>
</body>
</html>'
        );
    }
}
