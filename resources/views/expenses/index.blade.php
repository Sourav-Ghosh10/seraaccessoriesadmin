@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 15px; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; gap: 12px;">
        <button type="button" id="tabBtnExpenses" onclick="switchExpenseTab('expenses')" class="btn btn-primary" style="padding: 10px 22px; font-size: 14px; font-weight: 600; border-radius: 10px; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease;">
            <i class="fas fa-wallet"></i> Salesman Expenses
        </button>
        <button type="button" id="tabBtnReimbursements" onclick="switchExpenseTab('reimbursements')" class="btn glass" style="padding: 10px 22px; font-size: 14px; font-weight: 600; border-radius: 10px; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease;">
            <i class="fas fa-hand-holding-usd"></i> Salesman Reimbursements
        </button>
    </div>
    <button type="button" class="btn btn-primary" onclick="openAddReimbursementModal()" style="display: flex; align-items: center; gap: 8px; font-size: 14px; padding: 10px 20px; border-radius: 10px; background: var(--secondary); border-color: var(--secondary);">
        <i class="fas fa-plus"></i> Add Reimbursement
    </button>
</div>

@if(session('success'))
    <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #fff; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div id="tabContentExpenses" class="card" style="display: block;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Salesman Expenses</h3>
    </div>

    <div style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05);">
        <form method="GET" action="{{ route('expenses.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
            <input type="hidden" name="tab" value="expenses">
            <div>
                <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; display: block;">Salesman</label>
                <select name="salesman_id" class="form-control" onchange="this.form.submit()" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff; width: 100%; height: 44px; border-radius: 8px;">
                    <option value="">All Salesmen</option>
                    @foreach($salesmen as $salesman)
                        <option value="{{ $salesman->id }}" {{ request('salesman_id') == $salesman->id ? 'selected' : '' }}>
                            {{ $salesman->name }} {{ $salesman->emp_id ? '('.$salesman->emp_id.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; display: block;">Status</label>
                <select name="status" class="form-control" onchange="this.form.submit()" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff; width: 100%; height: 44px; border-radius: 8px;">
                    <option value="">All Statuses</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center; height: 44px;">Filter</button>
                <a href="{{ route('expenses.index') }}" class="btn glass" style="flex: 1; justify-content: center; height: 44px; text-decoration: none; display: flex; align-items: center;">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Salesman</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Receipt</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->created_at->format('d M Y, h:i A') }}</td>
                    <td>{{ $expense->salesman->name ?? '—' }}</td>
                    <td>{{ $expense->category->name ?? '—' }}</td>
                    <td>{{ $expense->description ?? '—' }}</td>
                    <td style="font-weight: bold; color: var(--primary);">
                        ₹{{ number_format($expense->amount, 2) }}
                        @if($expense->status == 'Approved' && $expense->approved_amount !== null && $expense->approved_amount != $expense->amount)
                            <div style="font-size: 11px; color: #22c55e; font-weight: normal;">Reimbursed: ₹{{ number_format($expense->approved_amount, 2) }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            @if($expense->receipt_photo_path)
                                <a href="{{ asset('uploads/' . $expense->receipt_photo_path) }}" target="_blank" style="color: var(--secondary); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 13px;">
                                    <i class="fas fa-image"></i> View Receipt
                                </a>
                            @endif
                            @if($expense->admin_receipt_path && $expense->admin_receipt_path !== $expense->receipt_photo_path)
                                <a href="{{ asset('uploads/' . $expense->admin_receipt_path) }}" target="_blank" style="color: #22c55e; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-file-invoice-dollar"></i> View Approval Receipt
                                </a>
                            @elseif(!$expense->receipt_photo_path && !$expense->admin_receipt_path)
                                <span style="color: var(--text-muted); font-size: 13px;">No Receipt</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $expense->status == 'Approved' ? 'badge-success' : ($expense->status == 'Pending' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $expense->status }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            @if($expense->status == 'Pending')
                                <form method="POST" action="{{ route('expenses.status.update', $expense->id) }}" onsubmit="return confirm('Are you sure you want to approve this expense?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Approved">
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px; background: #22c55e; border-color: #22c55e;">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('expenses.status.update', $expense->id) }}" onsubmit="return confirm('Are you sure you want to reject this expense?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Rejected">
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px; background: #ef4444; border-color: #ef4444;">Reject</button>
                                </form>
                            @else
                                <span style="color: var(--text-muted); font-size: 12px;">Processed</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 20px;">No expenses found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="tabContentReimbursements" class="card" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Salesman Reimbursements</h3>
    </div>

    <div style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05);">
        <form method="GET" action="{{ route('expenses.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
            <input type="hidden" name="tab" value="reimbursements">
            <div>
                <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; display: block;">Salesman</label>
                <select name="reimb_salesman_id" class="form-control" onchange="this.form.submit()" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff; width: 100%; height: 44px; border-radius: 8px;">
                    <option value="">All Salesmen</option>
                    @foreach($salesmen as $salesman)
                        <option value="{{ $salesman->id }}" {{ (request('reimb_salesman_id') == $salesman->id || (request('tab') == 'reimbursements' && request('salesman_id') == $salesman->id)) ? 'selected' : '' }}>
                            {{ $salesman->name }} {{ $salesman->emp_id ? '('.$salesman->emp_id.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center; height: 44px;">Filter</button>
                <a href="{{ route('expenses.index', ['tab' => 'reimbursements']) }}" class="btn glass" style="flex: 1; justify-content: center; height: 44px; text-decoration: none; display: flex; align-items: center;">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Salesman</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Document</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reimbursements as $reimbursement)
                <tr>
                    <td>{{ $reimbursement->created_at->format('d M, Y h:i A') }}</td>
                    <td>
                        <div style="font-weight: 600;">{{ $reimbursement->salesman->name ?? 'Unknown' }}</div>
                        @if($reimbursement->salesman && $reimbursement->salesman->emp_id)
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $reimbursement->salesman->emp_id }}</div>
                        @endif
                    </td>
                    <td>{{ $reimbursement->description ?? '—' }}</td>
                    <td><span style="color: #22c55e; font-weight: 700;">₹{{ number_format($reimbursement->amount, 2) }}</span></td>
                    <td>
                        @if($reimbursement->document_path)
                            <a href="{{ asset('uploads/' . $reimbursement->document_path) }}" target="_blank" style="color: var(--secondary); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 13px;">
                                <i class="fas fa-file-invoice-dollar"></i> View Document
                            </a>
                        @else
                            <span style="color: var(--text-muted); font-size: 13px;">No Document</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $reimbursement->status == 'Approved' ? 'badge-success' : ($reimbursement->status == 'Pending' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $reimbursement->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 20px;">No reimbursements recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('modals')
    <!-- Add Reimbursement Modal -->
    <div id="addReimbursementModal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); border-radius: 16px; animation: modalIn 0.3s ease-out;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus-circle"></i> Add Reimbursement
                </h3>
                <div onclick="closeAddReimbursementModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                </div>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px;">
                        Select Salesman <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="salesman_id" class="form-control" required style="width: 100%; background: #1e293b; border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; padding: 10px; font-size: 14px;">
                        <option value="">Select Salesman</option>
                        @foreach($salesmen as $salesman)
                            <option value="{{ $salesman->id }}">{{ $salesman->name }} {{ $salesman->emp_id ? '('.$salesman->emp_id.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px;">
                        Amount (₹) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required placeholder="0.00" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; padding: 10px; font-size: 14px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px;">
                        Document / Receipt Upload (PDF, PNG, JPG)
                    </label>
                    <input type="file" name="document" class="form-control" accept=".pdf,.png,.jpg,.jpeg,image/png,image/jpeg,application/pdf" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px dashed var(--glass-border); border-radius: 8px; color: #fff; padding: 10px; font-size: 14px; cursor: pointer;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px;">
                        Description / Notes
                    </label>
                    <textarea name="description" rows="2" class="form-control" placeholder="Optional notes about this reimbursement..." style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; padding: 10px; font-size: 14px;"></textarea>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 25px;">
                    <button type="button" class="btn glass" onclick="closeAddReimbursementModal()" style="padding: 10px 18px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 600;">Save Reimbursement</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@section('scripts')
<script>
    function switchExpenseTab(tab) {
        localStorage.setItem('activeExpenseTab', tab);
        const expensesBtn = document.getElementById('tabBtnExpenses');
        const reimbBtn = document.getElementById('tabBtnReimbursements');
        const expensesContent = document.getElementById('tabContentExpenses');
        const reimbContent = document.getElementById('tabContentReimbursements');

        if (tab === 'expenses') {
            if (expensesBtn) expensesBtn.className = 'btn btn-primary';
            if (reimbBtn) reimbBtn.className = 'btn glass';
            if (expensesContent) expensesContent.style.display = 'block';
            if (reimbContent) reimbContent.style.display = 'none';
        } else {
            if (expensesBtn) expensesBtn.className = 'btn glass';
            if (reimbBtn) reimbBtn.className = 'btn btn-primary';
            if (expensesContent) expensesContent.style.display = 'none';
            if (reimbContent) reimbContent.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            switchExpenseTab(tabParam);
        } else {
            const savedTab = localStorage.getItem('activeExpenseTab') || 'expenses';
            switchExpenseTab(savedTab);
        }
    });

    function openAddReimbursementModal() {
        document.getElementById('addReimbursementModal').style.display = 'flex';
    }

    function closeAddReimbursementModal() {
        document.getElementById('addReimbursementModal').style.display = 'none';
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('addReimbursementModal');
        if (event.target === modal) {
            closeAddReimbursementModal();
        }
    });
</script>
@endsection
@endsection
