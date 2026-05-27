<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shera Accessories | Premium CRM</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    @yield('styles')
    
    <script>
        window.APP_URL = "{{ url('/') }}";
        window.BASE_PATH = "{{ url('/') }}";
    </script>
</head>

<body>
    @if(Request::is('login') || Request::is('forgot-password') || Request::is('/') || Request::path() == '/')
        @yield('content')
    @else
        <div class="app-container">
            <!-- Sidebar Overlay -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <!-- Sidebar -->
            <aside class="sidebar" id="sidebar">
                <div class="logo">
                    <div style="display: flex; flex-direction: column; line-height: 1;">
                        <img src="{{ asset('assets/images/logo.jpg') }}" alt="Logo" style="width: 100px;">
                    </div>
                    <button class="mobile-close" id="closeSidebar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <ul class="nav-links">
                    @php $role = session('role', 'Admin'); @endphp

                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    @if($role == 'Admin' || $role == 'Operations')
                        <li class="nav-item">
                            <a href="{{ route('dealers') }}" class="nav-link {{ Request::is('dealers*') ? 'active' : '' }}">
                                <i class="fas fa-users"></i>
                                <span>Dealer Registration</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('salesmen') }}" class="nav-link {{ Request::is('salesmen*') ? 'active' : '' }}">
                                <i class="fas fa-user-tie"></i>
                                <span>Sales Registration</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('distributors') }}"
                                class="nav-link {{ Request::is('distributors*') ? 'active' : '' }}">
                                <i class="fas fa-truck"></i>
                                <span>Distributor Registration</span>
                            </a>
                        </li>
                    @endif

                    @if($role == 'Admin' || $role == 'Operations')
                        <li class="nav-item">
                            <a href="{{ route('estimate-requests') }}" class="nav-link {{ Request::is('estimate-requests*') ? 'active' : '' }}">
                                <i class="fas fa-calculator"></i>
                                <span>Get Estimate Request</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('order-requests') }}"
                                class="nav-link {{ Request::is('order-requests*') ? 'active' : '' }}">
                                <i class="fas fa-comment-alt"></i>
                                <span>Order Requests</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a href="{{ route('orders.index') }}" class="nav-link {{ Request::is('orders*') ? 'active' : '' }}">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Orders List</span>
                        </a>
                    </li>

                    @if($role == 'Admin' || $role == 'Operations')
                        <li class="nav-item">
                            <a href="{{ route('delivery') }}" class="nav-link {{ Request::is('delivery*') ? 'active' : '' }}">
                                <i class="fas fa-box-open"></i>
                                <span>Delivery Status</span>
                            </a>
                        </li>
                    @endif

                    @if($role == 'Admin' || $role == 'Account')
                        <li class="nav-item">
                            <a href="{{ route('invoices') }}" class="nav-link {{ Request::is('invoices*') ? 'active' : '' }}">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span>Invoice Management</span>
                            </a>
                        </li>
                    @endif

                    @if($role == 'Admin' || $role == 'Operations')
                        <li class="nav-item">
                            <a href="{{ route('rewards') }}" class="nav-link {{ Request::is('rewards*') ? 'active' : '' }}">
                                <i class="fas fa-gift"></i>
                                <span>Reward Points</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('price-list') }}"
                                class="nav-link {{ Request::is('price-list*') ? 'active' : '' }}">
                                <i class="fas fa-file-pdf"></i>
                                <span>Price List PDF</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('passbook') }}" class="nav-link {{ Request::is('passbook*') ? 'active' : '' }}">
                                <i class="fas fa-book"></i>
                                <span>Dealer Passbook</span>
                            </a>
                        </li>
                    @endif

                    @if($role == 'Admin' || $role == 'Account' || $role == 'Operations')
                        <li class="nav-item">
                            <a href="{{ route('payments.verify') }}" class="nav-link {{ Request::is('payments/verify*') ? 'active' : '' }}">
                                <i class="fas fa-file-invoice"></i>
                                <span>Verify Payments</span>
                            </a>
                        </li>
                    @endif

                    @if($role == 'Admin')
                        <li class="nav-item">
                            <a href="{{ route('users') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                                <i class="fas fa-user-shield"></i>
                                <span>User Management</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('settings') }}" class="nav-link {{ Request::is('settings*') ? 'active' : '' }}">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    @endif
                </ul>


            </aside>

            <div class="main-wrapper">
                <!-- Header -->
                <header class="header">
                    <div class="header-left">
                        <button class="mobile-toggle" id="mobileToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2 id="page-title">@yield('title', 'Dashboard')</h2>
                    </div>
                    <div class="header-right">
                        <div class="user-profile" id="userProfileDropdown">
                            <div class="avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-name">{{ $role }}</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>

                            <!-- Dropdown Menu -->
                            <div class="dropdown-menu" id="userDropdownMenu">
                                <a href="{{ route('login') }}" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Main Content -->
                <main class="main-content">
                    <div class="content-body animate-fade">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    @endif

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mobileToggle = document.getElementById('mobileToggle');
            const closeSidebar = document.getElementById('closeSidebar');

            if (mobileToggle) {
                mobileToggle.addEventListener('click', () => {
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                });
            }

            if (closeSidebar) {
                closeSidebar.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }

            if (overlay) {
                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }

            // User Profile Dropdown Toggle
            const userProfile = document.getElementById('userProfileDropdown');
            const dropdownMenu = document.getElementById('userDropdownMenu');

            if (userProfile && dropdownMenu) {
                userProfile.addEventListener('click', function (e) {
                    e.stopPropagation();
                    userProfile.classList.toggle('active');
                    dropdownMenu.classList.toggle('show');
                });

                document.addEventListener('click', function (e) {
                    if (!userProfile.contains(e.target)) {
                        userProfile.classList.remove('active');
                        dropdownMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>
    @yield('scripts')
    @stack('modals')
</body>

</html>