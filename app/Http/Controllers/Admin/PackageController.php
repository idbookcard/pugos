<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\PackageRequest;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    /**
     * 显示产品列表
     */
    public function index()
    {
        $packages = Package::with('category')
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->paginate(20);
            
        return view('master.packages.index', compact('packages'));
    }
    
    /**
     * 显示创建产品页面
     */
    public function create()
    {
        $categories = PackageCategory::where('active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'id');
            
        return view('master.packages.create', compact('categories'));
    }
    
    /**
     * 处理产品创建
     */
    public function store(PackageRequest $request)
    {
        // 生成slug
        $slug = Str::slug($request->input('name_en') ?: $request->input('name'));
        
        // 确保slug唯一
        $baseSlug = $slug;
        $count = 1;
        while (Package::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }
        
        // 处理features数组
        $features = explode("\n", $request->input('features'));
        $features = array_map('trim', $features);
        $features = array_filter($features);
        
        // 创建产品
        Package::create([
            'category_id' => $request->input('category_id'),
            'name' => $request->input('name'),
            'name_en' => $request->input('name_en'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'description_zh' => $request->input('description_zh'),
            'features' => json_encode($features),
            'available_extras' => $request->input('available_extras'),
            'price' => $request->input('price'),
            'original_price' => $request->input('original_price'),
            'delivery_days' => $request->input('delivery_days'),
            'package_type' => $request->input('package_type'),
            'is_api_product' => $request->input('is_api_product', false),
            'is_featured' => $request->input('is_featured', false),
            'active' => $request->input('active', true),
            'sort_order' => $request->input('sort_order', 0),
            'third_party_id' => $request->input('third_party_id'),
            'guest_post_da' => $request->input('guest_post_da')
        ]);
        
        return redirect()->route('master.packages.index')
            ->with('success', '产品创建成功');
    }
    
    /**
     * 显示产品编辑页面
     */
    public function edit($id)
    {
        $package = Package::findOrFail($id);
        
        $categories = PackageCategory::where('active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'id');
            
        // 将json特性转为文本
        $features = json_decode($package->features, true) ?: [];
        $featuresText = implode("\n", $features);
        
        return view('master.packages.edit', compact('package', 'categories', 'featuresText'));
    }
    
    /**
     * 处理产品更新
     */
    public function update(PackageRequest $request, $id)
    {
        $package = Package::findOrFail($id);
        
        // 处理slug更改
        $newSlug = Str::slug($request->input('name_en') ?: $request->input('name'));
        if ($newSlug != $package->slug) {
            // 确保slug唯一
            $baseSlug = $newSlug;
            $count = 1;
            while (Package::where('slug', $newSlug)->where('id', '!=', $id)->exists()) {
                $newSlug = $baseSlug . '-' . $count++;
            }
        } else {
            $newSlug = $package->slug;
        }
        
        // 处理features数组
        $features = explode("\n", $request->input('features'));
        $features = array_map('trim', $features);
        $features = array_filter($features);
        
        // 更新产品
        $package->update([
           'category_id' => $request->input('category_id'),
            'name' => $request->input('name'),
            'name_en' => $request->input('name_en'),
            'slug' => $newSlug,
            'description' => $request->input('description'),
            'description_zh' => $request->input('description_zh'),
            'features' => json_encode($features),
            'available_extras' => $request->input('available_extras'),
            'price' => $request->input('price'),
            'original_price' => $request->input('original_price'),
            'delivery_days' => $request->input('delivery_days'),
            'package_type' => $request->input('package_type'),
            'is_api_product' => $request->input('is_api_product', false),
            'is_featured' => $request->input('is_featured', false),
            'active' => $request->input('active', true),
            'sort_order' => $request->input('sort_order', 0),
            'third_party_id' => $request->input('third_party_id'),
            'guest_post_da' => $request->input('guest_post_da')
        ]);
        
        return redirect()->route('master.packages.index')
            ->with('success', '产品更新成功');
    }
    
    /**
     * 删除产品
     */
    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        
        // 检查是否有关联订单
        if ($package->orders()->exists()) {
            return back()->with('error', '该产品已有订单关联，无法删除');
        }
        
        $package->delete();
        
        return redirect()->route('master.packages.index')
            ->with('success', '产品已删除');
    }
    
    /**
     * 更新产品状态
     */
    public function updateStatus(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        
        $package->update([
            'active' => $request->input('active', false)
        ]);
        
        return response()->json(['success' => true]);
    }

     /**
     * 同步产品的额外选项
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncExtras($id, Request $request)
    {
        try {
            $package = Package::findOrFail($id);
            
            // 检查是否是API产品
            if (!$package->is_api_product || !$package->third_party_id) {
                return response()->json([
                    'success' => false,
                    'message' => '只有API产品才能同步额外选项'
                ], 400);
            }
            
            // 调用API服务
            $apiService = new SEOeStoreApiService();
            
            // 获取服务信息
            $serviceInfo = $apiService->getServiceInfo($package->third_party_id);
            
            if (!$serviceInfo || !isset($serviceInfo['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => '无法获取服务信息: ' . ($apiService->getLastError() ?? '未知错误')
                ], 500);
            }
            
            // 获取额外选项
            $availableExtras = [];
            
            // 如果服务有extras字段
            if (isset($serviceInfo['extras']) && !empty($serviceInfo['extras'])) {
                $extrasIds = explode(',', $serviceInfo['extras']);
                
                // 从数据库获取这些extras的详细信息
                $extras = ExtraOption::whereIn('extra_id', $extrasIds)
                    ->where('active', true)
                    ->get();
                
                foreach ($extras as $extra) {
                    $availableExtras[] = [
                        'id' => $extra->extra_id,
                        'code' => $extra->code,
                        'name' => $extra->name,
                        'price' => $extra->price,
                        'is_multiple' => $extra->is_multiple
                    ];
                }
                
                // 如果没有找到任何额外选项
                if (empty($availableExtras)) {
                    return response()->json([
                        'success' => false,
                        'message' => '服务存在额外选项，但未在系统中找到对应的选项信息，请先同步额外选项数据'
                    ], 404);
                }
            }
            
            // 更新包裹的可用额外选项
            $package->available_extras = json_encode($availableExtras);
            $package->save();
            
            return response()->json([
                'success' => true,
                'message' => '成功同步额外选项，共' . count($availableExtras) . '个选项',
                'extras_count' => count($availableExtras)
            ]);
        } catch (\Exception $e) {
            Log::error('同步额外选项失败: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '同步失败: ' . $e->getMessage()
            ], 500);
        }
    }



    
 /**
     * 更新产品的可用额外选项
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateExtras($id, Request $request)
    {
        $request->validate([
            'available_extras' => 'nullable|json'
        ]);
        
        try {
            $package = Package::findOrFail($id);
            
            // 更新可用额外选项
            $package->available_extras = $request->available_extras;
            $package->save();
            
            return response()->json([
                'success' => true,
                'message' => '成功更新额外选项'
            ]);
        } catch (\Exception $e) {
            Log::error('更新额外选项失败: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '更新失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 批量更新产品的额外选项
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchSyncExtras(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array',
            'package_ids.*' => 'required|exists:packages,id'
        ]);
        
        try {
            $success = 0;
            $failed = 0;
            $log = [];
            
            foreach ($request->package_ids as $packageId) {
                try {
                    $package = Package::findOrFail($packageId);
                    
                    // 跳过非API产品
                    if (!$package->is_api_product || !$package->third_party_id) {
                        $log[] = "包裹 #$packageId ({$package->name}): 跳过 - 不是API产品";
                        continue;
                    }
                    
                    // 调用同步方法
                    $result = $this->syncExtras($packageId, $request);
                    $resultData = json_decode($result->getContent(), true);
                    
                    if (isset($resultData['success']) && $resultData['success']) {
                        $success++;
                        $log[] = "包裹 #$packageId ({$package->name}): 成功 - {$resultData['message']}";
                    } else {
                        $failed++;
                        $log[] = "包裹 #$packageId ({$package->name}): 失败 - " . ($resultData['message'] ?? '未知错误');
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $log[] = "包裹 #$packageId: 失败 - {$e->getMessage()}";
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "批量同步完成：成功 $success 个，失败 $failed 个",
                'success_count' => $success,
                'failed_count' => $failed,
                'log' => $log
            ]);
        } catch (\Exception $e) {
            Log::error('批量同步额外选项失败: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '批量同步失败: ' . $e->getMessage()
            ], 500);
        }
    }
}