<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ThirdPartyApiSettings;
use App\Services\ThirdPartyApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThirdPartyOrderController extends Controller
{
    protected $apiService;
    
    public function __construct(ThirdPartyApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * 显示待审核的第三方订单列表
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'package'])
            ->whereHas('package', function($q) {
                $q->where('package_type', 'third_party');
            })
            ->whereRaw("(third_party_status = 'pending_approval' OR JSON_CONTAINS_PATH(order_data, 'one', '$.sent_to_third_party') = 1 AND JSON_EXTRACT(order_data, '$.sent_to_third_party') = false)");
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('target_url', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%")
                  ->orWhereHas('user', function($subq) use ($search) {
                      $subq->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $pendingOrders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // 获取自动发送状态
        $settings = ThirdPartyApiSettings::where('name', 'seoestore')->first();
        $autoSendEnabled = $settings ? $settings->settings->auto_send_enabled ?? false : false;
        
        return view('admin.third-party-orders.index', compact('pendingOrders', 'autoSendEnabled'));
    }
    
    /**
     * 显示订单详情
     */
    public function show(Order $order)
    {
        // 检查是否为第三方订单
        if ($order->package->package_type !== 'third_party') {
            return redirect()->route('admin.orders.show', $order);
        }
        
        // 获取额外服务信息
        $orderData = json_decode($order->order_data, true) ?: [];
        $extras = [];
        
        if (!empty($orderData['extras']) && !empty($orderData['extras']['ids'])) {
            $allExtras = $this->apiService->getLocalExtras();
            foreach ($orderData['extras']['ids'] as $extraId) {
                foreach ($allExtras as $extra) {
                    if ($extra['id'] == $extraId) {
                        $extras[] = $extra;
                        break;
                    }
                }
            }
        }
        
        return view('admin.third-party-orders.show', compact('order', 'orderData', 'extras'));
    }
    
    /**
     * 审核并发送订单到第三方平台
     */
    public function approve(Request $request, Order $order)
    {
        // 检查是否为第三方订单
        if ($order->package->package_type !== 'third_party') {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', '该订单不是第三方API订单');
        }
        
        // 检查订单状态
        $orderData = json_decode($order->order_data, true) ?: [];
        $sentToThirdParty = $orderData['sent_to_third_party'] ?? false;
        
        if ($sentToThirdParty) {
            return redirect()->route('admin.third-party-orders.show', $order)
                ->with('error', '该订单已发送到第三方平台');
        }
        
        // 发送订单到第三方API
        $result = $this->sendOrderToThirdParty($order);
        
        if ($result['success']) {
            return redirect()->route('admin.third-party-orders.show', $order)
                ->with('success', '订单已成功发送到第三方平台');
        } else {
            return redirect()->route('admin.third-party-orders.show', $order)
                ->with('error', '发送订单失败: ' . $result['message']);
        }
    }
    
    /**
     * 批量审核并发送订单
     */
    public function bulkApprove(Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        if (empty($orderIds)) {
            return redirect()->route('admin.third-party-orders.index')
                ->with('error', '未选择任何订单');
        }
        
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        
        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            if (!$order || $order->package->package_type !== 'third_party') {
                $failedCount++;
                continue;
            }
            
            $orderData = json_decode($order->order_data, true) ?: [];
            $sentToThirdParty = $orderData['sent_to_third_party'] ?? false;
            
            if ($sentToThirdParty) {
                $failedCount++;
                continue;
            }
            
            $result = $this->sendOrderToThirdParty($order);
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
                $errors[] = "订单 #{$order->id}: {$result['message']}";
            }
        }
        
        $message = "处理完成: {$successCount} 个订单成功, {$failedCount} 个订单失败";
        if (!empty($errors)) {
            $message .= "。错误信息: " . implode('; ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= "...(更多错误)";
            }
        }
        
        return redirect()->route('admin.third-party-orders.index')
            ->with($failedCount > 0 ? 'warning' : 'success', $message);
    }
    
    /**
     * 拒绝订单
     */
    public function reject(Request $request, Order $order)
    {
        $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);
        
        // 检查是否为第三方订单
        if ($order->package->package_type !== 'third_party') {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', '该订单不是第三方API订单');
        }
        
        try {
            // 更新订单状态
            $order->status = 'canceled';
            $order->third_party_status = 'rejected';
            
            // 更新订单数据
            $orderData = json_decode($order->order_data, true) ?: [];
            $orderData['reject_reason'] = $request->reject_reason;
            $orderData['rejected_at'] = now()->toDateTimeString();
            $orderData['rejected_by'] = auth()->user()->id;
            $order->order_data = json_encode($orderData);
            
            $order->save();
            
            // 退款给用户
            $user = $order->user;
            $user->balance += $order->total_price;
            $user->save();
            
            // 记录交易
            $user->transactions()->create([
                'amount' => $order->total_price,
                'type' => 'refund',
                'description' => "订单 #{$order->id} 被拒绝退款: {$request->reject_reason}",
                'status' => 'completed',
                'reference_id' => $order->id,
            ]);
            
            return redirect()->route('admin.third-party-orders.index')
                ->with('success', '订单已拒绝，金额已退回用户账户');
        } catch (\Exception $e) {
            Log::error('拒绝订单异常', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return redirect()->route('admin.third-party-orders.show', $order)
                ->with('error', '处理订单时发生错误: ' . $e->getMessage());
        }
    }
    
    /**
     * 更新自动发送设置
     */
    public function updateAutoSend(Request $request)
    {
        $autoSendEnabled = $request->has('auto_send_enabled');
        
        $settings = ThirdPartyApiSettings::where('name', 'seoestore')->first();
        if ($settings) {
            $settingsData = json_decode($settings->settings, true) ?: [];
            $settingsData['auto_send_enabled'] = $autoSendEnabled;
            $settings->settings = json_encode($settingsData);
            $settings->save();
            
            return redirect()->route('admin.third-party-orders.index')
                ->with('success', '自动发送设置已' . ($autoSendEnabled ? '启用' : '禁用'));
        }
        
        return redirect()->route('admin.third-party-orders.index')
            ->with('error', '更新设置失败: 找不到API设置');
    }
    
    /**
     * 自动发送待审核订单（由计划任务调用）
     */
    public function autoSendPendingOrders()
    {
        // 检查自动发送是否启用
        $settings = ThirdPartyApiSettings::where('name', 'seoestore')->first();
        if (!$settings || empty($settings->settings->auto_send_enabled)) {
            Log::info('自动发送订单已禁用');
            return;
        }
        
        // 获取所有待审核的第三方订单
        $pendingOrders = Order::with('package')
            ->whereHas('package', function($q) {
                $q->where('package_type', 'third_party');
            })
            ->whereRaw("(third_party_status = 'pending_approval' OR JSON_CONTAINS_PATH(order_data, 'one', '$.sent_to_third_party') = 1 AND JSON_EXTRACT(order_data, '$.sent_to_third_party') = false)")
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit(50) // 每次处理的最大数量
            ->get();
        
        $successCount = 0;
        $failedCount = 0;
        
        foreach ($pendingOrders as $order) {
            $result = $this->sendOrderToThirdParty($order);
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
                Log::error('自动发送订单失败', [
                    'order_id' => $order->id,
                    'error' => $result['message']
                ]);
            }
            
            // 添加延迟以避免API速率限制
            sleep(1);
        }
        
        Log::info("自动发送订单完成: {$successCount} 个成功, {$failedCount} 个失败");
    }
    
    /**
     * 发送订单到第三方平台
     */
    private function sendOrderToThirdParty(Order $order)
    {
        try {
            // 发送订单到第三方API
            $apiResponse = $this->apiService->placeThirdPartyOrder($order);
            
            if ($apiResponse && isset($apiResponse['order_id'])) {
                // 更新订单状态和第三方订单ID
                $order->third_party_order_id = $apiResponse['order_id'];
                $order->third_party_status = $apiResponse['status'] ?? 'processing';
                $order->status = 'processing';
                
                // 更新订单数据，标记为已发送
                $orderData = json_decode($order->order_data, true) ?: [];
                $orderData['sent_to_third_party'] = true;
                $orderData['sent_at'] = now()->toDateTimeString();
                $orderData['sent_by'] = auth()->user()->id;
                $order->order_data = json_encode($orderData);
                
                $order->save();
                
                return [
                    'success' => true,
                    'message' => '订单已成功发送到第三方平台'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '第三方API响应错误: ' . json_encode($apiResponse)
                ];
            }
        } catch (\Exception $e) {
            Log::error('发送订单到第三方平台异常', [
                'order_id' => $order->id, 
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => '发送订单时发生异常: ' . $e->getMessage()
            ];
        }
    }
}