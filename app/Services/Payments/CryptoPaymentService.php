<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use App\Models\CryptoPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CryptoPaymentService implements PaymentServiceInterface
{
    /**
     * 创建支付
     */
    public function createPayment($amount, $userId, $description = null)
    {
        // 获取加密货币类型（从请求中）
        $cryptoType = request('crypto_type', 'USDT');
        
        // 创建交易记录
        $transaction = Transaction::create([
            'user_id' => $userId,
            'transaction_type' => 'deposit',
            'balance_type' => 'main',
            'amount' => $amount,
            'payment_method' => 'crypto',
            'status' => 'pending',
            'reference_id' => 'CRYPTO' . date('YmdHis') . Str::random(6),
            'notes' => $description ?? $cryptoType . '充值'
        ]);
        
        try {
            // 获取汇率（示例，实际中应调用加密货币汇率API）
            $exchangeRate = $this->getCryptoExchangeRate($cryptoType);
            
            // 计算加密货币金额
            $cryptoAmount = $amount / $exchangeRate;
            
            // 生成钱包地址（示例，实际中可能需要对接钱包API）
            $walletAddress = $this->generateWalletAddress($cryptoType);
            
            // 创建加密货币支付记录
            $cryptoPayment = CryptoPayment::create([
                'user_id' => $userId,
                'transaction_id' => $transaction->id,
                'currency' => $cryptoType,
                'network' => $this->getNetwork($cryptoType),
                'amount' => $cryptoAmount,
                'amount_usd' => $amount,
                'wallet_address' => $walletAddress,
                'expires_at' => now()->addHours(2), // 2小时过期
            ]);
            
            // 更新交易记录
            $transaction->update([
                'payment_details' => json_encode([
                    'crypto_payment_id' => $cryptoPayment->id,
                    'crypto_type' => $cryptoType,
                    'crypto_amount' => $cryptoAmount,
                    'exchange_rate' => $exchangeRate,
                    'wallet_address' => $walletAddress
                ])
            ]);
            
            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'wallet_address' => $walletAddress,
                'crypto_amount' => round($cryptoAmount, 8),
                'crypto_type' => $cryptoType,
                'exchange_rate' => $exchangeRate,
                'expires_at' => $cryptoPayment->expires_at->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            // 发生异常，更新交易状态
            $transaction->update([
                'status' => 'failed',
                'notes' => $e->getMessage()
            ]);
            
            Log::error('加密货币支付创建失败: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => '创建加密货币支付失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 验证支付回调
     */
    public function verifyPayment($paymentData)
    {
        // 在实际实现中，这可能是由区块链监听服务回调
        // 这里简化为手动确认
        
        $cryptoPaymentId = $paymentData['crypto_payment_id'] ?? null;
        $txHash = $paymentData['tx_hash'] ?? null;
        
        if (!$cryptoPaymentId || !$txHash) {
            return [
                'verified' => false,
                'message' => '参数不完整'
            ];
        }
        
        $cryptoPayment = CryptoPayment::find($cryptoPaymentId);
        
        if (!$cryptoPayment) {
            return [
                'verified' => false,
                'message' => '支付记录不存在'
            ];
        }
        
        if ($cryptoPayment->status !== 'pending') {
            return [
                'verified' => true,
                'already_processed' => true,
                'user_id' => $cryptoPayment->user_id,
                'amount' => $cryptoPayment->amount_usd,
                'transaction_id' => $cryptoPayment->transaction_id
            ];
        }
        
        try {
            // 验证交易哈希（实际中应调用区块链API）
            $verified = $this->verifyTransaction($txHash, $cryptoPayment);
            
            if ($verified) {
                // 更新支付状态
                $cryptoPayment->update([
                    'status' => 'confirmed',
                    'tx_hash' => $txHash,
                    'confirmed_at' => now()
                ]);
                
                // 更新交易记录
                if ($cryptoPayment->transaction_id) {
                    $transaction = Transaction::find($cryptoPayment->transaction_id);
                    
                    if ($transaction) {
                        $transaction->update([
                            'status' => 'completed',
                            'payment_details' => json_encode(array_merge(
                                json_decode($transaction->payment_details ?? '{}', true),
                                [
                                    'tx_hash' => $txHash,
                                    'confirmed_at' => now()->format('Y-m-d H:i:s')
                                ]
                            ))
                        ]);
                    }
                }
                
                return [
                    'verified' => true,
                    'user_id' => $cryptoPayment->user_id,
                    'amount' => $cryptoPayment->amount_usd,
                    'transaction_id' => $cryptoPayment->transaction_id
                ];
            }
            
            return [
                'verified' => false,
                'message' => '交易验证失败'
            ];
        } catch (\Exception $e) {
            Log::error('加密货币支付验证失败: ' . $e->getMessage());
            
            return [
                'verified' => false,
                'message' => '验证异常: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 查询支付状态
     */
    public function queryPayment($transactionId)
    {
        $transaction = Transaction::find($transactionId);
        
        if (!$transaction) {
            return [
                'success' => false,
                'message' => '交易记录不存在'
            ];
        }
        
        $paymentDetails = json_decode($transaction->payment_details ?? '{}', true);
        $cryptoPaymentId = $paymentDetails['crypto_payment_id'] ?? null;
        
        if (!$cryptoPaymentId) {
            return [
                'success' => false,
                'message' => '未找到加密货币支付记录',
                'transaction' => $transaction
            ];
        }
        
        $cryptoPayment = CryptoPayment::find($cryptoPaymentId);
        
        if (!$cryptoPayment) {
            return [
                'success' => false,
                'message' => '加密货币支付记录不存在',
                'transaction' => $transaction
            ];
        }
        
        return [
            'success' => true,
            'status' => $cryptoPayment->status,
            'paid' => $cryptoPayment->status === 'confirmed',
            'expired' => ($cryptoPayment->status === 'pending' && $cryptoPayment->expires_at && $cryptoPayment->expires_at < now()),
            'wallet_address' => $cryptoPayment->wallet_address,
            'crypto_amount' => $cryptoPayment->amount,
            'crypto_type' => $cryptoPayment->currency,
            'tx_hash' => $cryptoPayment->tx_hash,
            'confirmed_at' => $cryptoPayment->confirmed_at ? $cryptoPayment->confirmed_at->format('Y-m-d H:i:s') : null,
            'transaction' => $transaction
        ];
    }
    
    /**
     * 获取加密货币汇率（示例）
     */
    protected function getCryptoExchangeRate($cryptoType)
    {
        // 实际中应调用汇率API
        $rates = [
            'USDT' => 7.2, // 1 USDT = 7.2 CNY
            'BTC' => 350000, // 1 BTC = 350,000 CNY
            'ETH' => 22000, // 1 ETH = 22,000 CNY
        ];
        
        return $rates[$cryptoType] ?? 7.2;
    }
    
    /**
     * 生成钱包地址（示例）
     */
    protected function generateWalletAddress($cryptoType)
    {
        // 实际中应对接钱包API
        // 这里返回示例地址
        $prefixes = [
            'USDT' => 'T',
            'BTC' => 'bc1',
            'ETH' => '0x',
        ];
        
        $prefix = $prefixes[$cryptoType] ?? '';
        return $prefix . Str::random(40);
    }
    
    /**
     * 获取网络类型
     */
    protected function getNetwork($cryptoType)
    {
        $networks = [
            'USDT' => 'TRC20',
            'BTC' => 'BTC',
            'ETH' => 'ERC20',
        ];
        
        return $networks[$cryptoType] ?? 'TRC20';
    }
    
    /**
     * 验证交易（示例）
     */
    protected function verifyTransaction($txHash, $cryptoPayment)
    {
        // 实际中应调用区块链API验证交易
        // 这里简化为所有交易都验证通过
        return true;
    }
}