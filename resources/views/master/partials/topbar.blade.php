<!-- resources/views/master/partials/topbar.blade.php -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow-sm">
    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3">
        <i class="bi bi-list"></i>
    </button>

    <!-- Page Title -->
    <h1 class="h5 mb-0 text-gray-800 d-none d-md-block">@yield('page-title', 'Dashboard')</h1>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ms-auto">
        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                <!-- Counter - Alerts -->
                <span class="badge rounded-pill bg-danger">3+</span>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Alerts Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="me-3">
                        <div class="rounded-circle bg-primary p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-bell-fill text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 12, 2023</div>
                        <span>A new order has been placed</span>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="me-3">
                        <div class="rounded-circle bg-success p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-check-circle text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 7, 2023</div>
                        Invoice #4589 has been paid
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="me-3">
                        <div class="rounded-circle bg-warning p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-exclamation-triangle text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 2, 2023</div>
                        API rate limit warning
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
            </div>
        </li>

        <!-- Nav Item - Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-envelope"></i>
                <!-- Counter - Messages -->
                <span class="badge rounded-pill bg-danger">7</span>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                    Message Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image me-3">
                        <img class="rounded-circle" src="https://ui-avatars.com/api/?name=John+Doe&background=4e73df&color=fff" alt="User Avatar">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div>
                        <div class="text-truncate">New customer inquiry about backlink packages.</div>
                        <div class="small text-gray-500">John Doe · 58m</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image me-3">
                        <img class="rounded-circle" src="https://ui-avatars.com/api/?name=Jane+Smith&background=1cc88a&color=fff" alt="User Avatar">
                        <div class="status-indicator"></div>
                    </div>
                    <div>
                        <div class="text-truncate">I need a refund for my last order.</div>
                        <div class="small text-gray-500">Jane Smith · 1d</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image me-3">
                        <img class="rounded-circle" src="https://ui-avatars.com/api/?name=Mike+Wilson&background=36b9cc&color=fff" alt="User Avatar">
                        <div class="status-indicator bg-warning"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Last month's report looks great!</div>
                        <div class="small text-gray-500">Mike Wilson · 2d</div>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="me-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}</span>
                <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4e73df&color=fff" width="32" height="32">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                    <i class="bi bi-person me-2 text-gray-400"></i>
                    Profile
                </a>
                <a class="dropdown-item" href="#">
                    <i class="bi bi-gear me-2 text-gray-400"></i>
                    Settings
                </a>
                <a class="dropdown-item" href="{{ route('home') }}" target="_blank">
                    <i class="bi bi-house me-2 text-gray-400"></i>
                    View Site
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-box-arrow-right me-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>