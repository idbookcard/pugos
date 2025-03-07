<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * 显示所有套餐
     */
    public function index()
    {
        $categories = PackageCategory::active()
            ->orderBy('sort_order')
            ->with(['packages' => function($query) {
                $query->where('active', 1)->orderBy('sort_order');
            }])
            ->get();
            
        return view('packages.index', compact('categories'));
    }
    
    /**
     * 显示月度套餐
     */
    public function monthly()
    {
        $packages = Package::monthly()->orderBy('sort_order')->get();
        return view('packages.monthly', compact('packages'));
    }
    
    /**
     * 显示单项套餐
     */
    public function single()
    {
        $packages = Package::single()->orderBy('sort_order')->get();
        return view('packages.single', compact('packages'));
    }
    
    /**
     * 显示第三方套餐
     */
    public function thirdParty()
    {
        $packages = Package::thirdParty()->orderBy('sort_order')->get();
        return view('packages.third-party', compact('packages'));
    }
    
    /**
     * 显示软文外链套餐
     */
    public function guestPost()
    {
        $packages = Package::guestPost()->orderBy('sort_order')->get();
        return view('packages.guest-post', compact('packages'));
    }
    
    /**
     * 显示单个套餐详情
     */
    public function show(Package $package)
    {
        if (!$package->active) {
            abort(404);
        }
        
        // 获取同类型的相关套餐
        $relatedPackages = Package::where('package_type', $package->package_type)
            ->where('id', '!=', $package->id)
            ->where('active', 1)
            ->limit(3)
            ->get();
            
        return view('packages.show', compact('package', 'relatedPackages'));
    }
} 