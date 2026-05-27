@extends('layouts.app')

@php
    if (!function_exists('getInitials')) {
        function getInitials($name) {
            $words = explode(' ', trim($name));
            $initials = '';
            foreach ($words as $w) {
                if (!empty($w)) {
                    $initials .= mb_substr($w, 0, 1);
                }
            }
            return strtoupper(mb_substr($initials, 0, 2));
        }
    }
@endphp

@section('content')
<div class="dashboard-wrapper">
    <div class="grid">
        @php $role = session('role', 'Admin'); @endphp
        
        @if($role == 'Admin' || $role == 'Operations')
            <div class="card">
                <div class="widget-icon" style="background: rgba(154, 90, 58, 0.1); color: var(--primary);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="widget-value">{{ number_format($totalDealers) }}</div>
                <div class="widget-label">Total Dealers</div>
            </div>
            <div class="card">
                <div class="widget-icon" style="background: rgba(74, 74, 74, 0.1); color: var(--secondary);">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="widget-value">{{ number_format($totalSalesmen) }}</div>
                <div class="widget-label">Total Salesmen</div>
            </div>
        @endif

        <div class="card">
            <div class="widget-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="widget-value">{{ number_format($totalOrders) }}</div>
            <div class="widget-label">Total Orders</div>
        </div>
        <div class="card">
            <div class="widget-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="widget-value">{{ number_format($pendingOrders) }}</div>
            <div class="widget-label">Pending Orders</div>
        </div>
    </div>

    @if($role == 'Admin')
    <div class="grid grid-sales" style="margin-top: 30px;">
        <div class="card">
            <h3 style="margin-bottom: 20px;">Monthly Sales Graph</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 20px;">Recent Order Status</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 14px;">Delivered Orders</span>
                    <span class="badge badge-success">{{ $deliveredOrders }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 14px;">Invoice Pending</span>
                    <span class="badge badge-warning">{{ $invoicePending }}</span>
                </div>
            </div>

            <h3 style="margin-top: 30px; margin-bottom: 20px;">Top Performance</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="glass" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">{{ getInitials($topDealerName) }}</div>
                    <div>
                        <div style="font-size: 14px; font-weight: 600;">{{ $topDealerName }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Top Dealer</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="glass" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">{{ getInitials($topSalesmanName) }}</div>
                    <div>
                        <div style="font-size: 14px; font-weight: 600;">{{ $topSalesmanName }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Top Salesman</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card" style="margin-top: 30px; text-align: center; padding: 100px;">
        <i class="fas fa-lock" style="font-size: 40px; color: var(--text-muted); margin-bottom: 20px;"></i>
        <h3>Limited Access</h3>
        <p style="color: var(--text-muted);">Detailed analytics and graphs are restricted for your role.</p>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const salesChartEl = document.getElementById('salesChart');
    if (salesChartEl) {
        const ctx = salesChartEl.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'Sales (₹)',
                    data: {!! json_encode($salesData) !!},
                    borderColor: '#9a5a3a',
                    backgroundColor: 'rgba(154, 90, 58, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#9a5a3a',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8' }
                    }
                }
            }
        });
    }
</script>
@endsection
