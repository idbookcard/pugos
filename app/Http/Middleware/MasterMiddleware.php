<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('master.login'); 
        }

        if (!Auth::user()->is_admin) { 
            abort(403, '您没有权限访问此页面');
        }

        return $next($request);
    }
}

