<?php
// app/Services/ThirdPartyApiService.php (optimized version)
namespace App\Services;

use App\Models\Order;
use App\Models\ThirdPartyApiSetting;
use App\Models\ExternalService;
use App\Models\ExternalServiceCategory;
use App\Models\OrderReport;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ThirdPartyApiService
{
    protected $apiUrl;
    protected $apiKey;
    protected $lastError;
    
    public function __construct()
    {
        $this->loadApiSettings();
    }
    
    /**
     * Get the last error message
     */
    public function getLastError()
    {
        return $this->lastError;
    }
    
    /**
     * Load API settings from database or config
     */
    protected function loadApiSettings()
    {
        $settings = ThirdPartyApiSetting::where('name', 'SEOeStore')->first();
        
        if ($settings) {
            $this->apiUrl = $settings->api_url;
            $this->apiKey = $settings->api_key;
        } else {
            // Fallback to config values
            $this->apiUrl = config('services.seoestore.api_url', 'https://panel.seoestore.net/api/v1');
            $this->apiKey = config('services.seoestore.api_key');
        }
        
        if (!$this->apiUrl || !$this->apiKey) {
            Log::warning('SEOeStore API settings not configured');
        }
    }
    
    /**
     * Send API request
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @return mixed
     */
    protected function sendRequest($endpoint, $params = [], $method = 'GET')
    {
        $url = $this->apiUrl . '/' . $endpoint;
        $params['key'] = $this->apiKey;
        
        try {
            if ($method === 'GET') {
                $response = Http::get($url, $params);
            } else {
                $response = Http::post($url, $params);
            }
            
            if ($response->successful()) {
                return $response->json();
            } else {
                $this->lastError = "API Request failed: " . $response->status() . " - " . $response->body();
                Log::error('SeoEstore API error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            $this->lastError = "API Request exception: " . $e->getMessage();
            Log::error('SeoEstore API exception', [
                'endpoint' => $endpoint,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Submit order to third-party API
     *
     * @param Order $order
     * @return array|bool
     */
    public function submitOrder(Order $order)
    {
        if (!$this->apiUrl || !$this->apiKey) {
            throw new \Exception('API settings not configured');
        }
        
        // Prepare the order data
        $serviceId = $order->external_service_id;
        
        if (!$serviceId) {
            throw new \Exception('External service ID not found');
        }
        
        // Extract keywords from the order
        $keywords = explode(',', $order->keywords);
        $primaryKeyword = trim($keywords[0]);
        
        // Format the API request data
        $params = [
            'service' => $serviceId,
            'link' => $order->target_url,
            'quantity' => $order->quantity ?? 1
        ];
        
        // Add additional parameters if available
        if (!empty($order->extra_data)) {
            $extras = is_array($order->extra_data) ? $order->extra_data : json_decode($order->extra_data, true);
            
            if (isset($extras['notes'])) {
                $params['comments'] = $extras['notes'];
            }
            
            if (isset($extras['anchor_text']) || isset($order->keywords)) {
                $params['anchor'] = $extras['anchor_text'] ?? $primaryKeyword;
            }
            
            if (isset($extras['article'])) {
                $params['article'] = $extras['article'];
            }
        }
        
        $result = $this->sendRequest('order', $params, 'POST');
        
        if ($result && isset($result['order'])) {
            // Update the order with external order ID
            $order->third_party_order_id = $result['order'];
            $order->status = 'processing';
            $order->save();
            
            // Log status change
            OrderStatusLog::create([
                'order_id' => $order->id,
                'old_status' => 'pending',
                'new_status' => 'processing',
                'notes' => 'Order submitted to third-party service. Reference ID: ' . $result['order'],
                'created_by' => Auth::id() ?? 1,
            ]);
            
            return [
                'order_id' => $result['order'],
                'status' => 'success'
            ];
        }
        
        throw new \Exception($this->lastError ?? 'Failed to submit order to third-party service');
    }
    
    /**
     * Get order status from third-party API
     *
     * @param string $thirdPartyOrderId
     * @return array|bool
     */
    public function getOrderStatus($thirdPartyOrderId)
    {
        if (!$this->apiUrl || !$this->apiKey) {
            throw new \Exception('API settings not configured');
        }
        
        $result = $this->sendRequest('status', ['order' => $thirdPartyOrderId]);
        
        if ($result && isset($result['status'])) {
            return [
                'status' => $this->mapExternalStatusToLocal($result['status']),
                'original_status' => $result['status'],
                'data' => $result
            ];
        }
        
        throw new \Exception($this->lastError ?? 'Failed to get order status');
    }
    
    /**
     * Sync all third-party products
     *
     * @return array
     */
    public function syncProducts()
    {
        if (!$this->apiUrl || !$this->apiKey) {
            throw new \Exception('API settings not configured');
        }
        
        $services = $this->sendRequest('services');
        
        if (!$services) {
            throw new \Exception($this->lastError ?? 'Failed to fetch services from API');
        }
        
        DB::beginTransaction();
        
        try {
            // Record current time for marking recently synced data
            $now = Carbon::now();
            
            // Sync categories
            $categories = [];
            
            foreach ($services as $service) {
                if (!isset($categories[$service['category']])) {
                    $category = ExternalServiceCategory::updateOrCreate(
                        ['name' => $service['category']],
                        [
                            'name' => $service['category'],
                            'name_en' => $service['category'],
                            'slug' => \Str::slug($service['category']),
                            'updated_at' => $now
                        ]
                    );
                    $categories[$service['category']] = $category->id;
                }
                
                // Sync services
                ExternalService::updateOrCreate(
                    ['external_id' => $service['service']],
                    [
                        'external_id' => $service['service'],
                        'category_id' => $categories[$service['category']],
                        'name' => $service['name'],
                        'description' => $service['description'] ?? '',
                        'price' => $service['rate'],
                        'required_fields' => json_encode([
                            'min' => $service['min'] ?? 1,
                            'max' => $service['max'] ?? 1,
                            'type' => $service['type'] ?? '',
                        ]),
                        'active' => 1,
                        'updated_at' => $now
                    ]
                );
            }
            
            // Optionally mark services not updated as inactive
            ExternalService::where('updated_at', '<', $now)->update(['active' => 0]);
            
            DB::commit();
            
            return [
                'success' => true,
                'total_services' => count($services),
                'categories' => count($categories)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync services', ['exception' => $e->getMessage()]);
            throw new \Exception('Failed to sync products: ' . $e->getMessage());
        }
    }
    
    /**
     * Update status for all processing orders
     *
     * @return array
     */
    public function updateAllOrderStatuses()
    {
        $processingOrders = Order::where('status', 'processing')
            ->whereNotNull('third_party_order_id')
            ->get();
        
        if ($processingOrders->isEmpty()) {
            return [
                'total' => 0,
                'updated' => 0,
                'completed' => 0
            ];
        }
        
        $results = [
            'total' => count($processingOrders),
            'updated' => 0,
            'completed' => 0,
            'failed' => 0
        ];
        
        foreach ($processingOrders as $order) {
            try {
                $statusResult = $this->getOrderStatus($order->third_party_order_id);
                
                if ($statusResult) {
                    $newStatus = $statusResult['status'];
                    $oldStatus = $order->status;
                    
                    if ($oldStatus !== $newStatus) {
                        $order->status = $newStatus;
                        $order->save();
                        
                        // If completed, set completed_at
                        if ($newStatus === 'completed' && !$order->completed_at) {
                            $order->completed_at = now();
                            $order->save();
                            $results['completed']++;
                        }
                        
                        // Log status change
                        OrderStatusLog::create([
                            'order_id' => $order->id,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                            'notes' => 'Status updated from third-party service',
                            'created_by' => 1 // System
                        ]);
                        
                        // Create order report
                        OrderReport::create([
                            'order_id' => $order->id,
                            'status' => $newStatus,
                            'report_data' => json_encode($statusResult['data']),
                            'source' => 'third_party',
                            'placed_at' => now()
                        ]);
                        
                        $results['updated']++;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to update order status', [
                    'order_id' => $order->id, 
                    'error' => $e->getMessage()
                ]);
                $results['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Map external status to local status
     *
     * @param string $externalStatus
     * @return string
     */
    protected function mapExternalStatusToLocal($externalStatus)
    {
        switch (strtolower($externalStatus)) {
            case 'pending':
                return 'pending';
            case 'in progress':
            case 'processing':
                return 'processing';
            case 'completed':
                return 'completed';
            case 'partial':
                return 'completed'; // Map partial to completed or handle differently
            case 'canceled':
            case 'cancelled':
                return 'canceled';
            case 'refunded':
                return 'refunded';
            default:
                return 'processing';
        }
    }
    
    /**
     * Get account balance
     *
     * @return float|bool
     */
    public function getBalance()
    {
        $result = $this->sendRequest('balance');
        
        if ($result && isset($result['balance'])) {
            return $result['balance'];
        }
        
        return false;
    }
}