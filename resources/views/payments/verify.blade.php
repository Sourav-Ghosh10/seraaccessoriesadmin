@extends('layouts.app')

@section('content')
    <style>
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="card animate-fade">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Verify Dealer Payments</h3>
            <button class="btn glass" onclick="location.reload()" style="font-size: 13px;">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #22c55e; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
                <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Filter Form style search -->
        <div style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05);">
            <div class="grid-3" style="gap: 15px;">
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Quick Search</label>
                    <div class="search-bar glass" style="border: 1px solid var(--glass-border); padding: 5px 15px; border-radius: 8px;">
                        <i class="fas fa-search" style="color: var(--text-muted);"></i>
                        <input type="text" id="paymentSearch" placeholder="Search ID or Shop Name..." onkeyup="filterPayments()" style="background: transparent; border: none; color: white; outline: none; width: 100%; height: 32px;">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Status Filter</label>
                    <select id="statusFilter" onchange="filterPayments()" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;">
                        <option value="all">All Submissions</option>
                        <option value="Pending">Pending Verification</option>
                        <option value="Approved">Approved Payments</option>
                        <option value="Rejected">Rejected Receipts</option>
                    </select>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px; width: 100%; text-align: right;">
                        <span id="resultCount">{{ count($submissions) }}</span> records found
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Shop Name</th>
                        <th>Amount</th>
                        <th>Date/Time</th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentTableBody">
                    @forelse($submissions as $submission)
                        <tr class="payment-row" data-search="{{ strtolower($submission->member->shop . ' ' . $submission->member->name . ' ' . $submission->id) }}" data-status="{{ $submission->status }}">
                            <td>#{{ str_pad($submission->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($submission->member->name) }}', '{{ addslashes($submission->member->email) }}', '{{ addslashes($submission->member->mobile) }}', '{{ addslashes($submission->member->ref_code ?? '') }}', 'Dealer', '{{ addslashes(preg_replace('/\r|\n/', ' ', $submission->member->address ?? '')) }}', '{{ addslashes($submission->member->shop ?? '') }}', '{{ addslashes($submission->member->city->city ?? '') }}', '{{ addslashes($submission->member->gst_no ?? '') }}', '{{ $submission->member->discount_percent ?? '' }}', '{{ addslashes($submission->member->salesman->name ?? '') }}', '{{ addslashes($distributors->firstWhere('dist_id', $submission->member->dist_id)->name ?? $submission->member->dist_id ?? '') }}')" style="font-weight: 500; color: #3b82f6; text-decoration: none; border-bottom: 1px dashed rgba(59, 130, 246, 0.3);">
                                    {{ $submission->member->shop ?? $submission->member->name }}
                                </a>
                            </td>
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

    <!-- View Receipt Modal -->
    <div id="receiptModal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 650px; padding: 25px; background: #0f172a; border: 1px solid var(--glass-border); border-radius: 16px; animation: modalIn 0.3s ease-out;">
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
        <div class="card" style="width: 100%; max-width: 450px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); border-radius: 16px; animation: modalIn 0.3s ease-out;">
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

@section('scripts')
    <script>
        function filterPayments() {
            const searchTerm = document.getElementById('paymentSearch').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('.payment-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.getAttribute('data-search');
                const status = row.getAttribute('data-status');
                const matchesSearch = text.includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('resultCount').innerText = visibleCount;
        }

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
            document.getElementById('rejectForm').action = `${window.BASE_PATH}/payments/${id}/reject`;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
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

        // Close on overlay clicks
        window.onclick = function (event) {
            if (event.target == document.getElementById('receiptModal')) {
                closeReceiptModal();
            }
            if (event.target == document.getElementById('rejectModal')) {
                closeRejectModal();
            }
            if (event.target == document.getElementById('memberDetailsModal')) {
                closeMemberModal();
            }
        }
    </script>
@endsection
