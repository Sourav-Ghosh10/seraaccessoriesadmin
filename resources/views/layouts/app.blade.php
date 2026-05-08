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
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>

<body>
    @if(Request::is('login') || Request::is('forgot-password') || Request::is('/') || Request::path() == '/')
        @yield('content')
    @else
        <div class="app-container">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="logo">
                    <div style="display: flex; flex-direction: column; line-height: 1;">
                        <img src="{{ asset('assets/images/logo.jpg') }}" alt="Logo" style="width: 100px;">
                    </div>
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

                    <li class="nav-item">
                        <a href="{{ route('order-requests') }}" class="nav-link {{ Request::is('order-requests*') ? 'active' : '' }}">
                            <i class="fas fa-comment-alt"></i>
                            <span>Order Requests</span>
                        </a>
                    </li>

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

                    <li class="nav-item">
                        <a href="{{ route('rewards') }}" class="nav-link {{ Request::is('rewards*') ? 'active' : '' }}">
                            <i class="fas fa-gift"></i>
                            <span>Reward Points</span>
                        </a>
                    </li>

                    @if($role == 'Admin' || $role == 'Operations')
                        <li class="nav-item">
                            <a href="{{ route('price-list') }}"
                                class="nav-link {{ Request::is('price-list*') ? 'active' : '' }}">
                                <i class="fas fa-file-pdf"></i>
                                <span>Price List PDF</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a href="{{ route('passbook') }}" class="nav-link {{ Request::is('passbook*') ? 'active' : '' }}">
                            <i class="fas fa-book"></i>
                            <span>Dealer Passbook</span>
                        </a>
                    </li>

                    @if($role == 'Admin')
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fas fa-user-shield"></i>
                                <span>User Management</span>
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="sidebar-footer"
                    style="margin-top: 20px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
                    <a href="{{ route('login') }}" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Header -->
                <header class="header">
                    <div class="header-left">
                        <h2 id="page-title">Dashboard</h2>
                    </div>
                    <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
                        <div class="search-bar glass"
                            style="padding: 8px 15px; border-radius: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-search" style="color: var(--text-muted);"></i>
                            <input type="text" placeholder="Search..."
                                style="background: transparent; border: none; color: white; outline: none; font-size: 14px;">
                        </div>
                        <div class="role-switcher glass" style="padding: 5px 10px; border-radius: 10px;">
                            <form action="{{ route('dashboard') }}" method="GET" id="roleForm">
                                <select name="role" onchange="this.form.submit()"
                                    style="background: transparent; border: none; color: white; outline: none; font-size: 12px; cursor: pointer;">
                                    <option value="Admin" {{ $role == 'Admin' ? 'selected' : '' }}>Role: Admin</option>
                                    <option value="Operations" {{ $role == 'Operations' ? 'selected' : '' }}>Role: Operations
                                    </option>
                                    <option value="Account" {{ $role == 'Account' ? 'selected' : '' }}>Role: Account</option>
                                </select>
                            </form>
                        </div>
                        <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar glass"
                                style="width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user"></i>
                            </div>
                            <span style="font-size: 14px; font-weight: 600;">{{ $role }}</span>
                        </div>
                    </div>
                </header>

                <div class="content-body animate-fade">
                    @yield('content')
                </div>
            </main>
        </div>
    @endif

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @yield('scripts')
</body>

</html>