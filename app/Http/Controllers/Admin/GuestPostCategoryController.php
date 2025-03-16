<?php
// app/Http/Controllers/Admin/GuestPostCategoryController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestPostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuestPostCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('master'); 
    }
    
    public function index()
    {
        $categories = GuestPostCategory::orderBy('sort_order')->paginate(15);
        return view('master.guest-post-categories.index', compact('categories'));
    }
    
    public function create()
    {
        return view('master.guest-post-categories.create');
    }
    
    public function store(Request $request)
    {
        $this->validateCategory($request);
        
        // Create slug from name if not provided
        $slug = $request->slug ?? Str::slug($request->name);
        
        GuestPostCategory::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'slug' => $slug,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'active' => $request->has('active'),
        ]);
        
        return redirect()->route('master.guest-post-categories.index')
            ->with('success', 'Guest Post category created successfully.');
    }
    
    public function edit(GuestPostCategory $category)
    {
        return view('master.guest-post-categories.edit', compact('category'));
    }
    
    public function update(Request $request, GuestPostCategory $category)
    {
        $this->validateCategory($request, $category->id);
        
        // Create slug from name if not provided
        $slug = $request->slug ?? Str::slug($request->name);
        
        $category->update([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'slug' => $slug,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'active' => $request->has('active'),
        ]);
        
        return redirect()->route('master.guest-post-categories.index')
            ->with('success', 'Guest Post category updated successfully.');
    }
    
    public function destroy(GuestPostCategory $category)
    {
        // Check if the category has any guest post sites
        if ($category->sites()->count() > 0) {
            return redirect()->route('master.guest-post-categories.index')
                ->with('error', 'Cannot delete category with associated guest post sites.');
        }
        
        $category->delete();
        
        return redirect()->route('master.guest-post-categories.index')
            ->with('success', 'Guest Post category deleted successfully.');
    }
    
    private function validateCategory(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => [
                'nullable', 
                'string', 
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    if ($value) {
                        $query = GuestPostCategory::where('slug', $value);
                        if ($id) {
                            $query->where('id', '!=', $id);
                        }
                        if ($query->exists()) {
                            $fail('The slug has already been taken.');
                        }
                    }
                },
            ],
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}