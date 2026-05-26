@extends('layouts.app')

@section('title', 'Dealer Passbook')

@section('content')
<div class="card">
    <div style="display: flex; gap: 15px; align-items: center; justify-content: flex-end; margin-bottom: 25px;">
        <div class="search-bar glass" style="max-width: 300px; width: 100%; border: 1px solid var(--glass-border);">
            <i class="fas fa-search" style="color: var(--text-muted);"></i>
            <input type="text" id="dealerSearch" placeholder="Search dealer name..." onkeyup="filterBalances()">
        </div>
        <button class="btn btn-primary" onclick="openBalanceModal()" style="white-space: nowrap;">
            <i class="fas fa-plus"></i> Update Balance
        </button>
    </div>

    <!-- Dealer Balance Summary -->
    <div class="table-container">
        <div style="padding: 20px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; color: var(--primary);">Dealer Balance Summary</h4>
        </div>
        <table id="balanceSummaryTable">
            <thead>
                <tr>
                    <th>Dealer Name</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="balanceSummaryBody">
                <!-- Data populated by JS -->
            </tbody>
        </table>
    </div>
</div>

@push('modals')
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
                @foreach($dealers as $dealer)
                    <option value="{{ $dealer->id }}">{{ $dealer->shop ?? $dealer->name }}</option>
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

@endsection

@section('scripts')
<script>
    // Live database serialized records
    let balances = [
        @foreach($dealers as $dealer)
        {
            id: {{ $dealer->id }},
            dealer: "{{ $dealer->shop ?? $dealer->name }}",
            total: {{ $dealer->dealerBalance->total_amount ?? 0 }},
            paid: {{ $dealer->dealerBalance->paid_amount ?? 0 }},
            due: {{ $dealer->dealerBalance->due_amount ?? 0 }}
        },
        @endforeach
    ];

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
        const dealerId = parseInt(select.value);
        if (!dealerId) {
            document.getElementById('totalAmount').value = 0;
            document.getElementById('paidAmount').value = 0;
            document.getElementById('dueAmount').value = 0;
            calculateNewBalance();
            return;
        }
        
        const record = balances.find(b => b.id === dealerId);
        if (record) {
            document.getElementById('totalAmount').value = record.total;
            document.getElementById('paidAmount').value = record.paid;
            document.getElementById('dueAmount').value = record.due;
        } else {
            document.getElementById('totalAmount').value = 0;
            document.getElementById('paidAmount').value = 0;
            document.getElementById('dueAmount').value = 0;
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

    function editBalance(dealerId) {
        const record = balances.find(b => b.id === dealerId);
        if (!record) return;

        document.getElementById('modalTitle').innerText = 'Update Dealer Balance';
        document.getElementById('dealerSelect').value = record.id;
        document.getElementById('dealerSelect').disabled = true;
        document.getElementById('totalAmount').value = record.total;
        document.getElementById('paidAmount').value = record.paid;
        document.getElementById('dueAmount').value = record.due;
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

    function renderBalances() {
        const body = document.getElementById('balanceSummaryBody');
        body.innerHTML = '';
        balances.forEach(b => {
            body.innerHTML += `
                <tr>
                    <td>${b.dealer}</td>
                    <td>₹ ${b.total.toLocaleString()}</td>
                    <td>₹ ${b.paid.toLocaleString()}</td>
                    <td style="color: ${b.due > 0 ? 'var(--accent)' : 'var(--success)'}; font-weight: 700;">₹ ${b.due.toLocaleString()}</td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn glass" style="padding: 5px 10px; font-size: 11px; background: rgba(255,255,255,0.05);" onclick="viewTransactions('${b.dealer}')" title="View Transactions">
                                <i class="fas fa-eye" style="color: var(--primary);"></i>
                            </button>
                            <button class="btn glass" style="padding: 5px 10px; font-size: 11px; background: rgba(255,255,255,0.05);" onclick="editBalance(${b.id})" title="Edit Balance">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }

    function filterBalances() {
        const input = document.getElementById('dealerSearch');
        const filter = input.value.toLowerCase();
        const tbody = document.getElementById('balanceSummaryBody');
        const rows = tbody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const dealerNameCell = rows[i].getElementsByTagName('td')[0];
            if (dealerNameCell) {
                const textValue = dealerNameCell.textContent || dealerNameCell.innerText;
                if (textValue.toLowerCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    }

    window.onclick = function(event) {
        if (event.target.id == 'balanceModal') {
            closeBalanceModal();
        }
    }

    // Initial render
    renderBalances();
</script>
@endsection
