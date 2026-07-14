@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Salesman Expenses</h3>
    </div>
    
    @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #fff; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
        <form method="GET" action="{{ route('expenses.index') }}" style="display: flex; gap: 10px;">
            <select name="status" class="form-control" style="width: 180px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
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
                                <button type="button" class="btn btn-primary" onclick="openApproveModal({{ $expense->id }}, '{{ addslashes($expense->salesman->name ?? '—') }}', '₹{{ number_format($expense->amount, 2) }}', '{{ $expense->amount }}')" style="padding: 5px 10px; font-size: 12px; background: #22c55e; border-color: #22c55e;">Approve</button>
                                <form method="POST" action="{{ route('expenses.status.update', $expense->id) }}">
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

@push('modals')
    <!-- Approve Expense Modal -->
    <div id="approveModal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 480px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); border-radius: 16px; animation: modalIn 0.3s ease-out;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #22c55e; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-check-circle"></i> Approve Expense
                </h3>
                <div onclick="closeApproveModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                </div>
            </div>

            <form id="approveForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="Approved">

                <div style="margin-bottom: 20px;">
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 15px; margin-bottom: 20px;">
                        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Salesman: <span id="approveModalSalesman" style="color: #fff; font-weight: 600;">—</span></div>
                        <div style="font-size: 13px; color: var(--text-muted);">Amount: <span id="approveModalAmount" style="color: var(--primary); font-weight: 700; font-size: 15px;">₹0.00</span></div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px;">
                            Reimbursement Amount (₹)
                        </label>
                        <input type="number" step="0.01" name="approved_amount" id="approved_amount_input" class="form-control" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; padding: 10px; font-size: 14px;">
                        <small style="display: block; margin-top: 6px; color: var(--text-muted); font-size: 11px;">You can modify if reimbursement differs from applied amount.</small>
                    </div>

                    <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px;">
                        Receipt Upload (PDF, PNG, JPG) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="file" name="admin_receipt" id="admin_receipt_input" class="form-control" accept=".pdf,.png,.jpg,.jpeg,image/png,image/jpeg,application/pdf" required style="width: 100%; background: rgba(255,255,255,0.03); border: 1px dashed var(--glass-border); border-radius: 8px; color: #fff; padding: 10px; font-size: 14px; cursor: pointer;">
                    <small style="display: block; margin-top: 6px; color: var(--text-muted); font-size: 11px;">Mandatory file upload (pdf, png, jpg accepted).</small>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 25px;">
                    <button type="button" class="btn glass" onclick="closeApproveModal()" style="padding: 10px 18px;">Cancel</button>
                    <button type="submit" class="btn" style="background: #22c55e; border-color: #22c55e; color: #fff; padding: 10px 20px; font-weight: 600;">Confirm</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@section('scripts')
<script>
    function openApproveModal(expenseId, salesmanName, amount, rawAmount) {
        const form = document.getElementById('approveForm');
        form.action = `{{ url('/expenses') }}/${expenseId}/status`;
        document.getElementById('approveModalSalesman').innerText = salesmanName;
        document.getElementById('approveModalAmount').innerText = amount;
        if (document.getElementById('approved_amount_input')) {
            document.getElementById('approved_amount_input').value = rawAmount || '';
        }
        document.getElementById('admin_receipt_input').value = '';
        const modal = document.getElementById('approveModal');
        modal.style.display = 'flex';
    }

    function closeApproveModal() {
        const modal = document.getElementById('approveModal');
        modal.style.display = 'none';
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('approveModal');
        if (event.target === modal) {
            closeApproveModal();
        }
    });
</script>
@endsection
@endsection
