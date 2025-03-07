@extends('layouts.app')

@section('title', '订单详情 #' . $order->id . ' - SEO外链服务平台')

@section('content')
<div class="container py-4">
    @php
    $breadcrumbs = [
        '用户中心' => route('customer.dashboard'),
        '我的订单' => route('customer.orders'),
        '订单 #'.$order->id => '',
    ];
    @endphp
    @include('partials.breadcrumb')
    
    <div class="row">
        <!-- 左侧菜单 -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('customer.dashboard') }}" class="list-group-item list-group-item-action py-3">
                            <i class="bi bi-grid me-2"></i>概览
                        </a>
                        <a href="{{ route('customer.orders') }}" class="list-group-item list-group-item-action active py-3">
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
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="bi bi-file-text me-2"></i>订单详情
                        </h5>
                        <div>
                            <a href="{{ route('customer.orders') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>返回订单列表
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="order-info">
                                <h6 class="fw-bold text-uppercase text-muted mb-3">订单信息</h6>
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">订单编号:</div>
                                    <div>#{{ $order->id }}</div>
                                </div>
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">下单日期:</div>
                                    <div>{{ $order->created_at->format('Y-m-d H:i:s') }}</div>
                                </div>
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">订单状态:</div>
                                    <div>
                                        @if($order->status == 'pending')
                                            <span class="badge bg-warning">待处理</span>
                                        @elseif($order->status == 'processing')
                                            <span class="badge bg-info">处理中</span>
                                        @elseif($order->status == 'completed')
                                            <span class="badge bg-success">已完成</span>
                                        @elseif($order->status == 'canceled')
                                            <span class="badge bg-danger">已取消</span>
                                        @endif
                                    </div>
                                </div>
                                @if($order->paid_at)
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">支付时间:</div>
                                    <div>{{ $order->paid_at->format('Y-m-d H:i:s') }}</div>
                                </div>
                                @endif
                                @if($order->completed_at)
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">完成时间:</div>
                                    <div>{{ $order->completed_at->format('Y-m-d H:i:s') }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="order-info">
                                <h6 class="fw-bold text-uppercase text-muted mb-3">服务信息</h6>
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">服务类型:</div>
                                    <div>
                                        @if($order->package->package_type == 'monthly')
                                            月度套餐
                                        @elseif($order->package->package_type == 'single')
                                            单项套餐
                                        @elseif($order->package->package_type == 'third_party')
                                            特色外链
                                        @elseif($order->package->package_type == 'guest_post')
                                            软文外链
                                        @endif
                                    </div>
                                </div>
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">服务套餐:</div>
                                    <div>{{ $order->package->name }}</div>
                                </div>
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">服务费用:</div>
                                    <div class="fw-bold text-primary">¥{{ number_format($order->total_price, 2) }}</div>
                                </div>
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">交付时间:</div>
                                    <div>{{ $order->package->delivery_days }} 天</div>
                                </div>
                                @if($order->package->package_type == 'guest_post')
                                <div class="info-item d-flex mb-2">
                                    <div class="fw-bold text-muted" style="width: 120px">站点权重:</div>
                                    <div>DA{{ $order->package->guest_post_da }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-uppercase text-muted mb-3">订单详情</h6>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="info-item d-flex mb-2">
                                        <div class="fw-bold text-muted" style="width: 120px">目标URL:</div>
                                        <div>
                                            <a href="{{ $order->target_url }}" target="_blank" class="text-primary">
                                                {{ $order->target_url }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="info-item d-flex mb-2">
                                        <div class="fw-bold text-muted" style="width: 120px">关键词:</div>
                                        <div>{{ $order->keywords }}</div>
                                    </div>
                                    @if($order->description)
                                    <div class="info-item d-flex mb-2">
                                        <div class="fw-bold text-muted" style="width: 120px">网站描述:</div>
                                        <div>{{ $order->description }}</div>
                                    </div>
                                    @endif
                                    @if($order->notes)
                                    <div class="info-item d-flex mb-2">
                                        <div class="fw-bold text-muted" style="width: 120px">特殊要求:</div>
                                        <div>{{ $order->notes }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 订单进度 -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-uppercase text-muted mb-3">订单进度</h6>
                            <div class="position-relative order-timeline">
                                <div class="progress" style="height: 3px;">
                                    @php
                                        $progress = 0;
                                        if($order->status == 'pending') $progress = 25;
                                        elseif($order->status == 'processing') $progress = 50;
                                        elseif($order->status == 'completed') $progress = 100;
                                        elseif($order->status == 'canceled') $progress = 100;
                                    @endphp
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-3">
                                    <div class="text-center">
                                        <div class="timeline-point {{ $progress >= 25 ? 'bg-primary' : 'bg-light border' }}">
                                            <i class="bi bi-check text-white"></i>
                                        </div>
                                        <p class="small mt-2">订单提交</p>
                                        <p class="small text-muted">{{ $order->created_at->format('Y-m-d') }}</p>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="timeline-point {{ $progress >= 50 ? 'bg-primary' : 'bg-light border' }}">
                                            <i class="bi bi-gear text-white"></i>
                                        </div>
                                        <p class="small mt-2">处理中</p>
                                        <p class="small text-muted">
                                            @if($order->status == 'processing' || $order->status == 'completed')
                                                {{ $order->updated_at->format('Y-m-d') }}
                                            @else
                                                &nbsp;
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="timeline-point {{ $progress >= 100 && $order->status != 'canceled' ? 'bg-primary' : 'bg-light border' }}">
                                            <i class="bi bi-check-all text-white"></i>
                                        </div>
                                        <p class="small mt-2">已完成</p>
                                        <p class="small text-muted">
                                            @if($order->status == 'completed')
                                                {{ $order->completed_at->format('Y-m-d') }}
                                            @else
                                                &nbsp;
                                            @endif
                                        </p>
                                    </div>
                                    
                                    @if($order->status == 'canceled')
                                    <div class="text-center">
                                        <div class="timeline-point bg-danger">
                                            <i class="bi bi-x text-white"></i>
                                        </div>
                                        <p class="small mt-2">已取消</p>
                                        <p class="small text-muted">{{ $order->updated_at->format('Y-m-d') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 外链报告 -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="fw-bold text-uppercase text-muted mb-3">外链报告</h6>
                            @if($order->reports->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>外链URL</th>
                                                <th>域名权重</th>
                                                <th>状态</th>
                                                <th>添加时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->reports as $report)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ $report->report_url }}" target="_blank" class="text-primary">
                                                        {{ Str::limit($report->report_url, 40) }} <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">DA{{ $report->domain_authority }}</span>
                                                </td>
                                                <td>
                                                    @if($report->status == 'active')
                                                        <span class="badge bg-success">活跃</span>
                                                    @elseif($report->status == 'pending')
                                                        <span class="badge bg-warning">待审核</span>
                                                    @elseif($report->status == 'removed')
                                                        <span class="badge bg-danger">已移除</span>
                                                    @endif
                                                </td>
                                                <td>{{ $report->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    <a href="{{ $report->report_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    @if($order->status == 'pending' || $order->status == 'processing')
                                        <i class="bi bi-info-circle me-2"></i>外链报告将在订单完成后显示，请耐心等待。
                                    @elseif($order->status == 'canceled')
                                        <i class="bi bi-exclamation-circle me-2"></i>订单已取消，无法查看外链报告。
                                    @else
                                        <i class="bi bi-info-circle me-2"></i>暂无外链报告数据。
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('customer.orders') }}" class="btn btn-outline-secondary">返回订单列表</a>
                        <div>
                            @if($order->status == 'completed')
                                <a href="{{ route('packages.show', $order->package) }}" class="btn btn-primary">再次购买</a>
                            @endif
                            
                            @if($order->status == 'pending')
                                <button class="btn btn-outline-danger" disabled>取消订单</button>
                                <div class="form-text text-muted small">请联系客服取消订单</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 其他推荐 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">您可能也会感兴趣</h5>
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

@push('styles')
<style>
.order-timeline .timeline-point {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 2;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.order-timeline .progress {
    position: absolute;
    width: 100%;
    top: 15px;
    z-index: 1;
}
</style>
@endpush
@endsection