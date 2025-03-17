<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * 显示管理后台首页仪表盘
     */
    public function index()
    {
        // 获取基础统计数据
        $totalOrders = Order::count();
        $totalUsers = User::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        
        // 获取最近订单
        $recentOrders = Order::with(['user', 'package'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // 计算当月收入与上月比较的百分比
        $currentMonthRevenue = $this->calculateRevenuePercentage();
        
        // 获取每周收入数据（近30天）
        $weeklyRevenueData = $this->getWeeklyRevenueData();
        
        // 获取产品销量数据
        $productSalesData = $this->getProductSalesData();
        
        return view('master.dashboard.index', compact(
            'totalOrders',
            'totalUsers',
            'totalRevenue',
            'pendingOrders',
            'recentOrders',
            'currentMonthRevenue',
            'weeklyRevenueData',
            'productSalesData'
        ));
    }
    
    /**
     * 计算当月收入与上月的百分比变化
     */
    private function calculateRevenuePercentage()
    {
        $now = Carbon::now();
        
        // 获取当月收入
        $currentMonthRevenue = Order::where('payment_status', 'paid')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum('total_amount');
        
        // 获取上月收入
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthRevenue = Order::where('payment_status', 'paid')
            ->whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->sum('total_amount');
        
        // 计算百分比变化
        if ($lastMonthRevenue > 0) {
            $percentage = (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
            return round($percentage, 1);
        }
        
        return 0; // 如果上月收入为0，返回0
    }
    
    /**
     * 获取每周收入数据（近30天）
     */
    private function getWeeklyRevenueData()
    {
        $end = Carbon::now();
        $start = Carbon::now()->subDays(30);
        
        // 按日期分组查询订单收入
        $revenues = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 准备数据
        $dates = [];
        $data = [];
        
        // 填充日期和收入数据
        $current = clone $start;
        while ($current <= $end) {
            $currentDate = $current->format('Y-m-d');
            $dates[] = $current->format('m-d');
            
            // 查找该日期的收入
            $revenueForDate = $revenues->firstWhere('date', $currentDate);
            $data[] = $revenueForDate ? floatval($revenueForDate->revenue) : 0;
            
            $current->addDay();
        }
        
        return [
            'labels' => $dates,
            'data' => $data
        ];
    }
    
    /**
     * 获取产品销量数据
     */
    private function getProductSalesData()
    {
        // 获取销量最高的5个产品
        $topProducts = Order::where('payment_status', 'paid')
            ->select('package_id', DB::raw('COUNT(*) as sales_count'))
            ->groupBy('package_id')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get();
        
        // 准备数据
        $labels = [];
        $data = [];
        
        foreach ($topProducts as $product) {
            $package = Package::find($product->package_id);
            if ($package) {
                $labels[] = $package->name;
                $data[] = $product->sales_count;
            }
        }
        
        // 如果产品不足5个，添加"其他"类别
        $totalSales = Order::where('payment_status', 'paid')->count();
        $topSales = array_sum($data);
        
        if ($totalSales > $topSales) {
            $labels[] = '其他';
            $data[] = $totalSales - $topSales;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}