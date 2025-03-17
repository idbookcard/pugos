@extends('layouts.app')

@section('title', '订单提交成功 - SEO外链服务平台')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="success-icon mb-4">
                        <div class="success-animation">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                        </div>
                    </div>
                    
                    <h1 class="display-6 fw-bold text-success mb-3">订单提交成功</h1>
                    <p class="lead mb-4">您的订单已成功提交，我们将尽快为您处理</p>
                    
                    <div class="order-details bg-light p-3 rounded mb-4 text-start">
                        <div class="row">
                            <div class="col-6 text-muted">订单号:</div>
                            <div class="col-6 text-end fw-bold">#{{ $order->id }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-muted">服务名称:</div>
                            <div class="col-6 text-end">{{ Str::limit($order->package->name, 30) }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-muted">订单金额:</div>
                            <div class="col-6 text-end fw-bold">¥{{ number_format($order->total_price, 2) }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-muted">订单状态:</div>
                            <div class="col-6 text-end">
                                <span class="badge bg-warning">待处理</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-muted">预计交付时间:</div>
                            <div class="col-6 text-end">{{ date('Y-m-d', strtotime('+' . $order->package->delivery_days . ' days')) }}</div>
                        </div>
                    </div>
                    
                    <div class="next-steps mb-4">
                        <h6 class="fw-bold mb-3">接下来会发生什么？</h6>
                        <div class="timeline">
                            <div class="timeline-item d-flex">
                                <div class="timeline-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    1
                                </div>
                                <div class="timeline-content ms-3 text-start">
                                    <h6 class="mb-1">订单审核</h6>
                                    <p class="small text-muted mb-0">我们将在24小时内审核您的订单</p>
                                </div>
                            </div>
                            <div class="timeline-item d-flex mt-3">
                                <div class="timeline-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    2
                                </div>
                                <div class="timeline-content ms-3 text-start">
                                    <h6 class="mb-1">开始处理</h6>
                                    <p class="small text-muted mb-0">我们的团队将开始为您建设外链</p>
                                </div>
                            </div>
                            <div class="timeline-item d-flex mt-3">
                                <div class="timeline-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    3
                                </div>
                                <div class="timeline-content ms-3 text-start">
                                    <h6 class="mb-1">提供报告</h6>
                                    <p class="small text-muted mb-0">工作完成后，您将收到详细的外链报告</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-primary me-2">
                            <i class="bi bi-eye me-1"></i> 查看订单
                        </a>
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-primary">
                            <i class="bi bi-grid me-1"></i> 用户中心
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="fw-bold">您可能还需要</h5>
                    <div class="row g-3 mt-2">
                        @foreach($recommendedPackages as $package)
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-2">{{ Str::limit($package->name, 30) }}</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-{{ $package->package_type == 'monthly' ? 'primary' : ($package->package_type == 'single' ? 'info' : ($package->package_type == 'guest_post' ? 'success' : 'secondary')) }}">
                                            {{ $package->package_type == 'monthly' ? '月度套餐' : ($package->package_type == 'single' ? '单项套餐' : ($package->package_type == 'guest_post' ? '软文外链' : '特色外链')) }}
                                        </span>
                                        <span class="fw-bold">¥{{ number_format($package->price, 2) }}</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 text-center">
                                    <a href="{{ route('packages.show', $package) }}" class="btn btn-sm btn-outline-primary">查看详情</a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* 成功动画 */
.success-animation {
    width: 100px;
    height: 100px;
    margin: 0 auto;
}

.checkmark {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #4BB71B;
    stroke-miterlimit: 10;
    box-shadow: inset 0px 0px 0px #4BB71B;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
}

.checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #4BB71B;
    fill: none;
    animation: stroke .6s cubic-bezier(0.650, 0.000, 0.450, 1.000) forwards;
}

.checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke .3s cubic-bezier(0.650, 0.000, 0.450, 1.000) .8s forwards;
}

.order-details .row {
    margin-bottom: 0.5rem;
}

.timeline-icon {
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: bold;
}
</style>
@endpush
@endsection