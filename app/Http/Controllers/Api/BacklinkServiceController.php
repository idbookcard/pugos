<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SeoEstoreApiService;
use App\Services\GuestPostService;
use App\Services\OrderService;
use App\Services\PackageService;
use App\Models\Package;
use App\Models\ExternalService;
use App\Models\ExternalServiceCategory;
use App\Models\GuestPostSite;
use App\Models\GuestPostCategory;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BacklinkServiceController extends Controller
{
    protected $seoEstoreApiService;
    protected $guestPostService;
    protected $orderService;
    protected $packageService;
    
    /**
     * 构造函数
     *
     * @param SeoEstoreApiService $seoEstoreApiService
     * @param GuestPostService $guestPostService
     * @param OrderService $orderService
     * @param PackageService $packageService
     */
    public function __construct(
        SeoEstoreApiService $seoEstoreApiService,
        GuestPostService $guestPostService,
        OrderService $orderService,
        PackageService $packageService
    ) {
        $this->seoEstoreApiService = $seoEstoreApiService;
        $this->guestPostService = $guestPostService;
        $this->orderService = $orderService;
        $this->packageService = $packageService;
    }
    
    /**
     * 获取所有套餐分类
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPackageCategories()
    {
        $categories = $this->packageService->getAllCategories();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    /**
     * 获取分类下的套餐
     *
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPackagesByCategory($categoryId)
    {
        $packages = $this->packageService->getPackagesByCategory($categoryId);
        
        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }
    
    /**
     * 获取套餐详情
     *
     * @param int $packageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPackageDetails($packageId)
    {
        $package = $this->packageService->getPackage($packageId);
        
        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => '找不到指定套餐'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $package
        ]);
    }
    
    /**
     * 获取所有第三方服务类别
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExternalServiceCategories()
    {
        $categories = ExternalServiceCategory::where('active', 1)
            ->orderBy('name_zh')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    /**
     * 获取分类下的第三方服务
     *
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExternalServicesByCategory($categoryId)
    {
        $services = ExternalService::where('category_id', $categoryId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }
    
    /**
     * 获取第三方服务详情
     *
     * @param int $serviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExternalServiceDetails($serviceId)
    {
        $service = ExternalService::find($serviceId);
        
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => '找不到指定服务'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }
    
    /**
     * 获取所有 Guest Post 类别
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGuestPostCategories()
    {
        $categories = GuestPostCategory::where('active', 1)
            ->orderBy('name_zh')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    /**
     * 获取分类下的 Guest Post 网站
     *
     * @param Request $request
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGuestPostSitesByCategory(Request $request, $categoryId)
    {
        $query = GuestPostSite::where('category_id', $categoryId)
            ->where('active', 1);
        
        // 筛选条件：价格范围
        if ($request->has('price_min')) {
            $query->where('price', '>=', (float)$request->price_min);
        }
        
        if ($request->has('price_max')) {
            $query->where('price', '<=', (float)$request->price_max);
        }
        
        // 筛选条件：DA 值范围
        if ($request->has('da_min')) {
            $query->where('domain_authority', '>=', (int)$request->da_min);
        }
        
        if ($request->has('da_max')) {
            $query->where('domain_authority', '<=', (int)$request->da_max);
        }
        
        // 排序
        $sortBy = $request->input('sort_by', 'price');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if (in_array($sortBy, ['price', 'domain_authority', 'domain_rating', 'traffic'])) {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        $sites = $query->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $sites
        ]);
    }
    
    /**
     * 获取 Guest Post 网站详情
     *
     * @param int $siteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGuestPostSiteDetails($siteId)
    {
        $site = GuestPostSite::find($siteId);
        
        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => '找不到指定网站'
            ], 404);
        }
        
        // 如果没有详细信息，尝试抓取
        if (empty($site->description)) {
            $result = $this->guestPostService->getSiteDetails($siteId);
            
            if ($result) {
                $site = $result['site'];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $site
        ]);
    }
    
    /**
     * 创建套餐订单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPackageOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'target_url' => 'required|url',
            'keywords' => 'required|string',
            'anchor_text' => 'nullable|string',
            'comments' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '请求参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $orderData = $request->all();
        $orderData['user_id'] = Auth::id();
        $orderData['payment_status'] = 'pending';
        
        $result = $this->orderService->createPackageOrder($orderData);
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => $this->orderService->getLastError() ?: '订单创建失败'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $result['order_id'],
                'message' => $result['message']
            ]
        ]);
    }
    
    /**
     * 创建第三方服务订单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createExternalServiceOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_service_id' => 'required|exists:external_services,id',
            'target_url' => 'required|url',
            'quantity' => 'required|integer|min:1',
            'anchor_text' => 'nullable|string',
            'comments' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '请求参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $orderData = $request->all();
        $orderData['user_id'] = Auth::id();
        $orderData['payment_status'] = 'pending';
        
        $result = $this->orderService->createExternalServiceOrder($orderData);
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => $this->orderService->getLastError() ?: '订单创建失败'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $result['order_id'],
                'message' => $result['message']
            ]
        ]);
    }
    
    /**
     * 创建 Guest Post 订单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createGuestPostOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_post_site_id' => 'required|exists:guest_post_sites,id',
            'target_url' => 'required|url',
            'title' => 'required|string',
            'content' => 'required|string|min:300',
            'anchor_text' => 'required|string',
            'comments' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '请求参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $orderData = $request->all();
        $orderData['user_id'] = Auth::id();
        $orderData['payment_status'] = 'pending';
        
        $result = $this->orderService->createGuestPostOrder($orderData);
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => $this->orderService->getLastError() ?: '订单创建失败'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $result['order_id'],
                'message' => $result['message']
            ]
        ]);
    }
    
    /**
     * 获取用户订单列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserOrders(Request $request)
    {
        $filters = $request->only(['status', 'service_type', 'date_from', 'date_to', 'search']);
        $perPage = $request->input('per_page', 10);
        
        $orders = $this->orderService->getUserOrders(Auth::id(), $filters, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
    
    /**
     * 获取订单详情
     *
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderDetails($orderId)
    {
        $result = $this->orderService->getOrderDetails($orderId, Auth::id());
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => $this->orderService->getLastError() ?: '找不到指定订单'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * 取消订单
     *
     * @param Request $request
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '请求参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->orderService->cancelOrder($orderId, $request->reason, Auth::id());
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => $this->orderService->getLastError() ?: '订单取消失败'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $result['order_id'],
                'message' => $result['message']
            ]
        ]);
    }
    
    /**
     * 搜索 Guest Post 网站
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchGuestPostSites(Request $request)
    {
        $query = GuestPostSite::where('active', 1);
        
        // 搜索条件
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }
        
        // 筛选条件：价格范围
        if ($request->has('price_min')) {
            $query->where('price', '>=', (float)$request->price_min);
        }
        
        if ($request->has('price_max')) {
            $query->where('price', '<=', (float)$request->price_max);
        }
        
        // 筛选条件：DA 值范围
        if ($request->has('da_min')) {
            $query->where('domain_authority', '>=', (int)$request->da_min);
        }
        
        if ($request->has('da_max')) {
            $query->where('domain_authority', '<=', (int)$request->da_max);
        }
        
        // 分类筛选
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // 排序
        $sortBy = $request->input('sort_by', 'price');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if (in_array($sortBy, ['price', 'domain_authority', 'domain_rating', 'traffic'])) {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        $sites = $query->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $sites
        ]);
    }
    
    /**
     * 获取订单统计信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderStats()
    {
        $userId = Auth::id();
        
        $stats = [
            'total' => Order::where('user_id', $userId)->count(),
            'pending' => Order::where('user_id', $userId)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $userId)->where('status', 'processing')->count(),
            'completed' => Order::where('user_id', $userId)->where('status', 'completed')->count(),
            'cancelled' => Order::where('user_id', $userId)->where('status', 'cancelled')->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * 获取推荐套餐
     *
     * @param int $limit
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeaturedPackages($limit = 6)
    {
        $packages = Package::where('active', 1)
            ->where('is_featured', 1)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }
}