@extends('layouts.app')

@section('title', '外链套餐 - 全部类别')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold mb-3">外链套餐</h1>
            <p class="lead text-secondary mb-4">我们提供多种类型的高质量外链套餐，满足不同网站的SEO需求</p>
            <div class="d-flex justify-content-center">
                <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
                    <a class="me-3 py-2 text-dark text-decoration-none" href="{{ route('packages.index') }}">
                        <i class="bi bi-grid-3x3-gap"></i> 全部套餐
                    </a>
                    <a class="me-3 py-2 text-dark text-decoration-none" href="{{ route('packages.monthly') }}">
                        <i class="bi bi-calendar-month"></i> 包月套餐
                    </a>
                    <a class="me-3 py-2 text-dark text-decoration-none" href="{{ route('packages.single') }}">
                        <i class="bi bi-box"></i> 单项套餐
                    </a>
                    <a class="me-3 py-2 text-dark text-decoration-none" href="{{ route('packages.third-party') }}">
                        <i class="bi bi-diagram-3"></i> 自助下单
                    </a>
                    <a class="py-2 text-dark text-decoration-none" href="{{ route('packages.guest-post') }}">
                        <i class="bi bi-file-earmark-text"></i> Guest Post
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- 筛选功能 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> 筛选选项
                    </h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="categoryFilter" class="form-label">套餐类别</label>
                            <select class="form-select" id="categoryFilter">
                                <option value="all" selected>全部类别</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="priceFilter" class="form-label">价格范围</label>
                            <select class="form-select" id="priceFilter">
                                <option value="all" selected>全部价格</option>
                                <option value="0-500">¥0 - ¥500</option>
                                <option value="500-1000">¥500 - ¥1000</option>
                                <option value="1000-2000">¥1000 - ¥2000</option>
                                <option value="2000+">¥2000以上</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sortOrder" class="form-label">排序方式</label>
                            <select class="form-select" id="sortOrder">
                                <option value="default" selected>默认排序</option>
                                <option value="price-asc">价格从低到高</option>
                                <option value="price-desc">价格从高到低</option>
                                <option value="delivery-asc">交付时间从快到慢</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" id="featuredOnly">
                                <label class="form-check-label" for="featuredOnly">
                                    只看推荐套餐
                                </label>
                            </div>
                            <button type="button" class="btn btn-primary" id="applyFilters">
                                <i class="bi bi-search"></i> 应用筛选
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 特色套餐 -->
    @php 
        $featuredPackages = collect();
        foreach($categories as $category) {
            foreach($category->packages as $package) {
                if($package->is_featured) {
                    $featuredPackages->push($package);
                }
            }
        }
    @endphp

    @if($featuredPackages->count() > 0)
    <div class="row mb-4 featured-packages-section">
        <div class="col-12">
            <h2 class="h3 border-start border-4 border-warning ps-3 mb-4">
                <i class="bi bi-star-fill text-warning"></i> 推荐套餐
            </h2>
        </div>
        
        @foreach($featuredPackages as $package)
            <div class="col-lg-4 col-md-6 mb-4 package-card-wrapper" 
                data-category="{{ $package->category_id }}"
                data-price="{{ $package->price }}"
                data-delivery="{{ $package->delivery_days }}"
                data-featured="1">
                <div class="card h-100 shadow-sm position-relative package-card">
                    @if($package->is_featured)
                        <div class="position-absolute top-0 end-0 bg-warning text-dark px-2 py-1 mt-2 me-2 rounded-pill fs-6 fw-bold featured-badge">
                            <i class="bi bi-star-fill"></i> 推荐
                        </div>
                    @endif
                    
                    <div class="card-header bg-light py-3">
                        <h3 class="h5 mb-0 text-center">{{ $package->name }}</h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <span class="fs-2 fw-bold text-primary">¥{{ number_format($package->price, 2) }}</span>
                            @if($package->original_price)
                                <span class="text-decoration-line-through text-muted ms-2">¥{{ number_format($package->original_price, 2) }}</span>
                                @php
                                    $discount = round((1 - $package->price / $package->original_price) * 100);
                                @endphp
                                <span class="badge bg-danger ms-2">{{ $discount }}% OFF</span>
                            @endif
                        </div>
                        
                        <p class="card-text mb-4">{{ $package->description }}</p>
                        
                        @if($package->features)
                            <div class="mb-4">
                                <ul class="list-group list-group-flush">
                                    @foreach(json_decode($package->features) as $feature)
                                        <li class="list-group-item bg-transparent ps-0">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i> {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            @if($package->package_type == 'guest_post' && $package->guest_post_da)
                                <span class="badge bg-info rounded-pill px-3 py-2">
                                    <i class="bi bi-graph-up"></i> DA {{ $package->guest_post_da }}+
                                </span>
                            @endif
                            
                            <span class="text-muted">
                                <i class="bi bi-clock"></i> {{ $package->delivery_days }} 天交付
                            </span>
                            
                            <span class="badge rounded-pill 
                                @if($package->package_type == 'monthly') bg-primary
                                @elseif($package->package_type == 'single') bg-success
                                @elseif($package->package_type == 'third_party') bg-secondary
                                @elseif($package->package_type == 'guest_post') bg-warning text-dark
                                @endif px-3 py-2">
                                @if($package->package_type == 'monthly') <i class="bi bi-calendar-month"></i> 包月
                                @elseif($package->package_type == 'single') <i class="bi bi-box"></i> 单项
                                @elseif($package->package_type == 'third_party') <i class="bi bi-diagram-3"></i> 自助
                                @elseif($package->package_type == 'guest_post') <i class="bi bi-file-earmark-text"></i> Guest Post
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-top-0 d-grid gap-2">
                        <a href="{{ route('packages.show', $package->slug) }}" class="btn btn-outline-primary">
                            <i class="bi bi-info-circle"></i> 查看详情
                        </a>
                        <a href="#" class="btn btn-primary">
                            <i class="bi bi-cart-plus"></i> 立即订购
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- 所有套餐类别 -->
    @foreach($categories as $category)
        @if($category->packages->count() > 0)
        <div class="row mb-5 category-section" id="category-{{ $category->id }}">
            <div class="col-12">
                <h2 class="h3 border-start border-4 border-primary ps-3 mb-4">
                    <i class="bi 
                        @if($category->slug == 'monthly-package') bi-calendar-month 
                        @elseif($category->slug == 'single-package') bi-box 
                        @elseif($category->slug == 'third-party') bi-diagram-3 
                        @elseif($category->slug == 'guest-post') bi-file-earmark-text 
                        @else bi-collection 
                        @endif"></i> 
                    {{ $category->name }}
                </h2>
                <p class="lead text-muted mb-4">{{ $category->description }}</p>
            </div>
            
            @foreach($category->packages as $package)
                <div class="col-lg-4 col-md-6 mb-4 package-card-wrapper" 
                    data-category="{{ $package->category_id }}"
                    data-price="{{ $package->price }}"
                    data-delivery="{{ $package->delivery_days }}"
                    data-featured="{{ $package->is_featured ? '1' : '0' }}">
                    <div class="card h-100 shadow-sm position-relative package-card">
                        @if($package->is_featured)
                            <div class="position-absolute top-0 end-0 bg-warning text-dark px-2 py-1 mt-2 me-2 rounded-pill fs-6 fw-bold featured-badge">
                                <i class="bi bi-star-fill"></i> 推荐
                            </div>
                        @endif
                        
                        <div class="card-header bg-light py-3">
                            <h3 class="h5 mb-0 text-center">{{ $package->name }}</h3>
                        </div>
                        
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <span class="fs-2 fw-bold text-primary">¥{{ number_format($package->price, 2) }}</span>
                                @if($package->original_price)
                                    <span class="text-decoration-line-through text-muted ms-2">¥{{ number_format($package->original_price, 2) }}</span>
                                    @php
                                        $discount = round((1 - $package->price / $package->original_price) * 100);
                                    @endphp
                                    <span class="badge bg-danger ms-2">{{ $discount }}% OFF</span>
                                @endif
                            </div>
                            
                            <p class="card-text mb-4">{{ $package->description }}</p>
                            
                            @if($package->features)
                                <div class="mb-4">
                                    <ul class="list-group list-group-flush">
                                        @foreach(json_decode($package->features) as $feature)
                                            <li class="list-group-item bg-transparent ps-0">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i> {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                @if($package->package_type == 'guest_post' && $package->guest_post_da)
                                    <span class="badge bg-info rounded-pill px-3 py-2">
                                        <i class="bi bi-graph-up"></i> DA {{ $package->guest_post_da }}+
                                    </span>
                                @endif
                                
                                <span class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $package->delivery_days }} 天交付
                                </span>
                                
                                <span class="badge rounded-pill 
                                    @if($package->package_type == 'monthly') bg-primary
                                    @elseif($package->package_type == 'single') bg-success
                                    @elseif($package->package_type == 'third_party') bg-secondary
                                    @elseif($package->package_type == 'guest_post') bg-warning text-dark
                                    @endif px-3 py-2">
                                    @if($package->package_type == 'monthly') <i class="bi bi-calendar-month"></i> 包月
                                    @elseif($package->package_type == 'single') <i class="bi bi-box"></i> 单项
                                    @elseif($package->package_type == 'third_party') <i class="bi bi-diagram-3"></i> 自助
                                    @elseif($package->package_type == 'guest_post') <i class="bi bi-file-earmark-text"></i> Guest Post
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-white border-top-0 d-grid gap-2">
                            <a href="{{ route('packages.show', $package->slug) }}" class="btn btn-outline-primary">
                                <i class="bi bi-info-circle"></i> 查看详情
                            </a>
                            <a href="#" class="btn btn-primary">
                                <i class="bi bi-cart-plus"></i> 立即订购
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if($category->packages->count() > 6)
                <div class="col-12 text-center mt-3">
                    @if($category->slug == 'monthly-package')
                        <a href="{{ route('packages.monthly') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-right-circle"></i> 查看更多包月套餐
                        </a>
                    @elseif($category->slug == 'single-package')
                        <a href="{{ route('packages.single') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-right-circle"></i> 查看更多单项套餐
                        </a>
                    @elseif($category->slug == 'third-party')
                        <a href="{{ route('packages.third-party') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-right-circle"></i> 查看更多自助下单
                        </a>
                    @elseif($category->slug == 'guest-post')
                        <a href="{{ route('packages.guest-post') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-right-circle"></i> 查看更多Guest Post
                        </a>
                    @endif
                </div>
            @endif
        </div>
        @endif
    @endforeach

    <!-- 无结果提示 -->
    <div class="row no-results-message d-none">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 未找到符合条件的套餐，请尝试调整筛选条件。
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 筛选功能
        const applyFiltersBtn = document.getElementById('applyFilters');
        const categoryFilter = document.getElementById('categoryFilter');
        const priceFilter = document.getElementById('priceFilter');
        const sortOrder = document.getElementById('sortOrder');
        const featuredOnly = document.getElementById('featuredOnly');
        const packageCards = document.querySelectorAll('.package-card-wrapper');
        const noResultsMessage = document.querySelector('.no-results-message');
        const featuredSection = document.querySelector('.featured-packages-section');
        const categorySections = document.querySelectorAll('.category-section');

        applyFiltersBtn.addEventListener('click', function() {
            filterPackages();
			let visibleCount = 0;
            
            packageCards.forEach(card => {
                const categoryId = card.dataset.category;
                const price = parseFloat(card.dataset.price);
                const delivery = parseInt(card.dataset.delivery);
                const isFeatured = card.dataset.featured === '1';
                let shouldShow = true;
                
                // 类别筛选
                if (categoryFilter.value !== 'all' && categoryId !== categoryFilter.value) {
                    shouldShow = false;
                }
                
                // 价格筛选
                if (priceFilter.value !== 'all') {
                    const [minPrice, maxPrice] = priceFilter.value.split('-');
                    if (maxPrice && (price < parseFloat(minPrice) || price > parseFloat(maxPrice))) {
                        shouldShow = false;
                    } else if (!maxPrice && price < parseFloat(minPrice)) {
                        shouldShow = false;
                    }
                }
                
                // 只看推荐套餐
                if (featuredOnly.checked && !isFeatured) {
                    shouldShow = false;
                }
                
                // 显示或隐藏
                if (shouldShow) {
                    card.classList.remove('d-none');
                    visibleCount++;
                } else {
                    card.classList.add('d-none');
                }
            });
            
            // 隐藏空的类别区块
            categorySections.forEach(section => {
                const visibleCardsInSection = section.querySelectorAll('.package-card-wrapper:not(.d-none)').length;
                if (visibleCardsInSection === 0) {
                    section.classList.add('d-none');
                } else {
                    section.classList.remove('d-none');
                }
            });
            
            // 显示或隐藏推荐套餐区块
            if (featuredSection) {
                const visibleFeaturedCards = featuredSection.querySelectorAll('.package-card-wrapper:not(.d-none)').length;
                if (visibleFeaturedCards === 0) {
                    featuredSection.classList.add('d-none');
                } else {
                    featuredSection.classList.remove('d-none');
                }
            }
            
            // 显示无结果提示
            if (visibleCount === 0) {
                noResultsMessage.classList.remove('d-none');
            } else {
                noResultsMessage.classList.add('d-none');
            }
            
            // 应用排序
            if (sortOrder.value !== 'default') {
                sortPackages(sortOrder.value);
            }
        });
        
        // 排序功能
        function sortPackages(sortBy) {
            const cardsContainer = document.querySelector('.row:not(.d-none)');
            const cards = Array.from(document.querySelectorAll('.package-card-wrapper:not(.d-none)'));
            
            // 根据选定的排序方式对卡片进行排序
            if (sortBy === 'price-asc') {
                cards.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
            } else if (sortBy === 'price-desc') {
                cards.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
            } else if (sortBy === 'delivery-asc') {
                cards.sort((a, b) => parseInt(a.dataset.delivery) - parseInt(b.dataset.delivery));
            }
            
            // 重新排列卡片
            cards.forEach(card => {
                const parent = card.parentNode;
                parent.appendChild(card);
            });
        }
        
        // 初始化筛选
        function filterPackages() {
            const categoryValue = categoryFilter.value;
            
            // 如果选择了特定类别，隐藏其他类别的区块
            if (categoryValue !== 'all') {
                categorySections.forEach(section => {
                    if (section.id === `category-${categoryValue}`) {
                        section.classList.remove('d-none');
                    } else {
                        section.classList.add('d-none');
                    }
                });
            } else {
                categorySections.forEach(section => {
                    section.classList.remove('d-none');
                });
            }
            
            // 应用其他筛选条件
            applyFiltersBtn.click();
        }
        
        // 页面加载时进行初始筛选
        filterPackages();
        
        // 添加套餐卡片悬停效果
        packageCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.querySelector('.package-card').classList.add('shadow');
            });
            
            card.addEventListener('mouseleave', function() {
                this.querySelector('.package-card').classList.remove('shadow');
            });
        });
    });
</script>
@endsection

@section('styles')
<style>
    .package-card {
        transition: all 0.3s ease;
    }
    
    .package-card:hover {
        transform: translateY(-5px);
    }
    
    .featured-badge {
        z-index: 10;
    }
    
    /* 添加渐变背景到特色套餐 */
    .package-card-wrapper[data-featured="1"] .card-header {
        background: linear-gradient(45deg, rgba(255,193,7,0.1), rgba(255,255,255,1));
    }
    
    /* 添加自定义筛选框样式 */
    #filterForm label {
        font-weight: 500;
    }
    
    #filterForm .form-select, #filterForm .btn {
        border-radius: 0.375rem;
    }
    
    #applyFilters {
        padding: 0.5rem 1rem;
    }
    
    /* 类别标题样式 */
    .border-start.border-4 {
        margin-top: 1rem;
    }
    
    /* 套餐卡片阴影 */
    .shadow-sm {
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
    }
    
    .shadow {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    
    /* 响应式调整 */
    @media (max-width: 768px) {
        .d-flex.justify-content-center nav {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .d-flex.justify-content-center nav a {
            margin-bottom: 0.5rem;
        }
        
        #filterForm .col-md-3 {
            margin-bottom: 1rem;
        }
    }
</style>
@endsection