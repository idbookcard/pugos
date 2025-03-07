<?php
// app/Http/Controllers/PackageController.php
namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $featured = Package::where('is_featured', true)
            ->where('active', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();
            
        $categories = PackageCategory::where('active', true)
            ->orderBy('sort_order')
            ->get();
            
        return view('packages.index', compact('featured', 'categories'));
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
    
    public function thirdParty()
    {
        $packages = Package::where('package_type', 'third_party')
            ->where('active', true)
            ->orderBy('sort_order')
            ->paginate(12);
            
        return view('packages.third_party', compact('packages'));
    }
    
    public function guestPost()
    {
        $packages = Package::where('package_type', 'guest_post')
            ->where('active', true)
            ->orderBy('sort_order')
            ->paginate(12);
            
        return view('packages.guest_post', compact('packages'));
    }
    
    public function show(Package $package)
    {
        if (!$package->active) {
            abort(404);
        }
        
        $relatedPackages = Package::where('category_id', $package->category_id)
            ->where('id', '!=', $package->id)
            ->where('active', true)
            ->orderBy('sort_order')
            ->take(4)
            ->get();
            
        return view('packages.show', compact('package', 'relatedPackages'));
    }
}