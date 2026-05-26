@extends('layouts.app')

@section('content')
    <div class="card animate-fade">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Verify Dealer Payments</h3>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #22c55e; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Dealer</th>
                        <th>Amount</th>
                        <th>Date/Time</th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                        <tr>
                            <td>#{{ str_pad($submission->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $submission->member->name }}</td>
                            <td style="font-weight: 600; color: var(--primary);">₹ {{ number_format($submission->amount, 2) }}</td>
                            <td><span style="font-size: 12px; color: var(--text-muted);">{{ $submission->created_at->format('Y-m-d H:i A') }}</span></td>
                            <td>
                                <span class="badge {{ $submission->status == 'Approved' ? 'badge-success' : ($submission->status == 'Rejected' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $submission->status }}
                                </span>
                            </td>
                            <td>
                                @if($submission->receipt_path)
                                    <button class="btn glass" style="padding: 4px 10px; font-size: 11px;" 
                                            onclick="viewReceipt('{{ asset('uploads/' . $submission->receipt_path) }}')">
                                        <i class="fas fa-file-invoice"></i> View Receipt
                                    </button>
                                @else
                                    <span style="font-size: 11px; color: var(--text-muted); font-style: italic;">No file</span>
                                @endif
                            </td>
                            <td>
                                @if($submission->status == 'Pending')
                                    <div style="display: flex; gap: 5px;">
                                        <form action="{{ route('payments.approve', $submission->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this payment of ₹ {{ number_format($submission->amount, 2) }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-primary" style="padding: 5px 12px; font-size: 11px; background: #22c55e; border-color: #22c55e;">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-danger" style="padding: 5px 12px; font-size: 11px; background: #ef4444; border-color: #ef4444;"
                                                onclick="openRejectModal('{{ $submission->id }}', '{{ number_format($submission->amount, 2) }}')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                @else
                                    <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">
                                        @if($submission->status == 'Approved')
                                            Approved
                                        @else
                                            Rejected: {{ $submission->remarks ?: 'No remarks' }}
                                        @endif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px 10px;">
                                <i class="fas fa-receipt" style="font-size: 30px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                No payment uploads found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@push('modals')
    <!-- View Receipt Modal -->
    <div id="receiptModal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 650px; padding: 25px; background: #0f172a; border: 1px solid var(--glass-border); border-radius: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Payment Receipt Copy</h3>
                <div onclick="closeReceiptModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                </div>
            </div>

            <div id="receiptContent" style="display: flex; justify-content: center; align-items: center; margin-bottom: 20px; min-height: 200px; max-height: 500px; overflow-y: auto;">
                <!-- Dynamically loaded receipt image or PDF link -->
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button class="btn btn-primary" onclick="closeReceiptModal()">Close Window</button>
            </div>
        </div>
    </div>

    <!-- Reject Payment Modal -->
    <div id="rejectModal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 450px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); border-radius: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #ef4444;">Reject Payment Receipt</h3>
                <div onclick="closeRejectModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                </div>
            </div>

            <form id="rejectForm" action="" method="POST">
                @csrf
                <div style="margin-bottom: 20px;">
                    <p style="font-size: 14px; margin: 0 0 15px 0;">You are rejecting the payment verification request of <strong style="color: var(--primary);">₹ <span id="rejectAmountSpan">0.00</span></strong>.</p>
                    <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px;">Rejection Reason (Remarks)</label>
                    <textarea name="remarks" class="form-control" style="height: 100px; background: rgba(255,255,255,0.03); width: 100%; border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; padding: 10px; font-size: 14px;" placeholder="Provide a brief explanation of why the receipt is rejected..."></textarea>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn glass" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn" style="background: #ef4444; border-color: #ef4444; color: #fff;">Reject Receipt</button>
                </div>
            </form>
        </div>
    </div>
@endpush

    <script>
        function viewReceipt(url) {
            const container = document.getElementById('receiptContent');
            if (url.toLowerCase().endsWith('.pdf')) {
                container.innerHTML = `
                    <div style="text-align: center; width: 100%;">
                        <a href="${url}" target="_blank" class="btn glass" style="display: inline-flex; align-items: center; gap: 10px; padding: 15px 25px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); text-decoration: none; color: var(--primary);">
                            <i class="fas fa-file-pdf" style="font-size: 30px; color: #ef4444;"></i>
                            <div style="text-align: left;">
                                <div style="font-weight: 600;">Open PDF File</div>
                                <div style="font-size: 11px; color: var(--text-muted);">View in browser or download receipt</div>
                            </div>
                        </a>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <img src="${url}" style="max-width: 100%; max-height: 450px; object-fit: contain; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                `;
            }
            document.getElementById('receiptModal').style.display = 'flex';
        }

        function closeReceiptModal() {
            document.getElementById('receiptModal').style.display = 'none';
        }

        function openRejectModal(id, amount) {
            document.getElementById('rejectAmountSpan').innerText = amount;
            document.getElementById('rejectForm').action = `${window.APP_URL}/payments/${id}/reject`;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        // Close on overlay clicks
        window.onclick = function (event) {
            if (event.target == document.getElementById('receiptModal')) {
                closeReceiptModal();
            }
            if (event.target == document.getElementById('rejectModal')) {
                closeRejectModal();
            }
        }
    </script>
@endsection
