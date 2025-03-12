<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\ApiSettingController;
use App\Http\Controllers\Admin\SystemController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 前台路由
Route::get('/', [HomeController::class, 'index'])->name('home');

// 产品路由
Route::prefix('packages')->name('packages.')->group(function () {
    Route::get('/', [PackageController::class, 'index'])->name('index');
    
    // 添加这些新路由
    Route::get('/monthly', [PackageController::class, 'monthly'])->name('monthly');
    Route::get('/single', [PackageController::class, 'single'])->name('single');
    Route::get('/guest-post', [PackageController::class, 'guestPost'])->name('guest-post');
    Route::get('/third-party', [PackageController::class, 'thirdParty'])->name('third-party');
    
    Route::get('/category/{slug}', [PackageController::class, 'category'])->name('category');
    Route::get('/{slug}', [PackageController::class, 'show'])->name('show');
});

// 认证用户路由
Route::middleware(['auth'])->group(function () {
    // 订单路由
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create/{slug}', [OrderController::class, 'create'])->name('create');
        Route::post('/store', [OrderController::class, 'store'])->name('store');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
    });
    
    // 钱包路由
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/deposit', [WalletController::class, 'processDeposit'])->name('process-deposit');
        Route::get('/check-payment/{id}', [WalletController::class, 'checkPaymentStatus'])->name('check-payment');
    });
    
    // 发票路由
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/store', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{id}/download', [InvoiceController::class, 'download'])->name('download');
    });
    
    // 用户个人资料
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
    Route::post('/profile', [HomeController::class, 'updateProfile'])->name('profile.update');
});

// 支付回调路由（不需要认证）
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/wechat/callback', [WalletController::class, 'wechatCallback'])->name('wechat.callback');
    Route::post('/alipay/callback', [WalletController::class, 'alipayCallback'])->name('alipay.callback');
    Route::post('/crypto/callback', [WalletController::class, 'cryptoCallback'])->name('crypto.callback');
});

// 后台路由
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // 后台首页
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // 产品管理
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [AdminPackageController::class, 'index'])->name('index');
        Route::get('/create', [AdminPackageController::class, 'create'])->name('create');
        Route::post('/store', [AdminPackageController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminPackageController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminPackageController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminPackageController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [AdminPackageController::class, 'updateStatus'])->name('update-status');
    });
    
    // 订单管理
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminOrderController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdminOrderController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminOrderController::class, 'update'])->name('update');
        Route::post('/{id}/submit-to-api', [AdminOrderController::class, 'submitToApi'])->name('submit-to-api');
        Route::post('/{id}/sync-api-status', [AdminOrderController::class, 'syncApiStatus'])->name('sync-api-status');
        Route::post('/{id}/upload-report', [AdminOrderController::class, 'uploadReport'])->name('upload-report');
        Route::post('/batch-sync-api', [AdminOrderController::class, 'batchSyncApiStatus'])->name('batch-sync-api');
    });
    
    // 用户管理
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminUserController::class, 'update'])->name('update');
        Route::post('/{id}/adjust-balance', [AdminUserController::class, 'adjustBalance'])->name('adjust-balance');
        Route::get('/{id}/transactions', [AdminUserController::class, 'transactions'])->name('transactions');
    });
    
    // 发票管理
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [AdminInvoiceController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminInvoiceController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [AdminInvoiceController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [AdminInvoiceController::class, 'reject'])->name('reject');
        Route::post('/{id}/upload', [AdminInvoiceController::class, 'uploadInvoice'])->name('upload');
        Route::get('/export', [AdminInvoiceController::class, 'export'])->name('export');
    });
    
    // API设置
    Route::prefix('api-settings')->name('api-settings.')->group(function () {
        Route::get('/', [ApiSettingController::class, 'index'])->name('index');
        Route::get('/create', [ApiSettingController::class, 'create'])->name('create');
        Route::post('/store', [ApiSettingController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ApiSettingController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ApiSettingController::class, 'update'])->name('update');
        Route::delete('/{id}', [ApiSettingController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/test', [ApiSettingController::class, 'testConnection'])->name('test');
        Route::post('/sync-products', [ApiSettingController::class, 'syncProducts'])->name('sync-products');
    });
    
    // 系统设置
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/settings', [SystemController::class, 'settings'])->name('settings');
        Route::post('/settings', [SystemController::class, 'updateSettings'])->name('update-settings');
        Route::get('/logs', [SystemController::class, 'logs'])->name('logs');
        Route::get('/api-logs', [SystemController::class, 'apiLogs'])->name('api-logs');
    });
});

// Laravel Auth 路由
require __DIR__.'/auth.php';