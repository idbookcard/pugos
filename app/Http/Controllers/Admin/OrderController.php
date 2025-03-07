<?php
// app/Http/Controllers/Admin/OrderController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ThirdPartyApiService;

class OrderController extends Controller
{
    protected $thirdPartyApiService;
    
    public function __construct(ThirdPartyApiService $thirdPartyApiService)
    {
        $this->middleware(['auth', 'admin']);
        $this->thirdPartyApiService = $thirdPartyApiService;
    }
    
    public function index(Request $request)
    {
        $query = Order::with(['user', 'package']);
        
        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('service_type') && $request->service_type != '') {
            $query->where('service_type', $request->service_type);
        }
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('target_url', 'like', "%{$search}%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Sort orders
        $query->orderBy('created_at', 'desc');
        
        $orders = $query->paginate(15);
        
        // Get counts for each status
        $statusCounts = [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'canceled' => Order::where('status', 'canceled')->count(),
            'rejected' => Order::where('status', 'rejected')->count(),
        ];
        
        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }
    
    public function show(Order $order)
    {
        $order->load(['user', 'package', 'reports', 'statusLogs', 'statusLogs.creator']);
        
        return view('admin.orders.show', compact('order'));
    }
    
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,canceled,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        // Update order status
        $order->status = $newStatus;
        
        // If marking as completed, set completed_at
        if ($newStatus == 'completed' && !$order->completed_at) {
            $order->completed_at = now();
        }
        
        $order->save();
        
        // Create status log
        OrderStatusLog::create([
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $request->notes ?? 'Status updated by admin',
            'created_by' => Auth::id(),
        ]);
        
        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }
    
    public function sendToThirdParty(Request $request, Order $order)
    {
        // Validate that this is a third-party service order
        if ($order->service_type != 'external') {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'This order is not for a third-party service.');
        }
        
        // Check if order is in the right status
        if ($order->status != 'pending') {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Only pending orders can be sent to third-party services.');
        }
        
        try {
            // Submit to the third-party API service
            $response = $this->thirdPartyApiService->submitOrder($order);
            
            // Update order with third-party order ID
            $order->third_party_order_id = $response['order_id'] ?? null;
            $order->status = 'processing';
            $order->save();
            
            // Log status change
            OrderStatusLog::create([
                'order_id' => $order->id,
                'old_status' => 'pending',
                'new_status' => 'processing',
                'notes' => 'Order submitted to third-party service. Reference ID: ' . $order->third_party_order_id,
                'created_by' => Auth::id(),
            ]);
            
            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order submitted to third-party service successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Failed to submit order to third-party service: ' . $e->getMessage());
        }
    }
}