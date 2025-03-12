<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * 处理传入请求，确保用户有管理员权限
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 检查用户是否已登录
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        // 检查用户是否是管理员
        // 这里假设用户表中有is_admin字段，或者通过其他方式判断管理员权限
        // 可以根据实际需求修改判断逻辑
        $user = Auth::user();
        
        // 如果你使用的是is_admin字段
        if (!$user->is_admin) {
            abort(403, '您没有权限访问此页面');
        }
        
        // 如果你使用的是用户角色模型
        // if (!$user->hasRole('admin')) {
        //     abort(403, '您没有权限访问此页面');
        // }
        
        return $next($request);
    }
}