<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 魔方财务回调控制器
// +----------------------------------------------------------------------

namespace app\controller;

use app\service\MofangFinanceService;
use app\service\FaceService;
use think\facade\Db;
use think\facade\Log;
use think\Request;

class MofangController
{
    protected MofangFinanceService $mofangService;
    protected FaceService $faceService;

    public function __construct()
    {
        $this->mofangService = new MofangFinanceService();
        $this->faceService = new FaceService();
    }

    /**
     * 魔方财务发起认证请求
     */
    public function createOrder(Request $request)
    {
        if (!$this->mofangService->isEnabled()) {
            return json([
                'code' => 403,
                'message' => '魔方财务对接未启用',
                'data' => null,
            ]);
        }
        
        $params = $request->post();
        
        $result = $this->mofangService->createVerification($params);
        
        if (!$result['success']) {
            return json([
                'code' => 400,
                'message' => $result['message'],
                'data' => null,
            ]);
        }
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * 魔方财务查询认证状态
     */
    public function queryStatus(Request $request)
    {
        if (!$this->mofangService->isEnabled()) {
            return json([
                'code' => 403,
                'message' => '魔方财务对接未启用',
                'data' => null,
            ]);
        }
        
        $token = $request->post('token', '');
        $sign = $request->post('sign', '');
        
        if (empty($token)) {
            return json([
                'code' => 400,
                'message' => 'token不能为空',
                'data' => null,
            ]);
        }
        
        // 验证签名
        $config = $this->mofangService->getConfig();
        $params = $request->post();
        unset($params['sign']);
        
        if (!$this->mofangService->verifySign($params, $sign, $config['api_key'])) {
            return json([
                'code' => 401,
                'message' => '签名验证失败',
                'data' => null,
            ]);
        }
        
        $result = $this->mofangService->queryStatus($token);
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * 认证完成回调魔方财务
     * 这个接口由本系统主动调用，不是魔方财务调用的
     */
    public function callback(Request $request)
    {
        $token = $request->post('token', '');
        $status = $request->post('status', '');
        
        if (empty($token) || empty($status)) {
            return json([
                'code' => 400,
                'message' => '参数不完整',
                'data' => null,
            ]);
        }
        
        $result = $this->faceService->getVerificationResult($token);
        
        if (!$result['success']) {
            return json([
                'code' => 404,
                'message' => $result['message'],
                'data' => null,
            ]);
        }
        
        $notifyResult = $this->mofangService->notifyMofang($token, $result);
        
        return json([
            'code' => 200,
            'message' => $notifyResult ? '回调成功' : '回调失败',
            'data' => [
                'notified' => $notifyResult,
            ],
        ]);
    }

    /**
     * 魔方财务插件信息
     */
    public function pluginInfo()
    {
        $info = $this->mofangService->getPluginInfo();
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => $info,
        ]);
    }

    /**
     * 魔方财务IPN回调（接收魔方财务的支付通知）
     */
    public function ipn(Request $request)
    {
        if (!$this->mofangService->isEnabled()) {
            return 'fail';
        }
        
        $params = $request->post();
        $sign = $params['sign'] ?? '';
        
        // 验证签名
        $config = $this->mofangService->getConfig();
        unset($params['sign']);
        
        if (!$this->mofangService->verifySign($params, $sign, $config['api_key'])) {
            Log::warning('魔方财务IPN签名验证失败');
            return 'fail';
        }
        
        $orderId = $params['order_id'] ?? '';
        $status = $params['status'] ?? '';
        
        if ($status === 'paid') {
            // 支付成功，创建认证记录
            $result = $this->mofangService->createVerification($params);
            
            if ($result['success']) {
                Log::info("魔方财务支付成功，创建认证: {$orderId}");
                return 'success';
            }
        }
        
        return 'success';
    }
}
