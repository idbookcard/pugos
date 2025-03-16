@extends('layouts.app')

@section('title', '单项外链套餐 - 按需选择高质量外链服务')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="fw-bold">单项外链套餐</h1>
            <p class="lead text-secondary">按需选择，一次性获取高质量外链，提升您网站的权重和流量</p>
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
                                <option value="0-300">¥300以下</option>
                                <option value="300-600">¥300 - ¥600</option>
                                <option value="600+">¥600以上</option>
                            </select>
                        </div>
                        
                        <!-- 外链数量筛选 -->
                        <div class="col-md-4">
                            <label for="linkCount" class="form-label">外链数量</label>
                            <select class="form-select" id="linkCount">
                                <option value="all" selected>不限</option>
                                <option value="1-5">1-5个</option>
                                <option value="6-10">6-10个</option>
                                <option value="10+">10个以上</option>
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
                                    <th scope="col">外链数量</th>
                                    <th scope="col">DA值</th>
                                    <th scope="col">交付天数</th>
                                    <th scope="col">永久链接</th>
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
                                    <td>{{ $package->delivery_days }}天</td>
                                    <td>
                                        @php
                                            $isPermanent = false;
                                            foreach($features as $feature) {
                                                if(str_contains(strtolower($feature), '永久')) {
                                                    $isPermanent = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if($isPermanent)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        @else
                                        <i class="bi bi-x-circle text-secondary"></i>
                                        @endif
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
                                            <a href="{{ route('orders.create', $package->slug) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-cart-plus"></i> 订购
                                            </a>
                                        
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
                <h2 class="h3 mb-0">所有单项套餐</h2>
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
    
    <!-- 单项套餐优势 -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h3 class="h4 mb-4 text-center">单项外链套餐优势</h3>
                    
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-bullseye fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>精准定位</h5>
                                    <p class="text-secondary mb-0">根据您的具体需求，选择最适合的外链套餐，无需长期订阅。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-speedometer2 fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>快速交付</h5>
                                    <p class="text-secondary mb-0">平均7-10天内完成外链发布，让您的网站迅速获得权重提升。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-award fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>高质量保证</h5>
                                    <p class="text-secondary mb-0">所有外链都来自高权重相关性强的优质网站，确保SEO效果。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-currency-dollar fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>经济实惠</h5>
                                    <p class="text-secondary mb-0">按需付费，无需长期投入，性价比高，适合预算有限的企业。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-infinity fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>永久有效</h5>
                                    <p class="text-secondary mb-0">一次付费，外链永久生效，长期为您的网站提供权重加持。</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-bar-chart-line fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>详细报告</h5>
                                    <p class="text-secondary mb-0">提供完整的外链建设报告，包括所有链接地址和发布数据。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 常见应用场景 -->
    <div class="row mt-5">
        <div class="col-12 text-center mb-4">
            <h3 class="h4">适用场景</h3>
            <p class="text-secondary">单项外链套餐适合以下应用场景</p>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-rocket-takeoff-fill text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">新网站启动</h5>
                    <p class="card-text text-secondary">新上线的网站需要快速建立初始外链，提高搜索引擎收录速度和初始权重。</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-graph-up-arrow text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">关键词提升</h5>
                    <p class="card-text text-secondary">针对特定关键词进行外链建设，提高目标页面在搜索引擎中的排名。</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-megaphone-fill text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">新产品推广</h5>
                    <p class="card-text text-secondary">新产品或服务上线时，通过外链建设增加曝光度和品牌知名度。</p>
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
                            单项外链套餐与包月套餐有什么区别？
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p class="mb-0">单项外链套餐是一次性购买的服务，您支付一次费用后获得固定数量的外链。而包月套餐则是按月持续提供外链服务，适合长期SEO策略。单项套餐适合预算有限或有特定短期需求的用户，包月套餐则适合需要持续提升网站权重的用户。</p>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 shadow-sm mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            套餐中的外链是永久有效的吗？
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p class="mb-0">是的，我们的单项外链套餐中提供的所有外链都是永久性的，不会因为时间推移而失效。但需要注意的是，外部网站可能会因自身原因进行调整，虽然这种情况较少发生，但我们无法100%保证所有外链永远有效。如发现链接失效，可联系我们的客服处理。</p>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 shadow-sm mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            我能指定外链发布在哪些网站上吗？
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p class="mb-0">标准套餐中，我们会根据您的行业和网站内容选择相关性高、权重好的网站发布外链。如果您有特定的网站偏好，可以联系我们的客服团队讨论定制服务，我们会尽量满足您的需求，但可能会产生额外费用。</p>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 shadow-sm">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            购买后多久能看到效果？
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p class="mb-0">外链的效果通常不会立即显现，搜索引擎需要时间来发现和评估新的外链。根据我们的经验，大多数客户会在1-4周内开始看到排名的变化，但具体效果取决于多种因素，包括您的网站年龄、行业竞争程度、现有SEO基础等。对于新网站或竞争激烈的关键词，可能需要更长时间才能看到明显效果。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 行动号召 -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow bg-primary text-white text-center">
                <div class="card-body py-5">
                    <h3 class="mb-3">需要立即提升网站排名？</h3>
                    <p class="lead mb-4">选择适合您的单项外链套餐，快速获取高质量外链</p>
                    <a href="#gridView" class="btn btn-light btn-lg px-4">
                        <i class="bi bi-cursor-fill me-2"></i>选择套餐
                    </a>
                    <p class="mt-3 mb-0">需要定制服务？<a href="#" class="text-white text-decoration-underline">联系我们</a></p>
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
        const linkCountSelect = document.getElementById('linkCount');
        const sortOptionsSelect = document.getElementById('sortOptions');
        
        function applyFilters() {
            const priceRange = priceRangeSelect.value;
            const linkCount = linkCountSelect.value;
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
                    if (priceRange === '0-300' && price >= 300) shouldShow = false;
                    if (priceRange === '300-600' && (price < 300 || price >= 600)) shouldShow = false;
                    if (priceRange === '600+' && price < 600) shouldShow = false;
                }
                
                // 外链数量筛选
                if (linkCount !== 'all' && shouldShow) {
                    // 提取外链数量
                    const getLinkNumber = () => {
                        const match = features.match(/(\d+)个.*链/);
                        return match ? parseInt(match[1]) : 0;
                    };
                    
                    const numLinks = getLinkNumber();
                    if (linkCount === '1-5' && (numLinks < 1 || numLinks > 5)) shouldShow = false;
                    if (linkCount === '6-10' && (numLinks < 6 || numLinks > 10)) shouldShow = false;
                    if (linkCount === '10+' && numLinks <= 10) shouldShow = false;
                }// 显示或隐藏元素
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
        linkCountSelect.addEventListener('change', applyFilters);
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
        
        // 添加购物车功能
        document.querySelectorAll('.btn-primary:not(a)').forEach(button => {
            button.addEventListener('click', function() {
                // 获取最近的套餐卡片或行
                const packageElement = this.closest('.package-card, .package-row, .package-list-item');
                const packageName = packageElement.querySelector('.card-title, h3, .d-flex span:last-child').textContent.trim();
                
                // 显示添加成功提示
                const toast = document.createElement('div');
                toast.className = 'position-fixed bottom-0 end-0 p-3';
                toast.style.zIndex = '11';
                toast.innerHTML = `
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong class="me-auto">添加成功</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            "${packageName}" 已添加到购物车
                        </div>
                    </div>
                `;
                
                document.body.appendChild(toast);
                
                // 3秒后自动关闭
                setTimeout(() => {
                    toast.querySelector('.toast').classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
                
                // 关闭按钮事件
                toast.querySelector('.btn-close').addEventListener('click', () => {
                    toast.querySelector('.toast').classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                });
            });
        });
    });
</script>
@endsection