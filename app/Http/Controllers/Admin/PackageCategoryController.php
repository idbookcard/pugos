<?php
// app/Http/Controllers/Admin/PackageCategoryController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }
    
    public function index()
    {
        $categories = PackageCategory::orderBy('sort_order')
            ->paginate(15);
        
        return view('admin.categories.index', compact('categories'));
    }
    
    public function create()
    {
        return view('admin.categories.create');
    }
    
    public function store(Request $request)
    {
        $this->validateCategory($request);
        
        // Create slug from name if not provided
        $slug = $request->slug ?? Str::slug($request->name);
        
        PackageCategory::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'slug' => $slug,
            'description' => $request->description,
            'description_zh' => $request->description_zh,
            'sort_order' => $request->sort_order ?? 0,
            'active' => $request->has('active'),
        ]);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }
    
    public function edit(PackageCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }
    
    public function update(Request $request, PackageCategory $category)
    {
        $this->validateCategory($request, $category->id);
        
        // Create slug from name if not provided
        $slug = $request->slug ?? Str::slug($request->name);
        
        $category->update([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'slug' => $slug,
            'description' => $request->description,
            'description_zh' => $request->description_zh,
            'sort_order' => $request->sort_order ?? 0,
            'active' => $request->has('active'),
        ]);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }
    
    public function destroy(PackageCategory $category)
    {
        // Check if the category has any packages
        if ($category->packages()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with associated packages.');
        }
        
        $category->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
    
    private function validateCategory(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:package_categories,slug' . ($id ? ',' . $id : ''),
            'description' => 'nullable|string',
            'description_zh' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}