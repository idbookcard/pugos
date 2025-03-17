<?php
// app/Http/Controllers/Admin/GuestPostController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestPostSite;
use App\Models\GuestPostCategory;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GuestPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('master'); 
    }
    
    public function index(Request $request)
    {
        $query = GuestPostSite::with('category');
        
        // Apply filters
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('domain_rating_min') && $request->domain_rating_min) {
            $query->where('domain_rating', '>=', $request->domain_rating_min);
        }
        
        if ($request->has('domain_rating_max') && $request->domain_rating_max) {
            $query->where('domain_rating', '<=', $request->domain_rating_max);
        }
        
        if ($request->has('price_min') && $request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }
        
        if ($request->has('price_max') && $request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }
        
        // Sort
        $sortField = $request->sort_by ?? 'domain_rating';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        $guestPosts = $query->paginate(15);
        $categories = GuestPostCategory::where('active', 1)->orderBy('name')->pluck('name', 'id');
        
        return view('master.guest-posts.index', compact('guestPosts', 'categories'));
    }
    
    public function create()
    {
        $categories = GuestPostCategory::where('active', 1)->orderBy('name')->pluck('name', 'id');
        return view('master.guest-posts.create', compact('categories'));
    }
    
    public function store(Request $request)
    {
        $this->validateGuestPost($request);
        
        DB::beginTransaction();
        
        try {
            // Create Guest Post site
            $guestPost = GuestPostSite::create([
                'category_id' => $request->category_id,
                'site_id' => $request->site_id ?? Str::random(10),
                'domain' => $request->domain,
                'title' => $request->title,
                'description' => $request->description,
                'requirements' => $request->requirements,
                'price' => $request->price,
                'domain_rating' => $request->domain_rating,
                'traffic' => $request->traffic,
                'metrics_data' => [
                    'da' => $request->domain_authority ?? null,
                    'pa' => $request->page_authority ?? null,
                    'tf' => $request->trust_flow ?? null,
                    'cf' => $request->citation_flow ?? null,
                    'language' => $request->language,
                    'country' => $request->country,
                ],
                'active' => $request->has('active'),
                'category_slug' => $request->category_slug ?? null,
            ]);
            
            // Create a corresponding package
            $package = Package::create([
                'third_party_id' => null,
                'guest_post_da' => $request->domain_rating,
                'name' => $request->domain . ' Guest Post',
                'name_en' => $request->domain . ' Guest Post',
                'slug' => Str::slug($request->domain . '-guest-post-' . Str::random(5)),
                'description' => $request->description,
                'description_zh' => null,
                'features' => json_encode([
                    'Domain Rating: ' . $request->domain_rating,
                    'Domain: ' . $request->domain,
                    'Traffic: ' . $request->traffic,
                ]),
                'price' => $request->price,
                'original_price' => $request->price * 1.2, // Example markup
                'delivery_days' => $request->delivery_days ?? 14,
                'package_type' => 'guest_post',
                'is_featured' => $request->has('is_featured'),
                'active' => $request->has('active'),
                'sort_order' => $request->sort_order ?? 0
            ]);
            
            DB::commit();
            
            return redirect()->route('master.guest-posts')
                ->with('success', 'Guest Post site added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('master.guest-posts.create')
                ->with('error', 'Failed to create Guest Post site: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function edit(GuestPostSite $package) // Using $package to match route parameter
    {
        $guestPost = $package;
        $categories = GuestPostCategory::where('active', 1)->orderBy('name')->pluck('name', 'id');
        
        // Find the corresponding package
        $packageRecord = Package::where('package_type', 'guest_post')
            ->where('name', 'like', $guestPost->domain . '%')
            ->first();
        
        return view('master.guest-posts.edit', compact('guestPost', 'categories', 'packageRecord'));
    }
    
    public function update(Request $request, GuestPostSite $package) // Using $package to match route parameter
    {
        $guestPost = $package;
        $this->validateGuestPost($request, $guestPost->id);
        
        DB::beginTransaction();
        
        try {
            // Update Guest Post site
            $guestPost->update([
                'category_id' => $request->category_id,
                'site_id' => $request->site_id ?? $guestPost->site_id,
                'domain' => $request->domain,
                'title' => $request->title,
                'description' => $request->description,
                'requirements' => $request->requirements,
                'price' => $request->price,
                'domain_rating' => $request->domain_rating,
                'traffic' => $request->traffic,
                'metrics_data' => [
                    'da' => $request->domain_authority ?? null,
                    'pa' => $request->page_authority ?? null,
                    'tf' => $request->trust_flow ?? null,
                    'cf' => $request->citation_flow ?? null,
                    'language' => $request->language,
                    'country' => $request->country,
                ],
                'active' => $request->has('active'),
                'category_slug' => $request->category_slug ?? $guestPost->category_slug,
            ]);
            
            // Find and update the corresponding package
            $package = Package::where('package_type', 'guest_post')
                ->where('name', 'like', $guestPost->domain . '%')
                ->first();
                
            if ($package) {
                $package->update([
                    'guest_post_da' => $request->domain_rating,
                    'name' => $request->domain . ' Guest Post',
                    'name_en' => $request->domain . ' Guest Post',
                    'description' => $request->description,
                    'features' => json_encode([
                        'Domain Rating: ' . $request->domain_rating,
                        'Domain: ' . $request->domain,
                        'Traffic: ' . $request->traffic,
                    ]),
                    'price' => $request->price,
                    'original_price' => $request->price * 1.2, // Example markup
                    'delivery_days' => $request->delivery_days ?? 14,
                    'is_featured' => $request->has('is_featured'),
                    'active' => $request->has('active'),
                    'sort_order' => $request->sort_order ?? $package->sort_order
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('master.guest-posts')
                ->with('success', 'Guest Post site updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('master.guest-posts.edit', $guestPost)
                ->with('error', 'Failed to update Guest Post site: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function destroy(GuestPostSite $package) // Using $package to match route parameter
    {
        $guestPost = $package;
        
        // Check if there are orders using this guest post
        $hasOrders = DB::table('orders')
            ->where('guest_post_site_id', $guestPost->id)
            ->exists();
            
        if ($hasOrders) {
            return redirect()->route('master.guest-posts')
                ->with('error', 'Cannot delete Guest Post site with existing orders.');
        }
        
        DB::beginTransaction();
        
        try {
            // Find and delete the corresponding package
            Package::where('package_type', 'guest_post')
                ->where('name', 'like', $guestPost->domain . '%')
                ->delete();
                
            // Delete the guest post site
            $guestPost->delete();
            
            DB::commit();
            
            return redirect()->route('master.guest-posts')
                ->with('success', 'Guest Post site deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('master.guest-posts')
                ->with('error', 'Failed to delete Guest Post site: ' . $e->getMessage());
        }
    }
    
    private function validateGuestPost(Request $request, $id = null)
    {
        return $request->validate([
            'category_id' => 'nullable|exists:guest_post_categories,id',
            'site_id' => 'nullable|string|max:255',
            'domain' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'domain_rating' => 'nullable|integer|min:0|max:100',
            'traffic' => 'nullable|integer|min:0',
            'domain_authority' => 'nullable|integer|min:0|max:100',
            'page_authority' => 'nullable|integer|min:0|max:100',
            'trust_flow' => 'nullable|integer|min:0|max:100',
            'citation_flow' => 'nullable|integer|min:0|max:100',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'active' => 'nullable|boolean',
            'category_slug' => 'nullable|string|max:255',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'delivery_days' => 'nullable|integer|min:1',
        ]);
    }
}