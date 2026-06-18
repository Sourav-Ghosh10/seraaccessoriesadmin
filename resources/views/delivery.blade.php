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
</style>
@endsection

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Delivery Status Management</h3>
    </div>

    <!-- Filter Form -->
    <form id="filterForm" method="GET" action="" style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05);">
        <div class="grid-4" style="gap: 15px; margin-bottom: 15px;">
            <div>
                <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Search</label>
                <div class="search-bar glass" style="border: 1px solid var(--glass-border); padding: 5px 15px; border-radius: 8px;">
                    <i class="fas fa-search" style="color: var(--text-muted);"></i>
                    <input type="text" name="search" id="searchInput" placeholder="Dealer or Shop..." value="{{ request('search') }}" style="background: transparent; border: none; color: white; outline: none; width: 100%; height: 32px;">
                </div>
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
                <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Delivery Status</label>
                <select name="delivery_status" id="deliveryStatusSelect" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;">
                    <option value="">All Deliveries</option>
                    <option value="pending" {{ request('delivery_status') == 'pending' ? 'selected' : '' }}>Not yet to Delivery</option>
                    <option value="out_for_delivery" {{ request('delivery_status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                    <option value="delivered" {{ request('delivery_status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="returned" {{ request('delivery_status') == 'returned' ? 'selected' : '' }}>Returned</option>
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

            {{-- <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Filter</button>
                <a href="{{ route('delivery') }}" class="btn glass" style="flex: 1; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">Reset</a>
            </div> --}}
        </div>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Shop Name</th>
                    <th>Salesman</th>
                    <th>Distributor</th>
                    <th>Expected Delivery</th>
                    <th>Transport Details</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>
                        <a href="{{ route('orders.show', $order->id) }}" style="font-weight: 700; color: #3b82f6; text-decoration: none;">
                            {{ $order->order_number }}
                        </a>
                    </td>
                    <td>
                        <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($order->member->name) }}', '{{ addslashes($order->member->email) }}', '{{ addslashes($order->member->mobile) }}', '{{ addslashes($order->member->ref_code ?? '') }}', 'Dealer', '{{ addslashes(preg_replace('/\r|\n/', ' ', $order->member->address ?? '')) }}', '{{ addslashes($order->member->shop ?? '') }}', '{{ addslashes($order->member->city->city ?? '') }}', '{{ addslashes($order->member->gst_no ?? '') }}', '{{ $order->member->discount_percent ?? '' }}', '{{ addslashes($order->member->salesman->name ?? '') }}', '{{ addslashes($distributors->firstWhere('dist_id', $order->member->dist_id)->name ?? $order->member->dist_id ?? '') }}')" style="font-weight: 500; color: #3b82f6; text-decoration: none; border-bottom: 1px dashed rgba(59, 130, 246, 0.3);">
                            {{ $order->member->shop ?? $order->member->name }}
                        </a>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $order->member->city->city ?? '' }}</div>
                    </td>
                    <td>
                        @if(isset($order->member->salesman))
                            <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($order->member->salesman->name) }}', '{{ addslashes($order->member->salesman->email) }}', '{{ addslashes($order->member->salesman->mobile) }}', '{{ addslashes($order->member->salesman->ref_code) }}', 'Salesman', '', '', '{{ addslashes($order->member->salesman->city->city ?? '') }}', '', '', '', '')" style="font-weight: 500; color: #3b82f6; text-decoration: none; border-bottom: 1px dashed rgba(59, 130, 246, 0.3);">
                                {{ $order->member->salesman->name }}
                            </a>
                        @else
                            <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td>
                        @if(isset($order->member->distributor))
                            <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($order->member->distributor->name) }}', '{{ addslashes($order->member->distributor->email) }}', '{{ addslashes($order->member->distributor->mobile) }}', '{{ addslashes($order->member->distributor->dist_id) }}', 'Distributor', '{{ addslashes(preg_replace('/\r|\n/', ' ', $order->member->distributor->address ?? '')) }}', '', '{{ addslashes($order->member->distributor->city->city ?? '') }}', '', '', '', '')" style="font-weight: 500; color: #3b82f6; text-decoration: none; border-bottom: 1px dashed rgba(59, 130, 246, 0.3);">
                                {{ $order->member->distributor->name }}
                            </a>
                        @else
                            <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>

                    <td>
                        @if($order->delivery)
                            {{ \Carbon\Carbon::parse($order->delivery->expected_delivery_at)->format('d M, Y') }}
                            <div style="font-size: 11px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($order->delivery->expected_delivery_at)->format('h:i A') }}</div>
                        @else
                            <span style="color: var(--text-muted);">Not Scheduled</span>
                        @endif
                    </td>
                    <td>
                        @if($order->delivery)
                            <div style="font-size: 13px;">{{ $order->delivery->vehicle_no }} ({{ $order->delivery->vehicle_type }})</div>
                            <div style="font-size: 11px; color: var(--text-muted);">Driver: {{ $order->delivery->driver_phone }}</div>
                        @else
                            <span style="color: var(--text-muted);">No Details</span>
                        @endif
                    </td>
                    <td>
                        @if($order->status == 'Confirmed' || $order->status == 'Processing')
                            <span class="badge badge-warning">Confirmed</span>
                        @elseif($order->status == 'Invoiced')
                            <span class="badge badge-info" style="background: rgba(14, 165, 233, 0.2); color: #0ea5e9;">Invoiced</span>
                        @elseif($order->status == 'Out for Delivery')
                            <span class="badge badge-primary" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6;">Out for Delivery</span>
                        @elseif($order->status == 'Delivered')
                            <span class="badge badge-success">Delivered</span>
                        @elseif($order->status == 'Returned')
                            <span class="badge badge-danger">Returned</span>
                        @else
                            <span class="badge badge-secondary">{{ $order->status }}</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn glass" style="padding: 5px 12px; font-size: 11px;" 
                            onclick="openDeliveryModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ $order->delivery->vehicle_no ?? '' }}', '{{ $order->delivery->vehicle_type ?? '' }}', '{{ $order->delivery->driver_phone ?? '' }}', '{{ $order->delivery ? \Carbon\Carbon::parse($order->delivery->expected_delivery_at)->format('Y-m-d') : date('Y-m-d') }}', '{{ $order->delivery ? \Carbon\Carbon::parse($order->delivery->expected_delivery_at)->format('H:i') : date('H:i') }}', '{{ addslashes($order->delivery->remarks ?? '') }}')">
                            <i class="fas fa-edit"></i> Update
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div id="paginationContainer" style="padding: 20px 0;">
            {{ $orders->appends(request()->query())->links() }}
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
@endpush

@section('scripts')
<!-- Delivery Modal -->
<div id="deliveryModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin-bottom: 50px; width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Update Delivery Status</h3>
            <div onclick="closeModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <input type="hidden" id="realOrderId">
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Order Reference</label>
            <input type="text" id="modalOrderId" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); cursor: not-allowed;" readonly>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
            <div class="form-group">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Vehicle No</label>
                <input type="text" id="vehicleNo" class="form-control" placeholder="AR-01-XXXX" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
            <div class="form-group">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Vehicle Type</label>
                <input type="text" id="vehicleType" class="form-control" placeholder="e.g. Truck, Van" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Phone No</label>
            <input type="tel" id="phoneNo" class="form-control" placeholder="Enter phone number" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 15px; margin-bottom: 20px;">
            <div class="form-group">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Expected Delivery Date</label>
                <input type="date" class="form-control" id="deliveryDate" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
            <div class="form-group">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Time</label>
                <input type="time" class="form-control" id="deliveryTime" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Delivery Remarks</label>
            <textarea class="form-control" id="deliveryRemarks" style="height: 120px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); resize: none;" placeholder="Enter any specific delivery instructions or current status..."></textarea>
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
            <button class="btn glass" onclick="closeModal()" style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
            <button class="btn btn-primary" id="submitBtn" onclick="submitDelivery()" style="padding: 12px 30px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Submit Update</button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

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
            applyFilters();
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

        $('#dateTypeSelect, #deliveryStatusSelect, input[name="single_date"], input[name="date_from"], input[name="date_to"]').on('change', function() {
            applyFilters();
        });

        $('#filterSalesman, #filterDistributor').on('change', function() {
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

    function openDeliveryModal(id, orderNum, vNo, vType, phone, date, time, remarks) {
        document.getElementById('modalOrderId').value = orderNum;
        document.getElementById('realOrderId').value = id;
        document.getElementById('vehicleNo').value = vNo || '';
        document.getElementById('vehicleType').value = vType || '';
        document.getElementById('phoneNo').value = phone || '';
        document.getElementById('deliveryDate').value = date;
        document.getElementById('deliveryTime').value = time;
        document.getElementById('deliveryRemarks').value = remarks || '';
        
        document.getElementById('deliveryModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('deliveryModal').style.display = 'none';
    }

    function submitDelivery() {
        const id = document.getElementById('realOrderId').value;
        const submitBtn = document.getElementById('submitBtn');
        
        const data = {
            vehicle_no: document.getElementById('vehicleNo').value,
            vehicle_type: document.getElementById('vehicleType').value,
            driver_phone: document.getElementById('phoneNo').value,
            expected_delivery_date: document.getElementById('deliveryDate').value,
            expected_delivery_time: document.getElementById('deliveryTime').value,
            delivery_remarks: document.getElementById('deliveryRemarks').value,
            _token: '{{ csrf_token() }}'
        };

        if (!data.expected_delivery_date) {
            alert('Please select an expected delivery date.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        fetch(`${window.BASE_PATH}/orders/${id}/update-delivery`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Error: ' + (result.message || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Update';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit Update';
        });
    }

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

    // Close modal on click outside
    window.onclick = function(event) {
        const modal = document.getElementById('deliveryModal');
        const memberModal = document.getElementById('memberDetailsModal');
        if (event.target == modal) {
            closeModal();
        }
        if (event.target == memberModal) {
            closeMemberModal();
        }
    }
</script>
@endsection
