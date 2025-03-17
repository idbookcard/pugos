<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChanged;
use App\Mail\ReportUploaded;
use App\Models\Order;
use App\Models\ApiOrder;
use App\Models\MonthlyOrderDetail;
use App\Models\MonthlyOrderWeeklyTask;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
        
        // 如果是包月订单，加载包月详情和周任务
        if ($order->service_type === 'monthly') {
            $order->load(['monthlyDetail', 'weeklyTasks']);
        }
        
        // 获取订单报告
        $reports = \App\Models\OrderReport::where('order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // 获取状态变更记录
        $statusLogs = \App\Models\OrderStatusLog::where('order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('master.orders.show', compact('order', 'reports', 'statusLogs'));
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
        
        // 如果是包月订单，加载包月详情和周任务
        if ($order->service_type === 'monthly') {
            $order->load(['monthlyDetail', 'weeklyTasks']);
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
        
        // 验证基本订单信息
        $request->validate([
            'status' => 'required|in:pending,processing,completed,canceled,rejected,refunded',
            'target_url' => 'required_unless:service_type,monthly|url',
            'keywords' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        // 更新订单基本信息
        $order->update([
            'status' => $request->input('status'),
            'target_url' => $request->input('target_url', $order->target_url),
            'keywords' => $request->input('keywords'),
            'article' => $request->input('article'),
            'extra_data' => $request->has('extra_data') ? json_encode($request->input('extra_data')) : $order->extra_data,
        ]);
        
        // 如果是包月订单，更新相关信息
        if ($order->service_type === 'monthly' && $request->has('weekly_tasks')) {
            // 更新每周任务状态
            foreach ($request->input('weekly_tasks') as $weekNumber => $task) {
                if (isset($task['status'])) {
                    $weeklyTask = MonthlyOrderWeeklyTask::where('order_id', $order->id)
                        ->where('week_number', $weekNumber)
                        ->first();
                        
                    if ($weeklyTask) {
                        $weeklyTask->update([
                            'status' => $task['status'],
                            'completed_at' => $task['status'] === 'completed' ? now() : $weeklyTask->completed_at
                        ]);
                    }
                }
            }
        }
        
        // 如果状态变更，记录日志
        if ($oldStatus != $request->input('status')) {
            // 记录状态变更日志
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
            
            // 发送状态变更邮件
            try {
                Mail::to($order->user->email)->send(
                    new OrderStatusChanged(
                        $order, 
                        $oldStatus, 
                        $request->input('status'),
                        $request->input('status_notes')
                    )
                );
            } catch (\Exception $e) {
                // 记录邮件发送错误，但不影响主流程
                \Log::error('订单状态变更邮件发送失败：' . $e->getMessage());
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
        
        // 基本验证规则
        $rules = [
            'report_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,csv,txt,zip|max:10240',
            'report_notes' => 'nullable|string',
        ];
        
        // 如果是包月订单，验证周数
        if ($order->service_type === 'monthly' && $request->has('week_number')) {
            $rules['week_number'] = 'required|integer|min:1|max:4';
        }
        
        $request->validate($rules);
        
        try {
            // 上传文件
            $file = $request->file('report_file');
            $path = $file->store('reports', 'public');
            
            // 准备报告数据
            $reportData = [
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'notes' => $request->input('report_notes')
            ];
            
            // 如果是包月订单和有周数，添加周数信息
            if ($order->service_type === 'monthly' && $request->has('week_number')) {
                $reportData['week_number'] = $request->input('week_number');
                
                // 更新对应周任务的状态
                $weeklyTask = MonthlyOrderWeeklyTask::where('order_id', $order->id)
                    ->where('week_number', $request->input('week_number'))
                    ->first();
                    
                if ($weeklyTask) {
                    $weeklyTask->update([
                        'status' => 'completed',
                        'completed_at' => now()
                    ]);
                }
                
                // 检查是否所有周任务都已完成
                $allWeeksCompleted = MonthlyOrderWeeklyTask::where('order_id', $order->id)
                    ->where('status', '!=', 'completed')
                    ->count() === 0;
                    
                // 如果所有周任务已完成，询问是否要标记整个订单为已完成
                if ($allWeeksCompleted && $order->status !== 'completed' && $request->has('mark_completed')) {
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
                        'notes' => '所有周任务已完成，自动标记订单为完成',
                        'created_by' => auth()->id()
                    ]);
                    
                    // 发送状态变更邮件
                    try {
                        Mail::to($order->user->email)->send(
                            new OrderStatusChanged(
                                $order, 
                                $oldStatus, 
                                'completed',
                                '所有周任务已完成，订单自动标记为已完成'
                            )
                        );
                    } catch (\Exception $e) {
                        \Log::error('订单状态变更邮件发送失败：' . $e->getMessage());
                    }
                }
            }
            
            // 创建报告记录
            $report = \App\Models\OrderReport::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'report_data' => json_encode($reportData),
                'source' => 'admin',
                'placed_at' => now(),
            ]);
            
            // 如果不是包月订单且订单状态为处理中，询问是否要更新为已完成
            if ($order->service_type !== 'monthly' && $order->status == 'processing' && $request->has('mark_completed')) {
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
                
                // 发送状态变更邮件
                try {
                    Mail::to($order->user->email)->send(
                        new OrderStatusChanged(
                            $order, 
                            $oldStatus, 
                            'completed',
                            '报告上传完成，订单已标记为已完成'
                        )
                    );
                } catch (\Exception $e) {
                    \Log::error('订单状态变更邮件发送失败：' . $e->getMessage());
                }
            }
            
            // 发送报告上传邮件
            try {
                Mail::to($order->user->email)->send(
                    new ReportUploaded($order, $report)
                );
            } catch (\Exception $e) {
                \Log::error('报告上传邮件发送失败：' . $e->getMessage());
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
    
    /**
     * 查看包月订单周任务详情
     */
    public function viewWeeklyTask($orderId, $weekNumber)
    {
        $order = Order::findOrFail($orderId);
        
        if ($order->service_type !== 'monthly') {
            return redirect()->route('master.orders.show', $orderId)
                ->with('error', '只有包月订单才有周任务');
        }
        
        $weeklyTask = MonthlyOrderWeeklyTask::where('order_id', $orderId)
            ->where('week_number', $weekNumber)
            ->firstOrFail();
            
        // 获取此周的报告
        $reports = \App\Models\OrderReport::where('order_id', $orderId)
            ->whereJsonContains('report_data->week_number', (int)$weekNumber)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('master.orders.weekly-task', compact('order', 'weeklyTask', 'reports'));
    }
    
    /**
     * 更新周任务状态
     */
    public function updateWeeklyTaskStatus(Request $request, $orderId, $weekNumber)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'notes' => 'nullable|string',
        ]);
        
        $order = Order::findOrFail($orderId);
        $weeklyTask = MonthlyOrderWeeklyTask::where('order_id', $orderId)
            ->where('week_number', $weekNumber)
            ->firstOrFail();
            
        $oldStatus = $weeklyTask->status;
        
        $weeklyTask->update([
            'status' => $request->input('status'),
            'completed_at' => $request->input('status') === 'completed' ? now() : null,
        ]);
        
        // 记录状态变更
        \App\Models\OrderStatusLog::create([
            'order_id' => $orderId,
            'old_status' => "周{$weekNumber}：{$oldStatus}",
            'new_status' => "周{$weekNumber}：{$request->input('status')}",
            'notes' => $request->input('notes') ?? '管理员手动变更周任务状态',
            'created_by' => auth()->id()
        ]);
        
        // 检查是否所有周任务都已完成
        $allWeeksCompleted = MonthlyOrderWeeklyTask::where('order_id', $orderId)
            ->where('status', '!=', 'completed')
            ->count() === 0;
            
        // 如果所有周任务已完成，询问是否要标记整个订单为已完成
        if ($allWeeksCompleted && $order->status !== 'completed' && $request->has('mark_order_completed')) {
            $oldOrderStatus = $order->status;
            
            $order->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
            
            // 记录状态变更
            \App\Models\OrderStatusLog::create([
                'order_id' => $orderId,
                'old_status' => $oldOrderStatus,
                'new_status' => 'completed',
                'notes' => '所有周任务已完成，自动标记订单为完成',
                'created_by' => auth()->id()
            ]);
            
            // 发送状态变更邮件
            try {
                Mail::to($order->user->email)->send(
                    new OrderStatusChanged(
                        $order, 
                        $oldOrderStatus, 
                        'completed',
                        '所有周任务已完成，订单自动标记为已完成'
                    )
                );
            } catch (\Exception $e) {
                \Log::error('订单状态变更邮件发送失败：' . $e->getMessage());
            }
        }
        
        // 如果单周状态变更为已完成，发送通知邮件
        if ($oldStatus != 'completed' && $request->input('status') == 'completed') {
            // 检查是否有对应的周报告
            $weeklyReports = \App\Models\OrderReport::where('order_id', $orderId)
                ->whereJsonContains('report_data->week_number', (int)$weekNumber)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($weeklyReports) {
                try {
                    Mail::to($order->user->email)->send(
                        new ReportUploaded($order, $weeklyReports)
                    );
                } catch (\Exception $e) {
                    \Log::error('周任务完成邮件发送失败：' . $e->getMessage());
                }
            }
        }
        
        return redirect()->route('master.orders.view-weekly-task', [$orderId, $weekNumber])
            ->with('success', '周任务状态更新成功');
    }


    /**
 * 创建周任务工单
 */
public function createWorkOrder(Request $request, $orderId, $weekNumber)
{
    $request->validate([
        'work_order_number' => 'required|string|max:255',
        'work_order_status' => 'nullable|string|max:255',
        'work_order_assignee' => 'nullable|string|max:255',
    ]);
    
    $weeklyTask = MonthlyOrderWeeklyTask::where('order_id', $orderId)
        ->where('week_number', $weekNumber)
        ->firstOrFail();
        
    $weeklyTask->update([
        'work_order_number' => $request->work_order_number,
        'work_order_created_at' => now(),
        'work_order_status' => $request->work_order_status ?? 'pending',
        'work_order_assignee' => $request->work_order_assignee,
    ]);
    
    // 记录工单创建日志
    \App\Models\OrderStatusLog::create([
        'order_id' => $orderId,
        'old_status' => "周{$weekNumber}：无工单",
        'new_status' => "周{$weekNumber}：创建工单 {$request->work_order_number}",
        'notes' => "创建周{$weekNumber}工单，编号：{$request->work_order_number}",
        'created_by' => auth()->id()
    ]);
    
    return redirect()->route('master.orders.view-weekly-task', [$orderId, $weekNumber])
        ->with('success', '工单创建成功');
}

/**
 * 更新周任务工单
 */
public function updateWorkOrder(Request $request, $orderId, $weekNumber)
{
    $request->validate([
        'work_order_number' => 'required|string|max:255',
        'work_order_status' => 'nullable|string|max:255',
        'work_order_assignee' => 'nullable|string|max:255',
    ]);
    
    $weeklyTask = MonthlyOrderWeeklyTask::where('order_id', $orderId)
        ->where('week_number', $weekNumber)
        ->firstOrFail();
    
    $oldWorkOrderNumber = $weeklyTask->work_order_number;
    $oldWorkOrderStatus = $weeklyTask->work_order_status;
        
    $weeklyTask->update([
        'work_order_number' => $request->work_order_number,
        'work_order_status' => $request->work_order_status,
        'work_order_assignee' => $request->work_order_assignee,
    ]);
    
    // 记录工单更新日志
    if ($oldWorkOrderNumber != $request->work_order_number || $oldWorkOrderStatus != $request->work_order_status) {
        \App\Models\OrderStatusLog::create([
            'order_id' => $orderId,
            'old_status' => "周{$weekNumber}：工单 {$oldWorkOrderNumber} ({$oldWorkOrderStatus})",
            'new_status' => "周{$weekNumber}：工单 {$request->work_order_number} ({$request->work_order_status})",
            'notes' => "更新周{$weekNumber}工单信息",
            'created_by' => auth()->id()
        ]);
    }
    
    return redirect()->route('master.orders.view-weekly-task', [$orderId, $weekNumber])
        ->with('success', '工单信息更新成功');
}
}