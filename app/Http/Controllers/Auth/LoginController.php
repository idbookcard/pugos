<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class LoginController extends Controller
{
    /**
     * 显示登录表单
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * 显示注册表单
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * 处理登录请求
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => '提供的凭据不匹配我们的记录。',
        ])->withInput($request->except('password'));
    }

    /**
     * 处理注册请求
     */
    public function register(Request $request)
    {
        // 验证表单数据
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 创建用户
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 如果需要邮箱验证
        // event(new Registered($user));

        // 自动登录用户
        Auth::login($user);

        // 重定向到首页或其他页面
        return redirect()->intended(route('home'));
    }

    /**
     * 处理退出登录请求
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * 验证用户邮箱
     */
    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();
        
        return redirect()->route('home')->with('status', '邮箱验证成功');
    }

    /**
     * 重新发送验证邮件
     */
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', '验证链接已发送到您的邮箱');
    }
}