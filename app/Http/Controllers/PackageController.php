<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * 显示所有产品分类
     */
    public function index()
    {
        $categories = PackageCategory::where('active', true)
            ->orderBy('sort_order')
            ->get();
            
        return view('packages.index', compact('categories'));
    }
    
    /**
     * 显示分类下的产品
     */
    public function category($slug)
    {
        $category = PackageCategory::where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();
            
        $packages = Package::where('category_id', $category->id)
            ->where('active', true)
            ->orderBy('sort_order')
            ->paginate(12);
            
        return view('packages.category', compact('category', 'packages'));
    }
    
    /**
     * 显示产品详情
     */
    public function show($slug)
    {
        $package = Package::where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();
            
        $relatedPackages = Package::where('category_id', $package->category_id)
            ->where('active', true)
            ->where('id', '!=', $package->id)
            ->limit(4)
            ->get();
            
        return view('packages.show', compact('package', 'relatedPackages'));
    }

    public function monthly()
    {
        $packages = Package::where('package_type', 'monthly')
            ->where('active', true)
            ->orderBy('sort_order')
            ->paginate(12);
            
        return view('packages.monthly', compact('packages'));
    }

    public function single()
    {
        $packages = Package::where('package_type', 'single')
            ->where('active', true)
            ->orderBy('sort_order')
            ->paginate(12);
            
        return view('packages.single', compact('packages'));
    }

    public function guestPost()
    {
        $packages = Package::where('package_type', 'guest-post')
            ->where('active', true)
            ->orderBy('sort_order')
            ->paginate(12);
            
        return view('packages.guest-post', compact('packages'));
    }

    public function thirdParty()
    {
        $packages = Package::where('package_type', 'third-party')
            ->where('active', true)
            ->orderBy('sort_order')
            ->paginate(12);
            
        return view('packages.third-party', compact('packages'));
    }
}