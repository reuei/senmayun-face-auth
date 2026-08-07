<?php
// +----------------------------------------------------------------------
// | 森码云实人认证系统 - 后台管理控制器
// +----------------------------------------------------------------------

namespace app\controller;

use app\service\FaceService;
use app\service\MofangFinanceService;
use think\facade\Db;
use think\facade\Log;
use think\Request;

class AdminController
{
    /**
     * 管理员登录
     */
    public function login(Request $request)
    {
        $username = $request->post('username', '');
        $password = $request->post('password', '');
        
        if (empty($username) || empty($password)) {
            return json([
                'code' => 400,
                'message' => '用户名和密码不能为空',
                'data' => null,
            ]);
        }
        
        $admin = Db::name('admin')->where('username', $username)->find();
        
        if (!$admin) {
            return json([
                'code' => 401,
                'message' => '用户名或密码错误',
                'data' => null,
            ]);
        }
        
        if ($admin['status'] != 1) {
            return json([
                'code' => 403,
                'message' => '账号已被禁用',
                'data' => null,
            ]);
        }
        
        // 验证密码
        if (!password_verify($password, $admin['password'])) {
            // 记录失败日志
            Db::name('operation_logs')->insert([
                'admin_id' => $admin['id'],
                'action' => 'login_failed',
                'detail' => json_encode(['username' => $username, 'ip' => sm_get_client_ip()]),
                'ip' => sm_get_client_ip(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            
            return json([
                'code' => 401,
                'message' => '用户名或密码错误',
                'data' => null,
            ]);
        }
        
        // 生成Token
        $token = bin2hex(random_bytes(32));
        $expireAt = date('Y-m-d H:i:s', time() + 7200);
        
        // 更新登录信息
        Db::name('admin')->where('id', $admin['id'])->update([
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => sm_get_client_ip(),
            'login_count' => Db::raw('login_count + 1'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        // 保存Token到Session或缓存
        session('admin_token', $token);
        session('admin_id', $admin['id']);
        session('admin_username', $admin['username']);
        
        // 记录操作日志
        Db::name('operation_logs')->insert([
            'admin_id' => $admin['id'],
            'action' => 'login',
            'detail' => json_encode(['ip' => sm_get_client_ip()]),
            'ip' => sm_get_client_ip(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        
        return json([
            'code' => 200,
            'message' => '登录成功',
            'data' => [
                'token' => $token,
                'admin' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'nickname' => $admin['nickname'],
                    'role' => $admin['role'],
                ],
                'expire_at' => $expireAt,
            ],
        ]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        session('admin_token', null);
        session('admin_id', null);
        session('admin_username', null);
        
        return json([
            'code' => 200,
            'message' => '退出成功',
            'data' => null,
        ]);
    }

    /**
     * 获取当前管理员信息
     */
    public function getCurrentAdmin()
    {
        $adminId = session('admin_id');
        
        if (!$adminId) {
            return json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ]);
        }
        
        $admin = Db::name('admin')->where('id', $adminId)->find();
        
        if (!$admin) {
            return json([
                'code' => 401,
                'message' => '管理员不存在',
                'data' => null,
            ]);
        }
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'nickname' => $admin['nickname'],
                'email' => $admin['email'],
                'role' => $admin['role'],
                'avatar' => $admin['avatar'],
                'last_login_at' => $admin['last_login_at'],
            ],
        ]);
    }

    /**
     * 仪表盘数据
     */
    public function dashboard()
    {
        // 今日统计
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        
        $todayTotal = Db::name('verifications')
            ->whereBetweenTime('created_at', $todayStart, $todayEnd)
            ->count();
        
        $todayPassed = Db::name('verifications')
            ->whereBetweenTime('created_at', $todayStart, $todayEnd)
            ->where('status', 1)
            ->count();
        
        $todayFailed = Db::name('verifications')
            ->whereBetweenTime('created_at', $todayStart, $todayEnd)
            ->where('status', 2)
            ->count();
        
        $passRate = $todayTotal > 0 ? round($todayPassed / $todayTotal * 100, 2) : 0;
        
        // 总统计
        $totalVerifications = Db::name('verifications')->count();
        $totalPassed = Db::name('verifications')->where('status', 1)->count();
        $totalUsers = Db::name('users')->count();
        
        // 近7天趋势
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayStart = $date . ' 00:00:00';
            $dayEnd = $date . ' 23:59:59';
            
            $dayTotal = Db::name('verifications')
                ->whereBetweenTime('created_at', $dayStart, $dayEnd)
                ->count();
            
            $dayPassed = Db::name('verifications')
                ->whereBetweenTime('created_at', $dayStart, $dayEnd)
                ->where('status', 1)
                ->count();
            
            $trend[] = [
                'date' => $date,
                'total' => $dayTotal,
                'passed' => $dayPassed,
                'failed' => $dayTotal - $dayPassed,
            ];
        }
        
        // 通道使用统计
        $channelStats = Db::name('verifications')
            ->field('channel, COUNT(*) as count')
            ->where('status', '<>', 0)
            ->group('channel')
            ->select()
            ->toArray();
        
        // 最近认证记录
        $recentVerifications = Db::name('verifications')
            ->order('created_at', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'today' => [
                    'total' => $todayTotal,
                    'passed' => $todayPassed,
                    'failed' => $todayFailed,
                    'pass_rate' => $passRate,
                ],
                'total' => [
                    'verifications' => $totalVerifications,
                    'passed' => $totalPassed,
                    'users' => $totalUsers,
                ],
                'trend' => $trend,
                'channel_stats' => $channelStats,
                'recent_verifications' => $recentVerifications,
            ],
        ]);
    }

    /**
     * 认证记录列表
     */
    public function verifications(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $status = $request->get('status', '');
        $channel = $request->get('channel', '');
        $keyword = $request->get('keyword', '');
        $startDate = $request->get('start_date', '');
        $endDate = $request->get('end_date', '');
        
        $query = Db::name('verifications');
        
        if ($status !== '') {
            $query->where('status', $status);
        }
        
        if ($channel) {
            $query->where('channel', $channel);
        }
        
        if ($keyword) {
            $query->where('token', 'like', "%{$keyword}%");
        }
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }
        
        $total = $query->count();
        $list = $query
            ->order('created_at', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'total' => $total,
                'page' => $page,
                'page_size' => $pageSize,
                'list' => $list,
            ],
        ]);
    }

    /**
     * 认证记录详情
     */
    public function verificationDetail(Request $request)
    {
        $id = $request->get('id', 0);
        
        $verification = Db::name('verifications')->where('id', $id)->find();
        
        if (!$verification) {
            return json([
                'code' => 404,
                'message' => '记录不存在',
                'data' => null,
            ]);
        }
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => $verification,
        ]);
    }

    /**
     * API通道列表
     */
    public function channels()
    {
        $channels = Db::name('api_channels')
            ->order('priority', 'asc')
            ->select()
            ->toArray();
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => $channels,
        ]);
    }

    /**
     * 保存API通道配置
     */
    public function saveChannel(Request $request)
    {
        $id = $request->post('id', 0);
        $name = $request->post('name', '');
        $code = $request->post('code', '');
        $config = $request->post('config', []);
        $priority = $request->post('priority', 1);
        $enabled = $request->post('enabled', 0);
        
        if (empty($name) || empty($code)) {
            return json([
                'code' => 400,
                'message' => '名称和代码不能为空',
                'data' => null,
            ]);
        }
        
        $data = [
            'name' => $name,
            'config' => json_encode($config),
            'priority' => $priority,
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($id > 0) {
            Db::name('api_channels')->where('id', $id)->update($data);
        } else {
            $data['code'] = $code;
            $data['created_at'] = date('Y-m-d H:i:s');
            Db::name('api_channels')->insert($data);
        }
        
        // 记录操作日志
        $this->logOperation('save_channel', ['id' => $id, 'name' => $name]);
        
        return json([
            'code' => 200,
            'message' => '保存成功',
            'data' => null,
        ]);
    }

    /**
     * 测试通道连接
     */
    public function testChannel(Request $request)
    {
        $id = $request->post('id', 0);
        
        $channel = Db::name('api_channels')->where('id', $id)->find();
        
        if (!$channel) {
            return json([
                'code' => 404,
                'message' => '通道不存在',
                'data' => null,
            ]);
        }
        
        $faceService = new FaceService();
        $config = json_decode($channel['config'], true) ?: [];
        $instance = $faceService->getChannelInstance($channel['code'], $config);
        
        if (!$instance) {
            return json([
                'code' => 400,
                'message' => '通道实例创建失败',
                'data' => null,
            ]);
        }
        
        $result = $instance->testConnection();
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * 切换通道状态
     */
    public function toggleChannel(Request $request)
    {
        $id = $request->post('id', 0);
        $enabled = $request->post('enabled', 0);
        
        Db::name('api_channels')->where('id', $id)->update([
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        $this->logOperation('toggle_channel', ['id' => $id, 'enabled' => $enabled]);
        
        return json([
            'code' => 200,
            'message' => '操作成功',
            'data' => null,
        ]);
    }

    /**
     * 获取魔方财务配置
     */
    public function mofangConfig()
    {
        $mofangService = new MofangFinanceService();
        $config = $mofangService->getConfig();
        
        // 隐藏敏感信息
        unset($config['api_key']);
        unset($config['callback_secret']);
        
        $config['has_api_key'] = !empty(sm_config('mofang.api_key', ''));
        $config['has_callback_secret'] = !empty(sm_config('mofang.callback_secret', ''));
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => $config,
        ]);
    }

    /**
     * 保存魔方财务配置
     */
    public function saveMofangConfig(Request $request)
    {
        $mofangService = new MofangFinanceService();
        
        $config = [
            'enabled' => $request->post('enabled', false),
            'api_url' => $request->post('api_url', ''),
            'api_username' => $request->post('api_username', ''),
            'auto_verify' => $request->post('auto_verify', true),
        ];
        
        // 只有填写了新密钥才更新
        $newApiKey = $request->post('api_key', '');
        if ($newApiKey) {
            $config['api_key'] = $newApiKey;
        }
        
        $newSecret = $request->post('callback_secret', '');
        if ($newSecret) {
            $config['callback_secret'] = $newSecret;
        }
        
        $result = $mofangService->saveConfig($config);
        
        $this->logOperation('save_mofang_config', []);
        
        return json([
            'code' => $result ? 200 : 500,
            'message' => $result ? '保存成功' : '保存失败',
            'data' => null,
        ]);
    }

    /**
     * 魔方财务订单列表
     */
    public function mofangOrders(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $status = $request->get('status', '');
        
        $query = Db::name('mofang_orders');
        
        if ($status !== '') {
            $query->where('status', $status);
        }
        
        $total = $query->count();
        $list = $query
            ->order('created_at', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'total' => $total,
                'page' => $page,
                'page_size' => $pageSize,
                'list' => $list,
            ],
        ]);
    }

    /**
     * 获取系统设置
     */
    public function settings()
    {
        $settings = Db::name('system_config')
            ->order('group', 'asc')
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        
        // 按分组整理
        $grouped = [];
        foreach ($settings as $setting) {
            $group = $setting['group'];
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $setting;
        }
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => $grouped,
        ]);
    }

    /**
     * 保存系统设置
     */
    public function saveSettings(Request $request)
    {
        $settings = $request->post('settings', []);
        
        if (!is_array($settings)) {
            return json([
                'code' => 400,
                'message' => '参数格式错误',
                'data' => null,
            ]);
        }
        
        foreach ($settings as $setting) {
            if (isset($setting['group']) && isset($setting['key'])) {
                Db::name('system_config')
                    ->where('group', $setting['group'])
                    ->where('key', $setting['key'])
                    ->update([
                        'value' => $setting['value'] ?? '',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }
        }
        
        $this->logOperation('save_settings', []);
        
        return json([
            'code' => 200,
            'message' => '保存成功',
            'data' => null,
        ]);
    }

    /**
     * 修改密码
     */
    public function changePassword(Request $request)
    {
        $adminId = session('admin_id');
        $oldPassword = $request->post('old_password', '');
        $newPassword = $request->post('new_password', '');
        
        if (empty($oldPassword) || empty($newPassword)) {
            return json([
                'code' => 400,
                'message' => '旧密码和新密码不能为空',
                'data' => null,
            ]);
        }
        
        if (strlen($newPassword) < 8) {
            return json([
                'code' => 400,
                'message' => '新密码长度不能少于8位',
                'data' => null,
            ]);
        }
        
        $admin = Db::name('admin')->where('id', $adminId)->find();
        
        if (!$admin || !password_verify($oldPassword, $admin['password'])) {
            return json([
                'code' => 401,
                'message' => '旧密码错误',
                'data' => null,
            ]);
        }
        
        Db::name('admin')->where('id', $adminId)->update([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        $this->logOperation('change_password', []);
        
        return json([
            'code' => 200,
            'message' => '密码修改成功',
            'data' => null,
        ]);
    }

    /**
     * 操作日志列表
     */
    public function operationLogs(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        
        $query = Db::name('operation_logs');
        
        $total = $query->count();
        $list = $query
            ->order('created_at', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();
        
        return json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'total' => $total,
                'page' => $page,
                'page_size' => $pageSize,
                'list' => $list,
            ],
        ]);
    }

    /**
     * 记录操作日志
     */
    protected function logOperation(string $action, array $detail = []): void
    {
        try {
            Db::name('operation_logs')->insert([
                'admin_id' => session('admin_id', 0),
                'action' => $action,
                'detail' => json_encode($detail),
                'ip' => sm_get_client_ip(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            Log::error('记录操作日志失败: ' . $e->getMessage());
        }
    }
}
