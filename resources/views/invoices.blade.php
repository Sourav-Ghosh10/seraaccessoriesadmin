@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Custom styling for dark mode select2 */
.select2-container--default .select2-selection--single,
.select2-container--default .select2-selection--multiple {
    background-color: #1e293b !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 6px !important;
    height: 42px !important;
}
.select2-container--default .select2-selection--multiple {
    overflow: hidden;
    position: relative;
    padding-right: 25px;
}
.select2-container--default .select2-selection--multiple::after {
    content: "";
    position: absolute;
    right: 15px;
    top: 40%;
    width: 8px;
    height: 8px;
    border-right: 2px solid #fff;
    border-bottom: 2px solid #fff;
    transform: translateY(-50%) rotate(45deg);
    pointer-events: none;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff;
    line-height: 40px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
}
.select2-dropdown {
    background-color: #0f172a;
    border: 1px solid rgba(255,255,255,0.1);
}
.select2-container--default .select2-search--inline .select2-search__field {
    color: #fff !important;
    font-weight: 500 !important;
    font-family: inherit;
    margin-top: 8px !important;
    margin-left: 8px !important;
}
.select2-container--default .select2-search--inline .select2-search__field::placeholder {
    color: #fff !important;
    opacity: 0.9;
}
.select2-container.has-multiple .select2-selection__choice {
    display: none !important;
}
.select2-container.has-multiple .select2-selection--multiple::before {
    content: var(--selected-text);
    color: #fff;
    display: block;
    line-height: 42px;
    padding: 0 14px;
    font-size: 14px;
    font-weight: 500;
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 5;
    pointer-events: none;
}
.select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
}
.select2-container--default .select2-results__option {
    color: #fff;
}
.select2-container--default .select2-results__option--selected {
    background-color: rgba(255,255,255,0.1);
}
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
    background-color: var(--primary);
    color: white;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    margin-top: 6px;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    background-color: transparent;
    color: #ef4444;
}

/* Action Dropdown Styles */
.action-menu-container {
    position: relative;
    display: inline-block;
}
.action-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 16px;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 6px;
    transition: all 0.2s;
}
.action-btn:hover, .action-btn.active {
    background: rgba(255,255,255,0.1);
    color: #fff;
}
.action-dropdown {
    display: none;
    position: fixed;
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    min-width: 170px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
    z-index: 9999;
    overflow: hidden;
    animation: fadeIn 0.2s ease-out;
}
.action-dropdown.show {
    display: block;
}
.action-dropdown a, .action-dropdown button {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 15px;
    color: #fff;
    text-decoration: none;
    background: transparent;
    border: none;
    font-size: 13px;
    text-align: left;
    cursor: pointer;
    transition: background 0.2s;
}
.action-dropdown a:hover, .action-dropdown button:hover {
    background: rgba(255,255,255,0.05);
}
.action-dropdown a i, .action-dropdown button i {
    color: var(--text-muted);
    width: 16px;
    text-align: center;
}
.action-dropdown a:hover i, .action-dropdown button:hover i {
    color: var(--primary);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endsection

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Invoice & Credit Note Management</h3>
        </div>

        <form id="filterForm" method="GET" action="" style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05);">
            <div class="grid-4" style="gap: 15px; margin-bottom: 15px;">
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Search</label>
                    <input type="text" name="search" id="searchInput" class="form-control" placeholder="ID, Dealer or Shop Name" value="{{ request('search') }}" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">City</label>
                    <select name="city_id[]" id="filterCity" class="form-control select2" multiple="multiple" style="width: 100%;">
                        <option value="all">Select All</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ in_array($city->id, (array)request('city_id')) ? 'selected' : '' }}>{{ $city->city }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Salesman</label>
                    <select name="salesman_id" id="filterSalesman" class="form-control select2" style="width: 100%;">
                        <option value="">All Salesmen</option>
                        @foreach($salesmen as $salesman)
                            <option value="{{ $salesman->id }}" {{ request('salesman_id') == $salesman->id ? 'selected' : '' }}>{{ $salesman->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Distributor</label>
                    <select name="dist_id" id="filterDistributor" class="form-control select2" style="width: 100%;">
                        <option value="">All Distributors</option>
                        @foreach($distributors as $dist)
                            <option value="{{ $dist->dist_id }}" {{ request('dist_id') == $dist->dist_id ? 'selected' : '' }}>{{ $dist->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid-4" style="gap: 15px; align-items: end;">
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Invoice Status</label>
                    <select name="invoice_status" id="filterInvoiceStatus" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;">
                        <option value="">All Invoices</option>
                        <option value="pending" {{ request('invoice_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="complete" {{ request('invoice_status') == 'complete' ? 'selected' : '' }}>Complete</option>
                        <option value="pending_credit_note" {{ request('invoice_status') == 'pending_credit_note' ? 'selected' : '' }}>Pending Credit Note</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Date Filter</label>
                    <select name="date_type" id="dateTypeSelect" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;" onchange="toggleDateInputs()">
                        <option value="">All Time</option>
                        <option value="individual" {{ request('date_type') == 'individual' ? 'selected' : '' }}>Individual Date</option>
                        <option value="range" {{ request('date_type') == 'range' ? 'selected' : '' }}>Date Range</option>
                    </select>
                </div>
                
                <div id="singleDateWrapper" style="display: {{ request('date_type') == 'individual' ? 'block' : 'none' }};">
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Select Date</label>
                    <input type="date" name="single_date" class="form-control" value="{{ request('single_date') }}" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: var(--text-muted);">
                </div>

                <div id="rangeDateWrapper1" style="display: {{ request('date_type') == 'range' ? 'block' : 'none' }};">
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: var(--text-muted);">
                </div>

                <div id="rangeDateWrapper2" style="display: {{ request('date_type') == 'range' ? 'block' : 'none' }};">
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: var(--text-muted);">
                </div>
            </div>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Dealer/Shop</th>
                        <th>Salesman</th>
                        <th>Distributor</th>
                        <th>Status</th>
                        <th>Invoice</th>
                        <th>Amount</th>
                        <th>Credit Note</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>
                                <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($order->member->name) }}', '{{ addslashes($order->member->email) }}', '{{ addslashes($order->member->mobile) }}', '{{ addslashes($order->member->ref_code ?? '') }}', 'Dealer', '{{ addslashes(preg_replace('/\r|\n/', ' ', $order->member->address ?? '')) }}', '{{ addslashes($order->member->shop ?? '') }}', '{{ addslashes($order->member->city->city ?? '') }}', '{{ addslashes($order->member->gst_no ?? '') }}', '{{ $order->member->discount_percent ?? '' }}', '{{ addslashes($order->member->salesman->name ?? '') }}', '{{ addslashes($distributors->firstWhere('dist_id', $order->member->dist_id)->name ?? $order->member->dist_id ?? '') }}')" style="font-weight: 500; color: #3b82f6; text-decoration: none;">
                                    {{ $order->member->shop ?? $order->member->name }}
                                </a>
                            </td>
                            <td>
                                @if(isset($order->member->salesman))
                                    <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($order->member->salesman->name) }}', '{{ addslashes($order->member->salesman->email) }}', '{{ addslashes($order->member->salesman->mobile) }}', '{{ addslashes($order->member->salesman->ref_code) }}', 'Salesman', '', '', '{{ addslashes($order->member->salesman->city->city ?? '') }}', '', '', '', '')" style="font-weight: 500; color: #3b82f6; text-decoration: none;">
                                        {{ $order->member->salesman->name }}
                                    </a>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($order->member->distributor))
                                    <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($order->member->distributor->name) }}', '{{ addslashes($order->member->distributor->email) }}', '{{ addslashes($order->member->distributor->mobile) }}', '{{ addslashes($order->member->distributor->dist_id) }}', 'Distributor', '{{ addslashes(preg_replace('/\r|\n/', ' ', $order->member->distributor->address ?? '')) }}', '', '{{ addslashes($order->member->distributor->city->city ?? '') }}', '', '', '', '')" style="font-weight: 500; color: #3b82f6; text-decoration: none;">
                                        {{ $order->member->distributor->name }}
                                    </a>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($order->status == 'Confirmed')
                                    <span class="badge badge-success">Confirmed</span>
                                @elseif($order->status == 'Out for Delivery')
                                    <span class="badge badge-primary" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6;">Out for Delivery</span>
                                @elseif($order->status == 'Delivered')
                                    <span class="badge badge-success">Delivered</span>
                                @elseif($order->status == 'Returned')
                                    <span class="badge badge-danger" style="background: rgba(239, 68, 68, 0.2); color: #ef4444;">Returned</span>
                                @elseif($order->status == 'Invoiced')
                                    <span class="badge badge-info" style="background: rgba(6, 182, 212, 0.2); color: #06b6d4;">Invoiced</span>
                                @else
                                    <span class="badge badge-warning">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($order->invoice)
                                    <strong>{{ $order->invoice->invoice_number }}</strong>
                                @else
                                    <span style="color: var(--text-muted); font-size: 13px;">Not Uploaded</span>
                                @endif
                            </td>
                            <td>
                                @if($order->invoice)
                                    <span style="font-weight: 600;">&#8377; {{ number_format($order->invoice->amount, 2) }}</span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 13px;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($order->creditNote)
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <strong>{{ $order->creditNote->credit_note_number }}</strong>
                                        @if($order->creditNote->note)
                                            <span style="font-size: 11px; color: var(--text-muted); line-height: 1.2; word-break: break-word;">{{ $order->creditNote->note }}</span>
                                        @endif
                                    </div>
                                @elseif($order->status == 'Returned')
                                    <span style="color: #ef4444; font-size: 13px; font-weight: 500;">Pending Credit Note</span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 13px;">-</span>
                                @endif
                            </td>
                            <td>
                                @php $role = session('role', 'Admin'); @endphp
                                @if($role == 'Admin' || $role == 'Account')
                                    <div class="action-menu-container">
                                        <button class="action-btn" onclick="toggleActionMenu(this, event)">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="action-dropdown">
                                            @if(!$order->invoice)
                                                <button type="button" onclick="openUploadInvoiceModal('{{ $order->id }}', '{{ $order->order_number }}')">
                                                    <i class="fas fa-file-invoice"></i> Upload Invoice
                                                </button>
                                            @endif
                                            @if($order->invoice)
                                                <a href="{{ asset('uploads/' . $order->invoice->file_path) }}" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> View Invoice PDF
                                                </a>
                                            @endif
                                            @if(in_array($order->status, ['Invoiced', 'Out for Delivery', 'Delivered']))
                                                <button type="button" onclick="markAsReturned('{{ $order->id }}', '{{ $order->order_number }}')">
                                                    <i class="fas fa-undo"></i> Mark as Returned
                                                </button>
                                            @endif
                                            @if($order->status == 'Returned' && !$order->creditNote)
                                                <button type="button" onclick="openUploadCreditNoteModal('{{ $order->id }}', '{{ $order->order_number }}')">
                                                    <i class="fas fa-file-signature"></i> Upload Credit Note
                                                </button>
                                            @endif
                                            @if($order->creditNote)
                                                <button type="button" onclick="viewCreditNote(
                                                    '{{ $order->creditNote->credit_note_number }}',
                                                    '{{ addslashes($order->creditNote->note ?? '') }}',
                                                    '{{ $order->creditNote->dealer_file_path ? asset('uploads/' . $order->creditNote->dealer_file_path) : '' }}',
                                                    '{{ $order->creditNote->distributor_file_path ? asset('uploads/' . $order->creditNote->distributor_file_path) : '' }}'
                                                )">
                                                    <i class="fas fa-eye" style="color: #ef4444;"></i> View Credit Note
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div id="paginationContainer" style="padding: 20px 0;">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

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

        <!-- Upload Invoice Modal -->
        <div id="uploadModal"
            style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(10px); overflow-y: auto; align-items: center; justify-content: center; padding: 20px;">
            <div class="card"
                style="width: 100%; max-width: 500px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: auto;">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Upload New Invoice</h3>
                    <div onclick="closeUploadModal()"
                        style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                    </div>
                </div>

                <form id="invoiceForm" onsubmit="event.preventDefault(); submitInvoice();">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Link
                            to Order</label>
                        <input type="text" id="invOrderNumberDisplay" class="form-control" readonly
                            style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #fff; cursor: not-allowed;">
                        <input type="hidden" id="invOrderId">
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Invoice
                            Number</label>
                        <input type="text" id="invNumber" class="form-control" placeholder="INV-2026-XXXX"
                            style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Invoice
                            Document (PDF/Image)</label>
                        <div style="border: 2px dashed rgba(255,255,255,0.1); border-radius: 12px; padding: 30px; text-align: center; background: rgba(255,255,255,0.02); cursor: pointer;"
                            onclick="document.getElementById('invoiceFile').click()">
                            <i class="fas fa-cloud-upload-alt"
                                style="font-size: 30px; color: var(--primary); margin-bottom: 10px;"></i>
                            <p id="fileNameDisplay" style="margin: 0; font-size: 13px; color: #cbd5e1;">Click to browse or drag
                                and drop invoice</p>
                            <input type="file" id="invoiceFile" style="display: none;" accept=".pdf,.jpg,.png" required
                                onchange="updateFileName(this)">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Invoice
                            Amount</label>
                        <input type="number" id="invAmount" class="form-control" placeholder="Enter amount..."
                            style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);" step="0.01"
                            required>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
                        <button type="button" class="btn glass" onclick="closeUploadModal()"
                            style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
                        <button type="submit" id="submitBtn" class="btn btn-primary"
                            style="padding: 12px 30px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Upload &
                            Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Upload Credit Note Modal -->
        <div id="creditNoteModal"
            style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(10px); overflow-y: auto; align-items: center; justify-content: center; padding: 20px;">
            <div class="card"
                style="width: 100%; max-width: 500px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: auto;">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Upload Credit Note</h3>
                    <div onclick="closeCreditNoteModal()"
                        style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                    </div>
                </div>

                <form id="creditNoteForm" onsubmit="event.preventDefault(); submitCreditNote();">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Link
                            to Order</label>
                        <input type="text" id="cnOrderNumberDisplay" class="form-control" readonly
                            style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #fff; cursor: not-allowed;">
                        <input type="hidden" id="cnOrderId">
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Credit Note</label>
                        <textarea id="cnNote" class="form-control" placeholder="Enter notes/remarks..."
                            style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); height: 90px; color: #fff; width: 100%; border-radius: 6px; padding: 10px; resize: vertical;" required></textarea>
                    </div>

                    <!-- Dealer Document -->
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-user" style="color: #ef4444; margin-right: 5px;"></i> Dealer Document (PDF/Image)
                        </label>
                        <div style="border: 2px dashed rgba(239,68,68,0.3); border-radius: 12px; padding: 20px; text-align: center; background: rgba(239,68,68,0.03); cursor: pointer;"
                            onclick="document.getElementById('dealerFile').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 24px; color: #ef4444; margin-bottom: 8px;"></i>
                            <p id="dealerFileNameDisplay" style="margin: 0; font-size: 13px; color: #cbd5e1;">Click to browse dealer credit note</p>
                            <input type="file" id="dealerFile" style="display: none;" accept=".pdf,.jpg,.png" onchange="updateDealerFileName(this)">
                        </div>
                    </div>

                    <!-- Distributor Document -->
                    <div class="form-group" style="margin-bottom: 30px;">
                        <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-truck" style="color: #f59e0b; margin-right: 5px;"></i> Distributor Document (PDF/Image)
                        </label>
                        <div style="border: 2px dashed rgba(245,158,11,0.3); border-radius: 12px; padding: 20px; text-align: center; background: rgba(245,158,11,0.03); cursor: pointer;"
                            onclick="document.getElementById('distributorFile').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 24px; color: #f59e0b; margin-bottom: 8px;"></i>
                            <p id="distributorFileNameDisplay" style="margin: 0; font-size: 13px; color: #cbd5e1;">Click to browse distributor credit note</p>
                            <input type="file" id="distributorFile" style="display: none;" accept=".pdf,.jpg,.png" onchange="updateDistributorFileName(this)">
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
                        <button type="button" class="btn glass" onclick="closeCreditNoteModal()"
                            style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
                        <button type="submit" id="cnSubmitBtn" class="btn btn-primary"
                            style="padding: 12px 30px; background: #ef4444; border-color: #ef4444; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3);">Upload &
                            Save</button>
                    </div>
                </form>
            </div>
        </div>

        <style>
            @keyframes modalIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .form-control:focus {
                outline: none;
                border-color: var(--primary);
            }
        </style>

        <!-- View Credit Note Modal -->
        <div id="viewCreditNoteModal"
            style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(10px); overflow-y: auto; align-items: flex-start; justify-content: center; padding: 40px 20px;">
            <div class="card"
                style="width: 100%; max-width: 520px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6); animation: modalIn 0.3s ease-out; margin: auto;">
                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                    <div>
                        <h3 style="margin: 0 0 4px; font-size: 20px; font-weight: 700;">Credit Note Details</h3>
                        <span id="vcnNumber" style="font-size: 13px; color: var(--text-muted); font-family: monospace;"></span>
                    </div>
                    <div onclick="closeViewCreditNoteModal()"
                        style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                    </div>
                </div>

                <!-- Note / Remarks -->
                <div style="margin-bottom: 24px;">
                    <label style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 8px;">Credit Note Remarks</label>
                    <div id="vcnNote" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 14px 16px; color: #e2e8f0; font-size: 14px; line-height: 1.6; min-height: 60px; white-space: pre-wrap;"></div>
                </div>

                <!-- Dealer Document -->
                <div id="vcnDealerSection" style="margin-bottom: 16px;">
                    <label style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 8px;">
                        <i class="fas fa-user" style="color: #ef4444; margin-right: 5px;"></i> Dealer Document
                    </label>
                    <a id="vcnDealerLink" href="#" target="_blank"
                        style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 18px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); border-radius: 10px; color: #ef4444; text-decoration: none; font-size: 13px; font-weight: 500; transition: background 0.2s;"
                        onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                        <i class="fas fa-file-pdf" style="font-size: 18px;"></i>
                        <span>Open Dealer Credit Note PDF</span>
                        <i class="fas fa-external-link-alt" style="font-size: 11px; opacity: 0.6;"></i>
                    </a>
                </div>

                <!-- Distributor Document -->
                <div id="vcnDistributorSection" style="margin-bottom: 28px;">
                    <label style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 8px;">
                        <i class="fas fa-truck" style="color: #f59e0b; margin-right: 5px;"></i> Distributor Document
                    </label>
                    <a id="vcnDistributorLink" href="#" target="_blank"
                        style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 18px; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.25); border-radius: 10px; color: #f59e0b; text-decoration: none; font-size: 13px; font-weight: 500; transition: background 0.2s;"
                        onmouseover="this.style.background='rgba(245,158,11,0.2)'" onmouseout="this.style.background='rgba(245,158,11,0.1)'">
                        <i class="fas fa-file-pdf" style="font-size: 18px;"></i>
                        <span>Open Distributor Credit Note PDF</span>
                        <i class="fas fa-external-link-alt" style="font-size: 11px; opacity: 0.6;"></i>
                    </a>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="button" class="btn glass" onclick="closeViewCreditNoteModal()"
                        style="border: none; background: rgba(255,255,255,0.05);">Close</button>
                </div>
            </div>
        </div>
    @endpush

@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filterCity').select2({
                placeholder: "Select Cities",
                width: '100%',
                closeOnSelect: false,
                templateResult: function(state) {
                    if (!state.id) { return state.text; }
                    return $('<span><input type="checkbox" ' + (state.selected ? 'checked' : '') + ' style="margin-right:8px; pointer-events:none; accent-color: var(--primary);" /> ' + state.text + '</span>');
                }
            });

            function syncCheckboxes() {
                setTimeout(function() {
                    var selectedVals = $('#filterCity').val() || [];
                    var selectedTexts = [];
                    $('#filterCity option:selected').each(function() {
                        selectedTexts.push($(this).text().trim());
                    });

                    $('#filterCity').data('select2').$results.find('.select2-results__option').each(function() {
                        var text = $(this).text().trim();
                        var $cb = $(this).find('input[type="checkbox"]');
                        if (text === 'Select All') {
                            var totalOptions = $('#filterCity option').length - 1;
                            $cb.prop('checked', selectedVals.length === totalOptions && totalOptions > 0);
                        } else {
                            $cb.prop('checked', selectedTexts.includes(text));
                        }
                    });
                }, 50);
            }

            $('#filterCity').on('select2:selecting', function (e) {
                if (e.params.args.data.id === 'all') {
                    e.preventDefault();
                    var $this = $(this);
                    var currentVals = $this.val() || [];
                    var totalOptions = $this.find('option').length - 1;
                    
                    if (currentVals.length >= totalOptions && totalOptions > 0) {
                        $this.val([]).trigger('change');
                    } else {
                        var allVals = [];
                        $this.find('option').each(function() {
                            if ($(this).val() && $(this).val() !== 'all') {
                                allVals.push($(this).val());
                            }
                        });
                        $this.val(allVals).trigger('change');
                    }
                    syncCheckboxes();
                }
            });

            $('#filterCity').on('select2:select select2:unselect', function (e) {
                syncCheckboxes();
            });

            $('#filterCity').on('select2:open', function() {
                syncCheckboxes();
            });

            function updatePlaceholder() {
                var count = $('#filterCity').val() ? $('#filterCity').val().length : 0;
                var $container = $('#filterCity').next('.select2-container');
                
                if (count > 0) {
                    $container.addClass('has-multiple');
                    $container[0].style.setProperty('--selected-text', '"' + count + (count === 1 ? ' city' : ' cities') + ' selected"');
                } else {
                    $container.removeClass('has-multiple');
                }
            }

            $('#filterCity').on('change select2:close', function() {
                updatePlaceholder();
                
                // Fetch dependent salesmen and distributors
                var selectedCities = $(this).val();
                
                // Preserve current selections
                var currentSalesman = $('#filterSalesman').val();
                var currentDistributor = $('#filterDistributor').val();

                $.ajax({
                    url: '/api/dependent-members',
                    method: 'GET',
                    data: { city_ids: selectedCities },
                    success: function(response) {
                        // Update Salesman dropdown
                        var salesmanSelect = $('#filterSalesman');
                        salesmanSelect.empty().append('<option value="">All Salesmen</option>');
                        response.salesmen.forEach(function(salesman) {
                            var selected = (currentSalesman == salesman.id) ? 'selected' : '';
                            salesmanSelect.append('<option value="' + salesman.id + '" ' + selected + '>' + salesman.name + '</option>');
                        });
                        salesmanSelect.trigger('change.select2');

                        // Update Distributor dropdown
                        var distSelect = $('#filterDistributor');
                        distSelect.empty().append('<option value="">All Distributors</option>');
                        response.distributors.forEach(function(dist) {
                            var selected = (currentDistributor == dist.dist_id) ? 'selected' : '';
                            distSelect.append('<option value="' + dist.dist_id + '" ' + selected + '>' + dist.name + '</option>');
                        });
                        distSelect.trigger('change.select2');
                    }
                });
            });

            // Initialize other select2
            $('#filterSalesman').select2({
                placeholder: "All Salesmen",
                allowClear: true
            });
            $('#filterDistributor').select2({
                placeholder: "All Distributors",
                allowClear: true
            });
            
            // Initial setup
            updatePlaceholder();

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

            $('#searchInput').on('input', function() {
                applyFilters();
            });

            $('#dateTypeSelect, input[name="single_date"], input[name="date_from"], input[name="date_to"], #filterInvoiceStatus').on('change', function() {
                applyFilters();
            });

            $('#filterSalesman, #filterDistributor, #filterCity').on('change', function() {
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

        function toggleDateInputs() {
            var type = document.getElementById('dateTypeSelect').value;
            var single = document.getElementById('singleDateWrapper');
            var range1 = document.getElementById('rangeDateWrapper1');
            var range2 = document.getElementById('rangeDateWrapper2');

            single.style.display = 'none';
            range1.style.display = 'none';
            range2.style.display = 'none';

            if (type === 'individual') {
                single.style.display = 'block';
            } else if (type === 'range') {
                range1.style.display = 'block';
                range2.style.display = 'block';
            }
        }
        function viewMemberDetails(name, email, mobile, code, role, address, shop, city, gst, discount, salesman, distributor) {
            document.getElementById('memberModalTitle').innerHTML = '<i class="fas fa-user-circle" style="color: var(--primary); font-size: 24px;"></i> <span>' + role + ' Details</span>';
            document.getElementById('memberModalName').innerText = name || 'N/A';
            document.getElementById('memberModalEmail').innerText = email || 'N/A';
            document.getElementById('memberModalPhone').innerText = mobile || 'N/A';
            document.getElementById('memberModalCode').innerText = code || 'N/A';
            
            // Toggle visibility for optional fields
            const toggleField = (id, value) => {
                const container = document.getElementById(id + 'Container');
                if (container) {
                    if (value && value.trim() !== '') {
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

        // Action Menu Toggle Logic
        function toggleActionMenu(btn, event) {
            event.stopPropagation();
            
            const isShowing = btn.classList.contains('active');
            
            // Close all menus first
            closeAllActionMenus();

            if (!isShowing) {
                // The dropdown is the next element sibling of the button in the HTML
                const dropdown = btn.nextElementSibling;
                if (!dropdown || !dropdown.classList.contains('action-dropdown')) return;

                dropdown.classList.add('show');
                btn.classList.add('active');
                
                // Move to body to avoid clipping and stacking context issues
                document.body.appendChild(dropdown);
                dropdown.btnRef = btn; // Save reference to move it back later
                
                // Calculate position based on button
                const btnRect = btn.getBoundingClientRect();
                const dropdownRect = dropdown.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                
                // Align right edge of dropdown with right edge of button
                let leftPos = btnRect.right - dropdownRect.width;
                if (leftPos < 0) leftPos = 10;
                dropdown.style.left = leftPos + 'px';
                
                // Position above or below depending on available space
                if (btnRect.bottom + dropdownRect.height > windowHeight && btnRect.top > dropdownRect.height) {
                    // Position above
                    dropdown.style.top = (btnRect.top - dropdownRect.height - 5) + 'px';
                } else {
                    // Position below
                    dropdown.style.top = (btnRect.bottom + 5) + 'px';
                }
            }
        }

        // Close action menus when clicking anywhere else, OR scrolling
        document.addEventListener('click', closeAllActionMenus);
        document.addEventListener('scroll', closeAllActionMenus, true); // Use capture phase to catch scrolls in table

        function closeAllActionMenus(event) {
            // Ignore clicks that are exactly on an action button (toggleActionMenu handles it)
            if (event && event.type === 'click' && event.target.closest('.action-btn')) {
                return; 
            }

            document.querySelectorAll('body > .action-dropdown.show').forEach(function(menu) {
                menu.classList.remove('show');
                if (menu.btnRef) {
                    menu.btnRef.classList.remove('active');
                    // Move it back to its original parent so Laravel logic deletes it properly if reloaded/modified
                    menu.btnRef.parentNode.appendChild(menu);
                    menu.btnRef = null;
                }
            });
        }

        function openUploadInvoiceModal(orderId, orderNumber) {
            document.getElementById('invOrderId').value = orderId;
            document.getElementById('invOrderNumberDisplay').value = orderNumber;
            
            // Reset fields
            document.getElementById('invNumber').value = '';
            document.getElementById('invAmount').value = '';
            document.getElementById('invoiceFile').value = '';
            document.getElementById('fileNameDisplay').innerText = 'Click to browse or drag and drop invoice';
            document.getElementById('fileNameDisplay').style.color = '#cbd5e1';

            document.getElementById('uploadModal').style.display = 'flex';
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
        }

        function openUploadCreditNoteModal(orderId, orderNumber) {
            document.getElementById('cnOrderId').value = orderId;
            document.getElementById('cnOrderNumberDisplay').value = orderNumber;

            // Reset fields
            document.getElementById('cnNote').value = '';
            document.getElementById('dealerFile').value = '';
            document.getElementById('distributorFile').value = '';
            document.getElementById('dealerFileNameDisplay').innerText = 'Click to browse dealer credit note';
            document.getElementById('dealerFileNameDisplay').style.color = '#cbd5e1';
            document.getElementById('distributorFileNameDisplay').innerText = 'Click to browse distributor credit note';
            document.getElementById('distributorFileNameDisplay').style.color = '#cbd5e1';

            document.getElementById('creditNoteModal').style.display = 'flex';
        }

        function closeCreditNoteModal() {
            document.getElementById('creditNoteModal').style.display = 'none';
        }

        function updateFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('fileNameDisplay').innerText = input.files[0].name;
                document.getElementById('fileNameDisplay').style.color = 'var(--primary)';
            }
        }

        function updateDealerFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('dealerFileNameDisplay').innerText = input.files[0].name;
                document.getElementById('dealerFileNameDisplay').style.color = '#ef4444';
            }
        }

        function updateDistributorFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('distributorFileNameDisplay').innerText = input.files[0].name;
                document.getElementById('distributorFileNameDisplay').style.color = '#f59e0b';
            }
        }

        function markAsReturned(orderId, orderNumber) {
            if (confirm(`Are you sure you want to mark order ${orderNumber} as Returned?`)) {
                const url = '{{ url("/orders") }}/' + orderId + '/mark-returned';
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Something went wrong!');
                });
            }
        }

        function submitInvoice() {
            const submitBtn = document.getElementById('submitBtn');
            const formData = new FormData();

            formData.append('order_id', document.getElementById('invOrderId').value);
            formData.append('invoice_number', document.getElementById('invNumber').value);
            formData.append('amount', document.getElementById('invAmount').value);
            formData.append('invoice_file', document.getElementById('invoiceFile').files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

            fetch('{{ route('invoices.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error'));
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Upload & Save';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong!');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Upload & Save';
                });
        }

        function submitCreditNote() {
            const submitBtn = document.getElementById('cnSubmitBtn');
            const formData = new FormData();

            const dealerFile = document.getElementById('dealerFile').files[0];
            const distributorFile = document.getElementById('distributorFile').files[0];

            if (!dealerFile && !distributorFile) {
                alert('Please upload at least one document (Dealer or Distributor).');
                return;
            }

            formData.append('order_id', document.getElementById('cnOrderId').value);
            formData.append('note', document.getElementById('cnNote').value);
            if (dealerFile) formData.append('dealer_file', dealerFile);
            if (distributorFile) formData.append('distributor_file', distributorFile);
            formData.append('_token', '{{ csrf_token() }}');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

            fetch('{{ route('credit-notes.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error'));
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Upload & Save';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong!');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Upload & Save';
                });
        }

        window.onclick = function (event) {
            const modal = document.getElementById('uploadModal');
            const cnModal = document.getElementById('creditNoteModal');
            if (event.target == modal) {
                closeUploadModal();
            } else if (event.target == cnModal) {
                closeCreditNoteModal();
            }
        }

        function viewCreditNote(number, note, dealerPath, distributorPath) {
            document.getElementById('vcnNumber').innerText = '#' + number;
            document.getElementById('vcnNote').innerText = note || 'No remarks provided.';
            
            const dealerSection = document.getElementById('vcnDealerSection');
            const dealerLink = document.getElementById('vcnDealerLink');
            if (dealerPath) {
                dealerSection.style.display = 'block';
                dealerLink.href = dealerPath;
            } else {
                dealerSection.style.display = 'none';
            }

            const distSection = document.getElementById('vcnDistributorSection');
            const distLink = document.getElementById('vcnDistributorLink');
            if (distributorPath) {
                distSection.style.display = 'block';
                distLink.href = distributorPath;
            } else {
                distSection.style.display = 'none';
            }

            document.getElementById('viewCreditNoteModal').style.display = 'flex';
        }

        function closeViewCreditNoteModal() {
            document.getElementById('viewCreditNoteModal').style.display = 'none';
        }
    </script>
@endsection