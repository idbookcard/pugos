{{-- resources/views/customer/wallet.blade.php --}}
@extends('layouts.app')

@section('title', '我的钱包 - SEO外链服务平台')

@section('content')
<div class="container py-4">
    @php
    $breadcrumbs = [
        '用户中心' => route('customer.dashboard'),
        '我的钱包' => '',
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
                        <a href="{{ route('customer.orders') }}" class="list-group-item list-group-item-action py-3">
                            <i class="bi bi-cart-check me-2"></i>我的订单
                        </a>
                        <a href="{{ route('customer.wallet') }}" class="list-group-item list-group-item-action active py-3">
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
            <!-- 钱包概览 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">钱包概览</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rechargeModal">
                        <i class="bi bi-plus-circle me-1"></i> 充值
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="balance-card p-4 text-center h-100 border rounded">
                                <div class="icon-wrapper mb-3">
                                    <i class="bi bi-wallet2 fs-1 text-primary"></i>
                                </div>
                                <h2 class="text-primary fw-bold mb-0">¥{{ number_format(Auth::user()->balance, 2) }}</h2>
                                <p class="text-muted">当前余额</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rechargeModal">充值</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card p-4 h-100 border rounded">
                                <h6 class="fw-bold mb-3">钱包统计</h6>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>总充值金额</span>
                                    <span class="fw-bold">¥{{ number_format($stats['total_deposits'] ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>总消费金额</span>
                                    <span class="fw-bold">¥{{ number_format($stats['total_payments'] ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>充值次数</span>
                                    <span class="fw-bold">{{ $stats['deposit_count'] ?? 0 }}次</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>消费次数</span>
                                    <span class="fw-bold">{{ $stats['payment_count'] ?? 0 }}次</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 交易记录 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">交易记录</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary {{ !request('type') ? 'active' : '' }}" onclick="location = '{{ route('customer.wallet') }}'">全部</button>
                        <button type="button" class="btn btn-outline-secondary {{ request('type') == 'deposit' ? 'active' : '' }}" onclick="location = '{{ route('customer.wallet', ['type' => 'deposit']) }}'">充值</button>
                        <button type="button" class="btn btn-outline-secondary {{ request('type') == 'payment' ? 'active' : '' }}" onclick="location = '{{ route('customer.wallet', ['type' => 'payment']) }}'">消费</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>交易号</th>
                                    <th>类型</th>
                                    <th>金额</th>
                                    <th>描述</th>
                                    <th>状态</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td>#{{ $transaction->id }}</td>
                                    <td>
                                        @if($transaction->type == 'deposit')
                                            <span class="badge bg-success">充值</span>
                                        @elseif($transaction->type == 'payment')
                                            <span class="badge bg-info">消费</span>
                                        @elseif($transaction->type == 'refund')
                                            <span class="badge bg-warning">退款</span>
                                        @endif
                                    </td>
                                    <td class="{{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                        {{ $transaction->amount > 0 ? '+' : '' }}¥{{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td>{{ $transaction->description }}</td>
                                    <td>
                                        @if($transaction->status == 'pending')
                                            <span class="badge bg-warning">处理中</span>
                                        @elseif($transaction->status == 'completed')
                                            <span class="badge bg-success">已完成</span>
                                        @elseif($transaction->status == 'failed')
                                            <span class="badge bg-danger">失败</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-wallet2 fs-1 text-muted mb-3"></i>
                                            <p class="text-muted mb-3">暂无交易记录</p>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#rechargeModal">
                                                <i class="bi bi-plus-circle me-1"></i> 立即充值
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-center">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 充值模态框 -->
<div class="modal fade" id="rechargeModal" tabindex="-1" aria-labelledby="rechargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rechargeModalLabel">账户充值</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rechargeForm" action="{{ route('customer.wallet.deposit') }}" method="POST">
                    @csrf
                    <!-- 充值金额选择 -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">选择充值金额</label>
                        <div class="row g-2 amount-options">
                            <div class="col-md-3 col-6">
                                <div class="form-check amount-option-card">
                                    <input class="form-check-input" type="radio" name="amount" id="amount100" value="100">
                                    <label class="form-check-label" for="amount100">
                                        <div class="text-center p-3 border rounded h-100">
                                            <h5 class="mb-0">¥100</h5>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check amount-option-card">
                                    <input class="form-check-input" type="radio" name="amount" id="amount500" value="500">
                                    <label class="form-check-label" for="amount500">
                                        <div class="text-center p-3 border rounded h-100">
                                            <h5 class="mb-0">¥500</h5>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check amount-option-card">
                                    <input class="form-check-input" type="radio" name="amount" id="amount1000" value="1000">
                                    <label class="form-check-label" for="amount1000">
                                        <div class="text-center p-3 border rounded h-100">
                                            <h5 class="mb-0">¥1000</h5>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check amount-option-card">
                                    <input class="form-check-input" type="radio" name="amount" id="amount2000" value="2000">
                                    <label class="form-check-label" for="amount2000">
                                        <div class="text-center p-3 border rounded h-100">
                                            <h5 class="mb-0">¥2000</h5>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="form-check amount-option-card">
                                    <input class="form-check-input" type="radio" name="amount" id="amountCustom" value="custom" checked>
                                    <label class="form-check-label" for="amountCustom">
                                        <div class="p-3 border rounded">
                                            <div class="d-flex align-items-center">
                                                <span class="me-3">自定义金额</span>
                                                <div class="input-group">
                                                    <span class="input-group-text">¥</span>
                                                    <input type="number" class="form-control" id="customAmount" name="custom_amount" min="100" step="100" value="500">
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 支付方式选择 -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">选择支付方式</label>
                        <div class="row">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="form-check payment-method-card">
                                    <input class="form-check-input" type="radio" name="payment_channel" id="alipay" value="alipay" checked>
                                    <label class="form-check-label" for="alipay">
                                        <div class="text-center p-3 border rounded h-100">
                                            <i class="bi bi-alipay fs-1 d-block mb-2"></i>
                                            <span>支付宝</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="form-check payment-method-card">
                                    <input class="form-check-input" type="radio" name="payment_channel" id="wechat" value="wechat">
                                    <label class="form-check-label" for="wechat">
                                        <div class="text-center p-3 border rounded h-100">
                                            <i class="bi bi-wechat fs-1 d-block mb-2"></i>
                                            <span>微信支付</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="form-check payment-method-card">
                                    <input class="form-check-input" type="radio" name="payment_channel" id="unionpay" value="unionpay">
                                    <label class="form-check-label" for="unionpay">
                                        <div class="text-center p-3 border rounded h-100">
                                            <i class="bi bi-credit-card fs-1 d-block mb-2"></i>
                                            <span>银联</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="form-check payment-method-card">
                                    <input class="form-check-input" type="radio" name="payment_channel" id="crypto" value="crypto">
                                    <label class="form-check-label" for="crypto">
                                        <div class="text-center p-3 border rounded h-100">
                                            <i class="bi bi-currency-bitcoin fs-1 d-block mb-2"></i>
                                            <span>数字货币</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 提交按钮 -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">确认充值</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 支付详情模态框（充值后显示） -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentDetailsModalLabel">请完成支付</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="payment-amount mb-3">
                        <span class="text-muted">支付金额</span>
                        <h2 class="fw-bold text-primary mb-0">¥<span id="paymentAmount">500.00</span></h2>
                    </div>
                    
                    <div class="qr-code-container mb-3">
                        <div id="alipayQR" class="payment-qr">
                            <img src="{{ asset('images/alipay-qrcode.png') }}" alt="支付宝二维码" class="img-fluid" style="max-width: 200px;">
                        </div>
                        <div id="wechatQR" class="payment-qr d-none">
                            <img src="{{ asset('images/wechat-qrcode.png') }}" alt="微信支付二维码" class="img-fluid" style="max-width: 200px;">
                        </div>
                        <div id="cryptoQR" class="payment-qr d-none">
                            <img src="{{ asset('images/crypto-qrcode.png') }}" alt="数字货币二维码" class="img-fluid" style="max-width: 200px;">
                            <p class="small mt-2 mb-0">USDT-TRC20: TS2Jpzm5CnUzBFsD2ty4ajCcuR3G6...</p>
                        </div>
                        <div id="unionpayForm" class="payment-form d-none">
                            <form class="text-start">
                                <div class="mb-3">
                                    <label for="cardNumber" class="form-label">卡号</label>
                                    <input type="text" class="form-control" id="cardNumber" placeholder="请输入银行卡号">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="expiryDate" class="form-label">有效期</label>
                                        <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="CVV">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="cardholderName" class="form-label">持卡人姓名</label>
                                    <input type="text" class="form-control" id="cardholderName" placeholder="请输入姓名">
                                </div>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-primary">确认支付</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <p class="payment-instructions">
                        <span id="alipayInstructions">请使用<strong>支付宝</strong>扫描上方二维码完成支付</span>
                        <span id="wechatInstructions" class="d-none">请使用<strong>微信</strong>扫描上方二维码完成支付</span>
                        <span id="cryptoInstructions" class="d-none">请使用<strong>数字货币钱包</strong>扫描上方二维码或转账至地址完成支付</span>
                        <span id="unionpayInstructions" class="d-none">请填写银行卡信息完成支付</span>
                    </p>
                    
                    <div class="order-id mt-3">
                        <span class="text-muted small">订单号：CR20250306001234</span>
                    </div>
                </div>
                
                <div class="payment-actions d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-success" id="paymentCompleted">我已完成支付</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.balance-card, .stats-card {
    transition: all 0.3s ease;
}

.balance-card:hover, .stats-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-5px);
}

.icon-wrapper {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    background-color: rgba(65, 105, 225, 0.1);
}

.amount-option-card, .payment-method-card {
    position: relative;
}

.amount-option-card .form-check-input, .payment-method-card .form-check-input {
    position: absolute;
    top: 10px;
    left: 15px;
    z-index: 10;
}

.amount-option-card label, .payment-method-card label {
    display: block;
    width: 100%;
    margin-bottom: 0;
    cursor: pointer;
}

.form-check-input:checked + .form-check-label > div {
    border-color: var(--primary-color) !important;
    background-color: rgba(65, 105, 225, 0.05);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 充值表单处理
    const rechargeForm = document.getElementById('rechargeForm');
    const amountCustom = document.getElementById('amountCustom');
    const customAmount = document.getElementById('customAmount');
    const amountOptions = document.querySelectorAll('input[name="amount"]');
    
    // 自定义金额输入框状态更新
    amountOptions.forEach(option => {
        option.addEventListener('change', function() {
            customAmount.disabled = !amountCustom.checked;
            if (amountCustom.checked) {
                customAmount.focus();
            }
        });
    });
    
    // 充值表单提交
    rechargeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 获取选中的金额
        let amount = 0;
        const selectedOption = document.querySelector('input[name="amount"]:checked');
        
        if (selectedOption.value === 'custom') {
            amount = customAmount.value;
        } else {
            amount = selectedOption.value;
        }
        
        // 获取选中的支付方式
        const paymentMethod = document.querySelector('input[name="payment_channel"]:checked').value;
        
        // 更新支付金额
        document.getElementById('paymentAmount').textContent = parseFloat(amount).toFixed(2);
        
        // 显示对应的支付方式
        document.getElementById('alipayQR').classList.add('d-none');
        document.getElementById('wechatQR').classList.add('d-none');
        document.getElementById('cryptoQR').classList.add('d-none');
        document.getElementById('unionpayForm').classList.add('d-none');
        
        document.getElementById('alipayInstructions').classList.add('d-none');
        document.getElementById('wechatInstructions').classList.add('d-none');
        document.getElementById('cryptoInstructions').classList.add('d-none');
        document.getElementById('unionpayInstructions').classList.add('d-none');
        
        document.getElementById(paymentMethod + 'QR')?.classList.remove('d-none');
        document.getElementById(paymentMethod + 'Form')?.classList.remove('d-none');
        document.getElementById(paymentMethod + 'Instructions').classList.remove('d-none');
        
        // 显示支付详情模态框
        $('#rechargeModal').modal('hide');
        $('#paymentDetailsModal').modal('show');
        
        // 模拟支付完成后的操作
        document.getElementById('paymentCompleted').addEventListener('click', function() {
            window.location.href = '{{ route('customer.wallet') }}?payment_success=1';
        });
    });
    
    // 如果有支付成功的参数，显示成功提示
    @if(request('payment_success'))
    setTimeout(function() {
        alert('充值成功！您的账户已成功充值。');
    }, 500);
    @endif
});
</script>
@endpush
@endsection