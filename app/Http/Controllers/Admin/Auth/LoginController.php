<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * 登录成功后重定向到的位置
     *
     * @var string
     */
    protected $redirectTo = '/admin/dashboard';

    /**
     * 创建一个新的控制器实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * 显示应用程序的登录表单
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('master.auth.login');
    }

    /**
     * 获取守卫实例
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('web');
    }

    /**
     * 尝试登录
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // 检查用户是否是管理员
        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user || !$user->is_admin) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => '该账号没有管理员权限']);
        }

        // 如果用户点击"记住我"但不是有效用户，则限制次数
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // 如果登录尝试失败，增加尝试次数
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * 登出用户
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('master.login');
    }
}