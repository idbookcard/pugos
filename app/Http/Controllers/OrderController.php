<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
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
        $this->middleware('auth');
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
     */
    public function create($slug)
    {
        $package = Package::where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();
            
        // 获取用户余额信息
        $user = auth()->user();
        $balance = $user->total_balance;
        $sufficientBalance = $balance >= $package->price;
            
        return view('orders.create', compact('package', 'balance', 'sufficientBalance'));
    }
    
    /**
     * 处理订单创建
     */
    public function store(OrderRequest $request)
    {
        $packageId = $request->input('package_id');
        $package = Package::findOrFail($packageId);
        
        // 检查余额是否足够
        $user = auth()->user();
        if ($user->total_balance < $package->price) {
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
                'quantity' => 1
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
            
        return view('orders.show', compact('order', 'report', 'statusLogs'));
    }
}