<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Package;
use App\Models\ExternalService;
use App\Models\GuestPostSite;
use Carbon\Carbon;

class OrderService
{
    protected $seoEstoreApiService;
    protected $guestPostService;
    protected $lastError;

    /**
     * 构造函数
     *
     * @param SeoEstoreApiService $seoEstoreApiService
     * @param GuestPostService $guestPostService
     */
    public function __construct(SeoEstoreApiService $seoEstoreApiService, GuestPostService $guestPostService)
    {
        $this->seoEstoreApiService = $seoEstoreApiService;
        $this->guestPostService = $guestPostService;
    }

    /**
     * 获取最后的错误信息
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * 创建套餐订单
     *
     * @param array $orderData
     * @return bool|array
     */
    public function createPackageOrder($orderData)
    {
        $package = Package::find($orderData['package_id']);
        
        if (!$package) {
            $this->lastError = "找不到指定套餐";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            $order = new Order();
            $order->user_id = $orderData['user_id'];
            $order->service_type = 'package';
            $order->package_id = $package->id;
            $order->target_url = $orderData['target_url'];
            $order->keywords = $orderData['keywords'] ?? '';
            $order->anchor_text = $orderData['anchor_text'] ?? '';
            $order->comments = $orderData['comments'] ?? '';
            $order->price = $package->price;
            $order->quantity = 1;
            $order->status = 'pending';
            $order->payment_status = $orderData['payment_status'] ?? 'pending';
            $order->order_date = Carbon::now();
            $order->save();
            
            DB::commit();
            
            return [
                'success' => true,
                'order_id' => $order->id,
                'message' => '订单已提交成功，等待支付'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "创建订单失败: " . $e->getMessage();
            Log::error('创建套餐订单失败', [
                'exception' => $e->getMessage(),
                'order_data' => $orderData
            ]);
            return false;
        }
    }

    /**
     * 创建外部服务订单
     *
     * @param array $orderData
     * @return bool|array
     */
    public function createExternalServiceOrder($orderData)
    {
        $service = ExternalService::find($orderData['external_service_id']);
        
        if (!$service) {
            $this->lastError = "找不到指定服务";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            $order = new Order();
            $order->user_id = $orderData['user_id'];
            $order->service_type = 'external';
            $order->external_service_id = $service->id;
            $order->target_url = $orderData['target_url'];
            $order->quantity = $orderData['quantity'] ?? 1;
            $order->anchor_text = $orderData['anchor_text'] ?? '';
            $order->comments = $orderData['comments'] ?? '';
            $order->price = $service->price * $order->quantity;
            $order->status = 'pending';
            $order->payment_status = $orderData['payment_status'] ?? 'pending';
            $order->order_date = Carbon::now();
            $order->save();
            
            DB::commit();
            
            return [
                'success' => true,
                'order_id' => $order->id,
                'message' => '订单已提交成功，等待支付'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "创建订单失败: " . $e->getMessage();
            Log::error('创建外部服务订单失败', [
                'exception' => $e->getMessage(),
                'order_data' => $orderData
            ]);
            return false;
        }
    }

    /**
     * 创建Guest Post订单
     *
     * @param array $orderData
     * @return bool|array
     */
    public function createGuestPostOrder($orderData)
    {
        return $this->guestPostService->placeGuestPostOrder(
            $orderData['guest_post_site_id'],
            $orderData
        );
    }

    /**
     * 处理订单支付
     *
     * @param int $orderId
     * @param array $paymentData
     * @return bool|array
     */
    public function processPayment($orderId, $paymentData)
    {
        $order = Order::find($orderId);
        
        if (!$order) {
            $this->lastError = "找不到指定订单";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            // 更新支付状态
            $order->payment_status = 'paid';
            $order->payment_method = $paymentData['payment_method'];
            $order->payment_id = $paymentData['payment_id'] ?? null;
            $order->paid_amount = $paymentData['amount'];
            $order->payment_date = Carbon::now();
            $order->save();
            
            // 根据设置决定是否自动提交到第三方
            $autoSubmit = config('services.order.auto_submit_after_payment', false);
            
            if ($autoSubmit && $order->service_type === 'external') {
                $this->submitExternalOrder($order->id);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'order_id' => $order->id,
                'message' => '支付处理成功'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "处理支付失败: " . $e->getMessage();
            Log::error('处理订单支付失败', [
                'exception' => $e->getMessage(),
                'order_id' => $orderId,
                'payment_data' => $paymentData
            ]);
            return false;
        }
    }

    /**
     * 提交外部订单到第三方平台
     *
     * @param int $orderId
     * @return bool|array
     */
    public function submitExternalOrder($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('service_type', 'external')
            ->where('payment_status', 'paid')
            ->whereNull('external_order_id')
            ->first();
        
        if (!$order) {
            $this->lastError = "找不到可提交的订单";
            return false;
        }
        
        $result = $this->seoEstoreApiService->placeOrder($order);
        
        if (!$result) {
            $this->lastError = $this->seoEstoreApiService->getLastError();
            return false;
        }
        
        return [
            'success' => true,
            'order_id' => $order->id,
            'external_order_id' => $result['external_order_id'],
            'message' => '订单已成功提交到第三方平台'
        ];
    }

    /**
     * 更新订单状态
     *
     * @param int $orderId
     * @param string $status
     * @param string $notes
     * @return bool|array
     */
    public function updateOrderStatus($orderId, $status, $notes = '')
    {
        $order = Order::find($orderId);
        
        if (!$order) {
            $this->lastError = "找不到指定订单";
            return false;
        }
        
        try {
            $oldStatus = $order->status;
            $order->status = $status;
            $order->admin_notes = $notes;
            $order->last_status_update = Carbon::now();
            $order->save();
            
            // 记录状态变更
            $this->logStatusChange($order, $oldStatus, $status, $notes);
            
            return [
                'success' => true,
                'order_id' => $order->id,
                'message' => '订单状态已更新'
            ];
        } catch (\Exception $e) {
            $this->lastError = "更新订单状态失败: " . $e->getMessage();
            Log::error('更新订单状态失败', [
                'exception' => $e->getMessage(),
                'order_id' => $orderId,
                'status' => $status
            ]);
            return false;
        }
    }

    /**
     * 记录状态变更
     *
     * @param Order $order
     * @param string $oldStatus
     * @param string $newStatus
     * @param string $notes
     * @return void
     */
    protected function logStatusChange($order, $oldStatus, $newStatus, $notes)
    {
        \App\Models\OrderStatusLog::create([
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'created_by' => auth()->id() ?? 0,
            'created_at' => Carbon::now()
        ]);
    }

    /**
     * 获取用户订单列表
     *
     * @param int $userId
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserOrders($userId, $filters = [], $perPage = 10)
    {
        $query = Order::where('user_id', $userId);
        
        // 应用筛选条件
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('order_date', '>=', Carbon::parse($filters['date_from']));
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('order_date', '<=', Carbon::parse($filters['date_to']));
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('target_url', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%");
            });
        }
        
        // 默认按时间倒序
        $query->orderBy('order_date', 'desc');
        
        return $query->paginate($perPage);
    }

    /**
     * 获取所有订单列表(管理员用)
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllOrders($filters = [], $perPage = 20)
    {
        $query = Order::query();
        
        // 应用筛选条件
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        
        if (!empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('order_date', '>=', Carbon::parse($filters['date_from']));
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('order_date', '<=', Carbon::parse($filters['date_to']));
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('target_url', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // 默认按时间倒序
        $query->orderBy('order_date', 'desc');
        
        return $query->paginate($perPage);
    }

    /**
     * 取消订单
     *
     * @param int $orderId
     * @param string $reason
     * @param int $userId
     * @return bool|array
     */
    public function cancelOrder($orderId, $reason, $userId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();
        
        if (!$order) {
            $this->lastError = "找不到可取消的订单";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            $oldStatus = $order->status;
            $order->status = 'cancelled';
            $order->cancellation_reason = $reason;
            $order->cancelled_at = Carbon::now();
            $order->save();
            
            // 记录状态变更
            $this->logStatusChange($order, $oldStatus, 'cancelled', $reason);
            
            // 如果已支付，创建退款记录
            if ($order->payment_status === 'paid') {
                $order->refund_status = 'pending';
                $order->save();
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'order_id' => $order->id,
                'message' => '订单已成功取消'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "取消订单失败: " . $e->getMessage();
            Log::error('取消订单失败', [
                'exception' => $e->getMessage(),
                'order_id' => $orderId,
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * 获取订单详情
     *
     * @param int $orderId
     * @param int|null $userId
     * @return bool|array
     */
    public function getOrderDetails($orderId, $userId = null)
    {
        $query = Order::with(['user', 'statusLogs']);
        
        // 如果提供了用户ID，检查订单所有权
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $order = $query->find($orderId);
        
        if (!$order) {
            $this->lastError = "找不到指定订单";
            return false;
        }
        
        // 根据订单类型加载关联信息
        if ($order->service_type === 'package') {
            $order->load('package');
        } elseif ($order->service_type === 'external') {
            $order->load('externalService');
        } elseif ($order->service_type === 'guest_post') {
            $order->load('guestPostSite');
        }
        
        // 如果是外部订单且有外部订单ID，获取最新状态
        if ($order->service_type === 'external' && $order->external_order_id) {
            $externalStatus = $this->seoEstoreApiService->getOrderStatus($order->external_order_id);
            if ($externalStatus) {
                $order->external_status = $externalStatus;
            }
        }
        
        return [
            'order' => $order,
            'status_history' => $order->statusLogs
        ];
    }

    /**
     * 批量更新外部订单状态
     *
     * @return array
     */
    public function updateExternalOrderStatuses()
    {
        return $this->seoEstoreApiService->updateOrderStatuses();
    }

    /**
     * 处理订单退款
     *
     * @param int $orderId
     * @param array $refundData
     * @return bool|array
     */
    public function processRefund($orderId, $refundData)
    {
        $order = Order::where('id', $orderId)
            ->where('payment_status', 'paid')
            ->where('refund_status', 'pending')
            ->first();
        
        if (!$order) {
            $this->lastError = "找不到可退款的订单";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            // 更新退款状态
            $order->refund_status = 'completed';
            $order->refund_amount = $refundData['amount'];
            $order->refund_notes = $refundData['notes'] ?? '';
            $order->refunded_at = Carbon::now();
            $order->save();
            
            // 如果订单状态不是已取消，则更新为已退款
            if ($order->status !== 'cancelled') {
                $oldStatus = $order->status;
                $order->status = 'refunded';
                $order->save();
                
                // 记录状态变更
                $this->logStatusChange($order, $oldStatus, 'refunded', $refundData['notes'] ?? '');
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'order_id' => $order->id,
                'message' => '订单退款已处理成功'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "处理退款失败: " . $e->getMessage();
            Log::error('处理订单退款失败', [
                'exception' => $e->getMessage(),
                'order_id' => $orderId,
                'refund_data' => $refundData
            ]);
            return false;
        }
    }
}