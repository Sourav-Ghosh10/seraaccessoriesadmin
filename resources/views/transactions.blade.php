@extends('layouts.app')

@section('title', 'Financial Ledger')

@section('styles')
<style>
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .txn-type-order { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
    .txn-type-payment { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
    .txn-type-credit { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border: 1px solid rgba(14, 165, 233, 0.2); }
    .txn-type-adjustment { background: rgba(255, 255, 255, 0.05); color: var(--text-muted); border: 1px solid rgba(255, 255, 255, 0.1); }
</style>
@endsection

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px;">
        <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--primary);">Financial Transaction Ledger</h3>
        <button class="btn glass" onclick="resetFilters()" style="font-size: 12px; padding: 6px 15px;">
            <i class="fas fa-sync-alt"></i> Reset Filters
        </button>
    </div>

    <!-- Global Search & Filter -->
    <div style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05);">
        <div class="grid-3" style="gap: 15px;">
            <div>
                <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; display: block;">Search Ledger</label>
                <div class="search-bar glass" style="border: 1px solid var(--glass-border); padding: 5px 15px; border-radius: 8px;">
                    <i class="fas fa-search" style="color: var(--text-muted);"></i>
                    <input type="text" id="txnSearch" placeholder="Reference or Shop..." onkeyup="filterTransactions()" style="background: transparent; border: none; color: white; outline: none; width: 100%; height: 32px;">
                </div>
            </div>
            <div>
                <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; display: block;">Managed By</label>
                <select id="userFilter" onchange="filterTransactions()" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;">
                    <option value="all">All Managers</option>
                    @foreach($salesmen as $salesman)
                        <option value="{{ $salesman->name }}">{{ $salesman->name }} (Salesman)</option>
                    @endforeach
                    @foreach($admins as $admin)
                        <option value="{{ $admin->name }}">{{ $admin->name }} (Admin)</option>
                    @endforeach
                    <option value="System Admin">System Admin</option>
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; display: block;">Entry Type</label>
                <select id="typeFilter" onchange="filterTransactions()" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;">
                    <option value="all">All Types</option>
                    <option value="Payment">Payment</option>
                    <option value="Order">Order</option>
                    <option value="Adjustment">Adjustment</option>
                    <option value="Credit Note">Credit Note</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="table-container">
        <table id="transactionTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Shop Name</th>
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
@endsection

@push('modals')
    <!-- Member Details Modal -->
    <div id="memberDetailsModal"
        style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(15px); align-items: center; justify-content: center;">
        <div class="card modal-content"
            style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); width: 550px; animation: modalIn 0.3s ease-out;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 id="memberModalTitle" style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-user-circle" style="color: var(--primary); font-size: 24px;"></i> 
                    <span>Member Details</span>
                </h3>
                <div onclick="closeMemberModal()" style="cursor: pointer; color: var(--text-muted);"><i class="fas fa-times"></i></div>
            </div>

            <div style="background: rgba(255,255,255,0.02); border-radius: 15px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div id="memberModalShopContainer" style="display: none; grid-column: span 2; margin-bottom: 5px;">
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Shop Name</label>
                    <div id="memberModalShop" style="color: #fff; font-size: 15px; font-weight: 600; margin-top: 4px;">-</div>
                </div>
                <div>
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Name</label>
                    <div id="memberModalName" style="color: #fff; font-size: 14px; font-weight: 500; margin-top: 4px;">-</div>
                </div>
                <div>
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">ID / Code</label>
                    <div id="memberModalCode" style="color: #fff; font-size: 14px; margin-top: 4px;">-</div>
                </div>
                <div>
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Phone Number</label>
                    <div style="color: #fff; font-size: 14px; margin-top: 4px;">
                        <i class="fas fa-phone-alt" style="color: var(--text-muted); margin-right: 6px;"></i><span id="memberModalPhone">-</span>
                    </div>
                </div>
                <div>
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Email Address</label>
                    <div style="color: #fff; font-size: 14px; margin-top: 4px;">
                        <i class="fas fa-envelope" style="color: var(--text-muted); margin-right: 6px;"></i><span id="memberModalEmail">-</span>
                    </div>
                </div>
                <div id="memberModalCityContainer" style="display: none;">
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">City</label>
                    <div id="memberModalCity" style="color: #fff; font-size: 14px; margin-top: 4px;">-</div>
                </div>
                <div id="memberModalGstContainer" style="display: none;">
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">GST Number</label>
                    <div id="memberModalGst" style="color: #fff; font-size: 14px; margin-top: 4px;">-</div>
                </div>
                <div id="memberModalDiscountContainer" style="display: none;">
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Discount</label>
                    <div id="memberModalDiscount" style="color: #fff; font-size: 14px; margin-top: 4px;">-</div>
                </div>
                <div id="memberModalSalesmanContainer" style="display: none;">
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Salesman</label>
                    <div id="memberModalSalesman" style="color: #fff; font-size: 14px; margin-top: 4px;">-</div>
                </div>
                <div id="memberModalDistributorContainer" style="display: none;">
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Distributor</label>
                    <div id="memberModalDistributor" style="color: #fff; font-size: 14px; margin-top: 4px;">-</div>
                </div>
                <div id="memberModalAddressContainer" style="display: none; grid-column: span 2; margin-top: 5px;">
                    <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Address</label>
                    <div id="memberModalAddress" style="color: #fff; font-size: 14px; margin-top: 4px; line-height: 1.5;">-</div>
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; justify-content: flex-end;">
                <button class="btn glass" onclick="closeMemberModal()">Close</button>
            </div>
        </div>
    </div>
@endpush

@section('scripts')
<script>
    // Live database serialized transactions
    const transactions = @json($transactions);

    function renderTransactions(data = transactions) {
        const body = document.getElementById('transactionBody');
        let html = '';

        if (!data || data.length === 0) {
            body.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 60px; color: var(--text-muted);"><i class="fas fa-info-circle" style="display: block; font-size: 24px; margin-bottom: 10px;"></i> No transactions found matching your criteria.</td></tr>';
            return;
        }

        data.forEach((t, index) => {
            let typeClass = '';
            if (t.type === 'Payment') typeClass = 'txn-type-payment';
            else if (t.type === 'Order') typeClass = 'txn-type-order';
            else if (t.type === 'Credit Note') typeClass = 'txn-type-credit';
            else typeClass = 'txn-type-adjustment';

            const m = t.member_details || {};
            const mName = (m.name || '').replace(/'/g, "\\'");
            const mEmail = (m.email || '').replace(/'/g, "\\'");
            const mMobile = (m.mobile || '').replace(/'/g, "\\'");
            const mCode = (m.code || '').replace(/'/g, "\\'");
            const mAddress = (m.address || '').replace(/'/g, "\\'");
            const mShop = (m.shop || '').replace(/'/g, "\\'");
            const mCity = (m.city || '').replace(/'/g, "\\'");
            const mGst = (m.gst || '').replace(/'/g, "\\'");
            const mSalesman = (m.salesman || '').replace(/'/g, "\\'");
            const mDistributor = (m.distributor || '').replace(/'/g, "\\'");

            html += `
                <tr class="animate-fade">
                    <td style="color: var(--text-muted); font-size: 13px;">${t.date}</td>
                    <td style="font-weight: 600;">
                        <a href="javascript:void(0)" onclick="viewMemberDetails('${mName}', '${mEmail}', '${mMobile}', '${mCode}', 'Dealer', '${mAddress}', '${mShop}', '${mCity}', '${mGst}', '${m.discount || ''}', '${mSalesman}', '${mDistributor}')" style="color: #3b82f6; text-decoration: none; border-bottom: 1px dashed rgba(59, 130, 246, 0.3);">
                            ${t.dealer}
                        </a>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: ${t.user.includes('Admin') || t.user.includes('Sourav') || t.user.includes('Amit') ? 'rgba(154, 90, 58, 0.1)' : 'rgba(255,255,255,0.05)'}; display: flex; align-items: center; justify-content: center; font-size: 12px; border: 1px solid var(--glass-border);">
                                <i class="fas ${t.user.includes('Admin') || t.user.includes('Sourav') || t.user.includes('Amit') ? 'fa-user-shield' : 'fa-user'}" style="color: ${t.user.includes('Admin') || t.user.includes('Sourav') || t.user.includes('Amit') ? 'var(--primary)' : 'var(--text-muted)'};"></i>
                            </div>
                            <span style="font-size: 14px; color: var(--light); opacity: 0.9;">${t.user}</span>
                        </div>
                    </td>
                    <td><span class="badge ${typeClass}">${t.type}</span></td>
                    <td style="font-weight: 700; color: ${t.type === 'Order' ? 'var(--accent)' : 'var(--success)'}">₹ ${Math.abs(t.amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                    <td style="font-family: 'JetBrains Mono', monospace; color: var(--text-muted); font-size: 12px;">${t.ref}</td>
                    <td><span class="badge badge-success" style="font-size: 10px; padding: 4px 10px;">${t.status}</span></td>
                </tr>
            `;
        });
        body.innerHTML = html;
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

    function viewMemberDetails(name, email, mobile, code, role, address, shop, city, gst, discount, salesman, distributor) {
        document.getElementById('memberModalTitle').innerHTML = '<i class="fas fa-user-circle" style="color: var(--primary); font-size: 24px;"></i> <span>' + role + ' Details</span>';
        document.getElementById('memberModalName').innerText = name || 'N/A';
        document.getElementById('memberModalEmail').innerText = email || 'N/A';
        document.getElementById('memberModalPhone').innerText = mobile || 'N/A';
        document.getElementById('memberModalCode').innerText = code || 'N/A';
        
        const toggleField = (id, value) => {
            const container = document.getElementById(id + 'Container');
            if (container) {
                if (value && value.trim() !== '' && value !== 'N/A') {
                    container.style.display = 'block';
                    document.getElementById(id).innerText = value;
                } else {
                    container.style.display = 'none';
                }
            }
        };

        toggleField('memberModalShop', shop);
        toggleField('memberModalAddress', address);
        toggleField('memberModalCity', city);
        toggleField('memberModalGst', gst);
        toggleField('memberModalDiscount', discount ? discount + '%' : '');
        toggleField('memberModalSalesman', salesman);
        toggleField('memberModalDistributor', distributor);
        
        document.getElementById('memberDetailsModal').style.display = 'flex';
    }

    function closeMemberModal() {
        document.getElementById('memberDetailsModal').style.display = 'none';
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

    // Close on overlay clicks
    window.onclick = function (event) {
        const modal = document.getElementById('memberDetailsModal');
        if (event.target == modal) {
            closeMemberModal();
        }
    }
</script>
@endsection
