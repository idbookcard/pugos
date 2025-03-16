<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use App\Models\ExtraOption;
use App\Services\OrderService;
use App\Services\AccountService;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;

class OrderController extends Controller
{
    protected $orderService;
    protected $accountService;
    
    public function __construct(OrderService $orderService, AccountService $accountService)
    {
        $this->orderService = $orderService;
        $this->accountService = $accountService;
        
        // 只对特定方法应用auth中间件
        $this->middleware('auth')->only(['index', 'store', 'show']);
    }
    
    /**
     * 显示用户订单列表
     */
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('orders.index', compact('orders'));
    }
    
    /**
     * 显示创建订单页面
     * 未登录用户也可访问此页面
     */
    public function create($slug)
    {
        $package = Package::where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();
            
        // 获取用户余额信息（如果已登录）
        $balance = 0;
        $sufficientBalance = false;
        
        if (auth()->check()) {
            $user = auth()->user();
            $balance = $user->total_balance;
            $sufficientBalance = $balance >= $package->price;
        }
        
        // 检查包含额外选项
        $hasExtras = !empty($package->available_extras);
            
        return view('orders.create', compact('package', 'balance', 'sufficientBalance', 'hasExtras'));
    }
    
    /**
     * 处理订单创建
     * 需要登录
     */
    public function store(OrderRequest $request)
    {
        $packageId = $request->input('package_id');
        $package = Package::findOrFail($packageId);
        
        // 计算总价格（包括额外选项）
        $totalPrice = $package->price;
        $selectedExtras = [];
        
        // 处理多选额外选项
        if ($request->has('extras') && is_array($request->input('extras'))) {
            foreach ($request->input('extras') as $extraId => $value) {
                // 查找extras中对应的选项
                if (!empty($package->available_extras)) {
                    $availableExtras = is_array($package->available_extras) ? 
                        $package->available_extras : 
                        json_decode($package->available_extras, true);
                    
                    foreach ($availableExtras as $extra) {
                        if ($extra['id'] == $extraId) {
                            $extraPrice = floatval($extra['price']) * 7.4 / 100 * 1.5; // 转换为人民币并加价
                            $totalPrice += $extraPrice;
                            
                            $selectedExtras[] = [
                                'id' => $extra['id'],
                                'code' => $extra['code'],
                                'name' => $extra['name'],
                                'price' => $extraPrice
                            ];
                            
                            break;
                        }
                    }
                }
            }
        }
        
        // 处理单选额外选项
        if ($request->has('extras_selection') && !empty($request->input('extras_selection'))) {
            $extraId = $request->input('extras_selection');
            
            if (!empty($package->available_extras)) {
                $availableExtras = is_array($package->available_extras) ? 
                    $package->available_extras : 
                    json_decode($package->available_extras, true);
                
                foreach ($availableExtras as $extra) {
                    if ($extra['id'] == $extraId) {
                        $extraPrice = floatval($extra['price']) * 7.4 / 100 * 1.5; // 转换为人民币并加价
                        $totalPrice += $extraPrice;
                        
                        $selectedExtras[] = [
                            'id' => $extra['id'],
                            'code' => $extra['code'],
                            'name' => $extra['name'],
                            'price' => $extraPrice
                        ];
                        
                        break;
                    }
                }
            }
        }
        
        // 检查余额是否足够
        $user = auth()->user();
        if ($user->total_balance < $totalPrice) {
            return redirect()->route('wallet.deposit')
                ->with('error', '余额不足，请先充值');
        }
        
        try {
            // 提交订单数据
            $orderData = [
                'target_url' => $request->input('target_url'),
                'keywords' => $request->input('keywords'),
                'article' => $request->input('article'),
                'extras' => $request->input('extras', []),
                'extras_selection' => $request->input('extras_selection'),
                'selected_extras' => $selectedExtras,
                'total_price' => $totalPrice,
                'quantity' => $request->input('quantity', 1),
                'notes' => $request->input('notes')
            ];
            
            // 创建订单
            $order = $this->orderService->createOrder(
                auth()->id(),
                $packageId,
                $orderData
            );
            
            return redirect()->route('orders.show', $order->id)
                ->with('success', '订单创建成功！');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', '创建订单失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 显示订单详情
     * 需要登录
     */
    public function show($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->with(['apiOrder'])
            ->findOrFail($id);
            
        // 获取订单报告
        $report = \App\Models\OrderReport::where('order_id', $id)
            ->latest()
            ->first();
            
        // 获取状态日志
        $statusLogs = \App\Models\OrderStatusLog::where('order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // 解析额外选项数据
        $selectedExtras = !empty($order->selected_extras) ? 
            (is_array($order->selected_extras) ? $order->selected_extras : json_decode($order->selected_extras, true)) : 
            [];
            
        return view('orders.show', compact('order', 'report', 'statusLogs', 'selectedExtras'));
    }
    
    /**
     * 获取包含extras的价格计算
     * 未登录用户也可访问
     */
    public function calculatePrice(Request $request)
    {
        $packageId = $request->input('package_id');
        $extrasIds = $request->input('extras', []);
        $extrasSelection = $request->input('extras_selection');
        $quantity = $request->input('quantity', 1);
        
        $package = Package::findOrFail($packageId);
        $totalPrice = $package->price;
        
        // 处理选中的额外选项
        if (!empty($package->available_extras)) {
            $availableExtras = is_array($package->available_extras) ? 
                $package->available_extras : 
                json_decode($package->available_extras, true);
                
            // 处理多选
            if (!empty($extrasIds) && is_array($extrasIds)) {
                foreach ($extrasIds as $extraId => $value) {
                    foreach ($availableExtras as $extra) {
                        if ($extra['id'] == $extraId) {
                            $totalPrice += floatval($extra['price']) * 7.4 / 100 * 1.5;
                            break;
                        }
                    }
                }
            }
            
            // 处理单选
            if (!empty($extrasSelection)) {
                foreach ($availableExtras as $extra) {
                    if ($extra['id'] == $extrasSelection) {
                        $totalPrice += floatval($extra['price']) * 7.4 / 100 * 1.5;
                        break;
                    }
                }
            }
        }
        
        // 计算数量
        $totalPrice *= intval($quantity);
        
        return response()->json([
            'success' => true,
            'total_price' => $totalPrice,
            'formatted_price' => number_format($totalPrice, 2) . ' 元'
        ]);
    }
}