<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 路由配置
// +----------------------------------------------------------------------

use think\facade\Route;

// API路由组
Route::group('api/v1', function () {
    // 认证相关
    Route::post('auth/init', 'ApiController/initAuth');
    Route::post('auth/verify-token', 'ApiController/verifyToken');
    Route::post('auth/result', 'ApiController/getResult');
    Route::post('auth/callback', 'ApiController/callback');
    
    // 人脸相关
    Route::post('face/detect', 'ApiController/faceDetect');
    Route::post('face/compare', 'ApiController/faceCompare');
    Route::post('face/liveness', 'ApiController/faceLiveness');
})->middleware(\app\middleware\ApiAuth::class);

// 魔方财务回调
Route::post('mofang/callback', 'MofangController/callback')
    ->middleware(\app\middleware\MofangAuth::class);

// 安装路由
Route::group('install', function () {
    Route::get('/', 'InstallController/index');
    Route::post('check-env', 'InstallController/checkEnv');
    Route::post('check-db', 'InstallController/checkDb');
    Route::post('install', 'InstallController/install');
})->middleware(\app\middleware\InstallCheck::class);

// 后台路由
Route::group('admin', function () {
    Route::post('login', 'AdminController/login');
    Route::get('logout', 'AdminController/logout');
})->middleware(\app\middleware\InstallCheck::class);

Route::group('admin', function () {
    // 仪表盘
    Route::get('dashboard', 'AdminController/dashboard');
    
    // 认证管理
    Route::get('verifications', 'AdminController/verificationList');
    Route::get('verifications/:id', 'AdminController/verificationDetail');
    
    // API通道管理
    Route::get('channels', 'AdminController/channelList');
    Route::post('channels', 'AdminController/channelCreate');
    Route::put('channels/:id', 'AdminController/channelUpdate');
    Route::delete('channels/:id', 'AdminController/channelDelete');
    Route::post('channels/:id/test', 'AdminController/channelTest');
    Route::post('channels/sort', 'AdminController/channelSort');
    
    // 魔方财务配置
    Route::get('mofang/config', 'AdminController/getMofangConfig');
    Route::post('mofang/config', 'AdminController/saveMofangConfig');
    
    // 系统设置
    Route::get('settings', 'AdminController/getSettings');
    Route::post('settings', 'AdminController/saveSettings');
    
    // 管理员
    Route::get('profile', 'AdminController/profile');
    Route::post('profile', 'AdminController/updateProfile');
})->middleware([\app\middleware\InstallCheck::class, \app\middleware\AdminAuth::class]);

// 前端页面路由 - 全部指向SPA入口
Route::get('/', 'HomeController/index');
Route::get('verify', 'HomeController/verify');
Route::get('result', 'HomeController/result');
Route::get('admin$', 'HomeController/admin');
Route::get('install$', 'HomeController/install');
Route::get('pricing', 'HomeController/pricing');
Route::get('about', 'HomeController/about');
Route::get('docs', 'HomeController/docs');
