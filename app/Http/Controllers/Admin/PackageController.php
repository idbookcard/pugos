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
            
        return view('admin.packages.index', compact('packages'));
    }
    
    /**
     * 显示创建产品页面
     */
    public function create()
    {
        $categories = PackageCategory::where('active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'id');
            
        return view('admin.packages.create', compact('categories'));
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
            'price' => $request->input('price'),
            'original_price' => $request->input('original_price'),
            'delivery_days' => $request->input('delivery_days'),
            'package_type' => $request->input('package_type'),
            'is_featured' => $request->input('is_featured', false),
            'active' => $request->input('active', true),
            'sort_order' => $request->input('sort_order', 0),
            'third_party_id' => $request->input('third_party_id'),
            'guest_post_da' => $request->input('guest_post_da')
        ]);
        
        return redirect()->route('admin.packages.index')
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
        
        return view('admin.packages.edit', compact('package', 'categories', 'featuresText'));
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
            'price' => $request->input('price'),
            'original_price' => $request->input('original_price'),
            'delivery_days' => $request->input('delivery_days'),
            'package_type' => $request->input('package_type'),
            'is_featured' => $request->input('is_featured', false),
            'active' => $request->input('active', true),
            'sort_order' => $request->input('sort_order', 0),
            'third_party_id' => $request->input('third_party_id'),
            'guest_post_da' => $request->input('guest_post_da')
        ]);
        
        return redirect()->route('admin.packages.index')
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
        
        return redirect()->route('admin.packages.index')
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
}