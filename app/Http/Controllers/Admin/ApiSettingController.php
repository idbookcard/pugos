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
        $apiSettings = ThirdPartyApiSetting::all();
        
        return view('admin.api_settings.index', compact('apiSettings'));
    }
    
    /**
     * 显示创建API设置页面
     */
    public function create()
    {
        return view('admin.api_settings.create');
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
        
        return redirect()->route('admin.api-settings.index')
            ->with('success', 'API设置创建成功');
    }
    
    /**
     * 显示编辑API设置页面
     */
    public function edit($id)
    {
        $apiSetting = ThirdPartyApiSetting::findOrFail($id);
        
        return view('admin.api_settings.edit', compact('apiSetting'));
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
            'settings' => $settings ? json_encode($settings) : null
        ]);
        
        return redirect()->route('admin.api-settings.index')
            ->with('success', 'API设置更新成功');
    }
    
    /**
     * 删除API设置
     */
    public function destroy($id)
    {
        $apiSetting = ThirdPartyApiSetting::findOrFail($id);
        $apiSetting->delete();
        
        return redirect()->route('admin.api-settings.index')
            ->with('success', 'API设置已删除');
    }
    
    /**
     * 测试API连接
     */
    public function testConnection($id)
    {
        $apiSetting = ThirdPartyApiSetting::findOrFail($id);
        
        if ($apiSetting->name !== 'seoestore') {
            return back()->with('error', '目前只支持测试SEOeStore API');
        }
        
        try {
            // 临时覆盖API服务的设置
            $originalApiKey = config('services.seoestore.api_key');
            $originalApiUrl = config('services.seoestore.api_url');
            
            config(['services.seoestore.api_key' => $apiSetting->api_key]);
            config(['services.seoestore.api_url' => $apiSetting->api_url]);
            
            // 尝试获取服务列表
            $services = $this->apiService->getServices();
            
            // 恢复原始设置
            config(['services.seoestore.api_key' => $originalApiKey]);
            config(['services.seoestore.api_url' => $originalApiUrl]);
            
            if (empty($services)) {
                return back()->with('error', 'API连接成功，但未获取到服务列表');
            }
            
            return back()->with('success', 'API连接成功，获取到 ' . count($services) . ' 个服务');
        } catch (\Exception $e) {
            return back()->with('error', 'API连接失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 同步API产品
     */
    public function syncProducts()
    {
        try {
            $result = $this->apiService->syncProducts();
            
            return redirect()->route('admin.packages.index')
                ->with('success', "同步成功！创建了 {$result['created']} 个新产品，更新了 {$result['updated']} 个产品。");
        } catch (\Exception $e) {
            return back()->with('error', '同步API产品失败: ' . $e->getMessage());
        }
    }
}