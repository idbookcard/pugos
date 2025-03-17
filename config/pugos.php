<?php

// config/services.php (update the existing file)

return [
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    
    // Add the following services configuration

    'wechat' => [
        'app_id' => env('WECHAT_APP_ID'),
        'mch_id' => env('WECHAT_MCH_ID'),
        'api_key' => env('WECHAT_API_KEY'),
        'api_url' => env('WECHAT_API_URL', 'https://api.mch.weixin.qq.com/pay'),
        'cert_path' => env('WECHAT_CERT_PATH'),
        'key_path' => env('WECHAT_KEY_PATH'),
    ],

    'alipay' => [
        'app_id' => env('ALIPAY_APP_ID'),
        'private_key' => env('ALIPAY_PRIVATE_KEY'),
        'public_key' => env('ALIPAY_PUBLIC_KEY'),
        'alipay_public_key' => env('ALIPAY_ALIPAY_PUBLIC_KEY'),
        'api_url' => env('ALIPAY_API_URL', 'https://openapi.alipay.com/gateway.do'),
        'return_url' => env('ALIPAY_RETURN_URL'),
        'notify_url' => env('ALIPAY_NOTIFY_URL'),
    ],
    
    'crypto' => [
        'webhook_key' => env('CRYPTO_WEBHOOK_KEY'),
    ],
    
    'seoestore' => [
        'api_url' => env('SEOESTORE_API_URL'),
        'api_key' => env('SEOESTORE_API_KEY'),
        'webhook_key' => env('SEOESTORE_WEBHOOK_KEY'),
    ],
];