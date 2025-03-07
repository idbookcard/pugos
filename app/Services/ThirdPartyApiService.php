<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ExternalService;
use App\Models\ExternalServiceCategory;
use App\Models\Order;
use Carbon\Carbon;

class SeoEstoreApiService
{
    protected $apiUrl;
    protected $apiKey;
    protected $lastError;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->apiUrl = config('services.seoestore.api_url', 'https://panel.seoestore.net/api/v1');
        $this->apiKey = config('services.seoestore.api_key');
    }

    /**
     * 获取最后的错误信息
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * 发送API请求
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
                $this->lastError = "API请求失败: " . $response->status() . " - " . $response->body();
                Log::error('SeoEstore API错误', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            $this->lastError = "API请求异常: " . $e->getMessage();
            Log::error('SeoEstore API异常', [
                'endpoint' => $endpoint,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 获取服务列表
     *
     * @return mixed
     */
    public function getServices()
    {
        return $this->sendRequest('services');
    }

    /**
     * 同步服务到本地数据库
     *
     * @return bool
     */
    public function syncServices()
    {
        $services = $this->getServices();
        
        if (!$services) {
            return false;
        }
        
        DB::beginTransaction();
        
        try {
            // 记录当前时间用于标记最新同步的数据
            $now = Carbon::now();
            
            // 同步分类
            $categories = [];
            
            foreach ($services as $service) {
                if (!isset($categories[$service['category']])) {
                    $category = ExternalServiceCategory::updateOrCreate(
                        ['name' => $service['category']],
                        [
                            'name' => $service['category'],
                            'name_zh' => $this->translateCategoryName($service['category']),
                            'updated_at' => $now
                        ]
                    );
                    $categories[$service['category']] = $category->id;
                }
                
                // 同步服务
                ExternalService::updateOrCreate(
                    ['external_id' => $service['service']],
                    [
                        'external_id' => $service['service'],
                        'category_id' => $categories[$service['category']],
                        'name' => $service['name'],
                        'name_zh' => '',  // 暂时为空，后续可以添加翻译逻辑
                        'description' => $service['description'] ?? '',
                        'description_zh' => '',  // 暂时为空，后续可以添加翻译逻辑
                        'price' => $service['rate'],
                        'min' => $service['min'] ?? 1,
                        'max' => $service['max'] ?? 1,
                        'type' => $service['type'] ?? '',
                        'active' => 1,
                        'last_sync' => $now
                    ]
                );
            }
            
            // 可选：将长时间未更新的服务标记为不活跃
            ExternalService::where('last_sync', '<', $now)->update(['active' => 0]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "同步服务失败: " . $e->getMessage();
            Log::error('同步服务失败', ['exception' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 简单的分类名称翻译功能
     * 实际项目中可以使用翻译API或预定义的翻译映射
     *
     * @param string $name
     * @return string
     */
    protected function translateCategoryName($name)
    {
        $translations = [
            'Social Media' => '社交媒体',
            'Social Signals' => '社交信号',
            'Web 2.0' => 'Web 2.0',
            'Forum Posting' => '论坛发帖',
            'Article Submission' => '文章提交',
            'Blog Comments' => '博客评论',
            'Profile Backlinks' => '个人资料反向链接',
            'Guest Posts' => '客座文章',
            'Other' => '其他'
        ];
        
        return $translations[$name] ?? $name;
    }

    /**
     * 提交订单到第三方平台
     *
     * @param Order $order
     * @return bool|array
     */
    public function placeOrder(Order $order)
    {
        if (!$order->external_service_id) {
            $this->lastError = "订单没有关联的外部服务ID";
            return false;
        }
        
        $externalService = ExternalService::find($order->external_service_id);
        if (!$externalService) {
            $this->lastError = "找不到关联的外部服务";
            return false;
        }
        
        $params = [
            'service' => $externalService->external_id,
            'link' => $order->target_url,
            'quantity' => $order->quantity
        ];
        
        // 添加可选参数
        if (!empty($order->anchor_text)) {
            $params['anchor'] = $order->anchor_text;
        }
        
        if (!empty($order->comments)) {
            $params['comments'] = $order->comments;
        }
        
        $result = $this->sendRequest('order', $params, 'POST');
        
        if ($result && isset($result['order'])) {
            // 更新订单的外部订单ID
            $order->external_order_id = $result['order'];
            $order->status = 'processing';
            $order->save();
            
            return [
                'external_order_id' => $result['order'],
                'status' => 'success'
            ];
        }
        
        return false;
    }

    /**
     * 自动提交待处理的订单
     *
     * @return array
     */
    public function autoSubmitPendingOrders()
    {
        $pendingOrders = Order::where('status', 'pending')
            ->where('external_service_id', '>', 0)
            ->whereNull('external_order_id')
            ->get();
        
        $results = [
            'total' => count($pendingOrders),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($pendingOrders as $order) {
            $result = $this->placeOrder($order);
            
            if ($result) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'order_id' => $order->id,
                    'error' => $this->getLastError()
                ];
            }
        }
        
        return $results;
    }

    /**
     * 获取订单状态
     *
     * @param string|int $orderId 外部订单ID
     * @return mixed
     */
    public function getOrderStatus($orderId)
    {
        $result = $this->sendRequest('status', ['order' => $orderId]);
        
        if ($result && isset($result['status'])) {
            return $result['status'];
        }
        
        return false;
    }

    /**
     * 批量获取订单状态
     *
     * @param array $orderIds 外部订单ID数组
     * @return mixed
     */
    public function getMultipleOrderStatus($orderIds)
    {
        if (empty($orderIds)) {
            return [];
        }
        
        $result = $this->sendRequest('status', ['orders' => implode(',', $orderIds)]);
        
        if ($result && isset($result['status'])) {
            return $result['status'];
        }
        
        return false;
    }

    /**
     * 更新所有处理中订单的状态
     *
     * @return array
     */
    public function updateOrderStatuses()
    {
        $processingOrders = Order::where('status', 'processing')
            ->whereNotNull('external_order_id')
            ->get();
        
        if ($processingOrders->isEmpty()) {
            return [
                'total' => 0,
                'updated' => 0,
                'completed' => 0
            ];
        }
        
        $externalOrderIds = $processingOrders->pluck('external_order_id')->toArray();
        $statusResults = $this->getMultipleOrderStatus($externalOrderIds);
        
        $results = [
            'total' => count($processingOrders),
            'updated' => 0,
            'completed' => 0
        ];
        
        if (!$statusResults) {
            return $results;
        }
        
        foreach ($processingOrders as $order) {
            if (isset($statusResults[$order->external_order_id])) {
                $externalStatus = $statusResults[$order->external_order_id];
                $newStatus = $this->mapExternalStatusToLocal($externalStatus);
                
                if ($order->status !== $newStatus) {
                    $order->status = $newStatus;
                    $order->last_status_update = Carbon::now();
                    $order->save();
                    
                    $results['updated']++;
                    
                    if ($newStatus === 'completed') {
                        $results['completed']++;
                    }
                }
            }
        }
        
        return $results;
    }

    /**
     * 将外部状态映射到本地状态
     *
     * @param string $externalStatus
     * @return string
     */
    protected function mapExternalStatusToLocal($externalStatus)
    {
        switch ($externalStatus) {
            case 'Pending':
                return 'pending';
            case 'In progress':
            case 'Processing':
                return 'processing';
            case 'Completed':
                return 'completed';
            case 'Partial':
                return 'partial';
            case 'Canceled':
            case 'Cancelled':
                return 'cancelled';
            case 'Refunded':
                return 'refunded';
            default:
                return 'processing';
        }
    }

    /**
     * 获取余额
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