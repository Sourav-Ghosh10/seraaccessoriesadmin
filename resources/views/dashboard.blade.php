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
            <a href="{{ route('dealers') }}" class="card" style="text-decoration: none; color: inherit;">
                <div class="widget-icon" style="background: rgba(154, 90, 58, 0.1); color: var(--primary);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="widget-value">{{ number_format($totalDealers) }}</div>
                <div class="widget-label">Total Dealers</div>
            </a>
            <a href="{{ route('salesmen') }}" class="card" style="text-decoration: none; color: inherit;">
                <div class="widget-icon" style="background: rgba(74, 74, 74, 0.1); color: var(--secondary);">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="widget-value">{{ number_format($totalSalesmen) }}</div>
                <div class="widget-label">Total Salesmen</div>
            </a>
        @endif

        <a href="{{ route('orders.index') }}" class="card" style="text-decoration: none; color: inherit;">
            <div class="widget-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="widget-value">{{ number_format($totalOrders) }}</div>
            <div class="widget-label">Total Orders</div>
        </a>
        <a href="{{ route('order-requests') }}" class="card" style="text-decoration: none; color: inherit;">
            <div class="widget-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="widget-value">{{ number_format($pendingOrders) }}</div>
            <div class="widget-label">Pending Orders</div>
        </a>
    </div>

    @if($role == 'Admin')
    <div class="grid grid-sales" style="margin-top: 30px;">
        <div class="card">
            <style>
                #customMonthPicker::-webkit-calendar-picker-indicator {
                    filter: invert(1);
                    cursor: pointer;
                }
            </style>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin-bottom: 0;">Order Graph</h3>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="month" id="customMonthPicker" onclick="this.showPicker()" class="form-control" style="display: none; width: auto; background: var(--bg-dark); color: var(--text-light); border: 1px solid var(--border-color); border-radius: 4px; padding: 4px 10px; font-size: 14px; cursor: pointer;">
                    <select id="chartFilter" class="form-control" style="width: auto; background: var(--bg-dark); color: var(--text-light); border: 1px solid var(--border-color); border-radius: 4px; padding: 5px 10px;">
                        <option value="6_months" style="color: #000; background: #fff;">Last 6 Months</option>
                        <option value="yearly" style="color: #000; background: #fff;">This Year</option>
                        <option value="monthly" style="color: #000; background: #fff;">This Month</option>
                        <option value="custom" style="color: #000; background: #fff;">Custom Month</option>
                    </select>
                </div>
            </div>
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
        let salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'Orders',
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

        const chartFilter = document.getElementById('chartFilter');
        const customMonthPicker = document.getElementById('customMonthPicker');

        function fetchChartData(filter, customMonth = null) {
            let url = `{{ route('dashboard.chart') }}?filter=${filter}`;
            if (customMonth) {
                url += `&month=${customMonth}`;
            }
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    salesChart.data.labels = data.labels;
                    salesChart.data.datasets[0].data = data.data;
                    salesChart.update();
                })
                .catch(error => console.error('Error fetching chart data:', error));
        }

        if (chartFilter) {
            chartFilter.addEventListener('change', function() {
                const filter = this.value;
                if (filter === 'custom') {
                    customMonthPicker.style.display = 'block';
                    if (customMonthPicker.value) {
                        fetchChartData(filter, customMonthPicker.value);
                    } else {
                        const now = new Date();
                        const currentMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
                        customMonthPicker.value = currentMonth;
                        fetchChartData(filter, currentMonth);
                    }
                } else {
                    customMonthPicker.style.display = 'none';
                    fetchChartData(filter);
                }
            });
        }

        if (customMonthPicker) {
            customMonthPicker.addEventListener('change', function() {
                if (chartFilter.value === 'custom') {
                    fetchChartData('custom', this.value);
                }
            });
        }
    }
</script>
@endsection
