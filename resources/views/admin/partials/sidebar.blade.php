// resources/views/admin/partials/sidebar.blade.php
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
        <div class="sidebar-brand-icon">
            <i class="fas fa-cogs"></i>
        </div>
        <div class="sidebar-brand-text mx-3">{{ config('app.name', 'PuGOS') }} <sup>Admin</sup></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>{{ __('Dashboard') }}</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        {{ __('Orders') }}
    </div>

    <!-- Nav Item - Orders -->
    <li class="nav-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.orders') }}">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>{{ __('Orders') }}</span>
        </a>
    </li>

    <!-- Nav Item - Third Party Orders -->
    <li class="nav-item {{ request()->routeIs('admin.third-party-orders*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.third-party-orders') }}">
            <i class="fas fa-fw fa-exchange-alt"></i>
            <span>{{ __('Third Party Orders') }}</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        {{ __('Products') }}
    </div>

    <!-- Nav Item - Package Categories Collapse Menu -->
    <li class="nav-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.categories.index') }}">
            <i class="fas fa-fw fa-folder"></i>
            <span>{{ __('Package Categories') }}</span>
        </a>
    </li>

    <!-- Nav Item - Packages -->
    <li class="nav-item {{ request()->routeIs('admin.packages*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.packages.index') }}">
            <i class="fas fa-fw fa-box"></i>
            <span>{{ __('Packages') }}</span>
        </a>
    </li>

    <!-- Nav Item - Guest Posts -->
    <li class="nav-item {{ request()->routeIs('admin.guest-posts*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.guest-posts') }}">
            <i class="fas fa-fw fa-newspaper"></i>
            <span>{{ __('Guest Posts') }}</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        {{ __('Finance') }}
    </div>

    <!-- Nav Item - Users -->
    <li class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.users.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>{{ __('Users') }}</span>
        </a>
    </li>

    <!-- Nav Item - Invoices -->
    <li class="nav-item {{ request()->routeIs('admin.invoices*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.invoices.index') }}">
            <i class="fas fa-fw fa-file-invoice"></i>
            <span>{{ __('Invoices') }}</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        {{ __('API & Integrations') }}
    </div>

    <!-- Nav Item - Third Party API -->
    <li class="nav-item {{ request()->routeIs('admin.third-party*') && !request()->routeIs('admin.third-party-orders*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseThirdParty" aria-expanded="false" aria-controls="collapseThirdParty">
            <i class="fas fa-fw fa-network-wired"></i>
            <span>{{ __('Third Party API') }}</span>
        </a>
        <div id="collapseThirdParty" class="collapse {{ request()->routeIs('admin.third-party*') && !request()->routeIs('admin.third-party-orders*') ? 'show' : '' }}" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">{{ __('SEOeStore API:') }}</h6>
                <a class="collapse-item {{ request()->routeIs('admin.third-party') ? 'active' : '' }}" href="{{ route('admin.third-party') }}">{{ __('Services') }}</a>
                <a class="collapse-item {{ request()->routeIs('admin.third-party.settings') ? 'active' : '' }}" href="{{ route('admin.third-party.settings') }}">{{ __('Settings') }}</a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>