{{-- resources/views/orders/create.blade.php --}}
@extends('layouts.app')

@section('title', '创建订单 - ' . $package->name)

@section('content')
<div class="container py-5">
    <!-- 返回链接 -->
    <div class="mb-4">
        <a href="{{ route('packages.show', $package->slug) }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> 返回套餐详情
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0">创建订单</h4>
                </div>
                
                <div class="card-body">
                    <form id="orderForm" action="{{ route('orders.store') }}" method="POST" x-data="orderForm()">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        
                        <!-- 套餐信息 -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $package->name }}</h5>
                                    <p class="text-muted mb-0">{{ $package->category->name }} • {{ $package->delivery_days }}天交付</p>
                                </div>
                                <div class="ms-auto">
                                    <span class="fs-5 fw-bold text-primary">¥{{ number_format($package->price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 订单信息 -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">订单信息</h5>
                            
                            <!-- 目标URL -->
                            <div class="mb-3">
                                <label for="target_url" class="form-label">目标网址 <span class="text-danger">*</span></label>
                                <input type="url" class="form-control @error('target_url') is-invalid @enderror" 
                                       id="target_url" name="target_url" value="{{ old('target_url') }}" required
                                       placeholder="https://example.com/your-page">
                                @error('target_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">请输入您想要提升排名的目标页面URL</div>
                            </div>
                            
                            <!-- 关键词 -->
                            <div class="mb-3">
                                <label for="keywords" class="form-label">关键词 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('keywords') is-invalid @enderror" 
                                       id="keywords" name="keywords" value="{{ old('keywords') }}" required
                                       placeholder="SEO外链服务,网站排名优化">
                                @error('keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">请输入1-3个关键词，多个关键词请用英文逗号分隔</div>
                            </div>
                            
                            <!-- 文章内容（如果需要） -->
                            @if(in_array($package->package_type, ['guest_post', 'third_party']))
                                <div class="mb-3">
                                    <label for="article" class="form-label">文章内容</label>
                                    <textarea class="form-control @error('article') is-invalid @enderror" 
                                              id="article" name="article" rows="6">{{ old('article') }}</textarea>
                                    @error('article')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">如果您有特定的文章内容，请在此提供。否则我们将为您创建内容。</div>
                                </div>
                            @endif
                            
                            <!-- 数量 -->
                            @if($package->min_quantity > 1 || $package->package_type == 'third_party')
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">数量</label>
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary" type="button" id="decrease-quantity">-</button>
                                        <input type="number" class="form-control text-center" id="quantity" name="quantity" 
                                               value="{{ old('quantity', $package->min_quantity) }}" min="{{ $package->min_quantity }}" step="1"
                                               @change="updateTotalPrice()">
                                        <button class="btn btn-outline-secondary" type="button" id="increase-quantity">+</button>
                                    </div>
                                    <div class="form-text">最小订购数量：{{ $package->min_quantity }}</div>
                                </div>
                            @else
                                <input type="hidden" name="quantity" value="1">
                            @endif
                            
                            <!-- 备注 -->
                            <div class="mb-3">
                                <label for="notes" class="form-label">备注（可选）</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">有任何特殊要求或需求，请在此说明</div>
                            </div>
                        </div>
                        
                        <!-- 额外选项 -->
                        @if(!empty($package->available_extras))
                        <hr class="my-4">
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">额外选项</h5>
                            <p class="text-muted mb-3">选择额外服务以增强您的订单效果</p>
                            
                            <div class="extras-list">
                                @php
                                    $availableExtras = is_array($package->available_extras) ? 
                                        $package->available_extras : 
                                        json_decode($package->available_extras, true);
                                    
                                    $multipleExtras = array_filter($availableExtras, function($extra) {
                                        return isset($extra['is_multiple']) && $extra['is_multiple'];
                                    });
                                    
                                    $singleExtras = array_filter($availableExtras, function($extra) {
                                        return !isset($extra['is_multiple']) || !$extra['is_multiple'];
                                    });
                                @endphp
                                
                                <!-- 多选额外选项 -->
                                @if(count($multipleExtras) > 0)
                                    <div class="mb-3">
                                        <div class="fw-semibold mb-2">多选服务（可同时选择多项）：</div>
                                        
                                        @foreach($multipleExtras as $extra)
                                            <div class="form-check mb-2 p-0">
                                                <div class="card hover-shadow">
                                                    <div class="card-body py-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       name="extras[{{ $extra['id'] }}]" 
                                                                       id="extra-{{ $extra['id'] }}" 
                                                                       value="1" 
                                                                       {{ old('extras.'.$extra['id']) ? 'checked' : '' }}
                                                                       @change="updateTotalPrice()">
                                                                <label class="form-check-label" for="extra-{{ $extra['id'] }}">
                                                                    {{ $extra['name'] ?? $extra['code'] }}
                                                                </label>
                                                            </div>
                                                            <div class="ms-auto text-primary fw-semibold">
                                                                +¥{{ number_format(floatval($extra['price']) * 7.4 / 100 * 1.5, 2) }}
                                                            </div>
                                                        </div>
                                                        @if(!empty($extra['code']) && ($extra['code'] != ($extra['name'] ?? '')))
                                                            <small class="text-muted d-block ps-4">{{ $extra['code'] }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <!-- 单选额外选项 -->
                                @if(count($singleExtras) > 0)
                                    <div class="mb-3">
                                        <div class="fw-semibold mb-2">单选服务（择一选择）：</div>
                                        
                                        <div class="card hover-shadow mb-2">
                                            <div class="card-body py-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" 
                                                           name="extras_selection" 
                                                           id="extras_selection_none" 
                                                           value="" 
                                                           {{ old('extras_selection') === null ? 'checked' : '' }}
                                                           @change="updateTotalPrice()">
                                                    <label class="form-check-label" for="extras_selection_none">
                                                        不需要额外选项
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @foreach($singleExtras as $extra)
                                            <div class="card hover-shadow mb-2">
                                                <div class="card-body py-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" 
                                                                   name="extras_selection" 
                                                                   id="extra-{{ $extra['id'] }}" 
                                                                   value="{{ $extra['id'] }}" 
                                                                   {{ old('extras_selection') == $extra['id'] ? 'checked' : '' }}
                                                                   @change="updateTotalPrice()">
                                                            <label class="form-check-label" for="extra-{{ $extra['id'] }}">
                                                                {{ $extra['name'] ?? $extra['code'] }}
                                                            </label>
                                                        </div>
                                                        <div class="ms-auto text-primary fw-semibold">
                                                            +¥{{ number_format(floatval($extra['price']) * 7.4 / 100 * 1.5, 2) }}
                                                        </div>
                                                    </div>
                                                    @if(!empty($extra['code']) && ($extra['code'] != ($extra['name'] ?? '')))
                                                        <small class="text-muted d-block ps-4">{{ $extra['code'] }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <hr class="my-4">
                        
                        <!-- 提交按钮 -->
                        <div class="d-grid">
                            @auth
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                    提交订单
                                </button>
                            @else
                                <button type="button" class="btn btn-primary btn-lg" id="login-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    提交订单
                                </button>
                            @endauth
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- 右侧订单摘要 -->
        <div class="col-lg-4 44444">
              <!-- 客服联系卡片 -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">需要帮助？</h5>
                    <p class="mb-3">如果您对订购流程有任何疑问，请联系我们的客服团队。</p>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-chat-dots-fill me-2"></i> 在线咨询
                        </a>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm position-sticky" style="top: 2rem;">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">订单摘要</h5>
                </div>
                
                <div class="card-body">
                    <!-- 套餐基本信息 -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>套餐价格</span>
                            <span>¥{{ number_format($package->price, 2) }}</span>
                        </div>
                    </div>
                    
                    <!-- 额外选项价格（通过Alpine.js动态更新） -->
                    <div class="mb-3" x-show="extrasPrice > 0">
                        <div class="d-flex justify-content-between">
                            <span>额外选项</span>
                            <span x-text="'¥' + extrasPrice.toFixed(2)"></span>
                        </div>
                    </div>
                    
                    <!-- 数量 -->
                    <div class="mb-3" x-show="quantity > 1">
                        <div class="d-flex justify-content-between">
                            <span>数量</span>
                            <span x-text="quantity + ' x ¥' + basePrice.toFixed(2)"></span>
                        </div>
                    </div>
                    
                    <!-- 总价 -->
                    <div class="d-flex justify-content-between fw-bold">
                        <span>总计</span>
                        <span class="fs-5 text-primary" id="total-price-display" x-text="'¥' + totalPrice.toFixed(2)"></span>
                    </div>
                    
                    <!-- 账户余额和登录状态 -->
                    <div class="mt-4">
    @auth
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="fw-medium">账户余额：</span>
            <span class="fs-5 fw-medium {{ $sufficientBalance ? 'text-success' : 'text-danger' }}">
                ¥{{ number_format(auth()->user()->total_balance, 2) }}
            </span>
        </div>
        
        @if(!$sufficientBalance)
            <div class="alert alert-warning py-2 mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                余额不足，请先<a href="{{ route('wallet.deposit') }}">充值</a>
            </div>
        @else
            <div class="alert alert-success py-2 mb-0">
                <i class="bi bi-check-circle-fill me-2"></i>
                余额充足，可以直接下单
            </div>
        @endif
    @else
        <div class="alert alert-info py-2 mb-0">
            <i class="bi bi-info-circle-fill me-2"></i>
            请先<a href="{{ route('login') }}?redirect={{ url()->current() }}">登录</a>或<a href="{{ route('register') }}">注册</a>后购买
        </div>
    @endauth
</div>
                </div>
            </div>
            
          
        </div>
    </div>
</div>

<!-- 未登录用户的登录提示模态框 -->
@guest
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">需要登录</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>请先登录或注册账户后再提交订单。</p>
                <p>登录后，您可以：</p>
                <ul>
                    <li>管理您的订单和历史记录</li>
                    <li>查看订单进度和报告</li>
                    <li>享受会员折扣和优惠</li>
                </ul>
            </div>
            <div class="modal-footer">
                <a href="{{ route('register') }}" class="btn btn-outline-primary">注册新账户</a>
                <a href="{{ route('login') }}?redirect={{ url()->current() }}" class="btn btn-primary">立即登录</a>
            </div>
        </div>
    </div>
</div>
@endguest
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function orderForm() {
        return {
            basePrice: {{ $package->price }},
            extrasPrice: 0,
            quantity: {{ old('quantity', $package->min_quantity ?? 1) }},
            totalPrice: {{ $package->price }},
            
            init() {
                this.updateTotalPrice();
                
                // 数量增减按钮
                document.getElementById('decrease-quantity')?.addEventListener('click', () => {
                    if (this.quantity > {{ $package->min_quantity ?? 1 }}) {
                        this.quantity--;
                        document.getElementById('quantity').value = this.quantity;
                        this.updateTotalPrice();
                    }
                });
                
                document.getElementById('increase-quantity')?.addEventListener('click', () => {
                    this.quantity++;
                    document.getElementById('quantity').value = this.quantity;
                    this.updateTotalPrice();
                });
            },
            
            updateTotalPrice() {
                // 获取基础价格
                let base = this.basePrice;
                
                // 获取数量
                this.quantity = parseInt(document.getElementById('quantity')?.value || 1);
                
                // 计算额外选项价格
                this.extrasPrice = 0;
                
                // 处理多选额外选项
                document.querySelectorAll('input[type="checkbox"][name^="extras"]:checked').forEach(checkbox => {
                    try {
                        // 从UI中提取价格
                        const priceText = checkbox.closest('.card').querySelector('.text-primary').textContent.trim();
                        const price = parseFloat(priceText.replace('+¥', '').replace(',', ''));
                        if (!isNaN(price)) {
                            this.extrasPrice += price;
                        }
                    } catch (e) {
                        console.error('解析多选额外选项价格出错:', e);
                    }
                });
                
                // 处理单选额外选项
                const selectedRadio = document.querySelector('input[type="radio"][name="extras_selection"]:checked');
                if (selectedRadio && selectedRadio.value) {
                    try {
                        // 从UI中提取价格
                        const priceText = selectedRadio.closest('.card').querySelector('.text-primary').textContent.trim();
                        const price = parseFloat(priceText.replace('+¥', '').replace(',', ''));
                        if (!isNaN(price)) {
                            this.extrasPrice += price;
                        }
                    } catch (e) {
                        console.error('解析单选额外选项价格出错:', e);
                    }
                }
                
                // 计算总价 (基础价格 + 额外选项) * 数量
                this.totalPrice = (base + this.extrasPrice) * this.quantity;
                
                // 检查余额是否充足 (仅登录用户)
                @auth
                const submitBtn = document.getElementById('submit-btn');
                if (submitBtn) {
                    if (this.totalPrice > {{ $balance ?? 0 }}) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = '余额不足，请先充值';
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.textContent = '提交订单';
                    }
                }
                @endauth
            }
        }
    }
</script>
@endsection

@section('styles')
<style>
    .hover-shadow {
        transition: all 0.2s ease;
    }
    .hover-shadow:hover {
        box-shadow: 0 .25rem .5rem rgba(0,0,0,.1)!important;
        border-color: #6c757d;
    }
</style>
@endsection