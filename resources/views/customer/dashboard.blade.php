@extends('layouts.app')

@section('title', '用户中心 - SEO外链服务平台')

@section('content')
<div class="container py-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="fw-bold">用户中心</h1>
                <p class="text-muted">欢迎回来，{{ Auth::user()->name }}</p>
            </div>
            <div class="col-md-5 text-md-end">
                <a href="{{ route('packages') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>购买新服务
                </a>
                <a href="{{ route('customer.wallet') }}" class="btn btn-outline-primary ms-2">
                    <i class="bi bi-wallet2 me-1"></i>充值
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- 左侧菜单 -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="user-profile p-4 text-center">
                        <div class="avatar mb-3">
                            <div class="bg-primary rounded-circle text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <span class="fs-1">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <h5 class="fw-bold">{{ Auth::user()->name }}</h5>
                        <p class="text-muted mb-0">{{ Auth::user()->email }}</p>
                    </div>
                    
                    <hr class="my-0">
                    
                    <div class="list-group list-group-flush">
                        <a href="{{ route('customer.dashboard') }}" class="list-group-item list-group-item-action active py-3">
                            <i class="bi bi-grid me-2"></i>概览
                        </a>
                        <a href="{{ route('customer.orders') }}" class="list-group-item list-group-item-action py-3">
                            <i class="bi bi-cart-check me-2"></i>我的订单
                        </a>
                        <a href="{{ route('customer.wallet') }}" class="list-group-item list-group-item-action py-3">
                            <i class="bi bi-wallet2 me-2"></i>我的钱包
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <i class="bi bi-gear me-2"></i>账号设置
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <i class="bi bi-question-circle me-2"></i>帮助中心
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 右侧内容 -->
        <div class="col-lg-9">
            <!-- 数据概览卡片 -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body dashboard-card">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                    <i class="bi bi-cart-check fs-2 text-primary"></i>
                                </div>
                            </div>
                            <h1 class="display-4">{{ $stats['total_orders'] ?? 0 }}</h1>
                            <p class="text-muted">总订单数</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body dashboard-card">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                    <i class="bi bi-check-circle fs-2 text-success"></i>
                                </div>
                            </div>
                            <h1 class="display-4">{{ $stats['completed_orders'] ?? 0 }}</h1>
                            <p class="text-muted">已完成订单</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body dashboard-card">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                    <i class="bi bi-wallet2 fs-2 text-info"></i>
                                </div>
                            </div>
                            <h1 class="display-4">¥{{ number_format(Auth::user()->balance, 0) }}</h1>
                            <p class="text-muted">账户余额</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 最近订单 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title fw-bold mb-0">最近订单</h5>
                        <a href="{{ route('customer.orders') }}" class="btn btn-sm btn-link">查看全部</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">订单号</th>
                                    <th scope="col">服务套餐</th>
                                    <th scope="col">金额</th>
                                    <th scope="col">状态</th>
                                    <th scope="col">订单日期</th>
                                    <th scope="col">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    <td>{{ Str::limit($order->package->name, 30) }}</td>
                                    <td>¥{{ number_format($order->total_price, 2) }}</td>
                                    <td>
                                        @if($order->status == 'pending')
                                            <span class="badge bg-warning">待处理</span>
                                        @elseif($order->status == 'processing')
                                            <span class="badge bg-info">处理中</span>
                                        @elseif($order->status == 'completed')
                                            <span class="badge bg-success">已完成</span>
                                        @elseif($order->status == 'canceled')
                                            <span class="badge bg-danger">已取消</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                            <p class="text-muted">暂无订单数据</p>
                                            <a href="{{ route('packages') }}" class="btn btn-sm btn-primary">浏览服务</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- 推荐服务 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">推荐服务</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="fw-bold">月度套餐</h5>
                                    <p>全方位的外链建设策略，帮助您的网站持续提升排名，包含多种类型的高质量外链。</p>
                                    <a href="{{ route('packages.monthly') }}" class="btn btn-sm btn-primary">查看详情</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="fw-bold">软文外链</h5>
                                    <p>在高权重网站上发布与您行业相关的优质内容，获得强大的域名权重和长期SEO价值。</p>
                                    <a href="{{ route('packages.guest-post') }}" class="btn btn-sm btn-primary">查看详情</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection