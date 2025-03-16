<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\ThirdPartyApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ThirdPartyOrderController extends Controller
{
    protected $apiService;
    
    public function __construct(ThirdPartyApiService $apiService)
    {
        $this->middleware('auth');
        $this->middleware('master'); 
        $this->apiService = $apiService;
    }
    
    public function index(Request $request)
    {
        $query = Order::with(['user', 'package'])
            ->where('service_type', 'external')
            ->where('status', 'pending');
        
        // Apply filters if provided
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
        
        // Sort orders by creation date
        $query->orderBy('created_at', 'desc');
        
        $pendingOrders = $query->paginate(15);
        
        // Fetch processing orders with third-party ID
        $processingOrders = Order::with(['user', 'package'])
            ->where('service_type', 'external')
            ->where('status', 'processing')
            ->whereNotNull('third_party_order_id')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'processing_page');
        
        return view('master.third-party-orders.index', compact('pendingOrders', 'processingOrders'));
    }
    
    public function approveOrders(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);
        
        $orderIds = $request->order_ids;
        $results = [
            'total' => count($orderIds),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($orderIds as $orderId) {
            $order = Order::findOrFail($orderId);
            
            // Verify order is pending and external type
            if ($order->status !== 'pending' || $order->service_type !== 'external') {
                $results['failed']++;
                $results['errors'][] = "Order #{$order->order_number} is not a pending external order.";
                continue;
            }
            
            try {
                // Send order to third-party API
                $response = $this->apiService->submitOrder($order);
                
                // Update order status
                $order->status = 'processing';
                $order->third_party_order_id = $response['order_id'];
                $order->save();
                
                // Log status change
                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'old_status' => 'pending',
                    'new_status' => 'processing',
                    'notes' => 'Order approved and sent to third-party service. Reference ID: ' . $response['order_id'],
                    'created_by' => Auth::id()
                ]);
                
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Order #{$order->order_number}: " . $e->getMessage();
                Log::error('Failed to submit order to third-party API', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if ($results['success'] > 0) {
            $message = "Successfully submitted {$results['success']} orders to the third-party service.";
            if ($results['failed'] > 0) {
                $message .= " Failed to submit {$results['failed']} orders.";
            }
            return redirect()->route('master.third-party-orders')
                ->with('success', $message)
                ->with('errors', $results['errors']);
        } else {
            return redirect()->route('master.third-party-orders')
                ->with('error', "Failed to submit all orders. Please check the error messages.")
                ->with('errors', $results['errors']);
        }
    }
    
    public function sendOrder(Request $request, Order $order)
    {
        // Verify order is pending and external type
        if ($order->status !== 'pending' || $order->service_type !== 'external') {
            return redirect()->route('master.third-party-orders')
                ->with('error', "Order #{$order->order_number} is not a pending external order.");
        }
        
        try {
            // Send order to third-party API
            $response = $this->apiService->submitOrder($order);
            
            return redirect()->route('master.third-party-orders')
                ->with('success', "Successfully submitted order #{$order->order_number} to the third-party service.");
        } catch (\Exception $e) {
            Log::error('Failed to submit order to third-party API', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('master.third-party-orders')
                ->with('error', "Failed to submit order #{$order->order_number}: " . $e->getMessage());
        }
    }
    
    public function sendAllOrders()
    {
        $pendingOrders = Order::where('service_type', 'external')
            ->where('status', 'pending')
            ->get();
            
        if ($pendingOrders->isEmpty()) {
            return redirect()->route('master.third-party-orders')
                ->with('info', "No pending external orders found.");
        }
        
        $results = [
            'total' => $pendingOrders->count(),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($pendingOrders as $order) {
            try {
                // Send order to third-party API
                $response = $this->apiService->submitOrder($order);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Order #{$order->order_number}: " . $e->getMessage();
                Log::error('Failed to submit order to third-party API', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if ($results['success'] > 0) {
            $message = "Successfully submitted {$results['success']} orders to the third-party service.";
            if ($results['failed'] > 0) {
                $message .= " Failed to submit {$results['failed']} orders.";
            }
            return redirect()->route('master.third-party-orders')
                ->with('success', $message)
                ->with('errors', $results['errors']);
        } else {
            return redirect()->route('master.third-party-orders')
                ->with('error', "Failed to submit all orders. Please check the error messages.")
                ->with('errors', $results['errors']);
        }
    }
    
    public function syncOrderStatuses()
    {
        try {
            $results = $this->apiService->updateAllOrderStatuses();
            
            $message = "Processed {$results['total']} orders. ";
            $message .= "Updated {$results['updated']} orders, completed {$results['completed']} orders.";
            
            if (isset($results['failed']) && $results['failed'] > 0) {
                $message .= " Failed to process {$results['failed']} orders.";
            }
            
            return redirect()->route('master.third-party-orders')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to sync order statuses', ['error' => $e->getMessage()]);
            
            return redirect()->route('master.third-party-orders')
                ->with('error', "Failed to sync order statuses: " . $e->getMessage());
        }
    }
}