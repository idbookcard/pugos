{{-- 套餐卡片组件 --}}
<div class="package-card {{ $package->is_featured ? 'featured' : '' }}">
    @if($package->is_featured)
        <div class="featured-badge">热门</div>
    @endif
    
    <div class="package-header">
        <h3 class="package-title">{{ $package->name }}</h3>
        <div class="package-price">
            <span class="current-price">¥{{ number_format($package->price, 2) }}</span>
            @if($package->original_price)
                <span class="original-price">¥{{ number_format($package->original_price, 2) }}</span>
            @endif
        </div>
    </div>
    
    <div class="package-body">
        <div class="package-description">
            {{ $package->description }}
        </div>
        
        @if($package->features)
            <div class="package-features">
                <ul>
                    @foreach(json_decode($package->features) as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @if($package->package_type == 'guest_post' && $package->guest_post_da)
            <div class="package-da">
                <span class="da-badge">DA {{ $package->guest_post_da }}+</span>
            </div>
        @endif
        
        <div class="package-delivery">
            <span class="delivery-days">预计交付时间: {{ $package->delivery_days }} 天</span>
        </div>
    </div>
    
    <div class="package-footer">
        <a href="{{ route('packages.show', $package->slug) }}" class="view-details-btn">查看详情</a>
        <a href="#" class="order-now-btn">立即订购</a>
    </div>
</div>