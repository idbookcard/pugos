<?php

// app/Helpers/AppHelpers.php
namespace App\Helpers;

use App\Models\SystemSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class AppHelpers
{
    /**
     * Get system setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getSetting($key, $default = null)
    {
        // Cache settings to avoid frequent database queries
        return Cache::remember('system_setting_' . $key, 3600, function () use ($key, $default) {
            $setting = SystemSetting::where('setting_key', $key)->first();
            return $setting ? $setting->setting_value : $default;
        });
    }
    
    /**
     * Format status for display
     *
     * @param string $status
     * @return array
     */
    public static function formatStatus($status)
    {
        $statusMap = [
            'pending' => ['label' => 'Pending', 'class' => 'bg-warning text-dark'],
            'processing' => ['label' => 'Processing', 'class' => 'bg-info text-white'],
            'completed' => ['label' => 'Completed', 'class' => 'bg-success text-white'],
            'canceled' => ['label' => 'Canceled', 'class' => 'bg-secondary text-white'],
            'rejected' => ['label' => 'Rejected', 'class' => 'bg-danger text-white'],
            'refunded' => ['label' => 'Refunded', 'class' => 'bg-dark text-white'],
            
            // Payment statuses
            'paid' => ['label' => 'Paid', 'class' => 'bg-success text-white'],
            'unpaid' => ['label' => 'Unpaid', 'class' => 'bg-warning text-dark'],
            
            // Transaction statuses
            'failed' => ['label' => 'Failed', 'class' => 'bg-danger text-white'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-secondary text-white'],
            
            // Invoice statuses
            'approved' => ['label' => 'Approved', 'class' => 'bg-primary text-white'],
            'sent' => ['label' => 'Sent', 'class' => 'bg-info text-white'],
            'expired' => ['label' => 'Expired', 'class' => 'bg-secondary text-white'],
        ];
        
        return $statusMap[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-secondary text-white'];
    }
    
    /**
     * Generate a unique order number
     *
     * @return string
     */
    public static function generateOrderNumber()
    {
        return 'ORD-' . date('YmdHis') . strtoupper(Str::random(4));
    }
    
    /**
     * Format price with currency
     *
     * @param float $price
     * @param string $currency
     * @return string
     */
    public static function formatPrice($price, $currency = 'CNY')
    {
        $symbols = [
            'CNY' => '¥',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥'
        ];
        
        $symbol = $symbols[$currency] ?? '';
        
        return $symbol . number_format($price, 2);
    }
    
    /**
     * Truncate text to a specific length with ellipsis
     *
     * @param string $text
     * @param int $length
     * @return string
     */
    public static function truncate($text, $length = 100)
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }
    
    /**
     * Convert package type to human-readable format
     *
     * @param string $type
     * @return string
     */
    public static function formatPackageType($type)
    {
        $types = [
            'single' => 'Single Package',
            'monthly' => 'Monthly Package',
            'third_party' => 'Third Party Service',
            'guest_post' => 'Guest Post'
        ];
        
        return $types[$type] ?? ucfirst($type);
    }
    
    /**
     * Get payment methods list
     *
     * @return array
     */
    public static function getPaymentMethods()
    {
        return [
            'wechat' => 'WeChat Pay',
            'alipay' => 'Alipay',
            'crypto' => 'Cryptocurrency',
            'balance' => 'Account Balance'
        ];
    }
    
    /**
     * Get crypto currencies list
     *
     * @return array
     */
    public static function getCryptoCurrencies()
    {
        return [
            'USDT' => 'Tether (USDT)',
            'BTC' => 'Bitcoin (BTC)',
            'ETH' => 'Ethereum (ETH)'
        ];
    }
    
    /**
     * Get crypto networks list by currency
     *
     * @param string $currency
     * @return array
     */
    public static function getCryptoNetworks($currency = null)
    {
        $networks = [
            'USDT' => [
                'TRC20' => 'TRON (TRC20)',
                'ERC20' => 'Ethereum (ERC20)',
                'BEP20' => 'Binance Smart Chain (BEP20)'
            ],
            'BTC' => [
                'Bitcoin' => 'Bitcoin Network'
            ],
            'ETH' => [
                'ERC20' => 'Ethereum Network'
            ]
        ];
        
        return $currency ? ($networks[$currency] ?? []) : $networks;
    }
}