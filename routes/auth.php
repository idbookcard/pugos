<?php
// routes/auth.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;


// 管理员登录路由
Route::get('master/login', [AdminLoginController::class, 'showLoginForm'])->name('master.login');
Route::post('master/login', [AdminLoginController::class, 'login']);
Route::post('master/logout', [AdminLoginController::class, 'logout'])->name('master.logout');



Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [LoginController::class, 'register'])->name('register.submit');

// 如果需要邮箱验证，还可以添加以下路由
Route::get('/verify-email/{id}/{hash}', [LoginController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [LoginController::class, 'resendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


