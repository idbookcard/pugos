{{-- resources/views/packages/show.blade.php --}}
@extends('layouts.app')

@section('title', $package->name)

@section('content')
<div class="container py-5">
    <!-- 返回链接 -->
    <div class="mb-4">
        <a href="{{ route('packages.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> 返回所有套餐
        </a>
    </div>
    
    <div class="row">
        <!-- 左侧产品详情 -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <!-- 产品标题和类别 -->
                    <div class="mb-4">
                        <h1 class="mb-2">{{ $package->name }}</h1>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-dark me-2">{{ $package->category->name }}</span>
                            <span class="badge bg-light text-dark me-2">{{ $package->delivery_days }}天交付</span>
                            @if($package->is_featured)
                                <span class="badge bg-primary">热门选择</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- 产品描述 -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">产品描述</h5>
                        <div class="product-description">
                            {!! nl2br(e($package->description)) !!}
                        </div>
                    </div>
                    
                    <!-- 产品特性 -->
                    @if(!empty($package->features))
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">产品特性</h5>
                            <div class="row">
                            @foreach(is_string($package->features) ? json_decode($package->features) : $package->features as $feature)
                            <div class="col-md-6 mb-2">
                                        <div class="d-flex">
                                            <div class="me-2 text-success"><i class="bi bi-check-circle-fill"></i></div>
                                            <div>{{ $feature }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- 额外选项预览 -->
                    @if(!empty($package->available_extras))
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">可选额外服务</h5>
                            <div class="row">
                                @php
                                    $availableExtras = json_decode($package->available_extras, true);
                                    // 限制显示前4个
                                    $previewExtras = array_slice($availableExtras, 0, 4);
                                @endphp
                                
                                @foreach($previewExtras as $extra)
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2 text-info"><i class="bi bi-puzzle-fill"></i></div>
                                            <div>
                                                {{ $extra['name'] ?? $extra['code'] }}
                                                <span class="text-muted ms-1">
                                                    (+¥{{ number_format(floatval($extra['price']) * 7.4 / 100 * 1.5, 2) }})
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if(count($availableExtras) > 4)
                                    <div class="col-12 mt-2">
                                        <p class="text-muted small">
                                            <i class="bi bi-info-circle"></i> 
                                            还有 {{ count($availableExtras) - 4 }} 个额外服务可在下单页面选择
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- FAQ 部分 -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">常见问题</h5>
                        <div class="accordion" id="packageFaq">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        订购此套餐需要提供哪些信息？
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#packageFaq">
                                    <div class="accordion-body">
                                        您需要提供目标网址和关键词。对于某些套餐，您可能需要提供文章内容或其他详细信息。下单时会有明确的表单指导您填写所需信息。
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        我如何跟踪订单进度？
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#packageFaq">
                                    <div class="accordion-body">
                                        下单后，您可以在"我的订单"页面查看订单状态和进度。我们会在每个关键节点更新状态，并在完成时提供详细报告。
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        如果我对结果不满意怎么办？
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#packageFaq">
                                    <div class="accordion-body">
                                        我们承诺提供高质量的服务。如果您对结果不满意，请在收到报告后7天内联系客服，我们将评估情况并提供适当的解决方案。
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        额外选项是如何工作的？
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#packageFaq">
                                    <div class="accordion-body">
                                        额外选项是对基础套餐的增强服务，您可以根据需求选择不同的额外选项。每个选项都有独立的价格，会在结算时添加到套餐价格中。
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 右侧订购卡片 -->
        <div class="col-lg-4">
              <!-- 客服联系卡片 -->
              <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">需要帮助？</h5>
                    <p class="mb-3">如果您对此套餐有任何疑问，请随时联系我们的客服团队。</p>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-chat-text-fill me-2"></i> 在线咨询
                        </a>
                        <a href="mailto:support@example.com" class="btn btn-outline-secondary">
                            <i class="bi bi-envelope-fill me-2"></i> 发送邮件
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm position-sticky" style="top: 2rem;">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">套餐详情</h5>
                    
                    <!-- 价格区域 -->
                    <div class="mb-4">
                        <div class="d-flex align-items-baseline mb-2">
                            <span class="fs-2 fw-bold text-primary me-2">¥{{ number_format($package->price, 2) }}</span>
                            @if($package->original_price > $package->price)
                                <span class="text-muted text-decoration-line-through">¥{{ number_format($package->original_price, 2) }}</span>
                                <span class="badge bg-danger ms-2">-{{ $package->discount_percent }}%</span>
                            @endif
                        </div>
                        
                        <p class="text-muted mb-0">预计交付时间：{{ $package->delivery_days }}天</p>
                    </div>
                    
                    <!-- 套餐类型标识 -->
                    <div class="mb-4">
                        <div class="d-flex flex-wrap">
                            @if($package->package_type == 'single')
                                <span class="badge bg-primary me-2 mb-2">单项套餐</span>
                            @elseif($package->package_type == 'monthly')
                                <span class="badge bg-success me-2 mb-2">包月套餐</span>
                            @elseif($package->package_type == 'third_party')
                                <span class="badge bg-info me-2 mb-2">第三方服务</span>
                            @elseif($package->package_type == 'guest_post')
                                <span class="badge bg-warning text-dark me-2 mb-2">Guest Post</span>
                            @endif
                            
                            @if($package->is_api_product)
                                <span class="badge bg-secondary me-2 mb-2">API 自动处理</span>
                            @endif
                            
                            @if(!empty($package->available_extras))
                                <span class="badge bg-info me-2 mb-2">含额外选项</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- 套餐内容概览 -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">套餐包含</h6>
                        <ul class="list-group list-group-flush">
                        @if(!empty($package->features))
    @foreach(array_slice(is_string($package->features) ? json_decode($package->features) : $package->features, 0, 5) as $feature)
        <li class="list-group-item border-0 px-0 py-1">
            <i class="bi bi-check-circle-fill text-success me-2"></i> {{ $feature }}
        </li>
    @endforeach
@else
    <li class="list-group-item border-0 px-0 py-1">
        <i class="bi bi-check-circle-fill text-success me-2"></i> {{ $package->name }}
    </li>
@endif
                        </ul>
                    </div>
                    
                    <!-- 购买按钮 -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('orders.create', $package->slug) }}" class="btn btn-primary btn-lg">
                            立即购买
                        </a>
                        
                        @guest
                            <p class="text-center text-muted small mt-2">
                                购买前请先 <a href="{{ route('login') }}">登录</a> 或 <a href="{{ route('register') }}">注册</a>
                            </p>
                        @endguest
                    </div>
                </div>
            </div>
            
          
        </div>
    </div>
    
    <!-- 相关产品推荐 -->
    @if(count($relatedPackages) > 0)
        <div class="mt-5">
            <h3 class="mb-4">您可能还喜欢</h3>
            <div class="row g-4">
                @foreach($relatedPackages as $relatedPackage)
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 shadow-sm hover-shadow">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-1">{{ $relatedPackage->name }}</h5>
                                <p class="text-muted small mb-3">{{ $relatedPackage->category->name }}</p>
                                
                                <div class="mb-3">
                                    <span class="fs-5 fw-bold text-primary">¥{{ number_format($relatedPackage->price, 2) }}</span>
                                </div>
                                
                                <p class="card-text small mb-3">{{ Str::limit($relatedPackage->description, 60) }}</p>
                            </div>
                            
                            <div class="card-footer bg-white border-top-0 pt-0">
                                <div class="d-grid">
                                    <a href="{{ route('packages.show', $relatedPackage->slug) }}" class="btn btn-outline-primary btn-sm">查看详情</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@section('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    
    .product-description {
        line-height: 1.7;
    }
</style>
@endsection