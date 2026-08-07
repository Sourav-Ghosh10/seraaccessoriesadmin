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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
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
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" style="width: 150px; height: auto;">
                    </div>
                    <button class="mobile-close" id="closeSidebar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <ul class="nav-links">
                    @php 
                        $role = trim(session('role', Auth::check() ? Auth::user()->role : 'Admin'));
                        $isAdmin = ($role === 'Admin');
                        $isAccount = ($role === 'Account');
                        $isOperation = in_array($role, ['Operation', 'Operations']);
                    @endphp

                    @if($isAdmin || $isAccount)
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                                <i class="fas fa-chart-line"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin || $isAccount)
                        <li class="nav-item">
                            <a href="{{ route('dealers') }}" class="nav-link {{ Request::is('dealers*') ? 'active' : '' }}">
                                <i class="fas fa-users"></i>
                                <span>Dealer Registration</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin)
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
                    @endif

                    @if($isAdmin || $isAccount)
                        <li class="nav-item">
                            <a href="{{ route('cities') }}" class="nav-link {{ Request::is('cities*') ? 'active' : '' }}">
                                <i class="fas fa-city"></i>
                                <span>City Management</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin || $isAccount || $isOperation)
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

                        <li class="nav-item">
                            <a href="{{ route('orders.index') }}" class="nav-link {{ Request::is('orders*') ? 'active' : '' }}">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Orders List</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('delivery') }}" class="nav-link {{ Request::is('delivery*') ? 'active' : '' }}">
                                <i class="fas fa-box-open"></i>
                                <span>Delivery Status</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin || $isAccount)
                        <li class="nav-item">
                            <a href="{{ route('invoices') }}" class="nav-link {{ Request::is('invoices*') ? 'active' : '' }}">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span>Invoice Management</span>
                            </a>
                        </li>
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
                    @endif

                    @if($isAdmin || $isAccount || $isOperation)
                        <li class="nav-item">
                            <a href="{{ route('price-list') }}"
                                class="nav-link {{ Request::is('price-list*') ? 'active' : '' }}">
                                <i class="fas fa-file-pdf"></i>
                                <span>Price List PDF</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin || $isAccount)
                        <li class="nav-item">
                            <a href="{{ route('passbook') }}" class="nav-link {{ Request::is('passbook*') ? 'active' : '' }}">
                                <i class="fas fa-book"></i>
                                <span>Passbook</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin)
                        <li class="nav-item">
                            <a href="{{ route('app-popups.index') }}" class="nav-link {{ Request::is('app-popups*') ? 'active' : '' }}">
                                <i class="fas fa-bullhorn"></i>
                                <span>App Popup Management</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin || $isAccount)
                        <li class="nav-item">
                            <a href="{{ route('payments.verify') }}" class="nav-link {{ Request::is('payments/verify*') ? 'active' : '' }}">
                                <i class="fas fa-file-invoice"></i>
                                <span>Verify Payments</span>
                            </a>
                        </li>
                    @endif

                    @if($isAdmin)
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
                        @php
                            $lastReadEstId = \App\Models\Setting::get('last_read_estimate_id', 0);
                            $lastReadOrdId = \App\Models\Setting::get('last_read_order_id', 0);

                            $headerPendingEstimates = \App\Models\Estimate::where('id', '>', $lastReadEstId)->where('status', 'Pending')->count();
                            $headerPendingOrders = \App\Models\OrderRequest::where('id', '>', $lastReadOrdId)->where('status', 'Pending')->count();
                            $headerTotalPending = $headerPendingEstimates + $headerPendingOrders;
                        @endphp
                        <div class="notification-container" style="position: relative; margin-right: 5px;">
                            <div class="notification-bell" id="notifBellBtn" style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative; transition: all 0.3s ease;">
                                <i class="fas fa-bell" style="font-size: 16px; color: var(--text-muted); transition: color 0.3s ease;"></i>
                                <span id="notifTotalBadge" style="position: absolute; top: -4px; right: -4px; background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 5px; border-radius: 10px; display: {{ $headerTotalPending > 0 ? 'inline-block' : 'none' }}; border: 2px solid #0f172a;">
                                    {{ $headerTotalPending }}
                                </span>
                            </div>

                            <!-- Notification Dropdown Menu -->
                            <div class="notification-dropdown-menu" id="notifDropdownMenu" style="display: none; position: absolute; right: 0; top: 48px; width: 330px; background: #0f172a; border: 1px solid var(--glass-border); border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.6); z-index: 10000; overflow: hidden; animation: fadeIn 0.2s ease;">
                                <div style="padding: 12px 20px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02);">
                                    <div>
                                        <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #fff;">Notifications</h4>
                                        <span id="notifSubtitle" style="font-size: 11px; color: var(--text-muted);">{{ $headerTotalPending }} New</span>
                                    </div>
                                    <button onclick="markAllNotificationsAsRead(event)" style="background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border); color: var(--primary); padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                                        <i class="fas fa-check-double"></i> Mark as read
                                    </button>
                                </div>
                                <div style="max-height: 300px; overflow-y: auto; padding: 6px 0;">
                                    <a href="{{ route('estimate-requests') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; text-decoration: none; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.03); transition: background 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='transparent'">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(234, 179, 8, 0.1); display: flex; align-items: center; justify-content: center; color: #eab308; font-size: 16px;">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </div>
                                            <div>
                                                <div style="font-size: 14px; font-weight: 600; color: #fff;">Get Estimate Requests</div>
                                                <div id="notifEstimatesSub" style="font-size: 12px; color: var(--text-muted);">{{ $headerPendingEstimates }} New Request(s)</div>
                                            </div>
                                        </div>
                                        <span id="notifEstimatesBadge" class="badge badge-warning" style="font-size: 12px; font-weight: 700;">{{ $headerPendingEstimates }}</span>
                                    </a>

                                    <a href="{{ route('order-requests') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; text-decoration: none; color: #fff; transition: background 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='transparent'">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #3b82f6; font-size: 16px;">
                                                <i class="fas fa-shopping-bag"></i>
                                            </div>
                                            <div>
                                                <div style="font-size: 14px; font-weight: 600; color: #fff;">Order Requests</div>
                                                <div id="notifOrdersSub" style="font-size: 12px; color: var(--text-muted);">{{ $headerPendingOrders }} New Request(s)</div>
                                            </div>
                                        </div>
                                        <span id="notifOrdersBadge" class="badge badge-primary" style="font-size: 12px; font-weight: 700;">{{ $headerPendingOrders }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
        @if(Session::has('success'))
            toastr.success("{!! Session::get('success') !!}");
        @endif
        @if(Session::has('error'))
            toastr.error("{!! Session::get('error') !!}");
        @endif
        @if(Session::has('info'))
            toastr.info("{!! Session::get('info') !!}");
        @endif
        @if(Session::has('warning'))
            toastr.warning("{!! Session::get('warning') !!}");
        @endif
    </script>
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

            // Notification Dropdown Toggle
            const notifBellBtn = document.getElementById('notifBellBtn');
            const notifDropdownMenu = document.getElementById('notifDropdownMenu');

            if (notifBellBtn && notifDropdownMenu) {
                notifBellBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isShown = notifDropdownMenu.style.display === 'block';
                    if (dropdownMenu) dropdownMenu.classList.remove('show');
                    notifDropdownMenu.style.display = isShown ? 'none' : 'block';
                });

                document.addEventListener('click', function (e) {
                    if (!notifBellBtn.contains(e.target) && !notifDropdownMenu.contains(e.target)) {
                        notifDropdownMenu.style.display = 'none';
                    }
                });
            }
        });

        function markAllNotificationsAsRead(e) {
            if (e) e.stopPropagation();
            fetch(`${window.BASE_PATH}/api/mark-notifications-read`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const badgeEl = document.getElementById('notifTotalBadge');
                        if (badgeEl) {
                            badgeEl.innerText = '0';
                            badgeEl.style.display = 'none';
                        }
                        const subEl = document.getElementById('notifSubtitle');
                        if (subEl) subEl.innerText = '0 New';
                        const estSubEl = document.getElementById('notifEstimatesSub');
                        if (estSubEl) estSubEl.innerText = '0 New Request(s)';
                        const estBadgeEl = document.getElementById('notifEstimatesBadge');
                        if (estBadgeEl) estBadgeEl.innerText = '0';
                        const ordSubEl = document.getElementById('notifOrdersSub');
                        if (ordSubEl) ordSubEl.innerText = '0 New Request(s)';
                        const ordBadgeEl = document.getElementById('notifOrdersBadge');
                        if (ordBadgeEl) ordBadgeEl.innerText = '0';
                    }
                })
                .catch(err => console.error('Error marking notifications read:', err));
        }

        $(document).ready(function() {
            // Override native alert to use Toastr
            window.alert = function(message) {
                if (typeof message === 'string') {
                    const msgLower = message.toLowerCase();
                    if (msgLower.includes('error') || msgLower.includes('fail') || msgLower.includes('wrong')) {
                        toastr.error(message);
                    } else if (msgLower.includes('success')) {
                        toastr.success(message);
                    } else {
                        toastr.warning(message);
                    }
                } else {
                    toastr.info(message);
                }
            };

            $(document).on('submit', '.swal-confirm-form', function(e) {
                e.preventDefault();
                const form = this;
                const msg = $(form).data('confirm-text') || 'Are you sure you want to proceed?';
                
                Swal.fire({
                    title: 'Confirmation Required',
                    text: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#9a5a3a', // Primary color
                    cancelButtonColor: 'rgba(255,255,255,0.1)',
                    confirmButtonText: 'Yes, proceed',
                    background: '#0f172a', // Dark theme background
                    color: '#fff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>

    <!-- Global AJAX Loader Script -->
    <script>
        (function() {
            var loaderCount = 0;

            function showLoader() {
                loaderCount++;
                var loaderEl = document.getElementById('globalLoader');
                if (loaderEl) loaderEl.style.display = 'flex';
            }

            function hideLoader() {
                loaderCount = Math.max(0, loaderCount - 1);
                var loaderEl = document.getElementById('globalLoader');
                if (loaderCount === 0 && loaderEl) {
                    loaderEl.style.display = 'none';
                }
            }

            window.showLoader = showLoader;
            window.hideLoader = hideLoader;

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

    @yield('scripts')
    @stack('modals')

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
                    
                    if (data.pending_estimates_count !== undefined && data.pending_orders_count !== undefined) {
                        const totalPending = (parseInt(data.pending_estimates_count) || 0) + (parseInt(data.pending_orders_count) || 0);
                        const badgeEl = document.getElementById('notifTotalBadge');
                        if (badgeEl) {
                            badgeEl.innerText = totalPending;
                            badgeEl.style.display = totalPending > 0 ? 'inline-block' : 'none';
                        }
                        const subEl = document.getElementById('notifSubtitle');
                        if (subEl) subEl.innerText = totalPending + ' New';
                        const estSubEl = document.getElementById('notifEstimatesSub');
                        if (estSubEl) estSubEl.innerText = data.pending_estimates_count + ' New Request(s)';
                        const estBadgeEl = document.getElementById('notifEstimatesBadge');
                        if (estBadgeEl) estBadgeEl.innerText = data.pending_estimates_count;
                        const ordSubEl = document.getElementById('notifOrdersSub');
                        if (ordSubEl) ordSubEl.innerText = data.pending_orders_count + ' New Request(s)';
                        const ordBadgeEl = document.getElementById('notifOrdersBadge');
                        if (ordBadgeEl) ordBadgeEl.innerText = data.pending_orders_count;
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