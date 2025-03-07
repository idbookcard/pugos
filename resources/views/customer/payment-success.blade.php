{{-- resources/views/customer/payment-success.blade.php --}}
@extends('layouts.app')

@section('title', '支付成功 - SEO外链服务平台')

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
                    
                    <h1 class="display-6 fw-bold text-success mb-3">支付成功</h1>
                    <p class="lead mb-4">您的账户已成功充值 <strong>¥{{ number_format($amount, 2) }}</strong></p>
                    
                    <div class="transaction-details bg-light p-3 rounded mb-4 text-start">
                        <div class="row">
                            <div class="col-6 text-muted">交易号:</div>
                            <div class="col-6 text-end fw-bold">{{ $transaction->id }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-muted">支付方式:</div>
                            <div class="col-6 text-end">
                                @if($transaction->reference_type == 'alipay')
                                    支付宝
                                @elseif($transaction->reference_type == 'wechat')
                                    微信支付
                                @elseif($transaction->reference_type == 'unionpay')
                                    银联
                                @elseif($transaction->reference_type == 'crypto')
                                    数字货币
                                @else
                                    其他
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-muted">交易时间:</div>
                            <div class="col-6 text-end">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-muted">当前余额:</div>
                            <div class="col-6 text-end fw-bold">¥{{ number_format(Auth::user()->balance, 2) }}</div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="{{ route('customer.wallet') }}" class="btn btn-primary me-2">
                            <i class="bi bi-wallet2 me-1"></i> 返回钱包
                        </a>
                        <a href="{{ route('packages') }}" class="btn btn-outline-primary">
                            <i class="bi bi-shop me-1"></i> 浏览服务
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="fw-bold">为您推荐</h5>
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

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

@keyframes scale {
    0%, 100% {
        transform: none;
    }
    50% {
        transform: scale3d(1.1, 1.1, 1);
    }
}

@keyframes fill {
    100% {
        box-shadow: inset 0px 0px 0px 30px rgba(75, 183, 27, 0.1);
    }
}

.transaction-details .row {
    margin-bottom: 0.5rem;
}
</style>
@endpush
@endsection

