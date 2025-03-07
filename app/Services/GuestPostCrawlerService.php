<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\GuestPostSite;
use App\Models\GuestPostCategory;
use Carbon\Carbon;

class GuestPostService
{
    protected $baseUrl;
    protected $lastError;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->baseUrl = config('services.guestpost.base_url', 'https://guestpostnow.com');
    }

    /**
     * 获取最后的错误信息
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * 从 guestpostnow.com 抓取分类列表
     *
     * @return array|bool
     */
    public function scrapeCategories()
    {
        try {
            $response = Http::get($this->baseUrl . '/categories');
            
            if (!$response->successful()) {
                $this->lastError = "请求失败: " . $response->status();
                return false;
            }
            
            $html = $response->body();
            
            // 使用 DOMDocument 解析 HTML
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);
            
            // 查找分类元素
            $categoryNodes = $xpath->query("//div[contains(@class, 'category-item')]");
            $categories = [];
            
            foreach ($categoryNodes as $node) {
                $nameNode = $xpath->query(".//h3", $node)->item(0);
                $linkNode = $xpath->query(".//a", $node)->item(0);
                $countNode = $xpath->query(".//span[contains(@class, 'count')]", $node)->item(0);
                
                if ($nameNode && $linkNode) {
                    $name = trim($nameNode->textContent);
                    $link = $linkNode->getAttribute('href');
                    $count = $countNode ? (int)preg_replace('/[^0-9]/', '', $countNode->textContent) : 0;
                    $slug = $this->extractSlugFromUrl($link);
                    
                    $categories[] = [
                        'name' => $name,
                        'name_zh' => $this->translateCategoryName($name),
                        'slug' => $slug,
                        'link' => $link,
                        'count' => $count
                    ];
                }
            }
            
            return $categories;
        } catch (\Exception $e) {
            $this->lastError = "抓取分类异常: " . $e->getMessage();
            Log::error('GuestPost抓取分类异常', ['exception' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 从 URL 中提取 slug
     *
     * @param string $url
     * @return string
     */
    protected function extractSlugFromUrl($url)
    {
        $parts = explode('/', rtrim($url, '/'));
        return end($parts);
    }

    /**
     * 简单的分类名称翻译功能
     *
     * @param string $name
     * @return string
     */
    protected function translateCategoryName($name)
    {
        $translations = [
            'Technology' => '科技',
            'Health' => '健康',
            'Business' => '商业',
            'Finance' => '金融',
            'Education' => '教育',
            'Travel' => '旅游',
            'Food' => '美食',
            'Fashion' => '时尚',
            'Sports' => '体育',
            'Entertainment' => '娱乐',
            'Digital Marketing' => '数字营销',
            'SEO' => 'SEO',
            'Lifestyle' => '生活方式',
            'News' => '新闻',
            'Automotive' => '汽车',
            'Real Estate' => '房地产',
            'Home Improvement' => '家居装修',
        ];
        
        return $translations[$name] ?? $name;
    }

    /**
     * 同步分类到数据库
     *
     * @return bool
     */
    public function syncCategories()
    {
        $categories = $this->scrapeCategories();
        
        if (!$categories) {
            return false;
        }
        
        DB::beginTransaction();
        
        try {
            $now = Carbon::now();
            
            foreach ($categories as $categoryData) {
                GuestPostCategory::updateOrCreate(
                    ['slug' => $categoryData['slug']],
                    [
                        'name' => $categoryData['name'],
                        'name_zh' => $categoryData['name_zh'],
                        'link' => $categoryData['link'],
                        'count' => $categoryData['count'],
                        'updated_at' => $now
                    ]
                );
            }
            
            // 可选：将长时间未更新的分类标记为不活跃
            GuestPostCategory::where('updated_at', '<', $now)->update(['active' => 0]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "同步分类失败: " . $e->getMessage();
            Log::error('同步分类失败', ['exception' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 从分类页面抓取网站列表
     *
     * @param string $categorySlug
     * @param int $page
     * @return array|bool
     */
    public function scrapeSitesFromCategory($categorySlug, $page = 1)
    {
        try {
            $url = "{$this->baseUrl}/category/{$categorySlug}";
            if ($page > 1) {
                $url .= "?page={$page}";
            }
            
            $response = Http::get($url);
            
            if (!$response->successful()) {
                $this->lastError = "请求失败: " . $response->status();
                return false;
            }
            
            $html = $response->body();
            
            // 使用 DOMDocument 解析 HTML
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);
            
            // 查找网站元素
            $siteNodes = $xpath->query("//div[contains(@class, 'site-item')]");
            $sites = [];
            
            foreach ($siteNodes as $node) {
                $nameNode = $xpath->query(".//h3", $node)->item(0);
                $linkNode = $xpath->query(".//a[contains(@class, 'site-link')]", $node)->item(0);
                $priceNode = $xpath->query(".//span[contains(@class, 'price')]", $node)->item(0);
                $daNode = $xpath->query(".//span[contains(text(), 'DA:')]", $node)->item(0);
                $drNode = $xpath->query(".//span[contains(text(), 'DR:')]", $node)->item(0);
                $trafficNode = $xpath->query(".//span[contains(text(), 'Traffic:')]", $node)->item(0);
                
                if ($nameNode && $linkNode) {
                    $name = trim($nameNode->textContent);
                    $link = $linkNode->getAttribute('href');
                    $price = $priceNode ? $this->extractPrice($priceNode->textContent) : 0;
                    
                    $da = $daNode ? $this->extractMetricValue($daNode->textContent, 'DA:') : 0;
                    $dr = $drNode ? $this->extractMetricValue($drNode->textContent, 'DR:') : 0;
                    $traffic = $trafficNode ? $this->extractTraffic($trafficNode->textContent) : 0;
                    
                    $sites[] = [
                        'name' => $name,
                        'link' => $link,
                        'price' => $price,
                        'domain_authority' => $da,
                        'domain_rating' => $dr,
                        'traffic' => $traffic,
                        'category_slug' => $categorySlug
                    ];
                }
            }
            
            // 检查是否有下一页
            $nextPageNode = $xpath->query("//a[contains(@class, 'pagination-next')]")->item(0);
            $hasNextPage = $nextPageNode !== null;
            
            return [
                'sites' => $sites,
                'has_next_page' => $hasNextPage,
                'current_page' => $page
            ];
        } catch (\Exception $e) {
            $this->lastError = "抓取网站异常: " . $e->getMessage();
            Log::error('GuestPost抓取网站异常', ['exception' => $e->getMessage(), 'category' => $categorySlug]);
            return false;
        }
    }

    /**
     * 从价格文本中提取数字
     *
     * @param string $priceText
     * @return float
     */
    protected function extractPrice($priceText)
    {
        return (float) preg_replace('/[^0-9.]/', '', $priceText);
    }

    /**
     * 从指标文本中提取数值
     *
     * @param string $text
     * @param string $prefix
     * @return int
     */
    protected function extractMetricValue($text, $prefix)
    {
        $text = str_replace($prefix, '', $text);
        return (int) preg_replace('/[^0-9]/', '', $text);
    }

    /**
     * 从流量文本中提取数值
     *
     * @param string $text
     * @return int
     */
    protected function extractTraffic($text)
    {
        $text = str_replace('Traffic:', '', $text);
        $text = trim($text);
        
        // 处理K/M/B单位
        $multiplier = 1;
        if (stripos($text, 'k') !== false) {
            $multiplier = 1000;
            $text = str_replace(['k', 'K'], '', $text);
        } elseif (stripos($text, 'm') !== false) {
            $multiplier = 1000000;
            $text = str_replace(['m', 'M'], '', $text);
        } elseif (stripos($text, 'b') !== false) {
            $multiplier = 1000000000;
            $text = str_replace(['b', 'B'], '', $text);
        }
        
        return (int) (floatval(preg_replace('/[^0-9.]/', '', $text)) * $multiplier);
    }

    /**
     * 同步特定分类的所有网站到数据库
     *
     * @param string $categorySlug
     * @return array
     */
    public function syncSitesForCategory($categorySlug)
    {
        $category = GuestPostCategory::where('slug', $categorySlug)->first();
        
        if (!$category) {
            $this->lastError = "找不到指定分类";
            return [
                'success' => false,
                'message' => $this->lastError
            ];
        }
        
        $page = 1;
        $totalSites = 0;
        $hasNextPage = true;
        
        DB::beginTransaction();
        
        try {
            $now = Carbon::now();
            
            while ($hasNextPage) {
                $result = $this->scrapeSitesFromCategory($categorySlug, $page);
                
                if (!$result) {
                    throw new \Exception($this->lastError);
                }
                
                $sites = $result['sites'];
                $hasNextPage = $result['has_next_page'];
                
                foreach ($sites as $siteData) {
                    GuestPostSite::updateOrCreate(
                        [
                            'name' => $siteData['name'],
                            'category_id' => $category->id
                        ],
                        [
                            'link' => $siteData['link'],
                            'price' => $siteData['price'],
                            'domain_authority' => $siteData['domain_authority'],
                            'domain_rating' => $siteData['domain_rating'],
                            'traffic' => $siteData['traffic'],
                            'active' => 1,
                            'updated_at' => $now
                        ]
                    );
                    
                    $totalSites++;
                }
                
                $page++;
                
                // 防止无限循环
                if ($page > 50) {
                    $hasNextPage = false;
                }
            }
            
            // 将长时间未更新的网站标记为不活跃
            GuestPostSite::where('category_id', $category->id)
                ->where('updated_at', '<', $now)
                ->update(['active' => 0]);
            
            DB::commit();
            
            return [
                'success' => true,
                'total_sites' => $totalSites,
                'category' => $category->name
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "同步网站失败: " . $e->getMessage();
            Log::error('同步网站失败', [
                'exception' => $e->getMessage(),
                'category' => $categorySlug
            ]);
            
            return [
                'success' => false,
                'message' => $this->lastError
            ];
        }
    }
    
    /**
     * 同步所有分类的网站
     *
     * @return array
     */
    public function syncAllSites()
    {
        $categories = GuestPostCategory::where('active', 1)->get();
        
        $results = [
            'total_categories' => $categories->count(),
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'total_sites' => 0,
            'errors' => []
        ];
        
        foreach ($categories as $category) {
            $result = $this->syncSitesForCategory($category->slug);
            $results['processed']++;
            
            if ($result['success']) {
                $results['success']++;
                $results['total_sites'] += $result['total_sites'];
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'category' => $category->name,
                    'error' => $result['message']
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * 获取单个网站的详细信息
     *
     * @param int $siteId
     * @return array|bool
     */
    public function getSiteDetails($siteId)
    {
        $site = GuestPostSite::find($siteId);
        
        if (!$site) {
            $this->lastError = "找不到指定网站";
            return false;
        }
        
        try {
            $response = Http::get($site->link);
            
            if (!$response->successful()) {
                $this->lastError = "请求失败: " . $response->status();
                return false;
            }
            
            $html = $response->body();
            
            // 使用 DOMDocument 解析 HTML
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);
            
            // 提取详细信息
            $descriptionNode = $xpath->query("//div[contains(@class, 'site-description')]")->item(0);
            $description = $descriptionNode ? trim($descriptionNode->textContent) : '';
            
            // 提取更多指标
            $metrics = [];
            $metricNodes = $xpath->query("//div[contains(@class, 'site-metrics')]/div");
            foreach ($metricNodes as $node) {
                $label = $xpath->query(".//span[contains(@class, 'metric-label')]", $node)->item(0);
                $value = $xpath->query(".//span[contains(@class, 'metric-value')]", $node)->item(0);
                
                if ($label && $value) {
                    $labelText = trim($label->textContent);
                    $valueText = trim($value->textContent);
                    $metrics[$labelText] = $valueText;
                }
            }
            
            // 提取发布要求
            $requirementsNode = $xpath->query("//div[contains(@class, 'publishing-requirements')]")->item(0);
            $requirements = $requirementsNode ? trim($requirementsNode->textContent) : '';
            
            // 更新网站信息
            $site->description = $description;
            $site->requirements = $requirements;
            $site->metrics_data = json_encode($metrics);
            $site->save();
            
            return [
                'site' => $site,
                'metrics' => $metrics
            ];
        } catch (\Exception $e) {
            $this->lastError = "获取网站详情异常: " . $e->getMessage();
            Log::error('获取网站详情异常', ['exception' => $e->getMessage(), 'site_id' => $siteId]);
            return false;
        }
    }
    
    /**
     * 提交Guest Post订单
     *
     * @param int $siteId
     * @param array $orderData
     * @return bool|array
     */
    public function placeGuestPostOrder($siteId, $orderData)
    {
        $site = GuestPostSite::find($siteId);
        
        if (!$site) {
            $this->lastError = "找不到指定网站";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            // 创建订单
            $order = new \App\Models\Order();
            $order->user_id = $orderData['user_id'];
            $order->service_type = 'guest_post';
            $order->guest_post_site_id = $siteId;
            $order->target_url = $orderData['target_url'];
            $order->title = $orderData['title'] ?? '';
            $order->content = $orderData['content'] ?? '';
            $order->anchor_text = $orderData['anchor_text'] ?? '';
            $order->comments = $orderData['comments'] ?? '';
            $order->price = $site->price;
            $order->quantity = 1;
            $order->status = 'pending';
            $order->payment_status = $orderData['payment_status'] ?? 'pending';
            $order->save();
            
            DB::commit();
            
            return [
                'success' => true,
                'order_id' => $order->id,
                'message' => '订单已提交成功，等待审核'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "提交订单失败: " . $e->getMessage();
            Log::error('提交Guest Post订单失败', [
                'exception' => $e->getMessage(),
                'site_id' => $siteId,
                'order_data' => $orderData
            ]);
            return false;
        }
    }
    
    /**
     * 管理员审核Guest Post订单
     *
     * @param int $orderId
     * @param string $status
     * @param string $adminNotes
     * @return bool|array
     */
    public function reviewGuestPostOrder($orderId, $status, $adminNotes = '')
    {
        $order = \App\Models\Order::where('id', $orderId)
            ->where('service_type', 'guest_post')
            ->first();
        
        if (!$order) {
            $this->lastError = "找不到指定订单";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            $order->status = $status;
            $order->admin_notes = $adminNotes;
            $order->review_time = Carbon::now();
            
            if ($status === 'rejected' && $order->payment_status === 'paid') {
                // 如果拒绝已支付的订单，应处理退款
                $order->refund_status = 'pending';
            }
            
            $order->save();
            
            DB::commit();
            
            // 发送邮件通知客户
            $this->sendOrderStatusNotification($order);
            
            return [
                'success' => true,
                'order_id' => $order->id,
                'message' => '订单已' . $this->getStatusText($status)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "审核订单失败: " . $e->getMessage();
            Log::error('审核Guest Post订单失败', [
                'exception' => $e->getMessage(),
                'order_id' => $orderId,
                'status' => $status
            ]);
            return false;
        }
    }
    
    /**
     * 获取状态文本
     *
     * @param string $status
     * @return string
     */
    protected function getStatusText($status)
    {
        $statusMap = [
            'pending' => '待审核',
            'processing' => '处理中',
            'completed' => '已完成',
            'rejected' => '已拒绝',
            'cancelled' => '已取消',
            'refunded' => '已退款'
        ];
        
        return $statusMap[$status] ?? $status;
    }
    
    /**
     * 发送订单状态通知
     *
     * @param \App\Models\Order $order
     * @return void
     */
    protected function sendOrderStatusNotification($order)
    {
        // 这里实现邮件通知逻辑
        // 可以使用 Laravel 的邮件功能
        // Mail::to($order->user->email)->send(new OrderStatusChanged($order));
    }
}