<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ApiOrder;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;
    
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    
    /**
     * 显示订单列表
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'package']);
        
        // 过滤条件
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('target_url', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // 排序
        $query->orderBy('created_at', 'desc');
        
        $orders = $query->paginate(20);
        
        // 获取过滤选项
        $statuses = Order::distinct()->pluck('status');
        $serviceTypes = Order::distinct()->pluck('service_type');
        
        return view('master.orders.index', compact('orders', 'statuses', 'serviceTypes'));
    }
    
    /**
     * 显示订单详情
     */
    public function show($id)
    {
        $order = Order::with(['user', 'package', 'apiOrder'])->findOrFail($id);
        
        // 获取订单报告
        $report = \App\Models\OrderReport::where('order_id', $id)
            ->latest()
            ->first();
            
        // 获取状态变更记录
        $statusLogs = \App\Models\OrderStatusLog::where('order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('master.orders.show', compact('order', 'report', 'statusLogs'));
    }
    
    /**
     * 显示编辑订单页面
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        
        // 检查订单是否可以编辑
        if (in_array($order->status, ['completed', 'canceled', 'refunded'])) {
            return redirect()->route('master.orders.show', $id)
                ->with('error', '该订单已完成/取消/退款，无法编辑');
        }
        
        return view('master.orders.edit', compact('order'));
    }
    
    /**
     * 更新订单
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // 检查订单是否可以编辑
        if (in_array($order->status, ['completed', 'canceled', 'refunded'])) {
            return redirect()->route('master.orders.show', $id)
                ->with('error', '该订单已完成/取消/退款，无法编辑');
        }
        
        // 记录旧状态
        $oldStatus = $order->status;
        
        // 验证请求
        $request->validate([
            'status' => 'required|in:pending,processing,completed,canceled,rejected,refunded',
            'target_url' => 'required|url',
            'keywords' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        // 更新订单
        $order->update([
            'status' => $request->input('status'),
            'target_url' => $request->input('target_url'),
            'keywords' => $request->input('keywords'),
            'article' => $request->input('article'),
            'extra_data' => $request->has('extra_data') ? json_encode($request->input('extra_data')) : $order->extra_data,
        ]);
        
        // 如果状态变更，记录日志
        if ($oldStatus != $request->input('status')) {
            \App\Models\OrderStatusLog::create([
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $request->input('status'),
                'notes' => $request->input('status_notes') ?? '管理员手动变更状态',
                'created_by' => auth()->id()
            ]);
            
            // 如果状态变为已完成，记录完成时间
            if ($request->input('status') == 'completed' && !$order->completed_at) {
                $order->update([
                    'completed_at' => now()
                ]);
            }
        }
        
        return redirect()->route('master.orders.show', $id)
            ->with('success', '订单更新成功');
    }
    
    /**
     * 提交订单到API
     */
    public function submitToApi($id)
    {
        $order = Order::findOrFail($id);
        
        // 检查订单类型和状态
        if ($order->service_type != 'external' && $order->service_type != 'package') {
            return back()->with('error', '只有第三方API和包含第三方API ID的套餐订单可以提交');
        }
        
        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', '只有待处理或处理中的订单可以提交到API');
        }
        
        if (!$order->package || !$order->package->third_party_id) {
            return back()->with('error', '订单关联的产品没有第三方API ID');
        }
        
        try {
            // 提交订单到API
            $result = $this->orderService->processApiOrder($id);
            
            if ($result) {
                return back()->with('success', '订单成功提交到API');
            } else {
                return back()->with('error', 'API提交失败，请查看系统日志');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'API提交异常: ' . $e->getMessage());
        }
    }
    
    /**
     * 同步API订单状态
     */
    public function syncApiStatus($id)
    {
        $order = Order::with('apiOrder')->findOrFail($id);
        
        if (!$order->apiOrder || !$order->apiOrder->api_order_id) {
            return back()->with('error', '该订单没有关联的API订单，或API订单ID为空');
        }
        
        try {
            // 同步API订单状态
            $result = $this->orderService->syncApiOrderStatus($order->apiOrder->id);
            
            return back()->with('success', 'API订单状态同步成功');
        } catch (\Exception $e) {
            return back()->with('error', 'API订单状态同步失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 上传订单报告
     */
    public function uploadReport(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $request->validate([
            'report_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,csv,txt,zip|max:10240',
            'report_notes' => 'nullable|string',
        ]);
        
        try {
            // 上传文件
            $file = $request->file('report_file');
            $path = $file->store('reports', 'public');
            
            // 创建报告记录
            \App\Models\OrderReport::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'report_data' => json_encode([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'notes' => $request->input('report_notes')
                ]),
                'source' => 'admin',
                'placed_at' => now(),
            ]);
            
            // 如果订单状态为处理中，询问是否要更新为已完成
            if ($order->status == 'processing' && $request->has('mark_completed')) {
                $oldStatus = $order->status;
                
                $order->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
                
                // 记录状态变更
                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'completed',
                    'notes' => '上传报告时标记为已完成',
                    'created_by' => auth()->id()
                ]);
            }
            
            return back()->with('success', '报告上传成功');
        } catch (\Exception $e) {
            return back()->with('error', '报告上传失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 批量同步API订单状态
     */
    public function batchSyncApiStatus()
    {
        try {
            $result = $this->orderService->syncApiOrderStatus();
            
            return back()->with('success', "状态同步成功，共处理 {$result['updated']} 个订单，其中 {$result['completed']} 个标记为已完成");
        } catch (\Exception $e) {
            return back()->with('error', '批量同步API订单状态失败: ' . $e->getMessage());
        }
    }
}