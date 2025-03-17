<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use App\Models\ExtraOption;
use App\Services\OrderService;
use App\Services\AccountService;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;

class OrderController extends Controller
{
    protected $orderService;
    protected $accountService;
    
    public function __construct(OrderService $orderService, AccountService $accountService)
    {
        $this->orderService = $orderService;
        $this->accountService = $accountService;
        
        // 只对特定方法应用auth中间件
        $this->middleware('auth')->only(['index', 'store', 'show']);
    }
    
    
    
    /**
     * 显示创建订单页面
     * 未登录用户也可访问此页面
     */
    public function create_old($slug)
    {
        $package = Package::where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();
            
        // 获取用户余额信息（如果已登录）
        $balance = 0;
        $sufficientBalance = false;
        
        if (auth()->check()) {
            $user = auth()->user();
            $balance = $user->total_balance;
            $sufficientBalance = $balance >= $package->price;
        }
        
        // 检查包含额外选项
        $hasExtras = !empty($package->available_extras);
            
        return view('orders.create', compact('package', 'balance', 'sufficientBalance', 'hasExtras'));
    }


     /**
     * 显示创建订单页面 - 根据订单类型选择对应视图
     */
    public function create(Request $request, $packageSlug)
    {

        $package = Package::where('slug', $packageSlug)->firstOrFail();
        $balance = auth()->check() ? auth()->user()->balance : 0;
        
        // 确保提供初始余额足够状态给视图
        $sufficientBalance = $balance >= $package->price;
        // 检查包含额外选项
        $hasExtras = !empty($package->available_extras);
        
        // 根据套餐类型选择不同的视图
        return view($this->getOrderViewByPackageType($package), compact('package', 'balance', 'sufficientBalance', 'hasExtras'));

    }

    /**
 * 获取适合当前套餐类型的视图名称
 *
 * @param Package $package
 * @return string
 */
private function getOrderViewByPackageType(Package $package)
{
    switch ($package->package_type) {
        case 'monthly':
            return 'orders.create-monthly-order';
        case 'third_party':
            return 'orders.create-api-order';
        case 'guest_post':
            return 'orders.create-guest-post-order';
        default: // 单项套餐
            return 'orders.create-single-order';
    }
}


    /**
     * 创建订单
     */
    public function store(Request $request)
    {
        // 验证基本订单信息
        $validatedData = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'order_type' => 'required|string',
        ]);
        
        // 获取套餐信息
        $package = Package::findOrFail($request->package_id);
        
        // 根据订单类型分发到不同的处理方法
        if ($package->package_type === 'monthly' || $request->order_type === 'monthly') {
            return $this->storeMonthlyOrder($request, $package);
        } elseif ($package->package_type === 'third_party') {
            return $this->storeAPIOrder($request, $package);
        } elseif ($package->package_type === 'guest_post') {
            return $this->storeGuestPostOrder($request, $package);
        } else { // 单项套餐
            return $this->storeSingleOrder($request, $package);
        }
    }

    /**
     * 处理包月订单特殊逻辑
     */
    private function storeMonthlyOrder(Request $request, Package $package)
    {
        // 验证包月订单专有数据
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'website' => 'required|url',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'contact_email' => 'required|email',
            'industry' => 'nullable|string|max:255',
            'business_hours' => 'nullable|string',
            'contact_name' => 'required|string|max:255',
            'description' => 'required|string',
            'keywords' => 'required|string',
            'social_media' => 'nullable|string',
            'custom_article' => 'nullable|file|mimes:doc,docx,pdf|max:10240',
            
            // 验证每周任务 - 使用现有视图的字段名
            'week1_url' => 'required|url',
            'week1_keywords' => 'required|string',
            'week1_description' => 'nullable|string',
            'week2_url' => 'required|url',
            'week2_keywords' => 'required|string',
            'week2_description' => 'nullable|string',
            'week3_url' => 'required|url',
            'week3_keywords' => 'required|string',
            'week3_description' => 'nullable|string',
            'week4_url' => 'required|url',
            'week4_keywords' => 'required|string',
            'week4_description' => 'nullable|string',
        ]);
        
        // 计算总价格（包括额外选项）
        $totalPrice = $package->price;
        $selectedExtras = $this->processExtras($request, $package, $totalPrice);
        
        // 检查余额是否足够
        $user = auth()->user();
        if ($user->balance < $totalPrice) {
            return redirect()->route('wallet.deposit')
                ->with('error', '余额不足，请先充值');
        }

        try {
            // 创建基本订单
            $order = new Order();
            $order->user_id = auth()->id();
            $order->package_id = $package->id;
            $order->service_type = $package->package_type;
            $order->order_number = 'ORD-' . date('Ymd') . '-' . Str::random(5);
            $order->total_amount = $totalPrice;
            $order->status = 'pending';
            $order->payment_status = 'unpaid';
            $order->selected_extras = $selectedExtras;
            $order->save();
            
            // 处理文件上传
            $articleFilePath = null;
            if ($request->hasFile('custom_article')) {
                $articleFilePath = $request->file('custom_article')->store('order_articles');
            }
            
            // 保存包月订单详情
            $monthlyDetail = new MonthlyOrderDetail();
            $monthlyDetail->order_id = $order->id;
            $monthlyDetail->company_name = $request->company_name;
            $monthlyDetail->website = $request->website;
            $monthlyDetail->phone = $request->phone;
            $monthlyDetail->address = $request->address;
            $monthlyDetail->contact_email = $request->contact_email;
            $monthlyDetail->industry = $request->industry;
            $monthlyDetail->business_hours = $request->business_hours;
            $monthlyDetail->contact_name = $request->contact_name;
            $monthlyDetail->description = $request->description;
            $monthlyDetail->services_keywords = $request->keywords; // 注意字段名映射
            $monthlyDetail->social_media = $request->social_media;
            $monthlyDetail->article_file_path = $articleFilePath;
            $monthlyDetail->save();
            
            // 保存每周任务 - 从扁平结构转换为数组结构
            $weeklyTasks = [
                [
                    'week_number' => 1,
                    'target_url' => $request->week1_url,
                    'keywords' => $request->week1_keywords,
                    'description' => $request->week1_description
                ],
                [
                    'week_number' => 2,
                    'target_url' => $request->week2_url,
                    'keywords' => $request->week2_keywords,
                    'description' => $request->week2_description
                ],
                [
                    'week_number' => 3,
                    'target_url' => $request->week3_url,
                    'keywords' => $request->week3_keywords,
                    'description' => $request->week3_description
                ],
                [
                    'week_number' => 4,
                    'target_url' => $request->week4_url,
                    'keywords' => $request->week4_keywords,
                    'description' => $request->week4_description
                ]
            ];
            
            foreach ($weeklyTasks as $task) {
                $weeklyTask = new MonthlyOrderWeeklyTask();
                $weeklyTask->order_id = $order->id;
                $weeklyTask->week_number = $task['week_number'];
                $weeklyTask->target_url = $task['target_url'];
                $weeklyTask->keywords = $task['keywords'];
                $weeklyTask->description = $task['description'];
                $weeklyTask->save();
            }
            
            // 执行后续处理逻辑，如更新余额、发送通知等
            $this->processOrderPayment($order);
            
            return redirect()->route('orders.show', $order->id)
                ->with('success', '包月订单创建成功！我们已开始处理您的订单。');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', '创建订单失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 处理第三方API订单
     */
    public function storeAPIOrder(Request $request, Package $package)
    {
        // 验证API订单数据
        $validatedData = $request->validate([
            'target_url' => 'required|url',
            'keywords' => 'required|string',
            'article' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);
        
        // 计算总价格（包括额外选项）
        $totalPrice = $package->price * $request->input('quantity', 1);
        $selectedExtras = $this->processExtras($request, $package, $totalPrice);
        
        // 检查余额是否足够
        $user = auth()->user();
        if ($user->balance < $totalPrice) {
            return redirect()->route('wallet.deposit')
                ->with('error', '余额不足，请先充值');
        }
        
        try {
            // 创建订单
            $order = new Order();
            $order->user_id = auth()->id();
            $order->package_id = $package->id;
            $order->service_type = 'external'; // 第三方API订单
            $order->order_number = 'API-' . date('Ymd') . '-' . Str::random(5);
            $order->total_amount = $totalPrice;
            $order->status = 'pending';
            $order->payment_status = 'unpaid';
            $order->target_url = $request->input('target_url');
            $order->keywords = $request->input('keywords');
            $order->article = $request->input('article');
            $order->selected_extras = $selectedExtras;
            $order->extra_data = [
                'quantity' => $request->input('quantity', 1),
                'notes' => $request->input('notes')
            ];
            $order->save();
            
            // 处理支付
            $this->processOrderPayment($order);
            
            // 如果是API订单，可能需要立即提交到第三方API
            if ($package->is_api_product) {
                // 这里添加调用第三方API的逻辑
                // $this->apiService->submitOrder($order);
            }
            
            return redirect()->route('orders.show', $order->id)
                ->with('success', '第三方API订单创建成功！我们已开始处理您的订单。');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', '创建订单失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 处理Guest Post订单特殊逻辑
     */
    private function storeGuestPostOrder(Request $request, Package $package)
    {
        // 验证Guest Post订单专有数据
        $validatedData = $request->validate([
            'target_url' => 'required|url',
            'keywords' => 'required|string',
            'article' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        
        // 计算总价格（包括额外选项）
        $totalPrice = $package->price;
        $selectedExtras = $this->processExtras($request, $package, $totalPrice);
        
        // 检查余额是否足够
        $user = auth()->user();
        if ($user->balance < $totalPrice) {
            return redirect()->route('wallet.deposit')
                ->with('error', '余额不足，请先充值');
        }
        
        try {
            // 创建订单
            $order = new Order();
            $order->user_id = auth()->id();
            $order->package_id = $package->id;
            $order->service_type = 'guest_post';
            $order->order_number = 'GP-' . date('Ymd') . '-' . Str::random(5);
            $order->total_amount = $totalPrice;
            $order->status = 'pending';
            $order->payment_status = 'unpaid';
            $order->target_url = $request->target_url;
            $order->keywords = $request->keywords;
            $order->article = $request->article;
            $order->selected_extras = $selectedExtras;
            $order->extra_data = [
                'notes' => $request->input('notes')
            ];
            $order->save();
            
            // 执行后续处理逻辑
            $this->processOrderPayment($order);
            
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Guest Post订单创建成功！我们已开始处理您的订单。');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', '创建订单失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 处理单项套餐订单逻辑
     */
    private function storeSingleOrder(Request $request, Package $package)
    {
        // 验证单项套餐订单数据
        $validatedData = $request->validate([
            'target_url' => 'required|url',
            'keywords' => 'required|string',
            'article' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        // 计算总价格（包括额外选项）
        $totalPrice = $package->price;
        $selectedExtras = $this->processExtras($request, $package, $totalPrice);
        
        // 检查余额是否足够
        $user = auth()->user();
        if ($user->balance < $totalPrice) {
            return redirect()->route('wallet.deposit')
                ->with('error', '余额不足，请先充值');
        }
        
        try {
            // 创建订单
            $order = new Order();
            $order->user_id = auth()->id();
            $order->package_id = $package->id;
            $order->service_type = 'package';
            $order->order_number = 'PKG-' . date('Ymd') . '-' . Str::random(5);
            $order->total_amount = $totalPrice;
            $order->status = 'pending';
            $order->payment_status = 'unpaid';
            $order->target_url = $request->target_url;
            $order->keywords = $request->keywords;
            $order->article = $request->article;
            $order->selected_extras = $selectedExtras;
            $order->extra_data = [
                'notes' => $request->input('notes')
            ];
            $order->save();
            
            // 执行后续处理逻辑
            $this->processOrderPayment($order);
            
            return redirect()->route('orders.show', $order->id)
                ->with('success', '单项套餐订单创建成功！我们已开始处理您的订单。');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', '创建订单失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 处理订单支付
     */
    private function processOrderPayment($order)
    {
        // 检查用户余额是否充足
        $user = auth()->user();
        if ($user->balance >= $order->total_amount) {
            // 创建交易记录
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->order_id = $order->id;
            $transaction->transaction_type = 'order_payment';
            $transaction->amount = $order->total_amount * -1; // 负数表示支出
            $transaction->status = 'completed';
            $transaction->reference_id = $order->order_number;
            $transaction->notes = '订单支付：' . $order->order_number;
            $transaction->save();
            
            // 创建钱包交易记录
            $walletTransaction = new WalletTransaction();
            $walletTransaction->user_id = $user->id;
            $walletTransaction->amount = $order->total_amount * -1; // 负数表示支出
            $walletTransaction->type = 'consumption';
            $walletTransaction->related_id = $order->id;
            $walletTransaction->description = '订单支付：' . $order->order_number;
            $walletTransaction->before_balance = $user->balance;
            $walletTransaction->after_balance = $user->balance - $order->total_amount;
            $walletTransaction->save();
            
            // 更新用户余额
            $user->balance -= $order->total_amount;
            $user->save();
            
            // 更新订单状态
            $order->payment_status = 'paid';
            $order->paid_at = now();
            $order->status = 'processing'; // 开始处理订单
            $order->save();
            
            // 记录订单状态变更
            $this->logOrderStatusChange($order->id, null, 'processing', '订单支付完成，开始处理');
            
            // 可以在此处添加发送订单确认邮件等逻辑
        }
    }
    
    /**
     * 处理额外选项并计算价格
     */
    private function processExtras(Request $request, Package $package, &$totalPrice)
    {
        $selectedExtras = [];
        
        // 处理多选额外选项
        if ($request->has('extras') && is_array($request->input('extras'))) {
            foreach ($request->input('extras') as $extraId => $value) {
                if ($value) {
                    // 查找extras中对应的选项
                    if (!empty($package->available_extras)) {
                        $availableExtras = is_array($package->available_extras) ? 
                            $package->available_extras : 
                            json_decode($package->available_extras, true);
                        
                        foreach ($availableExtras as $extra) {
                            if ($extra['id'] == $extraId) {
                                $extraPrice = floatval($extra['price']) * 7.4 / 100 * 1.5; // 转换为人民币并加价
                                $totalPrice += $extraPrice;
                                
                                $selectedExtras[] = [
                                    'id' => $extra['id'],
                                    'code' => $extra['code'] ?? '',
                                    'name' => $extra['name'] ?? '',
                                    'price' => $extraPrice
                                ];
                                
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        // 处理单选额外选项
        if ($request->has('extras_selection') && !empty($request->input('extras_selection'))) {
            $extraId = $request->input('extras_selection');
            
            if (!empty($package->available_extras)) {
                $availableExtras = is_array($package->available_extras) ? 
                    $package->available_extras : 
                    json_decode($package->available_extras, true);
                
                foreach ($availableExtras as $extra) {
                    if ($extra['id'] == $extraId) {
                        $extraPrice = floatval($extra['price']) * 7.4 / 100 * 1.5; // 转换为人民币并加价
                        $totalPrice += $extraPrice;
                        
                        $selectedExtras[] = [
                            'id' => $extra['id'],
                            'code' => $extra['code'] ?? '',
                            'name' => $extra['name'] ?? '',
                            'price' => $extraPrice
                        ];
                        
                        break;
                    }
                }
            }
        }
        
        return $selectedExtras;
    }
    
    /**
     * 记录订单状态变更
     */
    private function logOrderStatusChange($orderId, $oldStatus, $newStatus, $notes = '')
    {
        $statusLog = new \App\Models\OrderStatusLog();
        $statusLog->order_id = $orderId;
        $statusLog->old_status = $oldStatus;
        $statusLog->new_status = $newStatus;
        $statusLog->notes = $notes;
        $statusLog->created_by = auth()->id();
        $statusLog->save();
    }
    
    /**
     * 显示订单详情
     */
    public function show(Order $order)
    {
        // 检查是否是订单所有者或管理员
        if (auth()->id() != $order->user_id && !auth()->user()->is_admin) {
            abort(403, '无权查看此订单');
        }
        
        // 加载订单相关数据
        $order->load(['package', 'reports']);
        
        // 如果是包月订单，加载包月订单详情和每周任务
        if ($order->service_type === 'monthly') {
            $order->load(['monthlyDetail', 'weeklyTasks']);
        }
        
        return view('orders.show', compact('order'));
    }
    
    /**
     * 订单列表
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Order::query();
        
        // 非管理员只能看到自己的订单
        if (!auth()->user()->is_admin) {
            $query->where('user_id', auth()->id());
        }
        
        // 按状态筛选
        if ($status) {
            $query->where('status', $status);
        }
        
        // 排序并分页
        $orders = $query->with('package')
                        ->latest()
                        ->paginate(15);
        
        return view('orders.index', compact('orders', 'status'));
    }
    
    /**
     * 取消订单
     */
    public function cancel(Order $order)
    {
        // 检查是否是订单所有者或管理员
        if (auth()->id() != $order->user_id && !auth()->user()->is_admin) {
            abort(403, '无权取消此订单');
        }
        
        // 检查订单是否可以取消
        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', '只有待处理或处理中的订单可以取消');
        }
        
        try {
            // 记录原始状态
            $oldStatus = $order->status;
            
            // 更新订单状态
            $order->status = 'canceled';
            $order->save();
            
            // 记录状态变更
            $this->logOrderStatusChange($order->id, $oldStatus, 'canceled', '用户取消订单');
            
            // 如果已支付，退款给用户
            if ($order->payment_status === 'paid') {
                // 创建退款交易
                $transaction = new Transaction();
                $transaction->user_id = $order->user_id;
                $transaction->order_id = $order->id;
                $transaction->transaction_type = 'refund';
                $transaction->amount = $order->total_amount; // 正数表示收入
                $transaction->status = 'completed';
                $transaction->reference_id = 'REF-' . $order->order_number;
                $transaction->notes = '订单取消退款：' . $order->order_number;
                $transaction->save();
                
                // 创建钱包交易记录
                $user = \App\Models\User::find($order->user_id);
                $walletTransaction = new WalletTransaction();
                $walletTransaction->user_id = $user->id;
                $walletTransaction->amount = $order->total_amount; // 正数表示收入
                $walletTransaction->type = 'refund';
                $walletTransaction->related_id = $order->id;
                $walletTransaction->description = '订单取消退款：' . $order->order_number;
                $walletTransaction->before_balance = $user->balance;
                $walletTransaction->after_balance = $user->balance + $order->total_amount;
                $walletTransaction->save();
                
                // 更新用户余额
                $user->balance += $order->total_amount;
                $user->save();
                
                // 更新订单状态
                $order->payment_status = 'refunded';
                $order->save();
            }
            
            return redirect()->route('orders.show', $order->id)
                ->with('success', '订单已成功取消' . ($order->payment_status === 'refunded' ? '，退款已返回您的账户余额' : ''));
        } catch (\Exception $e) {
            return back()->with('error', '取消订单失败: ' . $e->getMessage());
        }
    }
}