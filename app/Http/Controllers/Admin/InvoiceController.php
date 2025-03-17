<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\InvoiceApprovalRequest;

class InvoiceController extends Controller
{
    protected $invoiceService;
    
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    
    /**
     * 显示发票申请列表
     */
    public function index(Request $request)
    {
        $query = Invoice::with('user');
        
        // 过滤条件
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->input('invoice_type'));
        }
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('tax_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // 排序
        $query->orderBy('created_at', 'desc');
        
        $invoices = $query->paginate(20);
        
        // 获取过滤选项
        $statuses = Invoice::distinct()->pluck('status');
        $invoiceTypes = Invoice::distinct()->pluck('invoice_type');
        
        return view('master.invoices.index', compact('invoices', 'statuses', 'invoiceTypes'));
    }
    
    /**
     * 显示发票详情
     */
    public function show($id)
    {
        $invoice = Invoice::with('user')->findOrFail($id);
        
        // 获取关联的交易记录
        $transactionIds = json_decode($invoice->related_transaction_ids ?? '[]', true);
        $transactions = [];
        
        if (!empty($transactionIds)) {
            $transactions = \App\Models\Transaction::whereIn('id', $transactionIds)
                ->orderBy('created_at')
                ->get();
        }
        
        return view('master.invoices.show', compact('invoice', 'transactions'));
    }
    
    /**
     * 审核发票申请
     */
    public function approve(InvoiceApprovalRequest $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        if ($invoice->status !== 'pending') {
            return back()->with('error', '只能审核待处理的发票申请');
        }
        
        try {
            // 如果上传了文件
            if ($request->hasFile('invoice_file')) {
                $file = $request->file('invoice_file');
                $path = $file->store('invoices', 'public');
                
                // 更新发票文件路径
                $invoice->file_path = $path;
            }
            
            // 审核通过发票
            $this->invoiceService->approveInvoice($id, auth()->id());
            
            return redirect()->route('master.invoices.index')
                ->with('success', '发票已审核通过并发送给用户');
        } catch (\Exception $e) {
            return back()->with('error', '审核发票失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 拒绝发票申请
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);
        
        $invoice = Invoice::findOrFail($id);
        
        if ($invoice->status !== 'pending') {
            return back()->with('error', '只能拒绝待处理的发票申请');
        }
        
        try {
            // 拒绝发票申请
            $this->invoiceService->rejectInvoice($id, $request->input('rejection_reason'), auth()->id());
            
            return redirect()->route('master.invoices.index')
                ->with('success', '发票申请已拒绝');
        } catch (\Exception $e) {
            return back()->with('error', '拒绝发票失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 上传电子发票
     */
    public function uploadInvoice(Request $request, $id)
    {
        $request->validate([
            'invoice_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        
        $invoice = Invoice::findOrFail($id);
        
        try {
            // 上传文件
            $file = $request->file('invoice_file');
            $path = $file->store('invoices', 'public');
            
            // 更新发票记录
            $invoice->update([
                'file_path' => $path,
                'status' => 'approved',
                'sent_at' => now()
            ]);
            
            return back()->with('success', '电子发票上传成功');
        } catch (\Exception $e) {
            return back()->with('error', '上传电子发票失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 导出发票数据
     */
    public function export(Request $request)
    {
        $query = Invoice::with('user')
            ->whereIn('status', ['approved', 'sent']);
        
        // 按日期范围过滤
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }
        
        // 获取数据
        $invoices = $query->get();
        
        // 创建CSV文件
        $filename = 'invoices_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $columns = [
            '发票号', '用户', '用户邮箱', '发票抬头', '税号', '金额', '发票类型', '状态', '申请日期', '审批日期'
        ];
        
        $callback = function() use ($invoices, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->user->name,
                    $invoice->user->email,
                    $invoice->title,
                    $invoice->tax_number,
                    $invoice->amount,
                    $invoice->invoice_type == 'vat' ? '增值税发票' : '普通发票',
                    $invoice->status == 'approved' ? '已审批' : '已发送',
                    $invoice->created_at->format('Y-m-d H:i:s'),
                    $invoice->sent_at ? $invoice->sent_at->format('Y-m-d H:i:s') : ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}