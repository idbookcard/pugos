<?php

// app/Http/Controllers/Api/ThirdPartyWebhookController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\OrderReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThirdPartyWebhookController extends Controller
{
    public function orderUpdate(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'order_id' => 'required|string',
            'status' => 'required|string',
            'api_key' => 'required|string',
        ]);
        
        // Verify API key
        $apiKey = config('services.seoestore.webhook_key');
        if ($request->api_key !== $apiKey) {
            Log::warning('Invalid API key in webhook request', [
                'ip' => $request->ip(),
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Find the order by third_party_order_id
        $order = Order::where('third_party_order_id', $request->order_id)->first();
        
        if (!$order) {
            Log::warning('Order not found in webhook request', [
                'third_party_order_id' => $request->order_id
            ]);
            return response()->json(['error' => 'Order not found'], 404);
        }
        
        // Map the external status to our internal status
        $oldStatus = $order->status;
        $newStatus = $this->mapExternalStatusToLocal($request->status);
        
        // Update order status if it has changed
        if ($oldStatus !== $newStatus) {
            $order->status = $newStatus;
            
            // If completed, set completed_at timestamp
            if ($newStatus === 'completed' && !$order->completed_at) {
                $order->completed_at = now();
            }
            
            $order->save();
            
            // Log the status change
            OrderStatusLog::create([
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notes' => 'Status updated by third-party webhook',
                'created_by' => 0 // System/API
            ]);
            
            // Create or update order report
            OrderReport::updateOrCreate(
                ['order_id' => $order->id, 'source' => 'webhook'],
                [
                    'status' => $newStatus,
                    'report_data' => json_encode($request->all()),
                    'placed_at' => now()
                ]
            );
            
            Log::info('Order status updated by webhook', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Map external status to local status
     *
     * @param string $externalStatus
     * @return string
     */
    private function mapExternalStatusToLocal($externalStatus)
    {
        $statusMap = [
            'pending' => 'pending',
            'in_progress' => 'processing',
            'processing' => 'processing',
            'partial' => 'completed', // Map partial to completed or handle differently
            'completed' => 'completed',
            'cancelled' => 'canceled',
            'canceled' => 'canceled',
            'refunded' => 'refunded',
        ];
        
        $externalStatus = strtolower(str_replace(' ', '_', $externalStatus));
        
        return $statusMap[$externalStatus] ?? 'processing';
    }
}