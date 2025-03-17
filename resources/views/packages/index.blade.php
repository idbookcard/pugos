{{-- resources/views/packages/index.blade.php --}}
@extends('layouts.app')

@section('title', '产品列表')

@section('content')
<div class="container py-5">
    <!-- 页面标题 -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">SEO 外链套餐</h1>
            <p class="text-muted">提升您网站的排名和曝光度，选择适合您的外链服务</p>
        </div>
        <div class="col-md-4">
            <div class="d-flex justify-content-end align-items-center h-100">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ request('category') ? $categories->firstWhere('slug', request('category'))->name : '所有类别' }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                        <li><a class="dropdown-item" href="{{ route('packages.index') }}">所有类别</a></li>
                        @foreach($categories as $category)
                            <li><a class="dropdown-item" href="{{ route('packages.index', ['category' => $category->slug]) }}">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 产品分类导航 -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ !request('type') ? 'active' : '' }}" href="{{ route('packages.index', ['category' => request('category')]) }}">所有套餐</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('type') == 'single' ? 'active' : '' }}" href="{{ route('packages.index', ['category' => request('category'), 'type' => 'single']) }}">单项套餐</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('type') == 'monthly' ? 'active' : '' }}" href="{{ route('packages.index', ['category' => request('category'), 'type' => 'monthly']) }}">包月套餐</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('type') == 'third_party' ? 'active' : '' }}" href="{{ route('packages.index', ['category' => request('category'), 'type' => 'third_party']) }}">第三方服务</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('type') == 'guest_post' ? 'active' : '' }}" href="{{ route('packages.index', ['category' => request('category'), 'type' => 'guest_post']) }}">Guest Post</a>
        </li>
    </ul>

    <!-- 产品列表 -->
    <div class="row g-4">
        @forelse($packages as $package)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm hover-shadow">
                    @if($package->is_featured)
                        <div class="position-absolute top-0 end-0">
                            <span class="badge bg-primary rounded-0 rounded-bottom-start px-3 py-2">热门</span>
                        </div>
                    @endif
                    
                    @if($package->discount_percent > 0)
                        <div class="position-absolute top-0 start-0">
                            <span class="badge bg-danger rounded-0 rounded-bottom-end px-3 py-2">-{{ $package->discount_percent }}%</span>
                        </div>
                    @endif
                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-1">{{ $package->name }}</h5>
                        <p class="text-muted small mb-3">{{ $package->category->name }}</p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="fs-4 fw-bold text-primary">¥{{ number_format($package->price, 2) }}</span>
                                @if($package->original_price > $package->price)
                                    <small class="text-muted text-decoration-line-through ms-2">¥{{ number_format($package->original_price, 2) }}</small>
                                @endif
                            </div>
                            <span class="badge bg-light text-dark">{{ $package->delivery_days }}天交付</span>
                        </div>
                        
                        <p class="card-text mb-3">{{ Str::limit($package->description, 80) }}</p>
                        
                        <!-- 套餐特性 -->
                        @if(!empty($package->features))
                            <div class="mb-3">
                                <ul class="list-unstyled">
                                    @foreach(json_decode($package->features) as $feature)
                                        @if($loop->index < 3)
                                            <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i> {{ $feature }}</li>
                                        @endif
                                    @endforeach
                                    
                                    @if(count(json_decode($package->features)) > 3)
                                        <li class="text-muted small">还有 {{ count(json_decode($package->features)) - 3 }} 项特性...</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                        
                        <!-- 额外选项指示器 -->
                        @if(!empty($package->available_extras))
                            <div class="mb-3">
                                <span class="badge bg-info text-white">
                                    <i class="bi bi-puzzle-fill me-1"></i> 
                                    {{ count(json_decode($package->available_extras)) }} 个可选额外服务
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-footer bg-white border-top-0 pt-0">
                        <div class="d-grid gap-2">
                            <a href="{{ route('packages.show', $package->slug) }}" class="btn btn-outline-primary">查看详情</a>
                            <a href="{{ route('orders.create', $package->slug) }}" class="btn btn-primary">立即购买</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill me-2"></i> 暂无产品，请尝试其他筛选条件
                </div>
            </div>
        @endforelse
    </div>
    
    <!-- 分页 -->
    <div class="d-flex justify-content-center mt-5">
        {{ $packages->withQueryString()->links() }}
    </div>
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
</style>
@endsection