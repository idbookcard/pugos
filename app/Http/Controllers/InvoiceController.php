<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use App\Http\Requests\InvoiceRequest;

class InvoiceController extends Controller
{
    protected $invoiceService;
    
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        $this->middleware('auth');
    }
    
    /**
     * 显示发票列表
     */
    public function index()
    {
        $invoices = Invoice::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // 计算可开票金额
        $invoiceableAmount = $this->invoiceService->calculateInvoiceableAmount(auth()->id());
        
        return view('invoices.index', compact('invoices', 'invoiceableAmount'));
    }
    
    /**
     * 显示创建发票页面
     */
    public function create()
    {
        $invoiceableAmount = $this->invoiceService->calculateInvoiceableAmount(auth()->id());
        
        if ($invoiceableAmount <= 0) {
            return redirect()->route('invoices.index')
                ->with('error', '暂无可开发票金额，请先充值。');
        }
        
        $user = auth()->user();
        $profile = $user->profile;
        
        return view('invoices.create', compact('invoiceableAmount', 'profile'));
    }
    
    /**
     * 处理发票申请
     */
    public function store(InvoiceRequest $request)
    {
        try {
            $this->invoiceService->requestInvoice(auth()->id(), [
                'title' => $request->input('title'),
                'tax_number' => $request->input('tax_number'),
                'amount' => $request->input('amount'),
                'invoice_type' => $request->input('invoice_type', 'regular'),
                'email' => $request->input('email'),
                'address' => $request->input('address'),
                'notes' => $request->input('notes')
            ]);
            
            return redirect()->route('invoices.index')
                ->with('success', '发票申请提交成功，请等待审核。');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', '申请发票失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 显示发票详情
     */
    public function show($id)
    {
        $invoice = Invoice::where('user_id', auth()->id())
            ->findOrFail($id);
            
        return view('invoices.show', compact('invoice'));
    }
    
    /**
     * 下载发票
     */
    public function download($id)
    {
        $invoice = Invoice::where('user_id', auth()->id())
            ->whereIn('status', ['approved', 'sent'])
            ->findOrFail($id);
            
        if (empty($invoice->file_path)) {
            return back()->with('error', '发票文件不存在');
        }
        
        return response()->download(storage_path('app/public/' . $invoice->file_path));
    }
}