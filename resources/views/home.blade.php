{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('title', '首页 - SEO外链服务平台')

@section('content')
<div class="hero-section text-white text-center text-md-start">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-4 fw-bold mb-4">提升您的网站排名<br>从高质量外链开始</h1>
                <p class="lead mb-4">我们提供专业的SEO外链建设服务，帮助您的网站在谷歌搜索结果中脱颖而出，获得持续的自然流量。</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                    <a href="{{ route('packages.monthly') }}" class="btn btn-light btn-lg px-4 py-2">月度套餐</a>
                    <a href="{{ route('packages.single') }}" class="btn btn-outline-light btn-lg px-4 py-2">单项套餐</a>
                    <a href="{{ route('packages.guest-post') }}" class="btn btn-outline-light btn-lg px-4 py-2">软文外链</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <img src="{{ asset('images/seo-illustration.svg') }}" alt="SEO优化插图" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- 服务优势 -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">我们的服务优势</h2>
            <p class="text-muted">专业的SEO外链解决方案，助力您的网站获得更好的排名</p>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 feature-box">
                <div class="card-body">
                    <i class="bi bi-graph-up-arrow mb-3"></i>
                    <h3>提升排名</h3>
                    <p>我们的外链来自高权重网站，能够有效提升您在谷歌搜索中的排名，获得更多自然流量。</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 feature-box">
                <div class="card-body">
                    <i class="bi bi-shield-check mb-3"></i>
                    <h3>安全可靠</h3>
                    <p>采用符合谷歌规则的白帽SEO技术，确保长期效益而不会带来任何搜索引擎惩罚的风险。</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 feature-box">
                <div class="card-body">
                    <i class="bi bi-cash-stack mb-3"></i>
                    <h3>性价比高</h3>
                    <p>以合理的价格获取高质量外链服务，并提供详细的外链报告，让您了解每一分钱的去向。</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 推荐套餐 -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">推荐套餐</h2>
            <p class="text-muted">为不同需求的客户提供多样化的外链解决方案</p>
        </div>
        
        @foreach($featuredPackages as $package)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 package-card {{ $package->is_featured ? 'featured-package' : '' }}">
                @if($package->is_featured)
                <span class="featured-badge">推荐</span>
                @endif
                
                <div class="card-header {{ $package->is_featured ? '' : 'bg-primary text-white' }}">
                    <h3 class="card-title mb-0">{{ Str::limit($package->name, 40) }}</h3>
                </div>
                
                <div class="card-body">
                    <div class="text-center mb-4">
                        <span class="price">¥{{ number_format($package->price, 2) }}</span>
                        <p class="text-muted">{{ $package->delivery_days }}天交付</p>
                    </div>
                    
                    <div class="mb-4">
                        {!! Str::limit(strip_tags($package->description), 120) !!}
                    </div>
                    
                    <ul class="feature-list list-unstyled">
                        @if($package->package_type == 'monthly')
                            <li>包含多周期外链建设</li>
                            <li>多样化外链来源</li>
                        @elseif($package->package_type == 'single')
                            <li>单次外链建设</li>
                            <li>快速交付</li>
                        @elseif($package->package_type == 'guest-post')
                            <li>高质量软文外链</li>
                            <li>DA{{ $package->guest_post_da }}站点</li>
                        @endif
                        <li>详细外链报告</li>
                    </ul>
                </div>
                
                <div class="card-footer text-center border-0 bg-transparent">
                    <a href="{{ route('packages.show', $package) }}" class="btn btn-primary">查看详情</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="text-center mb-5">
        <a href="{{ route('packages') }}" class="btn btn-outline-primary btn-lg">查看所有服务</a>
    </div>
    
    <!-- 外链类型介绍 -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">多样化的外链服务</h2>
            <p class="text-muted">满足不同场景的SEO需求</p>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-calendar-check fs-1 text-primary"></i>
                    </div>
                    <h4 class="fw-bold">月度套餐</h4>
                    <p>系统化的外链建设计划，每周按计划提供不同类型的外链，全方位提升网站权重。</p>
                    <a href="{{ route('packages.monthly') }}" class="btn btn-sm btn-outline-primary">了解更多</a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-link-45deg fs-1 text-primary"></i>
                    </div>
                    <h4 class="fw-bold">单项服务</h4>
                    <p>针对特定需求的单项外链服务，包括高DA外链、EDU外链、社交书签等多种类型。</p>
                    <a href="{{ route('packages.single') }}" class="btn btn-sm btn-outline-primary">了解更多</a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-stars fs-1 text-primary"></i>
                    </div>
                    <h4 class="fw-bold">特色外链</h4>
                    <p>与第三方平台合作的优质外链资源，提供更丰富的外链选择，满足多样化需求。</p>
                    <a href="{{ route('packages.third-party') }}" class="btn btn-sm btn-outline-primary">了解更多</a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-file-earmark-text fs-1 text-primary"></i>
                    </div>
                    <h4 class="fw-bold">软文外链</h4>
                    <p>在高质量网站上发布原创内容并包含您的链接，提供持久的SEO价值和品牌曝光。</p>
                    <a href="{{ route('packages.guest-post') }}" class="btn btn-sm btn-outline-primary">了解更多</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 为什么选择我们 -->
    <div class="row py-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <img src="{{ asset('images/why-choose-us.svg') }}" alt="为什么选择我们" class="img-fluid rounded shadow">
        </div>
        <div class="col-lg-6">
            <h2 class="fw-bold mb-4">为什么选择我们？</h2>
            
            <div class="d-flex mb-4">
                <div class="flex-shrink-0">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-people-fill text-white fs-4"></i>
                    </div>
                </div>
                <div class="ms-3">
                    <h4 class="fw-bold">专业的SEO团队</h4>
                    <p>我们拥有多年SEO经验的专业团队，了解搜索引擎的排名算法和最新变化，能够提供有效的外链策略。</p>
                </div>
            </div>
            
            <div class="d-flex mb-4">
                <div class="flex-shrink-0">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-award-fill text-white fs-4"></i>
                    </div>
                </div>
                <div class="ms-3">
                    <h4 class="fw-bold">高质量外链资源</h4>
                    <p>我们的外链来源包括高权重网站、教育类网站、专业博客等多样化的优质资源，确保外链质量和效果。</p>
                </div>
            </div>
            
            <div class="d-flex mb-4">
                <div class="flex-shrink-0">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-bar-chart-fill text-white fs-4"></i>
                    </div>
                </div>
                <div class="ms-3">
                    <h4 class="fw-bold">透明的报告系统</h4>
                    <p>每个订单完成后，我们提供详细的外链报告，包括链接位置、域名权重等信息，让您清楚了解服务效果。</p>
                </div>
            </div>
            
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-shield-check-fill text-white fs-4"></i>
                    </div>
                </div>
                <div class="ms-3">
                    <h4 class="fw-bold">安全的SEO策略</h4>
                    <p>我们只采用符合谷歌规则的白帽SEO技术，不使用任何可能导致惩罚的黑帽手段，确保长期稳定的效果。</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 客户评价 -->
    <div class="row py-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">客户评价</h2>
            <p class="text-muted">听听我们的客户怎么说</p>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
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
                    <p class="mb-3">自从使用了月度套餐服务后，我们网站的谷歌排名明显提升，关键词排名从第二页跃升到了首页，流量增长了30%以上。非常专业的服务！</p>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <span class="fw-bold">LM</span>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">李明</h5>
                            <p class="text-muted mb-0">科技公司CEO</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
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
                    <p class="mb-3">软文外链服务非常棒，文章质量高，链接自然，不仅提升了网站权重，还带来了相关流量。详细的报告让我对每个环节都一目了然。</p>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <span class="fw-bold">ZW</span>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">张伟</h5>
                            <p class="text-muted mb-0">电商网站负责人</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
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
                    <p class="mb-3">团队响应速度快，服务态度好。我们尝试了多种类型的外链套餐，效果都很明显，特别是EDU外链和高DA外链，为网站带来了可观的权重提升。</p>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <span class="fw-bold">WL</span>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">王丽</h5>
                            <p class="text-muted mb-0">营销总监</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 常见问题 -->
    <div class="row py-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">常见问题</h2>
            <p class="text-muted">解答您的疑惑</p>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">外链对网站排名有多大影响？</h5>
                    <p>外链是谷歌排名算法中的重要因素之一。高质量的外链相当于其他网站对您的"投票"，能够大幅提升网站的权威性和可信度，从而改善搜索排名。研究表明，外链质量和数量与搜索排名呈正相关。</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">订购后需要多久才能看到效果？</h5>
                    <p>SEO是一个长期过程，外链效果通常需要一段时间才能体现。一般来说，初步效果会在2-4周内开始显现，而明显的排名提升可能需要2-3个月时间。效果也受到网站本身质量、行业竞争程度等因素影响。</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">如何选择适合我网站的外链套餐？</h5>
                    <p>选择外链套餐需考虑您的网站现状、目标和预算。新网站可以先选择单项套餐，逐步建立基础外链；对于有一定基础的网站，月度套餐能提供全面的外链策略；竞争激烈的行业可考虑高DA软文外链提升权威性。</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">您们的外链安全吗？会不会被谷歌惩罚？</h5>
                    <p>我们只采用符合谷歌规则的白帽SEO技术，所有外链都来自真实高质量网站，内容相关性强，锚文本多样化，完全符合谷歌的自然外链标准。我们不使用任何可能导致惩罚的黑帽手段，确保您的网站安全。</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 行动召唤 -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body py-5 text-center">
                    <h2 class="fw-bold mb-3">准备好提升您的网站排名了吗？</h2>
                    <p class="lead mb-4">立即选择适合您的外链服务，开启SEO优化之旅</p>
                    <a href="{{ route('packages') }}" class="btn btn-light btn-lg">浏览所有服务</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection