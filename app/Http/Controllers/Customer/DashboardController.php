<?php
// app/Http/Controllers/Customer/DashboardController.php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $user = Auth::user();
        
        // Get recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Get recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Count orders by status
        $orderCounts = [
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'completed' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'total' => Order::where('user_id', $user->id)->count(),
        ];
        
        // Calculate total spent
        $totalSpent = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'order_payment')
            ->where('status', 'completed')
            ->sum('amount');
            
        return view('customer.dashboard', compact('user', 'recentOrders', 'recentTransactions', 'orderCounts', 'totalSpent'));
    }
}