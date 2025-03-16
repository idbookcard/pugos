<!-- resources/views/master/partials/sidebar.blade.php -->
<div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; min-height: 100vh;">
    <a href="{{ route('master.dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">{{ config('app.name', 'PuGOS') }}</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('master.dashboard') }}" class="nav-link text-white {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>
                {{ __('Dashboard') }}
            </a>
        </li>
        
        <!-- Orders Section -->
        <li>
            <a href="#orders-collapse" class="nav-link text-white" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('master.orders*') ? 'true' : 'false' }}" aria-controls="orders-collapse">
                <i class="bi bi-cart me-2"></i>
                {{ __('Orders') }}
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ request()->routeIs('master.orders*') ? 'show' : '' }}" id="orders-collapse">
                <ul class="nav flex-column ms-3 mt-2">
                    <li>
                        <a href="{{ route('master.orders.index') }}" class="nav-link text-white py-2 {{ request()->routeIs('master.orders.index') && !request()->has('status') ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-list me-2"></i>{{ __('All Orders') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.orders.index', ['status' => 'pending']) }}" class="nav-link text-white py-2 {{ request()->routeIs('master.orders.index') && request()->get('status') == 'pending' ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-hourglass me-2"></i>{{ __('Pending Orders') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.orders.index', ['status' => 'processing']) }}" class="nav-link text-white py-2 {{ request()->routeIs('master.orders.index') && request()->get('status') == 'processing' ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-gear me-2"></i>{{ __('Processing Orders') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.orders.index', ['status' => 'completed']) }}" class="nav-link text-white py-2 {{ request()->routeIs('master.orders.index') && request()->get('status') == 'completed' ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-check-circle me-2"></i>{{ __('Completed Orders') }}
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <!-- Packages Section -->
        <li>
            <a href="#packages-collapse" class="nav-link text-white" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('master.packages*') || request()->routeIs('master.extras*') ? 'true' : 'false' }}" aria-controls="packages-collapse">
                <i class="bi bi-box me-2"></i>
                {{ __('Packages') }}
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ request()->routeIs('master.packages*') || request()->routeIs('master.extras*') ? 'show' : '' }}" id="packages-collapse">
                <ul class="nav flex-column ms-3 mt-2">
                    <li>
                        <a href="{{ route('master.packages.index') }}" class="nav-link text-white py-2 {{ request()->routeIs('master.packages.index') ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-list me-2"></i>{{ __('All Packages') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.packages.create') }}" class="nav-link text-white py-2 {{ request()->routeIs('master.packages.create') ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('Add Package') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.extras.index') }}" class="nav-link text-white py-2 {{ request()->routeIs('master.extras.*') ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-puzzle me-2"></i>{{ __('Extra Options') }}
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        
        <!-- Users Section -->
        <li>
            <a href="{{ route('master.users.index') }}" class="nav-link text-white {{ request()->routeIs('master.users*') ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i>
                {{ __('Users') }}
            </a>
        </li>
        
        <!-- Invoices Section -->
        <li>
            <a href="{{ route('master.invoices.index') }}" class="nav-link text-white {{ request()->routeIs('master.invoices*') ? 'active' : '' }}">
                <i class="bi bi-receipt me-2"></i>
                {{ __('Invoices') }}
            </a>
        </li>
        
        <!-- API Settings -->
        <li>
            <a href="{{ route('master.api-settings.index') }}" class="nav-link text-white {{ request()->routeIs('master.api-settings*') ? 'active' : '' }}">
                <i class="bi bi-gear me-2"></i>
                {{ __('API Settings') }}
            </a>
        </li>
        
        <!-- System Settings -->
        <li>
            <a href="#system-collapse" class="nav-link text-white" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('master.system*') ? 'true' : 'false' }}" aria-controls="system-collapse">
                <i class="bi bi-sliders me-2"></i>
                {{ __('System') }}
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ request()->routeIs('master.system*') ? 'show' : '' }}" id="system-collapse">
                <ul class="nav flex-column ms-3 mt-2">
                    <li>
                        <a href="{{ route('master.system.settings') }}" class="nav-link text-white py-2 {{ request()->routeIs('master.system.settings') ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-gear-fill me-2"></i>{{ __('Settings') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.system.logs') }}" class="nav-link text-white py-2 {{ request()->routeIs('master.system.logs') ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-journal-text me-2"></i>{{ __('System Logs') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master.system.api-logs') }}" class="nav-link text-white py-2 {{ request()->routeIs('master.system.api-logs') ? 'bg-secondary bg-opacity-25 rounded' : '' }}">
                            <i class="bi bi-activity me-2"></i>{{ __('API Logs') }}
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=6f42c1&color=fff" alt="{{ auth()->user()->name }}" width="32" height="32" class="rounded-circle me-2">
            <strong>{{ auth()->user()->name }}</strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="{{ route('master.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>{{ __('Dashboard') }}</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>{{ __('Profile') }}</a></li>
            <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank"><i class="bi bi-house me-2"></i>{{ __('View Site') }}</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right me-2"></i>{{ __('Sign out') }}</a></li>
        </ul>
    </div>
</div>