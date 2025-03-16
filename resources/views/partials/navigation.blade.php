
{{-- resources/views/partials/navigation.blade.php --}}
<ul class="navbar-nav me-auto">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
            <i class="bi bi-house-door me-1"></i>首页
        </a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle {{ request()->routeIs('packages.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-boxes me-1"></i>服务套餐
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item {{ request()->routeIs('packages.monthly') ? 'active' : '' }}" href="{{ route('packages.monthly') }}">月度套餐</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('packages.single') ? 'active' : '' }}" href="{{ route('packages.single') }}">单项套餐</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('packages.third-party') ? 'active' : '' }}" href="{{ route('packages.third-party') }}">特色外链</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('packages.guest-post') ? 'active' : '' }}" href="{{ route('packages.guest-post') }}">软文外链</a></li>
        </ul>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="bi bi-info-circle me-1"></i>关于我们
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="bi bi-telephone me-1"></i>联系方式
        </a>
    </li>
</ul>

<ul class="navbar-nav ms-auto">
    @guest
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="">
                <i class="bi bi-box-arrow-in-right me-1"></i>登录
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="">
                <i class="bi bi-person-plus me-1"></i>注册
            </a>
        </li>
    @else
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                @if(Auth::user()->role === 'admin')
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i>管理控制台
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                @endif
                <li>
                    <a class="dropdown-item" href="">
                        <i class="bi bi-grid me-1"></i>用户中心
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="">
                        <i class="bi bi-cart-check me-1"></i>我的订单
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="">
                        <i class="bi bi-wallet2 me-1"></i>钱包 (¥{{ number_format(Auth::user()->balance, 2) }})
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i>退出登录
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>
    @endguest
</ul>