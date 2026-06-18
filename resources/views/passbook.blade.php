@extends('layouts.app')

@section('title', 'Dealer Passbook')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3>Dealer Account Ledger</h3>
        <div style="display: flex; gap: 15px; align-items: center;">
            <button class="btn glass" onclick="location.reload()" style="font-size: 13px;">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-primary" onclick="openBalanceModal()" style="white-space: nowrap;">
                <i class="fas fa-plus"></i> Update Balance
            </button>
        </div>
    </div>

    <!-- Filter Form style search -->
    <div style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05);">
        <form id="filterForm" method="GET" action="" class="grid-3" style="gap: 15px; align-items: flex-end;">
            <div style="grid-column: span 2;">
                <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Search Dealer</label>
                <div class="search-bar glass" style="border: 1px solid var(--glass-border); padding: 5px 15px; border-radius: 8px;">
                    <i class="fas fa-search" style="color: var(--text-muted);"></i>
                    <input type="text" name="search" id="dealerSearch" placeholder="Search ID, Shop or Dealer Name..." value="{{ request('search') }}" style="background: transparent; border: none; color: white; outline: none; width: 100%; height: 32px;">
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">Filter</button>
                <a href="{{ route('passbook') }}" class="btn glass" style="flex: 1; justify-content: center; text-decoration: none;">Reset</a>
            </div>
        </form>
    </div>

    <!-- Dealer Balance Summary -->
    <div class="table-container">
        <div style="padding: 20px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; color: var(--primary); font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Dealer Balance Summary</h4>
            <div style="font-size: 12px; color: var(--text-muted);">Total Dealers: {{ $dealers->total() }}</div>
        </div>
        <table id="balanceSummaryTable">
            <thead>
                <tr>
                    <th>Shop Name</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="balanceSummaryBody">
                @forelse($dealers as $dealer)
                @php
                    $balance = $dealer->dealerBalance;
                    $total = $balance->total_amount ?? 0;
                    $paid = $balance->paid_amount ?? 0;
                    $due = $balance->due_amount ?? 0;
                    
                    $distributors = \App\Models\Member::where('role', 'distributor')->get();
                    $distributorName = $distributors->firstWhere('dist_id', $dealer->dist_id)->name ?? $dealer->dist_id ?? '';
                @endphp
                <tr>
                    <td>
                        <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($dealer->name) }}', '{{ addslashes($dealer->email) }}', '{{ addslashes($dealer->mobile) }}', '{{ addslashes($dealer->ref_code ?? '') }}', 'Dealer', '{{ addslashes(preg_replace('/\r|\n/', ' ', $dealer->address ?? '')) }}', '{{ addslashes($dealer->shop ?? '') }}', '{{ addslashes($dealer->city->city ?? '') }}', '{{ addslashes($dealer->gst_no ?? '') }}', '{{ $dealer->discount_percent ?? '' }}', '{{ addslashes($dealer->salesman->name ?? '') }}', '{{ addslashes($distributorName) }}')" style="font-weight: 600; color: #3b82f6; text-decoration: none; border-bottom: 1px dashed rgba(59, 130, 246, 0.3);">
                            {{ $dealer->shop ?? $dealer->name }}
                        </a>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">{{ $dealer->city->city ?? '' }}</div>
                    </td>
                    <td style="font-weight: 500;">₹ {{ number_format($total, 2) }}</td>
                    <td style="color: var(--success); font-weight: 500;">₹ {{ number_format($paid, 2) }}</td>
                    <td style="color: {{ $due > 0 ? 'var(--accent)' : 'var(--success)' }}; font-weight: 700;">₹ {{ number_format($due, 2) }}</td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn glass" onclick="viewTransactions('{{ $dealer->shop ?? $dealer->name }}')" title="View Full Ledger">
                                <i class="fas fa-list-alt" style="color: var(--primary);"></i>
                            </button>
                            <button class="btn glass" onclick="editBalance({{ $dealer->id }}, '{{ $dealer->shop ?? $dealer->name }}', {{ $total }}, {{ $paid }}, {{ $due }})" title="Quick Adjustment">
                                <i class="fas fa-edit" style="color: #fbbf24;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 50px;">
                        <i class="fas fa-user-slash" style="display: block; font-size: 24px; margin-bottom: 10px; opacity: 0.5;"></i>
                        No dealers found matching your search.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div id="paginationContainer" style="padding: 20px;">
            {{ $dealers->appends(request()->query())->links() }}
        </div>
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

<!-- Add/Edit Balance Modal -->
<div id="balanceModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 22px; font-weight: 700;">Add Dealer Balance</h3>
            <div onclick="closeBalanceModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Select Dealer</label>
            <select id="dealerSelect" class="form-control" onchange="updateModalBalanceFields()" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                <option value="">Select a Dealer</option>
                @foreach($allDealers as $dealer)
                    <option value="{{ $dealer->id }}" 
                            data-total="{{ $dealer->dealerBalance->total_amount ?? 0 }}"
                            data-paid="{{ $dealer->dealerBalance->paid_amount ?? 0 }}"
                            data-due="{{ $dealer->dealerBalance->due_amount ?? 0 }}">
                        {{ $dealer->shop ?? $dealer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid-3" style="margin-top: 20px;">
            <div class="form-group">
                <label class="form-label" style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Current Total</label>
                <input type="number" id="totalAmount" class="form-control" readonly style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05); color: var(--text-muted);">
            </div>
            <div class="form-group">
                <label class="form-label" style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Current Paid</label>
                <input type="number" id="paidAmount" class="form-control" readonly style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05); color: var(--text-muted);">
            </div>
            <div class="form-group">
                <label class="form-label" style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Current Due</label>
                <input type="number" id="dueAmount" class="form-control" readonly style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05); color: var(--accent); font-weight: 700;">
            </div>
        </div>

        <div class="grid-3" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05);">
            <div class="form-group" style="grid-column: span 1;">
                <label class="form-label" style="color: var(--primary); font-size: 11px; text-transform: uppercase;">Transaction Type</label>
                <select id="adjustmentType" class="form-control" onchange="calculateNewBalance()" style="background: #1e293b; border-color: var(--glass-border); color: #fff;">
                    <option value="add">Add to Total (Bill)</option>
                    <option value="payment">Record Payment (Income)</option>
                </select>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label" style="color: var(--primary); font-size: 11px; text-transform: uppercase;">Transaction Amount</label>
                <input type="number" id="txnAmount" class="form-control" placeholder="Enter amount..." oninput="calculateNewBalance()" style="background: rgba(154, 90, 58, 0.05); border-color: var(--primary);">
            </div>
        </div>

        <div id="previewSection" style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.02); border-radius: 8px; display: none;">
            <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--text-muted);">
                <span>New Total: <strong id="previewTotal" style="color: #fff;">₹ 0</strong></span>
                <span>New Paid: <strong id="previewPaid" style="color: #fff;">₹ 0</strong></span>
                <span>New Due: <strong id="previewDue" style="color: var(--accent);">₹ 0</strong></span>
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <button class="btn glass" onclick="closeBalanceModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 12px 30px;">Cancel</button>
            <button id="saveBtn" class="btn btn-primary" onclick="saveBalance()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Update Balance</button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.form-control:focus { outline: none; border-color: var(--primary); }
</style>
@endpush

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // AJAX Filtering Logic
        var filterTimeout;
        function applyFilters() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                var form = $('#filterForm');
                $.ajax({
                    url: form.attr('action') || window.location.href,
                    data: form.serialize(),
                    success: function(response) {
                        var newTable = $(response).find('.table-container').html();
                        $('.table-container').html(newTable);
                    }
                });
            }, 300);
        }

        $('#dealerSearch').on('input', function() {
            applyFilters();
        });

        // Handle AJAX Pagination
        $(document).on('click', '#paginationContainer a', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            $.ajax({
                url: url,
                success: function(response) {
                    var newTable = $(response).find('.table-container').html();
                    $('.table-container').html(newTable);
                }
            });
        });
    });

    function calculateNewBalance() {
        const currentTotal = parseFloat(document.getElementById('totalAmount').value) || 0;
        const currentPaid = parseFloat(document.getElementById('paidAmount').value) || 0;
        
        const type = document.getElementById('adjustmentType').value;
        const amount = parseFloat(document.getElementById('txnAmount').value) || 0;

        let newTotal = currentTotal;
        let newPaid = currentPaid;

        if (type === 'add') newTotal += amount;
        else if (type === 'payment') newPaid += amount;

        const newDue = newTotal - newPaid;

        // Update Preview
        document.getElementById('previewSection').style.display = (amount !== 0) ? 'block' : 'none';
        document.getElementById('previewTotal').innerText = '₹ ' + newTotal.toLocaleString();
        document.getElementById('previewPaid').innerText = '₹ ' + newPaid.toLocaleString();
        document.getElementById('previewDue').innerText = '₹ ' + newDue.toLocaleString();

        return { newTotal, newPaid, newDue };
    }

    function updateModalBalanceFields() {
        const select = document.getElementById('dealerSelect');
        const selectedOption = select.options[select.selectedIndex];
        
        if (!select.value) {
            document.getElementById('totalAmount').value = 0;
            document.getElementById('paidAmount').value = 0;
            document.getElementById('dueAmount').value = 0;
        } else {
            document.getElementById('totalAmount').value = selectedOption.dataset.total || 0;
            document.getElementById('paidAmount').value = selectedOption.dataset.paid || 0;
            document.getElementById('dueAmount').value = selectedOption.dataset.due || 0;
        }
        calculateNewBalance();
    }

    function openBalanceModal() {
        document.getElementById('modalTitle').innerText = 'Add Dealer Balance';
        document.getElementById('dealerSelect').disabled = false;
        document.getElementById('dealerSelect').value = '';
        document.getElementById('totalAmount').value = 0;
        document.getElementById('paidAmount').value = 0;
        document.getElementById('dueAmount').value = 0;
        resetAdjustmentFields();
        document.getElementById('balanceModal').style.display = 'flex';
    }

    function closeBalanceModal() {
        document.getElementById('balanceModal').style.display = 'none';
    }

    function resetAdjustmentFields() {
        document.getElementById('adjustmentType').value = 'add';
        document.getElementById('txnAmount').value = '';
        document.getElementById('previewSection').style.display = 'none';
    }

    function editBalance(dealerId, dealerName, total, paid, due) {
        document.getElementById('modalTitle').innerText = 'Update Dealer Balance';
        document.getElementById('dealerSelect').value = dealerId;
        document.getElementById('dealerSelect').disabled = true;
        document.getElementById('totalAmount').value = total;
        document.getElementById('paidAmount').value = paid;
        document.getElementById('dueAmount').value = due;
        resetAdjustmentFields();
        document.getElementById('balanceModal').style.display = 'flex';
    }

    function viewTransactions(dealer) {
        window.location.href = `{{ route('transactions.index') }}?dealer=${encodeURIComponent(dealer)}`;
    }

    function saveBalance() {
        const dealerSelect = document.getElementById('dealerSelect');
        const dealerId = dealerSelect.value;
        const adjustmentType = document.getElementById('adjustmentType').value;
        const txnAmountInput = document.getElementById('txnAmount');
        const amount = parseFloat(txnAmountInput.value) || 0;

        if (!dealerId) {
            alert('Please select a dealer');
            return;
        }

        if (amount <= 0) {
            alert('Please enter a valid transaction amount greater than 0');
            return;
        }

        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        fetch('{{ route("passbook.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                member_id: dealerId,
                adjustment_type: adjustmentType,
                amount: amount
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'An error occurred.');
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update balance. Please try again.');
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
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

    window.onclick = function(event) {
        if (event.target.id == 'balanceModal') {
            closeBalanceModal();
        }
        if (event.target.id == 'memberDetailsModal') {
            closeMemberModal();
        }
    }
</script>
@endsection
