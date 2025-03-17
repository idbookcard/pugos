{{-- resources/views/packages/third-party-create.blade.php --}}
@extends('layouts.app')

@section('title', '订购 ' . $package->name . ' - SEO外链服务平台')

@section('content')
<div class="container py-4">
    @php
    $breadcrumbs = [
        '服务列表' => route('packages'),
        '第三方服务' => route('packages.third-party'),
        $package->name => route('packages.show', $package),
        '订购' => '',
    ];
    @endphp
    @include('partials.breadcrumb')
    
    <form method="POST" action="{{ route('packages.third-party.order', $package) }}" id="thirdPartyOrderForm">
        @csrf
        <div class="row">
            <!-- 左侧订单表单 -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h1 class="h4 card-title fw-bold mb-0">第三方服务订购详情</h1>
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
                                                特色外链服务 · {{ $package->delivery_days }}天交付 · 
                                                服务ID: {{ $package->third_party_service_id }}
                                            </p>
                                        </div>
                                        <div class="fw-bold ms-auto">¥{{ number_format($package->price, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 服务参数部分 -->
                        <div class="required-info mb-4">
                            <h5 class="fw-bold mb-3">服务参数</h5>
                            
                            <!-- 服务数量 -->
                            <div class="mb-3">
                                <label for="quantity" class="form-label">数量 <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $originalData['min_quantity'] ?? 1) }}" min="{{ $originalData['min_quantity'] ?? 1 }}" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">最小数量: {{ $originalData['min_quantity'] ?? 1 }}</div>
                            </div>
                            
                            <!-- 目标链接 -->
                            <div class="mb-3">
                                <label for="links" class="form-label">目标链接 <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('links') is-invalid @enderror" id="links" name="links" rows="3" required>{{ old('links') }}</textarea>
                                @error('links')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">多个链接请每行一个</div>
                            </div>
                            
                            <!-- 关键词 -->
                            <div class="mb-3">
                                <label for="keywords" class="form-label">关键词 <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('keywords') is-invalid @enderror" id="keywords" name="keywords" rows="2" required>{{ old('keywords') }}</textarea>
                                @error('keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">多个关键词请用逗号分隔，如: SEO优化,外链建设,谷歌排名</div>
                            </div>
                            
                            <!-- 额外服务 -->
                            <div class="mb-4">
                                <label class="form-label">额外服务 (可选)</label>
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50px;"></th>
                                                        <th>服务描述</th>
                                                        <th>单价</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($extras as $extra)
                                                    <tr>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input extra-checkbox" type="checkbox" value="{{ $extra['id'] }}" id="extra{{ $extra['id'] }}" name="extras[]" data-price="{{ $extra['price'] }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <label for="extra{{ $extra['id'] }}" class="form-check-label">
                                                                <strong>{{ $extra['code'] }}</strong>: {{ $extra['description'] }}
                                                            </label>
                                                        </td>
                                                        <td>${{ number_format($extra['price'], 4) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 文章类别 -->
                            <div class="mb-3">
                                <label for="article" class="form-label">文章类别 (可选)</label>
                                <select class="form-select @error('article') is-invalid @enderror" id="article" name="article">
                                    <option value="">-- 选择文章类别 --</option>
                                    @foreach($articleCategories as $category)
                                    <option value="{{ $category['code'] }}" {{ old('article') == $category['code'] ? 'selected' : '' }}>
                                        {{ $category['name'] }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('article')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">选择文章提交的类别</div>
                            </div>
                            
                            <!-- 层级设置 -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="enableTier" name="enable_tier" {{ old('enable_tier') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enableTier">
                                        启用层级外链 (Tier)
                                    </label>
                                </div>
                                <div id="tierOptionsContainer" class="mt-3 {{ old('enable_tier') ? '' : 'd-none' }}">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="tier_orders" class="form-label">一级外链订单号</label>
                                                <input type="text" class="form-control @error('tier_orders') is-invalid @enderror" id="tier_orders" name="tier_orders" value="{{ old('tier_orders') }}">
                                                @error('tier_orders')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">多个订单号请用逗号分隔，如: 1234,5678,9012</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 参考ID -->
                            <div class="mb-3">
                                <label for="ref_id" class="form-label">参考ID (可选)</label>
                                <input type="text" class="form-control @error('ref_id') is-invalid @enderror" id="ref_id" name="ref_id" value="{{ old('ref_id') }}" maxlength="15">
                                @error('ref_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">自定义项目名称，用于跟踪目的，最多15个字符</div>
                            </div>
                            
                            <!-- 订单备注 -->
                            <div class="mb-3">
                                <label for="notes" class="form-label">订单备注 (可选)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                <h6>第三方外链服务条款</h6>
                                <p>欢迎使用我们的第三方外链服务，请在订购前仔细阅读以下条款：</p>
                                <ol>
                                    <li><strong>服务内容</strong>：我们将按照所选服务和第三方平台的要求提供外链建设服务。</li>
                                    <li><strong>交付时间</strong>：交付时间取决于第三方平台的处理速度以及我们的审核时间，我们会尽力在承诺的时间内完成服务。</li>
                                    <li><strong>审核流程</strong>：您的订单提交后，我们会先进行人工审核，确认无误后再发送至第三方平台处理。</li>
                                    <li><strong>退款政策</strong>：订单审核前可以申请取消和退款，一旦订单发送至第三方平台开始处理，将不予退款。</li>
                                    <li><strong>结果保证</strong>：我们不能保证特定的排名结果，SEO效果受多种因素影响。</li>
                                    <li><strong>内容要求</strong>：提交的网站和关键词必须合法，不含违法内容。</li>
                                    <li><strong>报告提供</strong>：服务完成后，我们将从第三方平台获取详细的外链报告并提供给您。</li>
                                    <li><strong>客户责任</strong>：客户应提供准确的网站和关键词信息。</li>
                                    <li><strong>服务调整</strong>：我们保留根据第三方平台实际情况调整服务内容的权利。</li>
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
                                <span>基础服务费</span>
                                <span>¥<span id="basePrice">{{ number_format($package->price, 2) }}</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>数量</span>
                                <span><span id="quantityDisplay">1</span>x</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="extrasContainer" style="display: none;">
                                <span>额外服务费</span>
                                <span>¥<span id="extrasPrice">0.00</span></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>总计</span>
                                <span>¥<span id="totalPrice">{{ number_format($package->price, 2) }}</span></span>
                            </div>
                        </div>
                        
                        <!-- 流程说明 -->
                        <div class="mb-4">
                            <div class="alert alert-info">
                                <h6 class="alert-heading fw-bold"><i class="bi bi-info-circle me-2"></i>订单流程说明</h6>
                                <p class="small mb-0">您的订单提交后将进入人工审核环节，审核通过后会发送至第三方平台处理。整个过程您可在订单页面查看实时状态。</p>
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
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="submitButton" {{ Auth::user()->balance < $package->price ? 'disabled' : '' }}>
                            提交订单
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

.extra-checkbox:checked + .form-check-label {
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 表单验证
    const orderForm = document.getElementById('thirdPartyOrderForm');
    const acceptTerms = document.getElementById('acceptTerms');
    const submitButton = document.getElementById('submitButton');
    const quantityInput = document.getElementById('quantity');
    const quantityDisplay = document.getElementById('quantityDisplay');
    const basePrice = document.getElementById('basePrice');
    const totalPrice = document.getElementById('totalPrice');
    const extrasContainer = document.getElementById('extrasContainer');
    const extrasPrice = document.getElementById('extrasPrice');
    const extraCheckboxes = document.querySelectorAll('.extra-checkbox');
    const enableTier = document.getElementById('enableTier');
    const tierOptionsContainer = document.getElementById('tierOptionsContainer');
    
    // 基础价格
    const originalBasePrice = parseFloat('{{ $package->price }}');
    
    // 更新总价
    function updateTotalPrice() {
        const quantity = parseInt(quantityInput.value) || 1;
        let extras = 0;
        let selectedExtras = false;
        
        // 计算额外服务费
        extraCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedExtras = true;
                const extraPrice = parseFloat(checkbox.dataset.price) || 0;
                extras += extraPrice * 6.9; // 美元转人民币汇率约为6.9
            }
        });
        
        // 更新数量显示
        quantityDisplay.textContent = quantity;
        
        // 更新额外服务费显示
        if (selectedExtras) {
            extrasPrice.textContent = extras.toFixed(2);
            extrasContainer.style.display = 'flex';
        } else {
            extrasContainer.style.display = 'none';
        }
        
        // 计算总价
        const total = (originalBasePrice * quantity) + extras;
        totalPrice.textContent = total.toFixed(2);
        
        // 更新余额不足判断
        const userBalance = parseFloat('{{ Auth::user()->balance }}');
        if (userBalance < total) {
            submitButton.disabled = true;
            submitButton.textContent = '余额不足，请先充值';
        } else {
            submitButton.disabled = false;
            submitButton.textContent = '提交订单';
        }
    }
    
    // 监听数量变化
    quantityInput.addEventListener('input', updateTotalPrice);
    
    // 监听额外服务选择变化
    extraCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalPrice);
    });
    
    // 监听层级选项变化
    enableTier.addEventListener('change', function() {
        if (this.checked) {
            tierOptionsContainer.classList.remove('d-none');
        } else {
            tierOptionsContainer.classList.add('d-none');
        }
    });
    
    // 初始化总价计算
    updateTotalPrice();
    
    // 表单提交验证
    orderForm.addEventListener('submit', function(e) {
        if (!acceptTerms.checked) {
            e.preventDefault();
            alert('请阅读并同意服务条款');
            return false;
        }
        
        // 验证余额是否足够
        const total = parseFloat(totalPrice.textContent);
        const userBalance = parseFloat('{{ Auth::user()->balance }}');
        
        if (userBalance < total) {
            e.preventDefault();
            $('#rechargeModal').modal('show');
            return false;
        }
    });
    
    // 充值表单处理
    const rechargeForm = document.getElementById('rechargeForm');
    rechargeForm.addEventListener('submit', function(e) {
        // 在实际项目中此处可以添加验证逻辑
    });
});
</script>
@endpush
@endsection