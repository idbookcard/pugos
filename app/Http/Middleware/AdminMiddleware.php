<?php
// app/Http/Middleware/AdminMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this area.');
        }
        
        $user = Auth::user();
        
        // Check if user has admin role
        if (!$user->isAdmin()) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this area.');
        }
        
        return $next($request);
    }
}