@extends('layouts.app')

@section('title', 'All Transactions')

@section('content')
<div class="card">
    <div style="display: flex; gap: 15px; align-items: center; justify-content: space-between; margin-bottom: 25px;">
        <div style="display: flex; gap: 15px; align-items: center; flex: 1; flex-wrap: wrap;">
            <div class="search-bar glass" style="max-width: 300px; width: 100%; border: 1px solid var(--glass-border);">
                <i class="fas fa-search" style="color: var(--text-muted);"></i>
                <input type="text" id="txnSearch" placeholder="Search reference or dealer..." onkeyup="filterTransactions()">
            </div>
            <select id="userFilter" onchange="filterTransactions()" class="glass" style="padding: 10px 20px; border-radius: 20px; color: var(--light); border: 1px solid var(--glass-border); outline: none; background: var(--dark); cursor: pointer;">
                <option value="all">All Users (Managed By)</option>
                @foreach($salesmen as $salesman)
                    <option value="{{ $salesman->name }}">{{ $salesman->name }} (Salesman)</option>
                @endforeach
                @foreach($admins as $admin)
                    <option value="{{ $admin->name }}">{{ $admin->name }} (Admin)</option>
                @endforeach
                <option value="System Admin">System Admin</option>
            </select>
            <select id="typeFilter" onchange="filterTransactions()" class="glass" style="padding: 10px 20px; border-radius: 20px; color: var(--light); border: 1px solid var(--glass-border); outline: none; background: var(--dark); cursor: pointer;">
                <option value="all">All Types</option>
                <option value="Payment">Payment</option>
                <option value="Order">Order</option>
                <option value="Adjustment">Adjustment</option>
            </select>
        </div>
        <button class="btn glass" onclick="resetFilters()" style="padding: 10px 20px; font-size: 14px;">
            <i class="fas fa-sync-alt"></i> Reset Filters
        </button>
    </div>

    <!-- Transaction Table -->
    <div class="table-container">
        <table id="transactionTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Dealer Name</th>
                    <th>Managed By</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="transactionBody">
                <!-- Data populated by JS -->
            </tbody>
        </table>
    </div>
</div>

@section('scripts')
<script>
    // Live database serialized transactions
    const transactions = @json($transactions);

    function renderTransactions(data = transactions) {
        const body = document.getElementById('transactionBody');
        body.innerHTML = '';

        if (data.length === 0) {
            body.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 60px; color: var(--text-muted);"><i class="fas fa-info-circle" style="display: block; font-size: 24px; margin-bottom: 10px;"></i> No transactions found matching your criteria.</td></tr>';
            return;
        }

        data.forEach(t => {
            let typeBadge = '';
            if (t.type === 'Payment') {
                typeBadge = '<span class="badge badge-success">Payment</span>';
            } else if (t.type === 'Order') {
                typeBadge = '<span class="badge badge-warning" style="background: rgba(154, 90, 58, 0.15); color: var(--primary); border-color: rgba(154, 90, 58, 0.2);">Order</span>';
            } else {
                typeBadge = '<span class="badge glass" style="font-size: 10px; color: var(--text-muted);">Adjustment</span>';
            }

            body.innerHTML += `
                <tr class="animate-fade">
                    <td style="color: var(--text-muted); font-size: 13px;">${t.date}</td>
                    <td style="font-weight: 600;">${t.dealer}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: ${t.user.includes('Admin') || t.user.includes('Sourav') || t.user.includes('Amit') ? 'rgba(154, 90, 58, 0.1)' : 'rgba(255,255,255,0.05)'}; display: flex; align-items: center; justify-content: center; font-size: 12px; border: 1px solid var(--glass-border);">
                                <i class="fas ${t.user.includes('Admin') || t.user.includes('Sourav') || t.user.includes('Amit') ? 'fa-user-shield' : 'fa-user'}" style="color: ${t.user.includes('Admin') || t.user.includes('Sourav') || t.user.includes('Amit') ? 'var(--primary)' : 'var(--text-muted)'};"></i>
                            </div>
                            <span style="font-size: 14px;">${t.user}</span>
                        </div>
                    </td>
                    <td>${typeBadge}</td>
                    <td style="font-weight: 700; color: ${t.amount < 0 ? 'var(--accent)' : 'inherit'}">₹ ${Math.abs(t.amount).toLocaleString()}</td>
                    <td style="font-family: 'JetBrains Mono', monospace; color: var(--text-muted); font-size: 12px;">${t.ref}</td>
                    <td><span class="badge badge-success" style="font-size: 10px; padding: 4px 10px;">${t.status}</span></td>
                </tr>
            `;
        });
    }

    function filterTransactions() {
        const searchTerm = document.getElementById('txnSearch').value.toLowerCase();
        const userFilter = document.getElementById('userFilter').value;
        const typeFilter = document.getElementById('typeFilter').value;

        const filtered = transactions.filter(t => {
            const matchesSearch = t.dealer.toLowerCase().includes(searchTerm) || t.ref.toLowerCase().includes(searchTerm);
            const matchesUser = userFilter === 'all' || t.user === userFilter;
            const matchesType = typeFilter === 'all' || t.type === typeFilter;
            return matchesSearch && matchesUser && matchesType;
        });

        renderTransactions(filtered);
    }

    function resetFilters() {
        document.getElementById('txnSearch').value = '';
        document.getElementById('userFilter').value = 'all';
        document.getElementById('typeFilter').value = 'all';
        renderTransactions();
    }

    // Initial render
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const dealerParam = urlParams.get('dealer');
        if (dealerParam) {
            document.getElementById('txnSearch').value = dealerParam;
            filterTransactions();
        } else {
            renderTransactions();
        }
    });
</script>
@endsection
@endsection
