<?php
// app/Http/Controllers/Customer/OrderController.php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\OrderStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('customer.orders.index', compact('orders'));
    }
    
    public function show(Order $order)
    {
        // Security check: ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $order->load(['reports', 'statusLogs']);
        
        return view('customer.orders.show', compact('order'));
    }
    
    public function create(Package $package)
    {
        // Check if package is active
        if (!$package->active) {
            return redirect()->route('packages')->with('error', 'This package is no longer available.');
        }
        
        return view('customer.orders.create', compact('package'));
    }
    
    public function store(Request $request, Package $package)
    {
        // Check if package is active
        if (!$package->active) {
            return redirect()->route('packages')->with('error', 'This package is no longer available.');
        }
        
        // Validate the request based on package type
        $this->validateOrderRequest($request, $package);
        
        // Check if user has enough balance
        $user = Auth::user();
        if ($user->balance < $package->price) {
            return redirect()->route('customer.wallet')
                ->with('error', 'Insufficient balance. Please top up your account.');
        }
        
        // Create the order
        $order = $this->createOrder($request, $package, $user);
        
        // Process payment
        $this->processPayment($order, $user);
        
        // Update user balance
        $user->balance -= $package->price;
        $user->save();
        
        // Log order status change
        OrderStatusLog::create([
            'order_id' => $order->id,
            'old_status' => null,
            'new_status' => 'pending',
            'notes' => 'Order created',
            'created_by' => $user->id
        ]);
        
        return redirect()->route('customer.orders.success', $order)
            ->with('success', 'Order placed successfully!');
    }
    
    public function success(Order $order)
    {
        // Security check: ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('customer.orders.success', compact('order'));
    }
    
    private function validateOrderRequest(Request $request, Package $package)
    {
        $rules = [
            'target_url' => 'required|url',
            'keywords' => 'required|string|max:500',
        ];
        
        // Add additional validation based on package type
        if ($package->package_type == 'monthly' || $package->package_type == 'single') {
            $rules['description'] = 'nullable|string|max:1000';
        } elseif ($package->package_type == 'guest_post') {
            $rules['article'] = 'required|string|min:300';
            $rules['notes'] = 'nullable|string|max:1000';
        }
        
        return $request->validate($rules);
    }
    
    private function createOrder(Request $request, Package $package, $user)
    {
        // Generate a unique order number
        $orderNumber = 'ORD-' . strtoupper(Str::random(8));
        
        // Set the service type based on package type
        $serviceType = 'package';
        if ($package->package_type == 'third_party') {
            $serviceType = 'external';
        } elseif ($package->package_type == 'guest_post') {
            $serviceType = 'guest_post';
        }
        
        // Create order record
        $order = Order::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'order_number' => $orderNumber,
            'service_type' => $serviceType,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_amount' => $package->price,
            'target_url' => $request->target_url,
            'keywords' => $request->keywords,
            'article' => $request->article ?? null,
            'extra_data' => [
                'description' => $request->description ?? null,
                'notes' => $request->notes ?? null,
            ],
        ]);
        
        // Set the appropriate service ID based on package type
        if ($package->package_type == 'third_party') {
            $order->external_service_id = $package->third_party_id;
        } elseif ($package->package_type == 'guest_post') {
            $order->guest_post_site_id = $package->id; // Using package ID as site ID for simplicity
        }
        
        $order->save();
        
        return $order;
    }
    
    private function processPayment(Order $order, $user)
    {
        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'transaction_type' => 'order_payment',
            'amount' => $order->total_amount,
            'payment_method' => 'balance',
            'payment_details' => ['payment_source' => 'user_balance'],
            'status' => 'completed',
            'reference_id' => $order->order_number,
            'notes' => 'Payment for order #' . $order->order_number,
        ]);
        
        // Update order payment status
        $order->payment_status = 'paid';
        $order->paid_at = now();
        $order->save();
        
        return $transaction;
    }
}