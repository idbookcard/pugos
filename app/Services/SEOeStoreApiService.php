<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Order;
use App\Models\ApiOrder;
use App\Models\ExtraOption; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ThirdPartyApiSetting;
use Illuminate\Support\Facades\DB;

class SEOeStoreApiService
{
    protected $apiKey;
    protected $apiUrl;
    protected $email;
    protected $lastError;
    
    public function __construct()
    {
        // 从数据库配置获取API设置
        $apiSettings = ThirdPartyApiSetting::where('name', 'seoestore')->first();
        
        if ($apiSettings) {
            $this->apiKey = $apiSettings->api_key;
            $this->apiUrl = $apiSettings->api_url;
            // 从额外设置中获取电子邮件
            $settings = $apiSettings->settings ? json_decode($apiSettings->settings, true) : [];
            $this->email = $apiSettings->email ? $apiSettings->email : config('services.seoestore.email');
        } else {
            $this->apiKey = config('services.seoestore.api_key');
            $this->apiUrl = config('services.seoestore.api_url');
            $this->email = config('services.seoestore.email');
        }
        
        // 确保API URL末尾没有斜杠
        $this->apiUrl = rtrim($this->apiUrl, '/');
    }
    
    /**
     * 获取最后一个错误信息
     */
    public function getLastError()
    {
        return $this->lastError;
    }
    
    /**
     * 发送API请求
     *
     * @param string $action 操作名称
     * @param array $params 额外参数
     * @return array|false 返回响应数据或false
     */
    protected function sendRequest($action, $params = [])
    {
        try {
            // 准备请求数据
            $data = array_merge([
                'api_key' => $this->apiKey,
                'email' => $this->email,
                'action' => $action
            ], $params);
            
            // 发送请求
            $response = Http::asForm()->post($this->apiUrl, $data);
           
            // 记录请求，用于调试
            Log::debug('SEOeStore API Request', [
                'action' => $action,
                'params' => array_merge($params, ['api_key' => '[HIDDEN]']),
                'url' => $this->apiUrl
            ]);
            
            // 检查响应
            if ($response->successful()) {
                $responseData = $response->json();
             
                // 如果API返回了错误信息
                if (isset($responseData['error'])) {
                    $this->lastError = $responseData['error'];
                    Log::error('SEOeStore API Error: ' . $responseData['error']);
                    return false;
                }
                
                return $responseData;
            }
            
            $this->lastError = 'HTTP Error: ' . $response->status() . ' - ' . $response->body();
            Log::error('SEOeStore API HTTP Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return false;
        } catch (\Exception $e) {
            $this->lastError = 'Exception: ' . $e->getMessage();
            Log::error('SEOeStore API Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取账户余额
     */
    public function getBalance()
    {
        $response = $this->sendRequest('balance');
        
        if ($response && isset($response['balance'])) {
            return [
                'success' => true,
                'balance' => $response['balance'],
                'currency' => $response['currency'] ?? 'USD'
            ];
        }
        
        return [
            'success' => false,
            'message' => $this->lastError ?? '无法获取账户余额'
        ];
    }
    
    /**
     * 获取服务列表
     */
    public function getServices()
    {
        $response = $this->sendRequest('services');
       
        // 根据实际返回数据格式处理
        if ($response && isset($response['data']) && is_array($response['data'])) {
            return $response['data'];
        } elseif ($response && is_array($response) && !isset($response['success'])) {
            // 如果直接返回了数组，不包含在 'services' 键中
            return $response;
        }
        
        return [];
    }
    
    /**
     * 获取所有extras选项
     */
    public function getExtras()
    {
        $response = $this->sendRequest('extras');
        
        // 根据实际返回数据格式处理
        if ($response && isset($response['data']) && is_array($response['data'])) {
            return $response['data'];
        } elseif ($response && is_array($response) && !isset($response['success'])) {
            // 如果直接返回了数组
            return $response;
        }
        
        return [];
    }
    
    /**
     * 提交订单到API
     *
     * @param int $serviceId 服务ID
     * @param array $data 订单数据
     * @return array 响应结果
     */
    public function createOrder($serviceId, $data)
    {
        // 准备订单参数
        $params = [
            'service' => $serviceId,
            'link' => $data['target_url'] ?? '',
            'quantity' => $data['quantity'] ?? 1
        ];
        
        // 如果有额外的选项ID，添加到请求中
        if (!empty($data['extras_id'])) {
            $params['extras'] = $data['extras_id'];
        }
        
        // 添加可选参数
        if (!empty($data['keywords'])) {
            $params['keywords'] = $data['keywords'];
        }
        
        if (!empty($data['article'])) {
            $params['article'] = $data['article'];
        }
        
        if (!empty($data['notes'])) {
            $params['comments'] = $data['notes'];
        }
        
        // 处理额外参数
        if (!empty($data['extras']) && is_array($data['extras'])) {
            foreach ($data['extras'] as $key => $value) {
                if (!isset($params[$key]) && !empty($value)) {
                    $params[$key] = $value;
                }
            }
        }
        
        // 发送添加订单请求
        $response = $this->sendRequest('add', $params);
        
        if ($response && isset($response['order'])) {
            return [
                'success' => true,
                'order_id' => $response['order'],
                'response' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => $this->lastError ?? '订单创建失败',
            'response' => $response
        ];
    }
    
    /**
     * 获取订单状态
     *
     * @param string|int $orderId 订单ID
     * @return array 状态结果
     */
    public function getOrderStatus($orderId)
    {
        $response = $this->sendRequest('status', ['order' => $orderId]);
        
        if ($response && isset($response['status'])) {
            return [
                'success' => true,
                'status' => $response['status'],
                'remains' => $response['remains'] ?? 0,
                'start_count' => $response['start_count'] ?? 0,
                'currency' => $response['currency'] ?? null,
                'charge' => $response['charge'] ?? null,
                'response' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => $this->lastError ?? '获取订单状态失败',
            'response' => $response
        ];
    }
    
    /**
     * 获取多个订单状态
     *
     * @param array $orderIds 订单ID数组
     * @return array 状态结果数组
     */
    public function getMultipleOrderStatus($orderIds)
    {
        $orders = implode(',', $orderIds);
        $response = $this->sendRequest('status', ['orders' => $orders]);
        
        if ($response && isset($response['orders'])) {
            return [
                'success' => true,
                'orders' => $response['orders']
            ];
        }
        
        return [
            'success' => false,
            'message' => $this->lastError ?? '获取多个订单状态失败'
        ];
    }
    
    /**
     * 获取单个服务详情
     * 
     * @param string|int $serviceId 服务ID
     * @return array|false 服务详情或false
     */
    public function getServiceDetails($serviceId)
    {
        // 尝试从API获取单个服务详情
        // 如果API提供了获取单个服务的端点，可以直接调用
        $response = $this->sendRequest('service', ['id' => $serviceId]);
        
        if ($response && isset($response['data'])) {
            return $response['data'];
        }
        
        // 如果API不支持获取单个服务，则从所有服务中查找
        $services = $this->getServices();
        
        if (!empty($services)) {
            foreach ($services as $service) {
                if ($service['id'] == $serviceId) {
                    return $service;
                }
            }
        }
        
        // 记录日志
        Log::warning('无法获取服务详情: ' . $serviceId);
        
        return false;
    }
    
    /**
     * 获取服务的额外选项列表
     * 
     * @param string|int $serviceId 服务ID
     * @return array 额外选项列表
     */
    public function getServiceExtras($serviceId)
    {
        // 获取服务信息
        $service = $this->getServiceDetails($serviceId);
        
        if (!$service || !isset($service['extras']) || empty($service['extras'])) {
            return [];
        }
        
        // 获取所有extras
        $allExtras = $this->getExtras();
        
        if (empty($allExtras)) {
            return [];
        }
        
        // 提取服务的extras ID
        $extrasIds = explode(',', $service['extras']);
        
        // 过滤出服务的extras
        $serviceExtras = [];
        foreach ($allExtras as $extra) {
            if (in_array($extra['id'], $extrasIds)) {
                $serviceExtras[] = [
                    'id' => $extra['id'],
                    'code' => $extra['code'] ?? '',
                    'name' => $extra['description'] ?? $extra['name'] ?? '',
                    'price' => floatval($extra['price'] ?? 0),
                    'is_multiple' => ($extra['multiple'] ?? 0) == '1'
                ];
            }
        }
        
        return $serviceExtras;
    }
    
   /**
 * 同步API产品到本地，包括extras
 */
public function syncProducts()
{
    try {
        DB::beginTransaction();
        
        // 1. 获取所有服务
        $services = $this->getServices();
       
        if (empty($services)) {
            throw new \Exception('无法获取API服务列表');
        }
        
        // 2. 获取所有extras
        $extrasData = $this->getExtras();
        
        // 如果没有正确的API接口获取extras，可以手动导入
        // $extrasData = json_decode(file_get_contents(storage_path('app/extras.json')), true);
        
        // 如果extras为空，记录警告但继续执行
        if (empty($extrasData)) {
            Log::warning('无法获取额外选项数据，产品将不包含extras信息');
        }
        
        // 3. 处理extras，按ID建立查找表
        $extrasMap = [];
        foreach ($extrasData as $extra) {
            $extrasMap[$extra['id']] = $extra;
            
            // 同步到ExtraOption表
            ExtraOption::updateOrCreate(
                ['extra_id' => $extra['id']],
                [
                    'code' => $extra['code'],
                    'name' => $extra['description'],
                    'name_zh' => $this->translateToZh($extra['description']),
                    'price' => floatval($extra['price']),
                    'is_multiple' => $extra['multiple'] == '1',
                ]
            );
        }
        
        $categoryId = \App\Models\PackageCategory::where('slug', 'third-party')->first()->id ?? 3;
        $created = 0;
        $updated = 0;
        
        foreach ($services as $service) {
            // 查找是否已存在该API产品
            $package = Package::where('third_party_id', $service['id'])->first();
            
            // 从min_qty获取最小数量
            $minQuantity = isset($service['min_qty']) ? (int)$service['min_qty'] : 1;
            
            // 默认交付时间（由于API未提供，使用固定值）
            $deliveryDays = 7;
            
            // 处理价格
            $priceString = isset($service['price']) ? (string) $service['price'] : '0';
            $priceFloat = floatval($priceString);
           
            $originalPrice = round(($priceFloat * 7.4) / 100, 7);
            $sellPrice = round($originalPrice * 1.5, 7);
            
            // 处理extras
            $availableExtras = [];
            $extrasFeatures = [];
            
            if (!empty($service['extras'])) {
                $extrasIds = explode(',', $service['extras']);
                
                foreach ($extrasIds as $extraId) {
                    if (isset($extrasMap[$extraId])) {
                        $extra = $extrasMap[$extraId];
                        $availableExtras[] = [
                            'id' => $extra['id'],
                            'code' => $extra['code'],
                            'name' => $extra['description'],
                            'price' => floatval($extra['price']),
                            'is_multiple' => $extra['multiple'] == '1'
                        ];
                        
                        // 为产品特性添加主要extras
                        $extrasFeatures[] = "可选: " . $extra['description'];
                    }
                }
                
                // 限制extras特性数量，避免过多
                $extrasFeatures = array_slice($extrasFeatures, 0, 5);
            }
            
            // 生成产品特性
            $features = [
                '自动API提交',
                '实时状态更新',
                $deliveryDays . '天交付',
                '最小数量: ' . $minQuantity,
                'Contextual: ' . ($service['contextual'] == '1' ? '是' : '否')
            ];
            
            // 添加extras特性
            $features = array_merge($features, $extrasFeatures);
            
            $packageData = [
                'name' => $service['code'] . ' - ' . $service['description'] . ' (API)',
                'name_en' => $service['description'],
                'description' => $service['description'] ?? '',
                'description_zh' => $this->translateToZh($service['description'] ?? ''),
                'price' => $sellPrice,
                'original_price' => $originalPrice,
                'delivery_days' => $deliveryDays,
                'is_api_product' => true,
                'features' => json_encode($features),
                'available_extras' => json_encode($availableExtras),
                'min_quantity' => $minQuantity,
                'is_contextual' => $service['contextual'] == '1'
            ];
            
            if ($package) {
              
                // 更新现有产品
                $package->update($packageData);
              
               
                $updated++;
            } else {
                // 创建新产品
                $slug = Str::slug($service['code']) . '-' . rand(100, 999);
                
                Package::create(array_merge($packageData, [
                    'category_id' => $categoryId,
                    'third_party_id' => $service['id'],
                    'slug' => $slug,
                    'package_type' => 'third_party',
                    'active' => true
                ]));
                
                $created++;
            }
        }
        
        DB::commit();
        
        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($services),
            'extras_count' => count($extrasData)
        ];
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('产品同步失败: ' . $e->getMessage());
        throw $e;
    }
}
    
    /**
     * 为单个产品同步额外选项
     * 
     * @param Package $package 产品模型
     * @return array 同步结果
     */
    public function syncPackageExtras(Package $package)
    {
        if (!$package->is_api_product || !$package->third_party_id) {
            return [
                'success' => false,
                'message' => '只有API产品才能同步额外选项'
            ];
        }
        
        try {
            // 获取服务的额外选项
            $serviceExtras = $this->getServiceExtras($package->third_party_id);
            
            if (empty($serviceExtras)) {
                // 更新包裹的可用额外选项为空数组
                $package->available_extras = json_encode([]);
                $package->save();
                
                return [
                    'success' => true,
                    'message' => '此服务没有可用的额外选项',
                    'extras_count' => 0
                ];
            }
            
            // 更新包裹的可用额外选项
            $package->available_extras = json_encode($serviceExtras);
            $package->save();
            
            return [
                'success' => true,
                'message' => '成功同步额外选项，共' . count($serviceExtras) . '个选项',
                'extras_count' => count($serviceExtras)
            ];
        } catch (\Exception $e) {
            Log::error('为产品同步额外选项失败: ' . $e->getMessage(), [
                'package_id' => $package->id,
                'third_party_id' => $package->third_party_id
            ]);
            
            return [
                'success' => false,
                'message' => '同步失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 测试API连接
     * 用于测试SEOeStore API连接是否正常
     * 
     * @return array 测试结果，包含success、message和可能的data
     */
    public function test()
    {
        try {
            // 尝试获取服务列表作为测试
            $services = $this->getServices();
            
            if (empty($services)) {
                return [
                    'success' => false,
                    'message' => 'API连接成功，但未获取到服务列表',
                    'data' => null
                ];
            }
            
            // 也测试获取额外选项
            $extras = $this->getExtras();
            $extrasStatus = empty($extras) ? '但未获取到额外选项' : '并获取到 ' . count($extras) . ' 个额外选项';
            
            return [
                'success' => true,
                'message' => '连接成功，获取到 ' . count($services) . ' 个服务' . $extrasStatus,
                'data' => [
                    'service_count' => count($services),
                    'extras_count' => count($extras),
                    'first_services' => array_slice($services, 0, 3), // 返回前3个服务作为示例
                    'first_extras' => array_slice($extras, 0, 3)  // 返回前3个extras作为示例
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'API连接失败: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * 手动导入额外选项数据
     * 用于在API不提供extras接口时手动导入
     * 
     * @param array $extrasData 额外选项数据
     * @return array 导入结果
     */
    public function importExtras(array $extrasData)
    {
        try {
            $count = 0;
            
            foreach ($extrasData as $extra) {
                if (!isset($extra['id'])) {
                    Log::warning('额外选项数据缺少ID', ['extra' => $extra]);
                    continue;
                }
                
                ExtraOption::updateOrCreate(
                    ['extra_id' => $extra['id']],
                    [
                        'code' => $extra['code'] ?? '',
                        'name' => $extra['description'] ?? $extra['name'] ?? '',
                        'name_zh' => $this->translateToZh($extra['description'] ?? $extra['name'] ?? ''),
                        'price' => floatval($extra['price'] ?? 0),
                        'is_multiple' => ($extra['multiple'] ?? 0) == '1',
                        'active' => true
                    ]
                );
                $count++;
            }
            
            return [
                'success' => true,
                'message' => "成功导入 $count 个额外选项",
                'count' => $count
            ];
        } catch (\Exception $e) {
            Log::error('导入额外选项失败: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => '导入额外选项失败: ' . $e->getMessage()
            ];
        }
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