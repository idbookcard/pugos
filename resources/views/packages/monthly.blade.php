@extends('layouts.app')

@section('title', '包月外链套餐 - 持续提升您网站的权重')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="fw-bold">包月外链套餐</h1>
            <p class="lead text-secondary">持续、稳定地为您的网站建设高质量外链，提升搜索引擎排名</p>
        </div>
    </div>

    <!-- 筛选区域 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-funnel-fill text-primary me-2"></i>
                        <h5 class="card-title mb-0">筛选选项</h5>
                    </div>
                    
                    <div class="row g-3">
                        <!-- 价格筛选 -->
                        <div class="col-md-4">
                            <label for="priceRange" class="form-label">价格范围</label>
                            <select class="form-select" id="priceRange">
                                <option value="all" selected>所有价格</option>
                                <option value="0-1000">¥1000以下</option>
                                <option value="1000-2000">¥1000 - ¥2000</option>
                                <option value="2000+">¥2000以上</option>
                            </select>
                        </div>
                        
                        <!-- 特性筛选 -->
                        <div class="col-md-4">
                            <label for="featureFilter" class="form-label">套餐特性</label>
                            <select class="form-select" id="featureFilter">
                                <option value="all" selected>所有特性</option>
                                <option value="high-da">高DA值</option>
                                <option value="content">含内容创作</option>
                                <option value="report">详细报告</option>
                            </select>
                        </div>
                        
                        <!-- 排序选项 -->
                        <div class="col-md-4">
                            <label for="sortOptions" class="form-label">排序方式</label>
                            <select class="form-select" id="sortOptions">
                                <option value="recommended" selected>推荐排序</option>
                                <option value="price-low">价格从低到高</option>
                                <option value="price-high">价格从高到低</option>
                                <option value="links">外链数量</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 套餐比较表 -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex align-items-center">
                    <i class="bi bi-table text-primary me-2"></i>
                    <h5 class="mb-0">套餐比较</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">套餐名称</th>
                                    <th scope="col">每月外链数</th>
                                    <th scope="col">DA值</th>
                                    <th scope="col">内容创作</th>
                                    <th scope="col">报告频率</th>
                                    <th scope="col">价格</th>
                                    <th scope="col">操作</th>
                                </tr>
                            </thead>
                            <tbody id="packagesTableBody">
                                @foreach($packages as $package)
                                <tr class="package-row" 
                                    data-price="{{ $package->price }}"
                                    data-features="{{ json_encode($package->features) }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($package->is_featured)
                                            <span class="badge bg-warning me-2">推荐</span>
                                            @endif
                                            <span>{{ $package->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $features = $package->features;
                                            $linkCount = null;
                                            foreach($features as $feature) {
                                                if(preg_match('/(\d+)个.*链/', $feature, $matches)) {
                                                    $linkCount = $matches[1];
                                                    break;
                                                }
                                            }
                                        @endphp
                                        {{ $linkCount ?? '未指定' }}
                                    </td>
                                    <td>
                                        @php
                                            $da = null;
                                            foreach($features as $feature) {
                                                if(preg_match('/DA\s+(\d+)\+?/', $feature, $matches)) {
                                                    $da = $matches[1];
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if($da)
                                        <span class="badge bg-info">{{ $da }}+</span>
                                        @else
                                        <span class="text-muted">未指定</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $hasContent = false;
                                            foreach($features as $feature) {
                                                if(str_contains(strtolower($feature), '文章') || 
                                                   str_contains(strtolower($feature), '内容')) {
                                                    $hasContent = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if($hasContent)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        @else
                                        <i class="bi bi-x-circle text-secondary"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $reportFrequency = '月度';
                                            foreach($features as $feature) {
                                                if(str_contains(strtolower($feature), '周')) {
                                                    $reportFrequency = '周度';
                                                    break;
                                                } elseif(str_contains(strtolower($feature), '天')) {
                                                    $reportFrequency = '实时';
                                                    break;
                                                }
                                            }
                                        @endphp
                                        {{ $reportFrequency }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">¥{{ number_format($package->price, 2) }}</div>
                                        @if($package->original_price)
                                        <small class="text-decoration-line-through text-muted">
                                            ¥{{ number_format($package->original_price, 2) }}
                                        </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('packages.show', $package->slug) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-info-circle"></i> 详情
                                            </a>
                                            <button type="button" class="btn btn-sm btn-primary">
                                                <i class="bi bi-cart-plus"></i> 订购
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 套餐卡片显示 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h3 mb-0">所有包月套餐</h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary active" id="gridViewBtn">
                        <i class="bi bi-grid-3x3-gap"></i> 网格视图
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="listViewBtn">
                        <i class="bi bi-list-ul"></i> 列表视图
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 网格视图 -->
    <div class="row g-4" id="gridView">
        @foreach($packages as $package)
        <div class="col-md-6 col-lg-4 package-card" 
             data-price="{{ $package->price }}"
             data-features="{{ json_encode($package->features) }}">
            <div class="card h-100 border-0 shadow-sm {{ $package->is_featured ? 'border-primary' : '' }}">
                @if($package->is_featured)
                <div class="position-absolute top-0 end-0 p-2">
                    <span class="badge bg-warning">
                        <i class="bi bi-star-fill"></i> 推荐
                    </span>
                </div>
                @endif
                
                <div class="card-body">
                    <h3 class="card-title h5 mb-3">{{ $package->name }}</h3>
                    
                    <div class="price-tag mb-3">
                        <span class="fs-3 fw-bold text-primary">¥{{ number_format($package->price, 2) }}</span>
                        <span class="text-muted">/月</span>
                        @if($package->original_price)
                        <small class="d-block text-decoration-line-through text-muted">
                            原价: ¥{{ number_format($package->original_price, 2) }}
                        </small>
                        @endif
                    </div>
                    
                    <p class="card-text text-secondary">{{ $package->description }}</p>
                    
                    <hr>
                    
                    @if($package->features)
                    <h6 class="mb-2">套餐特点:</h6>
                    <ul class="list-group list-group-flush mb-4">
                        @foreach($package->features as $feature)
                        <li class="list-group-item px-0 border-0 d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    @endif
                    
                    <div class="d-flex mt-auto">
                        <span class="badge bg-light text-dark me-2">
                            <i class="bi bi-clock"></i> {{ $package->delivery_days }}天交付
                        </span>
                    </div>
                </div>
                
                <div class="card-footer bg-transparent border-0 d-flex justify-content-between">
                    <a href="{{ route('packages.show', $package->slug) }}" class="btn btn-outline-primary">
                        <i class="bi bi-info-circle"></i> 详情
                    </a>
                    <button type="button" class="btn btn-primary">
                        <i class="bi bi-cart-plus"></i> 订购
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- 列表视图 (默认隐藏) -->
    <div class="row d-none" id="listView">
        <div class="col-12">
            <div class="list-group">
                @foreach($packages as $package)
                <div class="list-group-item border-0 shadow-sm mb-3 package-list-item"
                     data-price="{{ $package->price }}"
                     data-features="{{ json_encode($package->features) }}">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <h3 class="h5 mb-0 me-2">{{ $package->name }}</h3>
                                @if($package->is_featured)
                                <span class="badge bg-warning">
                                    <i class="bi bi-star-fill"></i> 推荐
                                </span>
                                @endif
                            </div>
                            
                            <p class="mb-2 text-secondary">{{ $package->description }}</p>
                            
                            <div class="d-flex flex-wrap">
                                @php
                                    $features = $package->features;
                                    $topFeatures = array_slice((array)$features, 0, 3);
                                @endphp
                                
                                @foreach($topFeatures as $feature)
                                <span class="badge bg-light text-dark me-2 mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    {{ $feature }}
                                </span>
                                @endforeach
                                
                                @if(count((array)$features) > 3)
                                <a href="{{ route('packages.show', $package->slug) }}" class="badge bg-light text-primary mb-2">
                                    +{{ count((array)$features) - 3 }} 更多
                                </a>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="mb-3">
                                <span class="fs-4 fw-bold text-primary">¥{{ number_format($package->price, 2) }}</span>
                                <span class="text-muted">/月</span>
                                @if($package->original_price)
                                <small class="d-block text-decoration-line-through text-muted">
                                    原价: ¥{{ number_format($package->original_price, 2) }}
                                </small>
                                @endif
                            </div>
                            
                            <div class="btn-group">
                                <a href="{{ route('packages.show', $package->slug) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-info-circle"></i> 详情
                                </a>
                                <button type="button" class="btn btn-primary">
                                    <i class="bi bi-cart-plus"></i> 订购
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- 优势说明 -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h3 class="h4 mb-4 text-center">为什么选择我们的包月外链服务?</h3>
                    
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-graph-up-arrow fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>稳定提升排名</h5>
                                    <p class="text-secondary mb-0">持续的外链建设对搜索引擎排名有持久稳定的提升效果。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-shield-check fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>高质量保证</h5>
                                    <p class="text-secondary mb-0">所有外链都经过严格筛选，确保来源网站权重高、相关性强。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-file-earmark-bar-graph fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>详细报告</h5>
                                    <p class="text-secondary mb-0">定期提供详细的外链建设报告，让您清楚了解每一笔投资的效果。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-lightning-charge fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>高效执行</h5>
                                    <p class="text-secondary mb-0">专业团队高效执行，确保外链按时发布，提供及时的服务支持。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-person-gear fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>专业服务</h5>
                                    <p class="text-secondary mb-0">专业SEO团队提供一对一咨询服务，根据您的网站情况定制最佳外链策略。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-cash-coin fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>性价比高</h5>
                                    <p class="text-secondary mb-0">相比单次购买，包月套餐提供更多优质外链，为您节省预算。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 常见问题 -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="h4 mb-4 text-center">常见问题</h3>
            
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item border-0 shadow-sm mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            包月套餐与单次购买有什么区别？
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p class="mb-0">包月套餐提供持续的外链建设服务，每月定期为您的网站获取新的高质量外链，而不是一次性的服务。这样可以让您的网站获得更稳定的排名提升，同时从长远来看具有更高的性价比。包月套餐还包含更完善的报告和分析服务，帮助您了解外链效果。</p>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 shadow-sm mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            订购后多久能看到效果？
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p class="mb-0">外链建设是一个长期过程，通常需要1-3个月才能看到明显效果。搜索引擎需要时间来发现和索引新的外链，并计算其对您网站权重的影响。我们建议至少持续3个月的包月服务，以获得最佳效果。</p>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 shadow-sm mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            外链发布在哪些平台上？
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p class="mb-0">我们的外链来源包括各类高质量网站，如行业门户、知名博客、高权重论坛、新闻媒体等。所有外链来源都经过严格筛选，确保DA值和相关性符合要求，避免低质量网站。每个套餐对应不同等级的外链资源，详情可查看套餐说明或咨询客服。</p>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 shadow-sm">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            能否自定义锚文本和目标页面？
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p class="mb-0">是的，订购套餐后，您可以指定想要的锚文本和目标页面。我们的SEO专家会根据您的需求和行业最佳实践，提供建议，确保外链效果最大化。需要注意的是，为了避免过度优化导致的惩罚，我们会适当调整锚文本分布比例。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 客户评价 -->
    <div class="row mt-5">
        <div class="col-12 text-center mb-4">
            <h3 class="h4">客户评价</h3>
            <p class="text-secondary">看看其他客户使用我们的包月外链服务后的评价</p>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                    </div>
                    <p class="card-text mb-4">"使用包月套餐三个月后，我们网站的有机流量增长了40%，关键词排名明显提升。服务团队非常专业，每月的报告让我们清楚了解进展。"</p>
                    <div class="d-flex align-items-center">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <span class="text-white fw-bold">ZL</span>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0">张先生</h6>
                            <small class="text-muted">电子商务网站</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                        </div>
                    </div>
                    <p class="card-text mb-4">"以前尝试过很多SEO服务，但大多数只是昙花一现。使用这家的包月服务半年了，效果稳定持续，客服响应迅速，非常满意。"</p>
                    <div class="d-flex align-items-center">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <span class="text-white fw-bold">WL</span>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0">王女士</h6>
                            <small class="text-muted">教育培训机构</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                    </div>
                    <p class="card-text mb-4">"作为一个新上线的网站，我们选择了高级包月套餐，四个月后已经有几个关键词排在首页了。专业的外链质量和内容创作帮了大忙。"</p>
                    <div class="d-flex align-items-center">
                        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <span class="text-white fw-bold">LS</span>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0">李先生</h6>
                            <small class="text-muted">旅游服务平台</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 行动号召 -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow bg-primary text-white text-center">
                <div class="card-body py-5">
                    <h3 class="mb-3">准备好提升您的网站排名了吗？</h3>
                    <p class="lead mb-4">立即订购包月外链套餐，开启稳定增长之旅</p>
                    <a href="#gridView" class="btn btn-light btn-lg px-4">
                        <i class="bi bi-cursor-fill me-2"></i>选择套餐
                    </a>
                    <p class="mt-3 mb-0">有疑问？<a href="#" class="text-white text-decoration-underline">联系我们</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 视图切换功能
        const gridViewBtn = document.getElementById('gridViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        const gridView = document.getElementById('gridView');
        const listView = document.getElementById('listView');
        
        gridViewBtn.addEventListener('click', function() {
            gridView.classList.remove('d-none');
            listView.classList.add('d-none');
            gridViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
        });
        
        listViewBtn.addEventListener('click', function() {
            gridView.classList.add('d-none');
            listView.classList.remove('d-none');
            gridViewBtn.classList.remove('active');
            listViewBtn.classList.add('active');
        });
        
        // 筛选功能
        const priceRangeSelect = document.getElementById('priceRange');
        const featureFilterSelect = document.getElementById('featureFilter');
        const sortOptionsSelect = document.getElementById('sortOptions');
        
        function applyFilters() {
            const priceRange = priceRangeSelect.value;
            const featureFilter = featureFilterSelect.value;
            const sortOption = sortOptionsSelect.value;
            
            // 获取所有套餐卡片和表格行
            const packageCards = document.querySelectorAll('.package-card');
            const packageRows = document.querySelectorAll('.package-row');
            const packageListItems = document.querySelectorAll('.package-list-item');
            
            // 应用价格筛选和特性筛选
            [...packageCards, ...packageRows, ...packageListItems].forEach(item => {
                let shouldShow = true;
                const price = parseFloat(item.dataset.price);
                const features = item.dataset.features;
                
                // 价格筛选
                if (priceRange !== 'all') {
                    if (priceRange === '0-1000' && price >= 1000) shouldShow = false;
                    if (priceRange === '1000-2000' && (price < 1000 || price >= 2000)) shouldShow = false;
                    if (priceRange === '2000+' && price < 2000) shouldShow = false;
                }
                
                // 特性筛选
                if (featureFilter !== 'all' && shouldShow) {
                    if (featureFilter === 'high-da' && !features.includes('DA 30')) shouldShow = false;
                    if (featureFilter === 'content' && !features.includes('文章') && !features.includes('内容')) shouldShow = false;
                    if (featureFilter === 'report' && !features.includes('报告')) shouldShow = false;
                }
                
                // 显示或隐藏元素
                if (shouldShow) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
            
            // 排序功能
            if (sortOption !== 'recommended') {
                sortPackages(sortOption);
            }
        }
        
        function sortPackages(sortOption) {
            const gridContainer = document.getElementById('gridView');
            const listContainer = document.getElementById('listView').querySelector('.list-group');
            const tableBody = document.getElementById('packagesTableBody');
            
            // 排序网格卡片
            const gridItems = Array.from(gridContainer.querySelectorAll('.package-card:not(.d-none)'));
            // 排序列表项
            const listItems = Array.from(listContainer.querySelectorAll('.package-list-item:not(.d-none)'));
            // 排序表格行
            const tableRows = Array.from(tableBody.querySelectorAll('.package-row:not(.d-none)'));
            
            // 根据选项进行排序
            function sortByOption(items) {
                return items.sort((a, b) => {
                    const priceA = parseFloat(a.dataset.price);
                    const priceB = parseFloat(b.dataset.price);
                    
                    if (sortOption === 'price-low') return priceA - priceB;
                    if (sortOption === 'price-high') return priceB - priceA;
                    if (sortOption === 'links') {
                        // 提取外链数量进行排序
                        const getLinkCount = (element) => {
                            const features = element.dataset.features;
                            const match = features.match(/(\d+)个.*链/);
                            return match ? parseInt(match[1]) : 0;
                        };
                        
                        const linksA = getLinkCount(a);
                        const linksB = getLinkCount(b);
                        
                        return linksB - linksA; // 降序排列
                    }
                    
                    return 0;
                });
            }
            
            // 应用排序
            const sortedGridItems = sortByOption(gridItems);
            const sortedListItems = sortByOption(listItems);
            const sortedTableRows = sortByOption(tableRows);
            
            // 重新添加排序后的元素
            sortedGridItems.forEach(item => gridContainer.appendChild(item));
            sortedListItems.forEach(item => listContainer.appendChild(item));
            sortedTableRows.forEach(item => tableBody.appendChild(item));
        }
        
        // 添加事件监听
        priceRangeSelect.addEventListener('change', applyFilters);
        featureFilterSelect.addEventListener('change', applyFilters);
        sortOptionsSelect.addEventListener('change', applyFilters);
        
        // 初始应用筛选
        applyFilters();
        
        // 平滑滚动
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
</script>
@endsection