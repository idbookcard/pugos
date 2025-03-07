<?php
// app/Http/Controllers/Customer/GuestPostController.php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\GuestPostSite;
use App\Models\GuestPostCategory;
use App\Models\Package;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GuestPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        $query = GuestPostSite::with('category')
            ->where('active', 1);
        
        // Apply filters if provided
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('min_rating')) {
            $query->where('domain_rating', '>=', $request->min_rating);
        }
        
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Default sorting
        $sortField = $request->sort ?? 'domain_rating';
        $sortDirection = $request->direction ?? 'desc';
        
        $guestPosts = $query->orderBy($sortField, $sortDirection)->paginate(12);
        $categories = GuestPostCategory::orderBy('name')->pluck('name', 'id');
        
        return view('customer.guest-posts.index', compact('guestPosts', 'categories'));
    }
    
    public function show(GuestPostSite $guestPost)
    {
        if (!$guestPost->active) {
            abort(404);
        }
        
        // Find corresponding package
        $package = Package::where('package_type', 'guest_post')
            ->where(function($q) use ($guestPost) {
                $q->where('name', $guestPost->domain)
                  ->orWhere('name_en', $guestPost->domain);
            })
            ->first();
        
        if (!$package) {
            abort(404);
        }
        
        // Get similar guest posts by category or DA
        $similarPosts = GuestPostSite::where('id', '!=', $guestPost->id)
            ->where('active', 1)
            ->where(function($q) use ($guestPost) {
                $q->where('category_id', $guestPost->category_id)
                  ->orWhereBetween('domain_rating', [
                      max(0, $guestPost->domain_rating - 10),
                      min(100, $guestPost->domain_rating + 10)
                  ]);
            })
            ->orderBy('domain_rating', 'desc')
            ->take(4)
            ->get();
        
        return view('customer.guest-posts.show', compact('guestPost', 'package', 'similarPosts'));
    }
    
    public function order(GuestPostSite $guestPost)
    {
        if (!$guestPost->active) {
            abort(404);
        }
        
        // Find corresponding package
        $package = Package::where('package_type', 'guest_post')
            ->where(function($q) use ($guestPost) {
                $q->where('name', $guestPost->domain)
                  ->orWhere('name_en', $guestPost->domain);
            })
            ->first();
        
        if (!$package) {
            abort(404);
        }
        
        return view('customer.guest-posts.order', compact('guestPost', 'package'));
    }
    
    public function placeOrder(Request $request, GuestPostSite $guestPost)
    {
        if (!$guestPost->active) {
            abort(404);
        }
        
        // Find corresponding package
        $package = Package::where('package_type', 'guest_post')
            ->where(function($q) use ($guestPost) {
                $q->where('name', $guestPost->domain)
                  ->orWhere('name_en', $guestPost->domain);
            })
            ->first();
        
        if (!$package) {
            abort(404);
        }
        
        $request->validate([
            'target_url' => 'required|url',
            'keywords' => 'required|string|max:255',
            'article' => 'required|string|min:300',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $user = Auth::user();
        
        // Check if user has enough balance
        if ($user->balance < $package->price) {
            return redirect()->route('customer.wallet')
                ->with('error', 'Insufficient balance. Please add funds to your account.');
        }
        
        // Create order
        $orderNumber = 'GP-' . strtoupper(Str::random(8));
        
        $order = Order::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'guest_post_site_id' => $guestPost->id,
            'order_number' => $orderNumber,
            'service_type' => 'guest_post',
            'status' => 'pending',
            'payment_status' => 'paid',
            'total_amount' => $package->price,
            'target_url' => $request->target_url,
            'keywords' => $request->keywords,
            'article' => $request->article,
            'extra_data' => json_encode([
                'notes' => $request->notes,
                'domain' => $guestPost->domain,
            ]),
            'paid_at' => now(),
        ]);
        
        // Create transaction
        $user->transactions()->create([
            'order_id' => $order->id,
            'transaction_type' => 'order_payment',
            'amount' => -$package->price,
            'payment_method' => 'balance',
            'status' => 'completed',
            'reference_id' => $orderNumber,
            'notes' => 'Payment for guest post on ' . $guestPost->domain,
        ]);
        
        // Update user balance
        $user->balance -= $package->price;
        $user->save();
        
        // Create status log
        $order->statusLogs()->create([
            'old_status' => null,
            'new_status' => 'pending',
            'notes' => 'Order created and paid',
            'created_by' => $user->id,
        ]);
        
        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Your guest post order has been placed successfully!');
    }
}