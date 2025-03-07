<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\WalletController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ThirdPartyController;
use App\Http\Controllers\Admin\GuestPostController;
use App\Http\Controllers\Admin\ThirdPartyOrderController;
use App\Http\Controllers\Packages\ThirdPartyOrderController as CustomerThirdPartyOrderController;
use App\Http\Controllers\Api\ThirdPartyWebhookController;

// 公共路由
Route::get('/', [HomeController::class, 'index'])->name('home');

// 套餐路由
Route::get('/packages', [PackageController::class, 'index'])->name('packages');
Route::get('/packages/monthly', [PackageController::class, 'monthly'])->name('packages.monthly');
Route::get('/packages/single', [PackageController::class, 'single'])->name('packages.single');
Route::get('/packages/third-party', [PackageController::class, 'thirdParty'])->name('packages.third-party');
Route::get('/packages/guest-post', [PackageController::class, 'guestPost'])->name('packages.guest-post');
Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');

// 认证路由
Auth::routes();

// 客户路由 (需要认证)
Route::middleware(['auth'])->prefix('customer')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('customer.dashboard');
    
    // 订单相关
    Route::get('/orders', [OrderController::class, 'index'])->name('customer.orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('customer.orders.show');
    Route::get('/packages/{package}/order', [OrderController::class, 'create'])->name('customer.orders.create');
    Route::post('/packages/{package}/order', [OrderController::class, 'store'])->name('customer.orders.store');
    Route::get('/orders/{order}/success', [OrderController::class, 'success'])->name('customer.orders.success');
    
    // 钱包/余额管理
    Route::get('/wallet', [WalletController::class, 'index'])->name('customer.wallet');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('customer.wallet.deposit');
    
    // 第三方服务订单
    Route::get('/packages/third-party/{package}/order', [CustomerThirdPartyOrderController::class, 'create'])
        ->name('packages.third-party.order');
    Route::post('/packages/third-party/{package}/order', [CustomerThirdPartyOrderController::class, 'store']);
});

// 管理员路由 (需要认证和管理员权限)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // 套餐管理
    Route::resource('packages', AdminPackageController::class, ['as' => 'admin']);
    
    // 订单管理
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.update.status');
    Route::post('/orders/{order}/send-to-third-party', [AdminOrderController::class, 'sendToThirdParty'])
        ->name('admin.orders.send-to-third-party');
    
    // 外链报告管理
    Route::post('/orders/{order}/reports', [ReportController::class, 'store'])->name('admin.reports.store');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('admin.reports.destroy');
    
    // 用户管理
    Route::resource('users', UserController::class, ['as' => 'admin']);
    
    // 第三方API管理
    Route::get('/third-party', [ThirdPartyController::class, 'index'])->name('admin.third-party');
    Route::post('/third-party/sync', [ThirdPartyController::class, 'sync'])->name('admin.third-party.sync');
    Route::get('/third-party/settings', [ThirdPartyController::class, 'settings'])->name('admin.third-party.settings');
    Route::post('/third-party/settings', [ThirdPartyController::class, 'updateSettings'])
        ->name('admin.third-party.settings.update');
    
    // 第三方订单管理
    Route::get('/third-party-orders', [ThirdPartyOrderController::class, 'index'])->name('admin.third-party-orders');
    Route::post('/third-party-orders/approve', [ThirdPartyOrderController::class, 'approveOrders'])
        ->name('admin.third-party-orders.approve');
    Route::post('/third-party-orders/{order}/send', [ThirdPartyOrderController::class, 'sendOrder'])
        ->name('admin.third-party-orders.send');
    Route::post('/third-party-orders/send-all', [ThirdPartyOrderController::class, 'sendAllOrders'])
        ->name('admin.third-party-orders.send-all');
    
    // 访客发布管理
    Route::get('/guest-posts', [GuestPostController::class, 'index'])->name('admin.guest-posts');
    Route::get('/guest-posts/create', [GuestPostController::class, 'create'])->name('admin.guest-posts.create');
    Route::post('/guest-posts', [GuestPostController::class, 'store'])->name('admin.guest-posts.store');
    Route::get('/guest-posts/{package}/edit', [GuestPostController::class, 'edit'])->name('admin.guest-posts.edit');
    Route::put('/guest-posts/{package}', [GuestPostController::class, 'update'])->name('admin.guest-posts.update');
    Route::delete('/guest-posts/{package}', [GuestPostController::class, 'destroy'])->name('admin.guest-posts.destroy');
});

// 第三方集成的API路由
Route::prefix('api')->group(function () {
    Route::post('/webhooks/third-party/order-update', [ThirdPartyWebhookController::class, 'orderUpdate']);
});