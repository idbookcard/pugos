<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThirdPartyApiSetting;
use App\Services\SEOeStoreApiService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ApiSettingRequest;

class ApiSettingController extends Controller
{
    protected $apiService;
    
    public function __construct(SEOeStoreApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * 显示API设置页面
     */
    public function index()
    {
        $apiSettings = ThirdPartyApiSetting::paginate(10); // 每页 10 条
        
        return view('master.api_settings.index', compact('apiSettings'));
    }
    
    /**
     * 显示创建API设置页面
     */
    public function create()
    {
        return view('master.api_settings.create');
    }
    
    /**
     * 处理API设置创建
     */
    public function store(ApiSettingRequest $request)
    {
        $settings = null;
        if ($request->has('settings')) {
            $settings = json_decode($request->input('settings'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withInput()
                    ->with('error', 'Settings不是有效的JSON格式');
            }
        }
        
        ThirdPartyApiSetting::create([
            'name' => $request->input('name'),
            'api_key' => $request->input('api_key'),
            'api_secret' => $request->input('api_secret'),
            'api_url' => $request->input('api_url'),
            'settings' => $settings ? json_encode($settings) : null
        ]);
        
        return redirect()->route('master.api-settings.index')
            ->with('success', 'API设置创建成功');
    }
    
    /**
     * 显示编辑API设置页面
     */
    public function edit($id)
    {
        $apiSetting = ThirdPartyApiSetting::findOrFail($id);
        
        return view('master.api_settings.edit', compact('apiSetting'));
    }
    
    /**
     * 处理API设置更新
     */
    public function update(ApiSettingRequest $request, $id)
    {
        $apiSetting = ThirdPartyApiSetting::findOrFail($id);
        
        $settings = null;
        if ($request->has('settings')) {
            $settings = json_decode($request->input('settings'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withInput()
                    ->with('error', 'Settings不是有效的JSON格式');
            }
        }
        
        $apiSetting->update([
            'name' => $request->input('name'),
            'api_key' => $request->input('api_key'),
            'api_secret' => $request->input('api_secret'),
            'api_url' => $request->input('api_url'),
            'email' => $request->input('email'),
            'settings' => $settings ? json_encode($settings) : null
        ]);
        
        return redirect()->route('master.api-settings.index')
            ->with('success', 'API设置更新成功');
    }
    
    /**
     * 删除API设置
     */
    public function destroy($id)
    {
        $apiSetting = ThirdPartyApiSetting::findOrFail($id);
        $apiSetting->delete();
        
        return redirect()->route('master.api-settings.index')
            ->with('success', 'API设置已删除');
    }
    
    /**
 * 测试API连接（支持AJAX）
 */
public function testConnection($id)
{
    $apiSetting = ThirdPartyApiSetting::findOrFail($id);
    
    try {
        // 临时覆盖API服务的设置
        $originalApiKey = config('services.seoestore.api_key');
        $originalApiUrl = config('services.seoestore.api_url');
        $originalEmail = config('services.seoestore.email');
        
        config(['services.seoestore.api_key' => $apiSetting->api_key]);
        config(['services.seoestore.api_url' => $apiSetting->api_url]);
        
        // 从额外设置中获取email
        $settings = $apiSetting->settings ? json_decode($apiSetting->settings, true) : [];
        if (!empty($settings['email'])) {
            config(['services.seoestore.email' => $settings['email']]);
        }
        
        // 创建临时服务实例
        $apiService = new SEOeStoreApiService();
        
        // 使用服务的test方法
        $result = $apiService->test();
        
        // 恢复原始设置
        config(['services.seoestore.api_key' => $originalApiKey]);
        config(['services.seoestore.api_url' => $originalApiUrl]);
        config(['services.seoestore.email' => $originalEmail]);
        
        // 检查是否是AJAX请求
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($result);
        }
        
        // 非AJAX请求的原始重定向逻辑
        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }
        
        return back()->with('success', $result['message']);
    } catch (\Exception $e) {
        // 捕获异常并返回错误信息
        $errorMessage = 'API连接失败: ' . $e->getMessage();
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
        
        return back()->with('error', $errorMessage);
    }
}
    /**
     * 同步API产品
     */
    public function syncProducts()
    {
        try {
            $result = $this->apiService->syncProducts();
            
            return redirect()->route('master.packages.index')
                ->with('success', "同步成功！创建了 {$result['created']} 个新产品，更新了 {$result['updated']} 个产品。");
        } catch (\Exception $e) {
            return back()->with('error', '同步API产品失败: ' . $e->getMessage());
        }
    }
}