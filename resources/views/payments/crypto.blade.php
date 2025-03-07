@extends('layouts.app')

@section('title', '数字货币支付 - SEO外链服务平台')

@section('content')
<div class="container py-4">
    @php
    $breadcrumbs = [
        '用户中心' => route('customer.dashboard'),
        '我的钱包' => route('customer.wallet'),
        '数字货币支付' => '',
    ];
    @endphp
    @include('partials.breadcrumb')
    
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h1 class="h4 card-title fw-bold mb-0">数字货币支付</h1>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="payment-amount mb-3">
                            <span class="text-muted">支付金额</span>
                            <h2 class="fw-bold text-primary mb-0">¥{{ number_format($amount, 2) }}</h2>
                            <p class="text-muted mb-0">约等于 {{ $crypto_amount }} {{ $crypto_currency }}</p>
                        </div>
                        
                        <div class="crypto-options mb-4">
                            <div class="d-flex justify-content-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary active" data-crypto="USDT">USDT</button>
                                    <button type="button" class="btn btn-outline-primary" data-crypto="BTC">BTC</button>
                                    <button type="button" class="btn btn-outline-primary" data-crypto="ETH">ETH</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="crypto-network-options mb-4">
                            <div class="d-flex justify-content-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary active" data-network="TRC20">TRC20</button>
                                    <button type="button" class="btn btn-outline-secondary" data-network="ERC20">ERC20</button>
                                    <button type="button" class="btn btn-outline-secondary" data-network="BEP20">BEP20</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="qr-code-container mb-3">
                            <div class="usdt-container">
                                <img src="{{ asset('images/usdt-qrcode.png') }}" alt="USDT 支付二维码" class="img-fluid mb-3" style="max-width: 200px;">
                                <div class="address-container">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" value="TCn9ihVQbx5JLcKRMJXQFh3jyqQChMuJLy" readonly id="cryptoAddress">
                                        <button class="btn btn-outline-secondary" type="button" id="copyAddress">复制</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <span class="network-instruction">请使用支持 TRC20 网络的钱包转账 USDT 至上方地址</span>
                        </div>
                        
                        <div class="payment-timer mb-3">
                            <p class="text-muted">支付倒计时: <span id="timer">15:00</span></p>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div class="payment-verification mt-4">
                            <button class="btn btn-primary" id="verifyPayment">
                                <i class="bi bi-arrow-repeat me-2"></i>检查支付状态
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-outline-secondary" onclick="history.back()">返回</button>
                        <div class="order-id">
                            <span class="text-muted small">订单号：CR20250306001234</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">数字货币支付说明</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">请确保选择正确的加密货币和网络，发送错误的加密货币或使用错误的网络可能导致资金丢失。</li>
                        <li class="mb-2">请精确发送显示的金额，多付或少付都可能导致交易延迟或失败。</li>
                        <li class="mb-2">转账完成后，通常需要等待网络确认，这可能需要几分钟到几小时不等。</li>
                        <li class="mb-2">确认到账后，系统将自动为您的账户充值相应金额。</li>
                        <li>如有任何问题，请联系客服获取帮助。</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 复制地址
    const copyAddressBtn = document.getElementById('copyAddress');
    const cryptoAddress = document.getElementById('cryptoAddress');
    
    copyAddressBtn.addEventListener('click', function() {
        cryptoAddress.select();
        document.execCommand('copy');
        this.innerHTML = '已复制';
        setTimeout(() => {
            this.innerHTML = '复制';
        }, 2000);
    });
    
    // 切换加密货币
    const cryptoButtons = document.querySelectorAll('[data-crypto]');
    cryptoButtons.forEach(button => {
        button.addEventListener('click', function() {
            // 移除所有按钮的active类
            cryptoButtons.forEach(btn => btn.classList.remove('active'));
            // 为当前按钮添加active类
            this.classList.add('active');
            
            // 更新显示的金额和地址
            const crypto = this.getAttribute('data-crypto');
            // 在实际项目中这里需要通过API获取实时汇率和地址
        });
    });
    
    // 切换网络
    const networkButtons = document.querySelectorAll('[data-network]');
    networkButtons.forEach(button => {
        button.addEventListener('click', function() {
            // 移除所有按钮的active类
            networkButtons.forEach(btn => btn.classList.remove('active'));
            // 为当前按钮添加active类
            this.classList.add('active');
            
            // 更新网络说明
            const network = this.getAttribute('data-network');
            const networkInstruction = document.querySelector('.network-instruction');
            const crypto = document.querySelector('[data-crypto].active').getAttribute('data-crypto');
            networkInstruction.textContent = `请使用支持 ${network} 网络的钱包转账 ${crypto} 至上方地址`;
            
            // 更新地址
            // 在实际项目中这里需要通过API获取对应网络的地址
        });
    });
    
    // 倒计时
    let timeLeft = 15 * 60; // 15分钟
    const timerDisplay = document.getElementById('timer');
    const timerInterval = setInterval(function() {
        const minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        
        timerDisplay.textContent = `${minutes}:${seconds}`;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            timerDisplay.textContent = '00:00';
            // 订单超时处理
            alert('支付超时，请重新发起支付');
            window.location.href = '{{ route('customer.wallet') }}';
        }
        
        // 更新进度条
        const progressBar = document.querySelector('.progress-bar');
        const percentage = (timeLeft / (15 * 60)) * 100;
        progressBar.style.width = `${percentage}%`;
        
        timeLeft--;
    }, 1000);
    
    // 检查支付状态
    const verifyPaymentBtn = document.getElementById('verifyPayment');
    verifyPaymentBtn.addEventListener('click', function() {
        // 在实际项目中这里需要通过API检查支付状态
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 检查中...';
        this.disabled = true;
        
        // 模拟检查过程
        setTimeout(() => {
            // 随机模拟成功或失败
            const success = Math.random() > 0.5;
            
            if (success) {
                window.location.href = '{{ route('customer.wallet') }}?payment_success=1';
            } else {
                this.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>检查支付状态';
                this.disabled = false;
                alert('未检测到支付，请确认转账是否完成，或等待区块确认。');
            }
        }, 2000);
    });
});
</script>
@endpush
@endsection