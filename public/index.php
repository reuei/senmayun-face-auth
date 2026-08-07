<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 入口文件
// +----------------------------------------------------------------------

// 检测是否绑定到public目录
if (!is_file(__DIR__ . '/../composer.json')) {
    // 未正确绑定public目录，显示错误页面
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>部署错误 - 森码云实人认证系统</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 48px;
            max-width: 560px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .icon {
            width: 64px;
            height: 64px;
            background: #fef3c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        .icon svg { width: 32px; height: 32px; color: #f59e0b; }
        h1 { font-size: 24px; color: #1f2937; margin-bottom: 12px; }
        p { color: #6b7280; line-height: 1.6; margin-bottom: 24px; }
        .steps {
            background: #f9fafb;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .steps h3 { font-size: 16px; color: #374151; margin-bottom: 16px; }
        .steps ol { padding-left: 20px; color: #4b5563; line-height: 2; }
        code {
            background: #e5e7eb;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 14px;
            font-family: "SF Mono", Monaco, Consolas, monospace;
        }
        .footer {
            text-align: center;
            color: #9ca3af;
            font-size: 14px;
            margin-top: 32px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1>部署配置错误</h1>
        <p>检测到站点根目录未正确绑定到 <code>public</code> 文件夹。请按照以下步骤修正：</p>
        <div class="steps">
            <h3>虚拟主机 / cPanel 操作步骤：</h3>
            <ol>
                <li>登录您的主机控制面板</li>
                <li>找到「域名」或「站点根目录」设置</li>
                <li>将域名的根目录指向 <code>public/</code> 文件夹</li>
                <li>保存设置并等待生效</li>
            </ol>
        </div>
        <div class="steps">
            <h3>Nginx 配置示例：</h3>
            <ol>
                <li>编辑站点配置文件</li>
                <li>设置 <code>root /path/to/senmayun-face-auth/public;</code></li>
                <li>重载 Nginx 配置</li>
            </ol>
        </div>
        <div class="footer">
            森码云实人认证系统 v1.0.0
        </div>
    </div>
</body>
</html>';
    exit;
}

// 检测是否已安装
if (!is_file(__DIR__ . '/install.lock') && !isset($_GET['_install'])) {
    // 未安装，跳转到安装向导
    header('Location: /install');
    exit;
}

// 加载框架引导文件
require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$app = new think\App();
$app->initialize();

$response = $app->http->run();

$response->send();

$app->http->end($response);
