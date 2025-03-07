<?php
// app/Http/Controllers/Admin/PackageController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }
    
    public function index()
    {
        $packages = Package::with('category')
            ->orderBy('sort_order')
            ->paginate(15);
        
        return view('admin.packages.index', compact('packages'));
    }
    
    public function create()
    {
        $categories = PackageCategory::where('active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'id');
            
        $packageTypes = [
            'single' => 'Single Package',
            'monthly' => 'Monthly Package',
            'third_party' => 'Third Party Service',
            'guest_post' => 'Guest Post',
        ];
        
        return view('admin.packages.create', compact('categories', 'packageTypes'));
    }
    
    public function store(Request $request)
    {
        $this->validatePackage($request);
        
        // Create slug from name if not provided
        $slug = $request->slug ?? Str::slug($request->name);
        
        // Handle features array
        $features = null;
        if ($request->has('features')) {
            $features = json_encode(array_filter(explode("\n", $request->features)));
        }
        
        $package = Package::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'name_en' => $request->name_en,
            'slug' => $slug,
            'description' => $request->description,
            'description_zh' => $request->description_zh,
            'features' => $features,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'delivery_days' => $request->delivery_days,
            'package_type' => $request->package_type,
            'is_featured' => $request->has('is_featured'),
            'active' => $request->has('active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);
        
        if ($request->package_type === 'third_party') {
            $package->third_party_id = $request->third_party_id;
            $package->save();
        } elseif ($request->package_type === 'guest_post') {
            $package->guest_post_da = $request->guest_post_da;
            $package->save();
        }
        
        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully.');
    }
    
    public function show(Package $package)
    {
        return view('admin.packages.show', compact('package'));
    }
    
    public function edit(Package $package)
    {
        $categories = PackageCategory::where('active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'id');
            
        $packageTypes = [
            'single' => 'Single Package',
            'monthly' => 'Monthly Package',
            'third_party' => 'Third Party Service',
            'guest_post' => 'Guest Post',
        ];
        
        // Convert features array to newline-separated text for editing
        $featuresText = '';
        if ($package->features && is_array($package->features)) {
            $featuresText = implode("\n", $package->features);
        }
        
        return view('admin.packages.edit', compact('package', 'categories', 'packageTypes', 'featuresText'));
    }
    
    public function update(Request $request, Package $package)
    {
        $this->validatePackage($request, $package->id);
        
        // Create slug from name if not provided
        $slug = $request->slug ?? Str::slug($request->name);
        
        // Handle features array
        $features = null;
        if ($request->has('features')) {
            $features = json_encode(array_filter(explode("\n", $request->features)));
        }
        
        $package->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'name_en' => $request->name_en,
            'slug' => $slug,
            'description' => $request->description,
            'description_zh' => $request->description_zh,
            'features' => $features,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'delivery_days' => $request->delivery_days,
            'package_type' => $request->package_type,
            'is_featured' => $request->has('is_featured'),
            'active' => $request->has('active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);
        
        if ($request->package_type === 'third_party') {
            $package->third_party_id = $request->third_party_id;
            $package->guest_post_da = null;
            $package->save();
        } elseif ($request->package_type === 'guest_post') {
            $package->guest_post_da = $request->guest_post_da;
            $package->third_party_id = null;
            $package->save();
        } else {
            $package->third_party_id = null;
            $package->guest_post_da = null;
            $package->save();
        }
        
        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully.');
    }
    
    public function destroy(Package $package)
    {
        // Check if the package has any orders
        if ($package->orders()->count() > 0) {
            return redirect()->route('admin.packages.index')
                ->with('error', 'Cannot delete package with associated orders.');
        }
        
        $package->delete();
        
        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted successfully.');
    }
    
    private function validatePackage(Request $request, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:packages,slug' . ($id ? ',' . $id : ''),
            'category_id' => 'nullable|exists:package_categories,id',
            'description' => 'nullable|string',
            'description_zh' => 'nullable|string',
            'features' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'delivery_days' => 'nullable|integer|min:1',
            'package_type' => 'required|in:single,monthly,third_party,guest_post',
            'sort_order' => 'nullable|integer|min:0',
        ];
        
        if ($request->package_type === 'third_party') {
            $rules['third_party_id'] = 'required|string|max:255';
        } elseif ($request->package_type === 'guest_post') {
            $rules['guest_post_da'] = 'nullable|integer|min:0|max:100';
        }
        
        return $request->validate($rules);
    }
}