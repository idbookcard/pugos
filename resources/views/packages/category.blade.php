@extends('layouts.app')

@section('title', $category->name . ' - 外链套餐')

@section('content')
<div class="bg-light py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route('packages.index') }}" class="text-decoration-none">套餐</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
            </ol>
        </nav>

        <div class="row">
            <!-- Sidebar with categories -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm sticky-lg-top" style="top: 2rem; z-index: 1;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-grid-3x3-gap-fill me-2"></i>套餐分类
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach(\App\Models\PackageCategory::where('active', true)->orderBy('sort_order')->get() as $cat)
                                <a href="{{ route('packages.category', $cat->slug) }}" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $cat->id == $category->id ? 'active' : '' }}">
                                    {{ $cat->name }}
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ \App\Models\Package::where('category_id', $cat->id)->where('active', true)->count() }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Additional filter card -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel-fill me-2"></i>快速筛选
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('packages.monthly') }}" class="btn btn-outline-primary">
                                <i class="bi bi-calendar-month me-2"></i>包月套餐
                            </a>
                            <a href="{{ route('packages.single') }}" class="btn btn-outline-primary">
                                <i class="bi bi-link-45deg me-2"></i>单次发布
                            </a>
                            <a href="{{ route('packages.guest-post') }}" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-2"></i>Guest Post
                            </a>
                            <a href="{{ route('packages.third-party') }}" class="btn btn-outline-primary">
                                <i class="bi bi-box-seam me-2"></i>自助服务
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main content with packages -->
            <div class="col-lg-9">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h1 class="h2 fw-bold mb-0">{{ $category->name }}</h1>
                    <span class="badge bg-info rounded-pill">{{ $packages->total() }} 个套餐</span>
                </div>
                
                @if($category->description)
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>{{ $category->description }}
                </div>
                @endif
                
                <!-- Packages grid -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @forelse($packages as $package)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-shadow">
                                @if($package->is_featured)
                                <div class="position-absolute top-0 end-0 mt-2 me-2">
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-star-fill me-1"></i>热门
                                    </span>
                                </div>
                                @endif
                                
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-{{ $package->package_type == 'monthly' ? 'primary' : ($package->package_type == 'single' ? 'success' : ($package->package_type == 'guest-post' ? 'purple' : 'secondary')) }}">
                                            {{ $package->package_type == 'monthly' ? '包月' : ($package->package_type == 'single' ? '单次' : ($package->package_type == 'guest-post' ? 'Guest Post' : '第三方')) }}
                                        </span>
                                        <span class="text-muted small">
                                            <i class="bi bi-clock me-1"></i>{{ $package->delivery_days }}天
                                        </span>
                                    </div>
                                    
                                    <h5 class="card-title fw-bold">
                                        <a href="{{ route('packages.show', $package->slug) }}" class="text-decoration-none stretched-link text-dark">
                                            {{ $package->name }}
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted small mb-3">
                                        {{ Str::limit($package->description, 80) }}
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                        <span class="fs-4 fw-bold text-primary">¥{{ $package->formatted_price }}</span>
@if($package->original_price && $package->original_price > $package->price)
    <del class="small text-muted ms-1">¥{{ $package->formatted_original_price }}</del>
@endif
                                        </div>
                                        <a href="{{ route('packages.show', $package->slug) }}" class="btn btn-sm btn-outline-primary">
                                            查看详情
                                        </a>
                                        <a href="{{ route('orders.create', $package->slug) }}" class="btn btn-sm btn-primary">
                                            立即购买
                                        </a>
                                    </div>
                                </div>
                                
                                @if($package->features)
                                <div class="card-footer bg-light border-top">
                                    <div class="small text-muted">
                                        <div class="row row-cols-1 row-cols-sm-2 g-2">
                                            @foreach(array_slice(json_decode($package->features, true) ?? [], 0, 4) as $feature)
                                                <div class="col">
                                                    <i class="bi bi-check-circle-fill text-success me-1"></i>{{ $feature }}
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        @if(count(json_decode($package->features, true) ?? []) > 4)
                                            <div class="text-end mt-1">
                                                <span class="text-primary small">更多特性...</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>暂无可用套餐
                            </div>
                        </div>
                    @endforelse
                </div>
                
                <!-- Pagination -->
                <div class="mt-4 d-flex justify-content-center">
                    {{ $packages->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to action -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="fw-bold">找不到您需要的套餐？</h2>
                <p class="mb-0">我们可以为您定制专属的外链方案，满足您的特定需求。</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="" class="btn btn-light btn-lg">
                    <i class="bi bi-chat-dots-fill me-2"></i>联系我们
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }
</style>
@endpush
@endsection