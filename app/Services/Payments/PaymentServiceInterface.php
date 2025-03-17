<?php

namespace App\Services\Payments;

interface PaymentServiceInterface
{
    /**
     * 创建支付
     */
    public function createPayment($amount, $userId, $description = null);
    
    /**
     * 验证支付回调
     */
    public function verifyPayment($paymentData);
    
    /**
     * 查询支付状态
     */
    public function queryPayment($transactionId);
}