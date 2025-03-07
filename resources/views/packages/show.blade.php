{{-- resources/views/customer/orders/create.blade.php --}}
@extends('layouts.app')

@section('title', '订购 ' . $package->name . ' - SEO外链服务平台')

@section('content')
<div class="container py-4">
    @php
    $breadcrumbs = [
        '服务列表' => route('packages'),
        $package->name => route('packages.show', $package),
        '订购' => '',
    ];
    @endphp
    @include('partials.breadcrumb')
    
    <form method="POST" action="{{ route('customer.orders.store', $package) }}" id="orderForm">
        @csrf
        <div class="row">
            <!-- 左侧订单表单 -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h1 class="h4 card-title fw-bold mb-0">订购详情</h1>
                    </div>
                    <div class="card-body">
                        <div class="selected-package mb-4">
                            <h5 class="fw-bold mb-3">已选服务</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6>{{ $package->name }}</h6>
                                            <p class="mb-0 text-muted small">
                                                @if($package->package_type == 'monthly')
                                                    月度套餐
                                                @elseif($package->package_type == 'single')
                                                    单项套餐
                                                @elseif($package->package_type == 'third_party')
                                                    特色外链
                                                @elseif($package->package_type == 'guest_post')
                                                    软文外链 · DA{{ $package->guest_post_da }}
                                                @endif
                                                · {{ $package->delivery_days }}天交付
                                            </p>
                                        </div>
                                        <div class="fw-bold ms-auto">¥{{ number_format($package->price, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="required-info mb-4">
                            <h5 class="fw-bold mb-3">订单信息</h5>
                            <div class="mb-3">
                                <label for="target_url" class="form-label">目标网址 <span class="text-danger">*</span></label>
                                <input type="url" class="form-control @error('target_url') is-invalid @enderror" id="target_url" name="target_url" value="{{ old('target_url') }}" required>
                                @error('target_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">需要获取外链的目标网址，例如: https://www.example.com</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="keywords" class="form-label">关键词 <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('keywords') is-invalid @enderror" id="keywords" name="keywords" rows="2" required>{{ old('keywords') }}</textarea>
                                @error('keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">多个关键词使用逗号分隔，例如: SEO优化,外链建设,谷歌排名</div>
                            </div>
                            
                            @if(json_decode($package->required_fields)->description ?? false)
                            <div class="mb-3">
                                <label for="description" class="form-label">网站描述 <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">简要描述您的网站内容和目标，帮助我们创建更相关的内容</div>
                            </div>
                            @endif
                            
                            @if($package->package_type == 'monthly')
                            <div class="mb-3">
                                <label for="weekly_urls" class="form-label">周计划URL安排 <span class="text-danger">*</span></label>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="week1_url" class="form-label">第一周URL</label>
                                            <input type="url" class="form-control" id="week1_url" name="order_data[week1_url]" value="{{ old('order_data.week1_url') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="week2_url" class="form-label">第二周URL</label>
                                            <input type="url" class="form-control" id="week2_url" name="order_data[week2_url]" value="{{ old('order_data.week2_url') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="week3_url" class="form-label">第三周URL</label>
                                            <input type="url" class="form-control" id="week3_url" name="order_data[week3_url]" value="{{ old('order_data.week3_url') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="week4_url" class="form-label">第四周URL</label>
                                            <input type="url" class="form-control" id="week4_url" name="order_data[week4_url]" value="{{ old('order_data.week4_url') }}">
                                        </div>
                                        <div class="form-text">
                                            <i class="bi bi-info-circle me-1"></i> 您可以为每周提供不同的URL，也可以只填写一个URL用于整个月的外链建设
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">特殊要求 (可选)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">任何额外的特殊要求或说明</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 服务条款 -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title fw-bold mb-0">服务条款</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="terms-box p-3 border rounded" style="height: 200px; overflow-y: auto;">
                                <h6>SEO外链服务条款</h6>
                                <p>欢迎使用我们的SEO外链服务，请在订购前仔细阅读以下条款：</p>
                                <ol>
                                    <li><strong>服务内容</strong>：我们将按照所选套餐描述提供外链建设服务。</li>
                                    <li><strong>交付时间</strong>：我们会尽力在承诺的时间内完成服务，但实际时间可能会有所浮动。</li>
                                    <li><strong>退款政策</strong>：一旦订单开始处理，将不予退款。如有特殊情况，请联系客服。</li>
                                    <li><strong>结果保证</strong>：我们不能保证特定的排名结果，SEO效果受多种因素影响。</li>
                                    <li><strong>内容要求</strong>：提交的网站和关键词必须合法，不含违法内容。</li>
                                    <li><strong>报告提供</strong>：服务完成后，我们将提供详细的外链报告。</li>
                                    <li><strong>客户责任</strong>：客户应提供准确的网站和关键词信息。</li>
                                    <li><strong>服务调整</strong>：我们保留根据实际情况调整服务内容的权利。</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="acceptTerms" name="accept_terms" required>
                            <label class="form-check-label" for="acceptTerms">
                                我已阅读并同意服务条款
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 右侧订单摘要 -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm order-summary sticky-top" style="top: 20px; z-index: 999;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">订单摘要</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>服务费用</span>
                                <span>¥{{ number_format($package->price, 2) }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>总计</span>
                                <span>¥{{ number_format($package->price, 2) }}</span>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold">支付方式</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="balancePayment" value="balance" checked>
                                <label class="form-check-label" for="balancePayment">
                                    账户余额支付
                                    <span class="badge bg-info ms-2">
                                        当前余额: ¥{{ number_format(Auth::user()->balance, 2) }}
                                    </span>
                                </label>
                            </div>
                            
                            @if(Auth::user()->balance < $package->price)
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                您的余额不足，请先充值或选择其他支付方式
                            </div>
                            
                            <div class="recharge-options mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm mb-2 w-100" data-bs-toggle="modal" data-bs-target="#rechargeModal">
                                    <i class="bi bi-plus-circle me-1"></i> 立即充值
                                </button>
                            </div>
                            @endif
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" {{ Auth::user()->balance < $package->price ? 'disabled' : '' }}>
                            确认订购
                        </button>
                        
                        <div class="order-help text-center">
                            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#contactModal">
                                <i class="bi bi-question-circle me-1"></i> 需要帮助？联系客服
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 充值模态框 -->
<div class="modal fade" id="rechargeModal" tabindex="-1" aria-labelledby="rechargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rechargeModalLabel">账户充值</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rechargeForm" action="{{ route('customer.wallet.deposit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="amount" class="form-label">充值金额</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" class="form-control" id="amount" name="amount" min="100" step="100" value="{{ max($package->price - Auth::user()->balance, 100) }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">充值方式</label>
                        <div class="payment-methods">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_channel" id="alipay" value="alipay" checked>
                                        <label class="form-check-label" for="alipay">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-alipay fs-4 me-2"></i>
                                                <span>支付宝</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_channel" id="wechat" value="wechat">
                                        <label class="form-check-label" for="wechat">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-wechat fs-4 me-2"></i>
                                                <span>微信支付</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_channel" id="bank" value="bank">
                                        <label class="form-check-label" for="bank">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-bank fs-4 me-2"></i>
                                                <span>银行卡</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_channel" id="crypto" value="crypto">
                                        <label class="form-check-label" for="crypto">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-currency-bitcoin fs-4 me-2"></i>
                                                <span>数字货币</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">立即充值</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 联系客服模态框 -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">联系客服</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bi bi-headset display-1 text-primary"></i>
                    <h4 class="mt-3">我们随时为您提供帮助</h4>
                    <p class="text-muted">选择您喜欢的联系方式</p>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-chat-dots fs-1 text-primary mb-3"></i>
                                <h5>在线聊天</h5>
                                <p class="small text-muted">工作时间: 9:00-22:00</p>
                                <button class="btn btn-outline-primary btn-sm">开始聊天</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-telephone fs-1 text-primary mb-3"></i>
                                <h5>电话咨询</h5>
                                <p class="small text-muted">工作时间: 9:00-18:00</p>
                                <a href="tel:+86-21-12345678" class="btn btn-outline-primary btn-sm">拨打电话</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.order-summary {
    transition: all 0.3s ease;
}

@media (max-width: 991.98px) {
    .order-summary {
        position: static !important;
    }
}

.payment-method-card {
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    margin-bottom: 0;
}

.payment-method-card .form-check-input {
    margin-top: 0.3rem;
}

.payment-method-card label {
    width: 100%;
    cursor: pointer;
}

.form-check-input:checked + .form-check-label .payment-method-card {
    border-color: var(--primary-color);
    background-color: rgba(65, 105, 225, 0.05);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 表单验证
    const orderForm = document.getElementById('orderForm');
    const acceptTerms = document.getElementById('acceptTerms');
    
    orderForm.addEventListener('submit', function(e) {
        if (!acceptTerms.checked) {
            e.preventDefault();
            alert('请阅读并同意服务条款');
            return false;
        }
        
        // 验证余额是否足够
        @if(Auth::user()->balance < $package->price)
        e.preventDefault();
        $('#rechargeModal').modal('show');
        return false;
        @endif
    });
    
    // 充值表单处理
    const rechargeForm = document.getElementById('rechargeForm');
    rechargeForm.addEventListener('submit', function(e) {
        // 在实际项目中此处可以添加验证逻辑
        // 提交后会自动跳转到支付页面
    });
});
</script>
@endpush
@endsection