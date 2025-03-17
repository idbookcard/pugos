{{-- resources/views/orders/create-single-order.blade.php --}}
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
                    <h4 class="mb-0">创建单项套餐订单</h4>
                </div>
                
                <div class="card-body">
                    <form id="orderForm" action="{{ route('orders.store') }}" method="POST" x-data="singleOrderForm()">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        <input type="hidden" name="order_type" value="single">
                        
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
                            
                            <!-- 套餐描述 -->
                            <div class="mt-3">
                                <div class="alert alert-light">
                                    <h6 class="alert-heading fw-bold"><i class="bi bi-info-circle-fill me-2"></i>套餐内容</h6>
                                    <p>{{ $package->description }}</p>
                                    
                                    @if(is_array($package->features) || is_object($package->features))
                                        <ul class="mb-0 ps-4">
                                            @foreach($package->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 订单信息 -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">目标信息</h5>
                            
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
                                <label for="keywords" class="form-label">目标关键词 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('keywords') is-invalid @enderror" 
                                       id="keywords" name="keywords" value="{{ old('keywords') }}" required
                                       placeholder="SEO外链服务,网站排名优化">
                                @error('keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">请输入1-3个关键词，多个关键词请用英文逗号分隔</div>
                            </div>
                            
                            <!-- 数量选择 - 仅当最小数量大于1或套餐支持多数量订购时显示 -->
                            @if($package->min_quantity > 1)
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
                        </div>
                        
                        <hr class="my-4">
                     
                        
                        <!-- 额外选项 -->
                        @if(!empty($package->available_extras))
                        <hr class="my-4">
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">增值服务</h5>
                            <p class="text-muted mb-3">选择额外服务以增强您的外链效果</p>
                            
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
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" x-bind:disabled="!canSubmit">
                                    <span x-show="!canSubmit">余额不足，请先充值</span>
                                    <span x-show="canSubmit">提交订单</span>
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
        <div class="col-lg-4">
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
                    
                    <!-- 数量 -->
                    <div class="mb-3" x-show="quantity > 1">
                        <div class="d-flex justify-content-between">
                            <span>数量</span>
                            <span x-text="quantity + ' x ¥' + basePrice.toFixed(2)"></span>
                        </div>
                    </div>
                    
                    <!-- 额外选项价格（通过Alpine.js动态更新） -->
                    <div class="mb-3" x-show="extrasPrice > 0">
                        <div class="d-flex justify-content-between">
                            <span>额外选项</span>
                            <span x-text="'¥' + extrasPrice.toFixed(2)"></span>
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
                            <div class="d-flex justify-content-between">
                                <span>账户余额</span>
                                <span class="fs-5 fw-medium" x-bind:class="canSubmit ? 'text-success' : 'text-danger'">
                                    ¥{{ number_format(auth()->user()->balance, 2) }}
                                </span>
                            </div>
                            
                            <!-- 余额不足警告 -->
                            <div class="mt-2" x-show="!canSubmit">
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    余额不足，请先<a href="{{ route('wallet.deposit') }}">充值</a>
                                </div>
                            </div>
                            
                            <!-- 余额充足提示 -->
                            <div class="mt-2" x-show="canSubmit">
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    余额充足，可以直接下单
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info py-2 mb-0">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                请先<a href="{{ route('login') }}?redirect={{ url()->current() }}">登录</a>或<a href="{{ route('register') }}">注册</a>后购买
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
            
            <!-- 交付流程卡片 -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">服务流程</h5>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <li class="timeline-item mb-4">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-1">提交订单</h6>
                                <p class="small text-muted mb-0">填写目标网址和关键词</p>
                            </div>
                        </li>
                        <li class="timeline-item mb-4">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-1">审核确认</h6>
                                <p class="small text-muted mb-0">我们会在24小时内审核您的订单</p>
                            </div>
                        </li>
                        <li class="timeline-item mb-4">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-1">创建外链</h6>
                                <p class="small text-muted mb-0">我们的专家开始为您建立高质量外链</p>
                            </div>
                        </li>
                        <li class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-1">完成交付</h6>
                                <p class="small text-muted mb-0">提供详细报告，交付时间{{ $package->delivery_days }}天</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- 客服支持卡片 -->
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
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
    function singleOrderForm() {
        return {
            basePrice: {{ $package->price }},
            extrasPrice: 0,
            quantity: {{ old('quantity', $package->min_quantity ?? 1) }},
            totalPrice: {{ $package->price }},
            canSubmit: {{ $sufficientBalance ? 'true' : 'false' }},
            
            init() {
                this.updateTotalPrice();
                
                // 数量增减按钮
                const decreaseBtn = document.getElementById('decrease-quantity');
                const increaseBtn = document.getElementById('increase-quantity');
                
                if (decreaseBtn) {
                    decreaseBtn.addEventListener('click', () => {
                        if (this.quantity > {{ $package->min_quantity ?? 1 }}) {
                            this.quantity--;
                            document.getElementById('quantity').value = this.quantity;
                            this.updateTotalPrice();
                        }
                    });
                }
                
                if (increaseBtn) {
                    increaseBtn.addEventListener('click', () => {
                        this.quantity++;
                        document.getElementById('quantity').value = this.quantity;
                        this.updateTotalPrice();
                    });
                }
                
                // 表单提交前验证
                const form = document.getElementById('orderForm');
                if (form) {
                    form.addEventListener('submit', (e) => {
                        @auth
                        if (!this.canSubmit) {
                            e.preventDefault();
                            alert('余额不足，请先充值');
                            return false;
                        }
                        @endauth
                        
                        // 验证必填字段
                        const requiredFields = form.querySelectorAll('[required]');
                        let hasEmptyFields = false;
                        
                        requiredFields.forEach(field => {
                            if (!field.value.trim()) {
                                field.classList.add('is-invalid');
                                hasEmptyFields = true;
                            } else {
                                field.classList.remove('is-invalid');
                            }
                        });
                        
                        if (hasEmptyFields) {
                            e.preventDefault();
                            alert('请填写所有必填字段');
                            return false;
                        }
                        
                        return true;
                    });
                }
            },
            
            updateTotalPrice() {
                // 获取基础价格
                let base = this.basePrice;
                
                // 获取数量
                const quantityInput = document.getElementById('quantity');
                this.quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                
                // 计算额外选项价格
                this.extrasPrice = 0;
                
                // 处理多选额外选项
                document.querySelectorAll('input[type="checkbox"][name^="extras"]:checked').forEach(checkbox => {
                    try {
                        // 从UI中提取价格
                        const priceText = checkbox.closest('.card').querySelector('.text-primary').textContent.trim();
                        // 更精确的价格提取
                        const priceMatch = priceText.match(/\d+(\.\d+)?/);
                        if (priceMatch) {
                            const price = parseFloat(priceMatch[0]);
                            if (!isNaN(price)) {
                                this.extrasPrice += price;
                            }
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
                        // 更精确的价格提取
                        const priceMatch = priceText.match(/\d+(\.\d+)?/);
                        if (priceMatch) {
                            const price = parseFloat(priceMatch[0]);
                            if (!isNaN(price)) {
                                this.extrasPrice += price;
                            }
                        }
                    } catch (e) {
                        console.error('解析单选额外选项价格出错:', e);
                    }
                }
                
                // 计算总价 (基础价格 * 数量 + 额外选项)
                this.totalPrice = (base * this.quantity) + this.extrasPrice;
                
                // 检查余额是否充足 (仅登录用户)
                @auth
                const userBalance = parseFloat({{ auth()->user()->balance }});
                this.canSubmit = this.totalPrice <= userBalance;
                @endauth
            }
        }
    }
    
    // 页面加载后确保价格计算正确
    document.addEventListener('DOMContentLoaded', function() {
        // 延迟一下确保Alpine.js已加载
        setTimeout(() => {
            if (window.Alpine) {
                const form = document.getElementById('orderForm')?.__x?.$data;
                if (form && typeof form.updateTotalPrice === 'function') {
                    form.updateTotalPrice();
                }
            }
        }, 200);
        
        // 监听所有额外选项的变化以确保价格更新
        document.querySelectorAll('input[type="checkbox"][name^="extras"], input[type="radio"][name="extras_selection"]').forEach(input => {
            input.addEventListener('change', function() {
                if (window.Alpine) {
                    const form = document.getElementById('orderForm')?.__x?.$data;
                    if (form && typeof form.updateTotalPrice === 'function') {
                        form.updateTotalPrice();
                    }
                }
            });
        });
    });
</script>
@endsection
