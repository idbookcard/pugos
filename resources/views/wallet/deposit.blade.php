@extends('layouts.app')

@section('title', '充值余额')

@section('content')
<div class="bg-light py-4">
  <div class="container">
    <!-- 面包屑导航 -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('wallet.index') }}" class="text-decoration-none">我的钱包</a></li>
        <li class="breadcrumb-item active" aria-current="page">充值余额</li>
      </ol>
    </nav>
    
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <!-- 充值表单卡片 -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h1 class="card-title fs-3 fw-bold mb-4">充值余额</h1>
            
            <form action="{{ route('wallet.process-deposit') }}" method="POST">
              @csrf
              
              <!-- 充值金额 -->
              <div class="mb-4">
                <label for="amount" class="form-label fw-medium">充值金额 <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">¥</span>
                  <input type="number" name="amount" id="amount" min="1" step="0.01" 
                         value="{{ old('amount', request('amount', 100)) }}" 
                         class="form-control py-2 @error('amount') is-invalid @enderror" 
                         placeholder="0.00" required>
                </div>
                @error('amount')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              
              <!-- 快速选择金额 -->
              <div class="mb-4">
                <label class="form-label fw-medium">快速选择金额</label>
                <div class="row g-2">
                  <div class="col-6 col-sm-3">
                    <button type="button" class="btn btn-light w-100 preset-amount" data-amount="100">¥100</button>
                  </div>
                  <div class="col-6 col-sm-3">
                    <button type="button" class="btn btn-light w-100 preset-amount" data-amount="200">¥200</button>
                  </div>
                  <div class="col-6 col-sm-3">
                    <button type="button" class="btn btn-light w-100 preset-amount" data-amount="500">¥500</button>
                  </div>
                  <div class="col-6 col-sm-3">
                    <button type="button" class="btn btn-light w-100 preset-amount" data-amount="1000">¥1000</button>
                  </div>
                </div>
              </div>
              
              <!-- 支付方式 -->
              <div class="mb-4">
                <label class="form-label fw-medium">支付方式 <span class="text-danger">*</span></label>
                <div class="row g-3">
                  <!-- 微信支付 -->
                  <div class="col-12 col-md-4">
                    <div class="card payment-option h-100 border">
                      <div class="card-body p-3">
                        <div class="form-check">
                          <input class="form-check-input payment-radio" type="radio" name="payment_method" 
                                 id="wechat" value="wechat" 
                                 {{ old('payment_method', request('payment_method')) == 'wechat' ? 'checked' : '' }}>
                          <label class="form-check-label w-100" for="wechat">
                            <div class="d-flex align-items-center">
                              <div class="bg-success bg-opacity-10 p-2 rounded">
                                <i class="bi bi-wechat text-success fs-4"></i>
                              </div>
                              <span class="ms-3 fw-medium">微信支付</span>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- 支付宝 -->
                  <div class="col-12 col-md-4">
                    <div class="card payment-option h-100 border">
                      <div class="card-body p-3">
                        <div class="form-check">
                          <input class="form-check-input payment-radio" type="radio" name="payment_method" 
                                 id="alipay" value="alipay" 
                                 {{ old('payment_method', request('payment_method')) == 'alipay' ? 'checked' : '' }}>
                          <label class="form-check-label w-100" for="alipay">
                            <div class="d-flex align-items-center">
                              <div class="bg-primary bg-opacity-10 p-2 rounded">
                                <i class="bi bi-credit-card text-primary fs-4"></i>
                              </div>
                              <span class="ms-3 fw-medium">支付宝</span>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- 加密货币 -->
                  <div class="col-12 col-md-4">
                    <div class="card payment-option h-100 border">
                      <div class="card-body p-3">
                        <div class="form-check">
                          <input class="form-check-input payment-radio" type="radio" name="payment_method" 
                                 id="crypto" value="crypto" 
                                 {{ old('payment_method', request('payment_method')) == 'crypto' ? 'checked' : '' }}>
                          <label class="form-check-label w-100" for="crypto">
                            <div class="d-flex align-items-center">
                              <div class="bg-warning bg-opacity-10 p-2 rounded">
                                <i class="bi bi-currency-bitcoin text-warning fs-4"></i>
                              </div>
                              <span class="ms-3 fw-medium">加密货币</span>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @error('payment_method')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              
              <!-- 加密货币选项 -->
              <div id="crypto-options" class="mb-4 {{ old('payment_method', request('payment_method')) == 'crypto' ? '' : 'd-none' }}">
                <label class="form-label fw-medium">选择加密货币</label>
                <div class="row g-3">
                  <!-- USDT-TRC20 -->
                  <div class="col-12 col-md-4">
                    <div class="card crypto-option h-100 border">
                      <div class="card-body p-3">
                        <div class="form-check">
                          <input class="form-check-input crypto-radio" type="radio" name="crypto_type" 
                                 id="usdt" value="USDT" 
                                 {{ old('crypto_type') == 'USDT' ? 'checked' : 'checked' }}>
                          <label class="form-check-label w-100" for="usdt">
                            <div class="d-flex align-items-center">
                              <div class="bg-success bg-opacity-10 p-2 rounded">
                                <i class="bi bi-coin text-success fs-4"></i>
                              </div>
                              <span class="ms-3 fw-medium">USDT-TRC20</span>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- 比特币 -->
                  <div class="col-12 col-md-4">
                    <div class="card crypto-option h-100 border">
                      <div class="card-body p-3">
                        <div class="form-check">
                          <input class="form-check-input crypto-radio" type="radio" name="crypto_type" 
                                 id="btc" value="BTC" 
                                 {{ old('crypto_type') == 'BTC' ? 'checked' : '' }}>
                          <label class="form-check-label w-100" for="btc">
                            <div class="d-flex align-items-center">
                              <div class="bg-warning bg-opacity-10 p-2 rounded">
                                <i class="bi bi-currency-bitcoin text-warning fs-4"></i>
                              </div>
                              <span class="ms-3 fw-medium">比特币</span>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- 以太坊 -->
                  <div class="col-12 col-md-4">
                    <div class="card crypto-option h-100 border">
                      <div class="card-body p-3">
                        <div class="form-check">
                          <input class="form-check-input crypto-radio" type="radio" name="crypto_type" 
                                 id="eth" value="ETH" 
                                 {{ old('crypto_type') == 'ETH' ? 'checked' : '' }}>
                          <label class="form-check-label w-100" for="eth">
                            <div class="d-flex align-items-center">
                              <div class="bg-indigo bg-opacity-10 p-2 rounded">
                                <i class="bi bi-currency-exchange text-indigo fs-4"></i>
                              </div>
                              <span class="ms-3 fw-medium">以太坊</span>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @error('crypto_type')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              
              <!-- 提交按钮 -->
              <div class="mt-4">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-medium">
                  确认充值
                </button>
              </div>
            </form>
          </div>
        </div>
        
        <!-- 充值说明卡片 -->
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h2 class="fs-5 fw-semibold mb-3">充值说明</h2>
            
            <div class="d-flex mb-3">
              <i class="bi bi-info-circle text-primary me-2 mt-1"></i>
              <p class="text-secondary mb-0">微信支付和支付宝充值一般即时到账，加密货币需要等待网络确认，一般10-30分钟。</p>
            </div>
            
            <div class="d-flex mb-3">
              <i class="bi bi-info-circle text-primary me-2 mt-1"></i>
              <p class="text-secondary mb-0">充值金额可申请开具发票，赠送金额不可开具发票。</p>
            </div>
            
            <div class="d-flex">
              <i class="bi bi-info-circle text-primary me-2 mt-1"></i>
              <p class="text-secondary mb-0">如充值遇到问题，请联系客服处理。</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // 处理支付方式选择
    const paymentRadios = document.querySelectorAll('.payment-radio');
    const cryptoOptions = document.getElementById('crypto-options');
    const paymentCards = document.querySelectorAll('.payment-option');
    
    // 初始化选中状态
    updatePaymentCardStyles();
    
    paymentRadios.forEach(radio => {
      radio.addEventListener('change', function() {
        updatePaymentCardStyles();
        
        // 显示/隐藏加密货币选项
        if (this.value === 'crypto') {
          cryptoOptions.classList.remove('d-none');
        } else {
          cryptoOptions.classList.add('d-none');
        }
      });
    });
    
    // 处理加密货币选择
    const cryptoRadios = document.querySelectorAll('.crypto-radio');
    const cryptoCards = document.querySelectorAll('.crypto-option');
    
    // 初始化选中状态
    updateCryptoCardStyles();
    
    cryptoRadios.forEach(radio => {
      radio.addEventListener('change', function() {
        updateCryptoCardStyles();
      });
    });
    
    // 处理预设金额
    const presetButtons = document.querySelectorAll('.preset-amount');
    const amountInput = document.getElementById('amount');
    
    presetButtons.forEach(button => {
      button.addEventListener('click', function() {
        const amount = this.dataset.amount;
        amountInput.value = amount;
        
        // 移除所有按钮的活动状态
        presetButtons.forEach(btn => btn.classList.remove('btn-primary', 'text-white'));
        presetButtons.forEach(btn => btn.classList.add('btn-light'));
        
        // 为当前按钮添加活动状态
        this.classList.remove('btn-light');
        this.classList.add('btn-primary', 'text-white');
      });
    });
    
    // 辅助函数：更新支付卡片样式
    function updatePaymentCardStyles() {
      paymentCards.forEach(card => {
        // 重置所有卡片样式
        card.classList.remove('border-primary');
        card.style.boxShadow = '';
      });
      
      // 为选中的卡片添加样式
      paymentRadios.forEach(radio => {
        if (radio.checked) {
          const card = radio.closest('.payment-option');
          card.classList.add('border-primary');
          card.style.boxShadow = '0 0 0 0.2rem rgba(13, 110, 253, 0.15)';
        }
      });
    }
    
    // 辅助函数：更新加密货币卡片样式
    function updateCryptoCardStyles() {
      cryptoCards.forEach(card => {
        // 重置所有卡片样式
        card.classList.remove('border-primary');
        card.style.boxShadow = '';
      });
      
      // 为选中的卡片添加样式
      cryptoRadios.forEach(radio => {
        if (radio.checked) {
          const card = radio.closest('.crypto-option');
          card.classList.add('border-primary');
          card.style.boxShadow = '0 0 0 0.2rem rgba(13, 110, 253, 0.15)';
        }
      });
    }
  });
</script>
@endpush
@endsection