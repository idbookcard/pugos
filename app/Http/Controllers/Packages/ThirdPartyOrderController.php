<?php

namespace App\Http\Controllers\Packages;

use App\Http\Controllers\Controller;
use App\Models\BacklinkPackage;
use App\Models\Order;
use App\Services\ThirdPartyApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ThirdPartyOrderController extends Controller
{
    protected $apiService;
    
    public function __construct(ThirdPartyApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * 显示第三方服务订单创建页面
     */
    public function create(BacklinkPackage $package)
    {
        // 检查是否为第三方服务包
        if ($package->package_type !== 'third_party') {
            return redirect()->route('packages.show', $package)->with('error', '该服务不是自助外链服务');
        }
        
        if (!$package->is_active) {
            return redirect()->route('packages.third-party')->with('error', '该服务套餐不可用');
        }
        
        // 从本地数据库获取原始数据
        $originalData = json_decode($package->original_item_data, true) ?: [];
        
        // 从本地数据库获取额外服务列表
        $extras = $this->apiService->getLocalExtras();
        
        // 从本地数据库获取文章分类列表
        $articleCategories = $this->apiService->getLocalArticleCategories();
        
        return view('packages.third-party-create', compact('package', 'originalData', 'extras', 'articleCategories'));
    }
    
    /**
     * 处理第三方服务订单提交
     */
    public function store(Request $request, BacklinkPackage $package)
    {
        // 检查是否为第三方服务包
        if ($package->package_type !== 'third_party') {
            return redirect()->route('packages.show', $package)->with('error', '该服务不是自助外链服务');
        }
        
        // 验证请求数据
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'links' => 'required|string',
            'keywords' => 'required|string',
            'extras' => 'nullable|array',
            'extras.*' => 'integer',
            'article' => 'nullable|string',
            'enable_tier' => 'nullable|boolean',
            'tier_orders' => 'nullable|string|required_if:enable_tier,1',
            'ref_id' => 'nullable|string|max:15',
            'notes' => 'nullable|string',
            'accept_terms' => 'required|accepted',
        ]);
        
        // 获取原始数据中的最小数量
        $originalData = json_decode($package->original_item_data, true) ?: [];
        $minQuantity = $originalData['min_quantity'] ?? 1;
        
        // 检查数量是否满足最小要求
        if ($validated['quantity'] < $minQuantity) {
            return redirect()->back()->withInput()->with('error', "数量不能少于 {$minQuantity}");
        }
        
        // 计算订单总价
        $totalPrice = $package->price * $validated['quantity'];
        
        // 计算额外服务费用
        $extrasPrice = 0;
        $extrasData = [];
        if (!empty($validated['extras'])) {
            $allExtras = $this->apiService->getLocalExtras();
            $selectedExtras = [];
            
            foreach ($validated['extras'] as $extraId) {
                foreach ($allExtras as $extra) {
                    if ($extra['id'] == $extraId) {
                        $extraPrice = $extra['price'] * 6.9; // 美元转人民币，汇率约为6.9
                        $extrasPrice += $extraPrice;
                        $selectedExtras[] = $extraId;
                        break;
                    }
                }
            }
            
            $extrasData = [
                'ids' => $selectedExtras,
                'price' => $extrasPrice
            ];
        }
        
        // 添加额外服务费用到总价
        $totalPrice += $extrasPrice;
        
        // 检查用户余额是否足够
        $user = Auth::user();
        if ($user->balance < $totalPrice) {
            return redirect()->route('customer.wallet')
                ->with('error', '余额不足，请先充值');
        }
        
        // 处理链接和关键词格式
        $links = explode("\n", str_replace("\r\n", "\n", trim($validated['links'])));
        $links = array_filter($links, function($link) {
            return !empty(trim($link));
        });
        
        $keywords = explode(",", $validated['keywords']);
        $keywords = array_map('trim', $keywords);
        $keywords = array_filter($keywords, function($keyword) {
            return !empty($keyword);
        });
        
        // 确保链接和关键词数量匹配
        if (count($links) > count($keywords)) {
            // 重复最后一个关键词以匹配链接数量
            $lastKeyword = end($keywords);
            while (count($keywords) < count($links)) {
                $keywords[] = $lastKeyword;
            }
        } elseif (count($keywords) > count($links)) {
            // 只使用前N个关键词，其中N是链接数量
            $keywords = array_slice($keywords, 0, count($links));
        }
        
        try {
            // 开始数据库事务
            \DB::beginTransaction();
            
            // 创建订单
            $order = new Order();
            $order->user_id = $user->id;
            $order->package_id = $package->id;
            $order->status = 'pending'; // 初始状态为待审核
            $order->third_party_status = 'pending_approval'; // 标记为等待审核
            $order->target_url = $links[0]; // 主要目标URL
            $order->keywords = implode(', ', $keywords);
            $order->notes = $validated['notes'];
            $order->total_price = $totalPrice;
            $order->paid_at = now();
            
            // 保存额外的订单数据
            $orderData = [
                'service_id' => $package->third_party_service_id,
                'quantity' => $validated['quantity'],
                'links' => $links,
                'extras' => $extrasData,
                'article' => $validated['article'] ?? null,
                'ref_id' => $validated['ref_id'] ?? null,
                'sent_to_third_party' => false, // 标记为尚未发送到第三方
            ];
            
            // 添加层级信息（如果启用）
            if (!empty($validated['enable_tier']) && $validated['enable_tier'] == 1) {
                $orderData['tier'] = 1;
                $orderData['tier_orders'] = array_map('trim', explode(',', $validated['tier_orders']));
            }
            
            $order->order_data = json_encode($orderData);
            $order->save();
            
            // 扣除用户余额
            $user->balance -= $totalPrice;
            $user->save();
            
            // 记录交易
            $user->transactions()->create([
                'amount' => -$totalPrice,
                'type' => 'payment',
                'description' => "支付订单 #{$order->id} - {$package->name}",
                'status' => 'completed',
                'reference_id' => $order->id,
            ]);
            
            \DB::commit();
            
            // 订单成功，重定向到订单成功页面
            return redirect()->route('customer.orders.success', $order)
                ->with('success', '订单已成功提交，我们将尽快为您处理');
            
        } catch (\Exception $e) {
            // 发生异常，回滚事务
            \DB::rollBack();
            Log::error('第三方订单处理异常', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            return redirect()->back()->withInput()
                ->with('error', '订单处理过程中发生错误：' . $e->getMessage());
        }
    }
}