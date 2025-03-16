<!-- resources/views/admin/dashboard/index.blade.php -->
@extends('master.layouts.master')

@section('title', '管理后台 - 首页')

@section('content')
<div class="px-3 px-md-0">
  <h1 class="fs-2 fw-semibold text-dark">仪表盘</h1>
</div>

<!-- 统计卡片行 -->
<div class="mt-4 row g-3">
  <!-- 订单统计卡片 -->
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body p-3 p-lg-4">
        <div class="d-flex align-items-center">
          <div class="bg-primary rounded p-3 d-flex align-items-center justify-content-center">
            <i class="bi bi-file-text text-white fs-4"></i>
          </div>
          <div class="ms-3">
            <h6 class="text-muted fs-sm mb-1">总订单数</h6>
            <h2 class="fs-4 fw-semibold mb-0">{{ $totalOrders }}</h2>
          </div>
        </div>
      </div>
      <div class="card-footer bg-light border-0 py-2">
        <a href="{{ route('master.orders.index') }}" class="text-decoration-none fw-medium text-primary small">
          查看全部
        </a>
      </div>
    </div>
  </div>

  <!-- 用户统计卡片 -->
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body p-3 p-lg-4">
        <div class="d-flex align-items-center">
          <div class="bg-success rounded p-3 d-flex align-items-center justify-content-center">
            <i class="bi bi-people text-white fs-4"></i>
          </div>
          <div class="ms-3">
            <h6 class="text-muted fs-sm mb-1">用户总数</h6>
            <h2 class="fs-4 fw-semibold mb-0">{{ $totalUsers }}</h2>
          </div>
        </div>
      </div>
      <div class="card-footer bg-light border-0 py-2">
        <a href="{{ route('master.users.index') }}" class="text-decoration-none fw-medium text-success small">
          查看全部
        </a>
      </div>
    </div>
  </div>

  <!-- 收入统计卡片 -->
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body p-3 p-lg-4">
        <div class="d-flex align-items-center">
          <div class="bg-warning rounded p-3 d-flex align-items-center justify-content-center">
            <i class="bi bi-currency-yen text-white fs-4"></i>
          </div>
          <div class="ms-3">
            <h6 class="text-muted fs-sm mb-1">收入总额</h6>
            <h2 class="fs-4 fw-semibold mb-0">¥{{ number_format($totalRevenue, 2) }}</h2>
          </div>
        </div>
      </div>
      <div class="card-footer bg-light border-0 py-2">
        <span class="fw-medium text-warning small">
          {{ $currentMonthRevenue }}% <span class="text-muted">较上月</span>
        </span>
      </div>
    </div>
  </div>

  <!-- 待处理事项卡片 -->
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body p-3 p-lg-4">
        <div class="d-flex align-items-center">
          <div class="bg-danger rounded p-3 d-flex align-items-center justify-content-center">
            <i class="bi bi-clock-history text-white fs-4"></i>
          </div>
          <div class="ms-3">
            <h6 class="text-muted fs-sm mb-1">待处理订单</h6>
            <h2 class="fs-4 fw-semibold mb-0">{{ $pendingOrders }}</h2>
          </div>
        </div>
      </div>
      <div class="card-footer bg-light border-0 py-2">
        <a href="{{ route('master.orders.index', ['status' => 'pending']) }}" class="text-decoration-none fw-medium text-danger small">
          立即处理
        </a>
      </div>
    </div>
  </div>
</div>

<!-- 最近订单 -->
<div class="mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fs-4 fw-medium mb-0">最近订单</h2>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="bg-light">
          <tr>
            <th class="border-0 py-3 ps-4">订单号</th>
            <th class="border-0 py-3">用户</th>
            <th class="border-0 py-3">产品</th>
            <th class="border-0 py-3">金额</th>
            <th class="border-0 py-3">状态</th>
            <th class="border-0 py-3">下单时间</th>
            <th class="border-0 py-3 text-end pe-4">操作</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentOrders as $order)
          <tr>
            <td class="py-3 ps-4">
              <span class="fw-medium">{{ $order->order_number }}</span>
            </td>
            <td class="py-3">
              <div>{{ $order->user->name }}</div>
              <div class="small text-muted">{{ $order->user->email }}</div>
            </td>
            <td class="py-3">{{ $order->package->name ?? 'N/A' }}</td>
            <td class="py-3">¥{{ number_format($order->total_amount, 2) }}</td>
            <td class="py-3">
              <span class="badge 
                @if($order->status == 'completed') 
                  bg-success-subtle text-success
                @elseif($order->status == 'processing')
                  bg-primary-subtle text-primary
                @elseif($order->status == 'pending')
                  bg-warning-subtle text-warning
                @elseif($order->status == 'canceled')
                  bg-danger-subtle text-danger
                @else
                  bg-secondary-subtle text-secondary
                @endif
                rounded-pill px-2 py-1">
                {{ ucfirst($order->status) }}
              </span>
            </td>
            <td class="py-3 text-muted">{{ $order->created_at->format('Y-m-d H:i') }}</td>
            <td class="py-3 text-end pe-4">
              <a href="{{ route('master.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                查看
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="py-4 text-center text-muted">
              暂无订单数据
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- 统计图表 -->
<div class="row g-4 mt-2">
  <!-- 每周收入统计 -->
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h3 class="fs-5 fw-medium mb-4">周收入统计（近30天）</h3>
        <div style="height: 300px">
          <canvas id="weekly-revenue-chart"></canvas>
        </div>
      </div>
    </div>
  </div>
  
  <!-- 产品销量统计 -->
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h3 class="fs-5 fw-medium mb-4">产品销量比例</h3>
        <div style="height: 300px">
          <canvas id="product-sales-chart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 每周收入统计图表
    const weeklyRevenueCtx = document.getElementById('weekly-revenue-chart').getContext('2d');
    const weeklyRevenueChart = new Chart(weeklyRevenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($weeklyRevenueData['labels']) !!},
            datasets: [{
                label: '周收入',
                data: {!! json_encode($weeklyRevenueData['data']) !!},
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderColor: 'rgba(13, 110, 253, 0.8)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '¥' + value;
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // 产品销量比例图表
    const productSalesCtx = document.getElementById('product-sales-chart').getContext('2d');
    const productSalesChart = new Chart(productSalesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($productSalesData['labels']) !!},
            datasets: [{
                data: {!! json_encode($productSalesData['data']) !!},
                backgroundColor: [
                    'rgba(13, 110, 253, 0.8)',   // primary
                    'rgba(25, 135, 84, 0.8)',    // success
                    'rgba(255, 193, 7, 0.8)',    // warning
                    'rgba(220, 53, 69, 0.8)',    // danger
                    'rgba(108, 117, 125, 0.8)'   // secondary
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});
</script>
@endpush