<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Order;
use App\Models\ApiOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ThirdPartyApiSetting;

class SEOeStoreApiService
{
    protected $apiKey;
    protected $apiUrl;
    
    public function __construct()
    {
        // 从数据库配置获取API设置
        $apiSettings = ThirdPartyApiSetting::where('name', 'seoestore')->first();
        
        if ($apiSettings) {
            $this->apiKey = $apiSettings->api_key;
            $this->apiUrl = $apiSettings->api_url;
        } else {
            $this->apiKey = config('services.seoestore.api_key');
            $this->apiUrl = config('services.seoestore.api_url');
        }
    }
    
    /**
     * 获取服务列表
     */
    public function getServices()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->apiUrl . '/services');
            
            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }
            
            Log::error('SEOeStore API Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('SEOeStore API Exception: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 提交订单到API
     */
    public function createOrder($serviceId, $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->post($this->apiUrl . '/orders', [
                'service' => $serviceId,
                'quantity' => $data['quantity'] ?? 1,
                'links' => $data['target_url'],
                'keywords' => $data['keywords'] ?? '',
                'extras' => $data['extras'] ?? [],
                'article' => $data['article'] ?? '',
                'notes' => $data['notes'] ?? '',
            ]);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'order_id' => $response->json()['data']['id'] ?? null,
                    'response' => $response->json()
                ];
            }
            
            Log::error('SEOeStore API Order Error: ' . $response->body());
            return [
                'success' => false,
                'message' => $response->json()['message'] ?? '订单创建失败',
                'response' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('SEOeStore API Order Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'response' => null
            ];
        }
    }
    
    /**
     * 获取订单状态
     */
    public function getOrderStatus($apiOrderId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->apiUrl . '/orders/' . $apiOrderId);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json()['data']['status'] ?? 'unknown',
                    'report' => $response->json()['data']['report'] ?? null,
                    'response' => $response->json()
                ];
            }
            
            Log::error('SEOeStore API Status Error: ' . $response->body());
            return [
                'success' => false,
                'message' => $response->json()['message'] ?? '获取订单状态失败',
                'response' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('SEOeStore API Status Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'response' => null
            ];
        }
    }
    
    /**
     * 同步API产品到本地
     */
    public function syncProducts()
    {
        $services = $this->getServices();
        
        if (empty($services)) {
            throw new \Exception('无法获取API服务列表');
        }
        
        $categoryId = \App\Models\PackageCategory::where('slug', 'third-party')->first()->id ?? 3;
        $created = 0;
        $updated = 0;
        
        foreach ($services as $service) {
            // 查找是否已存在该API产品
            $package = Package::where('third_party_id', $service['id'])->first();
            
            if ($package) {
                // 更新现有产品
                $package->update([
                    'name' => $service['name'] . ' (API)',
                    'name_en' => $service['name'],
                    'description' => $service['description'] ?? '',
                    'description_zh' => $this->translateToZh($service['name'] . ': ' . $service['description']),
                    'price' => $service['price'] * 1.2, // 加价20%
                    'original_price' => $service['price'],
                    'delivery_days' => $service['delivery_days'] ?? 7,
                    'features' => json_encode([
                        '自动API提交',
                        '实时状态更新',
                        $service['delivery_days'] ? $service['delivery_days'].'天交付' : '快速交付',
                        '详细报告'
                    ])
                ]);
                
                $updated++;
            } else {
                // 创建新产品
                $slug = Str::slug($service['name']) . '-' . rand(100, 999);
                
                Package::create([
                    'category_id' => $categoryId,
                    'third_party_id' => $service['id'],
                    'name' => $service['name'] . ' (API)',
                    'name_en' => $service['name'],
                    'slug' => $slug,
                    'description' => $service['description'] ?? '',
                    'description_zh' => $this->translateToZh($service['name'] . ': ' . $service['description']),
                    'features' => json_encode([
                        '自动API提交',
                        '实时状态更新',
                        $service['delivery_days'] ? $service['delivery_days'].'天交付' : '快速交付',
                        '详细报告'
                    ]),
                    'price' => $service['price'] * 1.2, // 加价20%
                    'original_price' => $service['price'],
                    'delivery_days' => $service['delivery_days'] ?? 7,
                    'package_type' => 'third_party',
                    'active' => true
                ]);
                
                $created++;
            }
        }
        
        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($services)
        ];
    }
    
    /**
     * 简单翻译到中文（实际实现可以对接翻译API）
     */
    protected function translateToZh($text)
    {
        // 在实际实现中，你可以使用百度/Google翻译API
        // 这里简单返回原文
        return $text . " (中文翻译将在此显示)";
    }
}