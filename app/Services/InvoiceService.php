<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceApproved;

class InvoiceService
{
    /**
     * 计算用户可开票金额
     */
    public function calculateInvoiceableAmount($userId)
    {
        // 获取所有已完成的充值（主余额）
        $rechargeAmount = Transaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('balance_type', 'main')
            ->where('status', 'completed')
            ->sum('amount');
            
        // 获取所有已开票金额
        $invoicedAmount = Invoice::where('user_id', $userId)
            ->whereIn('status', ['approved', 'sent'])
            ->sum('amount');
            
        return max(0, $rechargeAmount - $invoicedAmount);
    }
    
    /**
     * Transactions eligible for invoice
     * 获取用户可开票的充值交易
     */
    public function getInvoiceableTransactions($userId)
    {
        // 获取所有已完成的充值交易（主余额）
        $transactions = Transaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('balance_type', 'main')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // 获取所有已开票的交易ID
        $invoicedTransactionIds = [];
        $invoices = Invoice::where('user_id', $userId)
            ->whereIn('status', ['approved', 'sent'])
            ->get();
            
        foreach ($invoices as $invoice) {
            $relatedIds = json_decode($invoice->related_transaction_ids ?? '[]', true);
            $invoicedTransactionIds = array_merge($invoicedTransactionIds, $relatedIds);
        }
        
        // 过滤出未开票的交易
        return $transactions->filter(function ($transaction) use ($invoicedTransactionIds) {
            return !in_array($transaction->id, $invoicedTransactionIds);
        });
    }
    

/**
 * 提交发票申请
 */
public function requestInvoice($userId, $data)
{
    // 验证参数
    if (empty($data['title']) || empty($data['amount']) || empty($data['email'])) {
        throw new \Exception('发票信息不完整');
    }
    
    // 验证可开票金额
    $invoiceableAmount = $this->calculateInvoiceableAmount($userId);
    if ($data['amount'] > $invoiceableAmount) {
        throw new \Exception("可开票金额不足，当前可开票金额: {$invoiceableAmount}元");
    }
    
    // 如果是增值税发票，验证税号
    if ($data['invoice_type'] === 'vat' && empty($data['tax_number'])) {
        throw new \Exception('增值税发票必须提供税号');
    }
    
    DB::beginTransaction();
    try {
        // 生成发票编号
        $invoiceNumber = 'INV' . date('YmdHis') . rand(100, 999);
        
        // 创建发票记录
        $invoice = Invoice::create([
            'user_id' => $userId,
            'invoice_number' => $invoiceNumber,
            'invoice_type' => $data['invoice_type'] ?? 'regular',
            'title' => $data['title'],
            'tax_number' => $data['tax_number'] ?? null,
            'amount' => $data['amount'],
            'email' => $data['email'],
            'address' => $data['address'] ?? null,
            'bank_info' => $data['bank_info'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending'
        ]);
        
        // 将可开票交易关联到发票
        $transactions = $this->getInvoiceableTransactions($userId);
        $relatedTransactionIds = [];
        $remainingAmount = $data['amount'];
        
        foreach ($transactions as $transaction) {
            if ($remainingAmount <= 0) {
                break;
            }
            
            $amount = min($transaction->amount, $remainingAmount);
            $relatedTransactionIds[] = $transaction->id;
            $remainingAmount -= $amount;
        }
        
        // 更新相关交易ID
        $invoice->update(['related_transaction_ids' => json_encode($relatedTransactionIds)]);
        
        DB::commit();
        return $invoice;
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('创建发票申请失败: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * 审核发票申请
 */
public function approveInvoice($invoiceId, $adminId = null)
{
    $invoice = Invoice::findOrFail($invoiceId);
    
    if ($invoice->status !== 'pending') {
        throw new \Exception('只能审核待处理的发票申请');
    }
    
    DB::beginTransaction();
    try {
        // 生成电子发票（实际中可能对接第三方发票服务）
        $filePath = $this->generateInvoiceFile($invoice);
        
        // 更新发票状态
        $invoice->update([
            'status' => 'approved',
            'file_path' => $filePath,
            'sent_at' => now()
        ]);
        
        // 发送邮件通知
        Mail::to($invoice->email)->send(new InvoiceApproved($invoice));
        
        DB::commit();
        return $invoice;
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('审核发票失败: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * 拒绝发票申请
 */
public function rejectInvoice($invoiceId, $reason, $adminId = null)
{
    $invoice = Invoice::findOrFail($invoiceId);
    
    if ($invoice->status !== 'pending') {
        throw new \Exception('只能拒绝待处理的发票申请');
    }
    
    // 更新发票状态
    $invoice->update([
        'status' => 'rejected',
        'rejection_reason' => $reason
    ]);
    
    return $invoice;
}

/**
 * 生成电子发票文件（示例）
 */
protected function generateInvoiceFile($invoice)
{
    // 实际中可能对接第三方发票服务
    // 这里简单模拟返回文件路径
    $fileName = 'invoice_' . $invoice->invoice_number . '.pdf';
    $filePath = 'invoices/' . $fileName;
    
    // 在实际实现中，这里应创建PDF文件
    
    return $filePath;
}

}