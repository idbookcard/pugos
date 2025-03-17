<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class PaymentFactory
{
    /**
     * 创建支付服务实例
     */
    public static function create($paymentMethod)
    {
        switch ($paymentMethod) {
            case 'wechat':
                return new WechatPaymentService();
            case 'alipay':
                return new AlipayPaymentService();
            case 'crypto':
                return new CryptoPaymentService();
            default:
                throw new InvalidArgumentException("不支持的支付方式: {$paymentMethod}");
        }
    }
}