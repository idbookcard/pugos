<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Package;
use App\Models\ApiOrder;
use App\Services\AccountService;
use App\Services\SEOeStoreApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $accountService;
    protected $apiService;
    
    public function __construct(AccountService $accountService, SEOeStoreApiService $apiService)
    {
        $this->accountService = $accountService;
        $this->apiService = $apiService;
    }
    
    /**
     * 创建新订单
     */
    public function createOrder($userId, $packageId, $data)
    {
        $package = Package::findOrFail($packageId);
        $quantity = $data['quantity'] ?? 1;
        $totalAmount = $package->price * $quantity;
        
        // 验证余额
        if (!$this->accountService->checkBalance($userId, $totalAmount)) {
            throw new \Exception('账户余额不足');
        }
        
        DB::beginTransaction();
        try {
            // 创建订单
            $order = Order::create([
                'user_id' => $userId,
                'package_id' => $packageId,
                'order_number' => $this->generateOrderNumber(),
                'service_type' => $this->determineServiceType($package),
                'status' => 'pending',
                'payment_status' => 'unpaid', // 先设为未支付
                'total_amount' => $totalAmount,
                'target_url' => $data['target_url'],
                'keywords' => $data['keywords'] ?? null,
                'article' => $data['article'] ?? null,
                'extra_data' => isset($data['extras']) ? json_encode($data['extras']) : null,
                'order_date' => now()
            ]);
            
            // 记录订单状态
            \App\Models\OrderStatusLog::create([
                'order_id' => $order->id,
                'new_status' => 'pending',
                'notes' => '订单创建',
                'created_by' => $userId
            ]);
            
            // 扣减余额
            $this->accountService->processConsumption($userId, $totalAmount, $order->id);
            
            // 更新订单为已支付
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now()
            ]);
            
           
// 如果是第三方API产品，检查是否需要自动提交
if ($package->isThirdParty() && $package->third_party_id) {
    // 创建API订单记录
    $apiOrder = ApiOrder::create([
        'order_id' => $order->id
    ]);
    
    // 更新订单状态为处理中
    $order->update(['status' => 'processing']);
    
    // 记录订单状态变更
    \App\Models\OrderStatusLog::create([
        'order_id' => $order->id,
        'old_status' => 'pending',
        'new_status' => 'processing',
        'notes' => '自动提交到API处理',
        'created_by' => 0 // 系统操作
    ]);
    
    // 提交API订单
    $this->processApiOrder($order->id);
}

DB::commit();
return $order;
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('创建订单失败: ' . $e->getMessage());
    throw $e;
}
}
    
/**
 * 处理API订单
 */
public function processApiOrder($orderId)
{
    $order = Order::with(['apiOrder'])->findOrFail($orderId);
    
    // 检查是否为第三方产品
    if ($order->service_type !== 'external' && $order->service_type !== 'package') {
        throw new \Exception('非API产品订单');
    }
    
    // 获取包裹信息
    $package = Package::find($order->package_id);
    if (!$package || !$package->third_party_id) {
        throw new \Exception('API产品信息不存在');
    }
    
    // 检查是否已有API订单记录
    if (!$order->apiOrder) {
        // 创建API订单记录
        $apiOrder = ApiOrder::create([
            'order_id' => $order->id
        ]);
    } else {
        $apiOrder = $order->apiOrder;
    }
    
    // 准备API订单数据
    $apiOrderData = [
        'target_url' => $order->target_url,
        'keywords' => $order->keywords,
        'article' => $order->article,
        'extras' => json_decode($order->extra_data, true),
        'quantity' => 1,
    ];
    
    // 调用API创建订单
    $result = $this->apiService->createOrder(
        $package->third_party_id,
        $apiOrderData
    );
    
    // 更新ApiOrder记录
    $apiOrder->update([
        'api_order_id' => $result['order_id'],
        'api_status' => $result['success'] ? 'submitted' : 'failed',
        'api_response' => json_encode($result['response']),
        'submitted_at' => now(),
    ]);
    
    // 如果API提交失败，记录错误并更新订单状态
    if (!$result['success']) {
        Log::error('API订单提交失败: ' . ($result['message'] ?? '未知错误') . ' 订单ID: ' . $order->id);
        
        // 记录状态日志
        \App\Models\OrderStatusLog::create([
            'order_id' => $order->id,
            'old_status' => $order->status,
            'new_status' => $order->status, // 状态不变
            'notes' => 'API提交失败: ' . ($result['message'] ?? '未知错误'),
            'created_by' => 0 // 系统操作
        ]);
        
        return false;
    }
    
    return true;
}

/**
 * 同步API订单状态
 */
public function syncApiOrderStatus($apiOrderId = null)
{
    // 如果提供了特定的API订单ID，只同步该订单
    if ($apiOrderId) {
        $apiOrders = ApiOrder::where('id', $apiOrderId)->get();
    } else {
        // 否则同步所有处理中的API订单
        $apiOrders = ApiOrder::whereNotNull('api_order_id')
            ->whereNull('completed_at')
            ->get();
    }
    
    $updated = 0;
    $completed = 0;
    
    foreach ($apiOrders as $apiOrder) {
        // 获取API订单状态
        $result = $this->apiService->getOrderStatus($apiOrder->api_order_id);
        
        if (!$result['success']) {
            continue;
        }
        
        // 更新API订单状态
        $apiOrder->api_status = $result['status'];
        
        // 如果订单已完成
        if ($result['status'] === 'completed') {
            $apiOrder->completed_at = now();
            $completed++;
            
            // 更新主订单状态
            $order = Order::find($apiOrder->order_id);
            if ($order) {
                $oldStatus = $order->status;
                $order->status = 'completed';
                $order->completed_at = now();
                $order->save();
                
                // 创建订单报告
                \App\Models\OrderReport::create([
                    'order_id' => $order->id,
                    'status' => 'completed',
                    'report_data' => json_encode($result['response']),
                    'source' => 'api',
                    'placed_at' => now()
                ]);
                
                // 记录状态变更
                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'completed',
                    'notes' => 'API订单完成',
                    'created_by' => 0 // 系统操作
                ]);
            }
        }
        
        $apiOrder->save();
        $updated++;
    }
    
    return [
        'updated' => $updated,
        'completed' => $completed
    ];
}

/**
 * 生成订单号
 */
protected function generateOrderNumber()
{
    $prefix = date('Ymd');
    $suffix = mt_rand(1000, 9999);
    
    $orderNumber = $prefix . $suffix;
    
    // 确保订单号唯一
    while (Order::where('order_number', $orderNumber)->exists()) {
        $suffix = mt_rand(1000, 9999);
        $orderNumber = $prefix . $suffix;
    }
    
    return $orderNumber;
}

/**
 * 确定服务类型
 */
protected function determineServiceType($package)
{
    switch ($package->package_type) {
        case 'third_party':
            return 'external';
        case 'guest_post':
            return 'guest_post';
        default:
            return 'package';
    }
}