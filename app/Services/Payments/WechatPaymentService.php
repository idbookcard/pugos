<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WechatPaymentService implements PaymentServiceInterface
{
    /**
     * 创建支付
     */
    public function createPayment($amount, $userId, $description = null)
    {
        // 创建交易记录
        $transaction = Transaction::create([
            'user_id' => $userId,
            'transaction_type' => 'deposit',
            'balance_type' => 'main',
            'amount' => $amount,
            'payment_method' => 'wechat',
            'status' => 'pending',
            'reference_id' => 'WX' . date('YmdHis') . Str::random(6),
            'notes' => $description ?? '微信充值'
        ]);
        
        try {
            // 集成微信支付SDK（示例）
            $app = \EasyWeChat\Factory::payment([
                'app_id' => config('services.wechat.app_id'),
                'mch_id' => config('services.wechat.mch_id'),
                'key' => config('services.wechat.key'),
                'notify_url' => route('payment.wechat.callback'),
            ]);
            
            $result = $app->order->unify([
                'body' => '账户充值',
                'out_trade_no' => $transaction->reference_id,
                'total_fee' => intval($amount * 100), // 微信支付金额单位为分
                'trade_type' => 'NATIVE', // 网页扫码支付
            ]);
            
            if ($result['return_code'] === 'SUCCESS' && $result['result_code'] === 'SUCCESS') {
                // 生成二维码URL
                $qrcode = $result['code_url'];
                
                // 更新交易记录
                $transaction->update([
                    'payment_details' => json_encode([
                        'qrcode' => $qrcode,
                        'prepay_id' => $result['prepay_id'] ?? null
                    ])
                ]);
                
                return [
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'qrcode' => $qrcode,
                    'reference_id' => $transaction->reference_id
                ];
            }
            
            // 创建支付失败
            $transaction->update([
                'status' => 'failed',
                'payment_details' => json_encode($result)
            ]);
            
            Log::error('微信支付创建失败: ' . json_encode($result));
            
            return [
                'success' => false,
                'message' => $result['err_code_des'] ?? '创建支付失败'
            ];
        } catch (\Exception $e) {
            // 发生异常，更新交易状态
            $transaction->update([
                'status' => 'failed',
                'notes' => $e->getMessage()
            ]);
            
            Log::error('微信支付异常: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => '支付服务异常: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 验证支付回调
     */
    public function verifyPayment($paymentData)
    {
        try {
            // 微信支付回调验证（示例）
            $app = \EasyWeChat\Factory::payment([
                'app_id' => config('services.wechat.app_id'),
                'mch_id' => config('services.wechat.mch_id'),
                'key' => config('services.wechat.key'),
            ]);
            
            $response = $app->handlePaidNotify(function ($message, $fail) {
                // 查找对应的交易记录
                $transaction = Transaction::where('reference_id', $message['out_trade_no'])->first();
                
                if (!$transaction || $transaction->status === 'completed') {
                    return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
                }
                
                // 验证支付金额
                if (intval($transaction->amount * 100) !== intval($message['total_fee'])) {
                    return $fail('金额不匹配'); // 告诉微信，我认为这笔订单支付金额与商户订单金额不一致
                }
                
                // 判断支付状态
                if ($message['return_code'] === 'SUCCESS' && $message['result_code'] === 'SUCCESS') {
                    // 更新交易状态
                    $transaction->update([
                        'status' => 'completed',
                        'payment_details' => json_encode(array_merge(
                            json_decode($transaction->payment_details ?? '{}', true),
                            ['callback_data' => $message]
                        ))
                    ]);
                    
                    // 更新用户余额
                    app(AccountService::class)->processRecharge(
                        $transaction->user_id,
                        $transaction->amount,
                        'wechat',
                        $transaction->id
                    );
                    
                    return true; // 告诉微信，我已经处理完成
                }
                
                return $fail('支付失败'); // 告诉微信，我认为这笔订单支付失败
            });
            
            return $response;
        } catch (\Exception $e) {
            Log::error('微信支付回调处理失败: ' . $e->getMessage());
            return false;
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
        
        try {
            // 微信支付查询（示例）
            $app = \EasyWeChat\Factory::payment([
                'app_id' => config('services.wechat.app_id'),
                'mch_id' => config('services.wechat.mch_id'),
                'key' => config('services.wechat.key'),
            ]);
            
            $result = $app->order->queryByOutTradeNumber($transaction->reference_id);
            
            if ($result['return_code'] === 'SUCCESS' && $result['result_code'] === 'SUCCESS') {
                if ($result['trade_state'] === 'SUCCESS' && $transaction->status !== 'completed') {
                    // 交易成功但本地状态未更新，执行更新
                    $transaction->update([
                        'status' => 'completed',
                        'payment_details' => json_encode(array_merge(
                            json_decode($transaction->payment_details ?? '{}', true),
                            ['query_result' => $result]
                        ))
                    ]);
                    
                    // 更新用户余额
                    app(AccountService::class)->processRecharge(
                        $transaction->user_id,
                        $transaction->amount,
                        'wechat',
                        $transaction->id
                    );
                }
                
                return [
                    'success' => true,
                    'status' => $result['trade_state'],
                    'paid' => $result['trade_state'] === 'SUCCESS',
                    'transaction' => $transaction
                ];
            }
            
            return [
                'success' => false,
                'message' => $result['err_code_des'] ?? '查询失败',
                'transaction' => $transaction
            ];
        } catch (\Exception $e) {
            Log::error('微信支付查询失败: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => '查询异常: ' . $e->getMessage(),
                'transaction' => $transaction
            ];
        }
    }
}