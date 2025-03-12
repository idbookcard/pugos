<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountService
{
    /**
     * 检查用户余额是否足够
     */
    public function checkBalance($userId, $amount)
    {
        $user = User::findOrFail($userId);
        return ($user->balance + $user->gift_balance) >= $amount;
    }
    
    /**
     * 处理充值
     */
    public function processRecharge($userId, $amount, $paymentMethod, $transactionId)
    {
        $user = User::findOrFail($userId);
        
        DB::beginTransaction();
        try {
            // 记录原余额
            $beforeBalance = $user->balance;
            
            // 更新用户余额
            $user->balance += $amount;
            $user->save();
            
            // 记录交易日志
            WalletTransaction::create([
                'user_id' => $userId,
                'amount' => $amount,
                'type' => 'recharge',
                'balance_type' => 'main',
                'related_id' => $transactionId,
                'description' => "通过{$paymentMethod}充值{$amount}元",
                'before_balance' => $beforeBalance,
                'after_balance' => $user->balance,
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('处理充值失败: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 处理消费
     */
    public function processConsumption($userId, $amount, $orderId)
    {
        $user = User::findOrFail($userId);
        
        // 检查余额是否足够
        if (($user->balance + $user->gift_balance) < $amount) {
            throw new \Exception('账户余额不足');
        }
        
        DB::beginTransaction();
        try {
            // 记录原余额
            $beforeGiftBalance = $user->gift_balance;
            $beforeMainBalance = $user->balance;
            
            // 优先使用赠送余额
            $giftAmount = min($user->gift_balance, $amount);
            if ($giftAmount > 0) {
                $user->gift_balance -= $giftAmount;
                $amount -= $giftAmount;
                
                // 记录赠送余额交易
                WalletTransaction::create([
                    'user_id' => $userId,
                    'amount' => -$giftAmount,
                    'type' => 'consumption',
                    'balance_type' => 'gift',
                    'related_id' => $orderId,
                    'description' => "订单支付(赠送余额)",
                    'before_balance' => $beforeGiftBalance,
                    'after_balance' => $user->gift_balance,
                ]);
            }
            
            // 使用主余额
            if ($amount > 0) {
                $user->balance -= $amount;
                
                // 记录主余额交易
                WalletTransaction::create([
                    'user_id' => $userId,
                    'amount' => -$amount,
                    'type' => 'consumption',
                    'balance_type' => 'main',
                    'related_id' => $orderId,
                    'description' => "订单支付(主余额)",
                    'before_balance' => $beforeMainBalance,
                    'after_balance' => $user->balance,
                ]);
            }
            
            $user->save();
            
            // 创建交易记录
            \App\Models\Transaction::create([
                'user_id' => $userId,
                'order_id' => $orderId,
                'transaction_type' => 'order_payment',
                'balance_type' => 'both', // 表示可能同时使用了两种余额
                'amount' => -($giftAmount + $amount),
                'payment_method' => 'balance',
                'payment_details' => json_encode([
                    'main_balance' => $amount,
                    'gift_balance' => $giftAmount
                ]),
                'status' => 'completed',
                'notes' => '订单支付'
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('处理消费失败: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 添加赠送余额（不可开发票）
     */
    public function addGiftBalance($userId, $amount, $description = '系统赠送', $relatedId = null)
    {
        $user = User::findOrFail($userId);
        
        DB::beginTransaction();
        try {
            // 记录原余额
            $beforeBalance = $user->gift_balance;
            
            // 更新赠送余额
            $user->gift_balance += $amount;
            $user->save();
            
            // 记录交易日志
            WalletTransaction::create([
                'user_id' => $userId,
                'amount' => $amount,
                'type' => 'gift',
                'balance_type' => 'gift',
                'related_id' => $relatedId,
                'description' => $description,
                'before_balance' => $beforeBalance,
                'after_balance' => $user->gift_balance,
            ]);
            
            // 创建交易记录
            if ($relatedId) {
                \App\Models\Transaction::create([
                    'user_id' => $userId,
                    'transaction_type' => 'adjustment',
                    'balance_type' => 'gift',
                    'amount' => $amount,
                    'payment_method' => 'system',
                    'status' => 'completed',
                    'notes' => $description
                ]);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('添加赠送余额失败: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 计算用户可开票金额
     */
    public function calculateInvoiceableAmount($userId)
    {
        // 只有主余额充值记录可以开发票
        $rechargeAmount = \App\Models\Transaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('balance_type', 'main')
            ->where('status', 'completed')
            ->sum('amount');
            
        // 已开票金额
        $invoicedAmount = \App\Models\Invoice::where('user_id', $userId)
            ->whereIn('status', ['approved', 'sent'])
            ->sum('amount');
            
        return max(0, $rechargeAmount - $invoicedAmount);
    }
}