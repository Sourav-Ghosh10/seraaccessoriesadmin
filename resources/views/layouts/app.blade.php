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
    <!-- Global AJAX Loader -->
    <div id="globalLoader" style="
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(2, 6, 23, 0.55);
        backdrop-filter: blur(3px);
        align-items: center;
        justify-content: center;
        pointer-events: all;
    ">
        <div style="
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        ">
            <div style="
                width: 48px;
                height: 48px;
                border: 3px solid rgba(255,255,255,0.1);
                border-top-color: var(--primary, #9a5a3a);
                border-radius: 50%;
                animation: loaderSpin 0.75s linear infinite;
            "></div>
            <span style="color: rgba(255,255,255,0.6); font-size: 13px; letter-spacing: 0.5px;">Loading...</span>
        </div>
    </div>
    <style>
        @keyframes loaderSpin {
            to { transform: rotate(360deg); }
        }
    </style>

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
                            <a href="{{ route('salesmen') }}" class="nav-link {{ Request::is('salesmen*') && !Request::is('salesman-attendance*') ? 'active' : '' }}">
                                <i class="fas fa-user-tie"></i>
                                <span>Sales Registration</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('expenses.index') }}" class="nav-link {{ Request::is('expenses*') ? 'active' : '' }}">
                                <i class="fas fa-receipt"></i>
                                <span>Salesman Expenses</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('salesman.attendance') }}" class="nav-link {{ Request::is('salesman-attendance*') ? 'active' : '' }}">
                                <i class="fas fa-calendar-check"></i>
                                <span>Salesman Attendance</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('distributors') }}"
                                class="nav-link {{ Request::is('distributors*') ? 'active' : '' }}">
                                <i class="fas fa-truck"></i>
                                <span>Distributor Registration</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('cities') }}" class="nav-link {{ Request::is('cities*') ? 'active' : '' }}">
                                <i class="fas fa-city"></i>
                                <span>City Management</span>
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
                            <a href="{{ route('redeem-requests') }}" class="nav-link {{ Request::is('redeem-requests*') ? 'active' : '' }}">
                                <i class="fas fa-coins"></i>
                                <span>Redeem Requests</span>
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

    <!-- Global AJAX Loader Script -->
    <script>
        (function() {
            var loaderCount = 0;

            function showLoader() {
                loaderCount++;
                document.getElementById('globalLoader').style.display = 'flex';
            }

            function hideLoader() {
                loaderCount = Math.max(0, loaderCount - 1);
                if (loaderCount === 0) {
                    document.getElementById('globalLoader').style.display = 'none';
                }
            }

            // jQuery global AJAX hooks (covers $.ajax, $.get, $.post)
            if (typeof $ !== 'undefined') {
                $(document).ajaxStart(function() { showLoader(); });
                $(document).ajaxStop(function()  { hideLoader(); });
            } else {
                // Wait for jQuery if loaded later
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof $ !== 'undefined') {
                        $(document).ajaxStart(function() { showLoader(); });
                        $(document).ajaxStop(function()  { hideLoader(); });
                    }
                });
            }

            // Native fetch interceptor (covers all fetch() calls)
            var _origFetch = window.fetch;
            window.fetch = function() {
                // Don't show loader for the background polling request
                var url = arguments[0];
                var isPolling = typeof url === 'string' && url.indexOf('check-new-requests') !== -1;
                if (!isPolling) showLoader();
                return _origFetch.apply(this, arguments).finally(function() {
                    if (!isPolling) hideLoader();
                });
            };
        })();
    </script>

    @auth
    @php
        $initialEstimateId = \App\Models\Estimate::max('id') ?? 0;
        $initialOrderId = \App\Models\OrderRequest::max('id') ?? 0;
    @endphp
    <!-- New Data Notification Popup -->
    <div id="newDataPopup">
        <div class="popup-icon">
            <i class="fas fa-bell"></i>
        </div>
        <div class="popup-content">
            <p id="newDataMessage" class="popup-message"></p>
            <div class="popup-actions">
                <button class="btn glass" onclick="closeNewDataPopup()" style="padding: 6px 12px; font-size: 13px;">Cancel</button>
                <button id="newDataRefreshBtn" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">Refresh</button>
            </div>
        </div>
    </div>
    
    <script>
        let lastEstimateId = {{ $initialEstimateId }};
        let lastOrderId = {{ $initialOrderId }};
        
        setInterval(function() {
            fetch(`${window.BASE_PATH}/api/check-new-requests?last_estimate_id=${lastEstimateId}&last_order_id=${lastOrderId}`)
                .then(r => r.json())
                .then(data => {
                    let showPopup = false;
                    let message = "";
                    let redirectUrl = "";
                    
                    if (data.new_estimates > 0) {
                        message = `You have ${data.new_estimates} new Estimate Request(s)!`;
                        redirectUrl = "{{ route('estimate-requests') }}";
                        showPopup = true;
                        lastEstimateId = data.max_estimate_id;
                    } else if (data.new_orders > 0) {
                        message = `You have ${data.new_orders} new Order Request(s)!`;
                        redirectUrl = "{{ route('order-requests') }}";
                        showPopup = true;
                        lastOrderId = data.max_order_id;
                    }
                    
                    if (showPopup) {
                        document.getElementById('newDataMessage').innerText = message;
                        document.getElementById('newDataRefreshBtn').onclick = function() {
                            window.location.href = redirectUrl;
                        };
                        document.getElementById('newDataPopup').style.display = 'flex';
                    }
                })
                .catch(err => console.error('Error polling for new requests:', err));
        }, 15000);

        function closeNewDataPopup() {
            document.getElementById('newDataPopup').style.display = 'none';
        }
    </script>
    @endauth
</body>

</html>