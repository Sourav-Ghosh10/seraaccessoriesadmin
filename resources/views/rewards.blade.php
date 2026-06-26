@extends('layouts.app')

@section('title', 'Reward Points')

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
.action-dropdown button {
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
.action-dropdown button:hover {
    background: rgba(255,255,255,0.05);
}
.action-dropdown button i {
    color: var(--text-muted);
    width: 16px;
    text-align: center;
}
.action-dropdown button:hover i {
    color: var(--primary);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.form-control:focus { outline: none; border-color: var(--primary); }
</style>
@endsection

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Reward Points Management</h3>
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

    <div id="statsGrid" class="grid">
        <div class="card">
            <h4>Shop Points</h4>
            <div style="font-size: 24px; font-weight: 700; color: var(--primary); margin-top: 10px;">
                {{ number_format($dealerPointsSum) }} pts
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">Total distributed points</div>
        </div>
        <div class="card">
            <h4>Salesman Points</h4>
            <div style="font-size: 24px; font-weight: 700; color: var(--secondary); margin-top: 10px;">
                {{ number_format($salesmanPointsSum) }} pts
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">Total distributed points</div>
        </div>
    </div>

    <div class="table-container" style="margin-top: 30px;">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Shop Name</th>
                    <th>Salesman</th>
                    <th>Shop Points</th>
                    <th>Salesman Points</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $order)
                @php
                    $dealerPoints = $order->rewardTransactions->filter(function($tx) {
                        return $tx->member->role == 'dealer';
                    })->sum('points');
                    
                    $salesmanPoints = $order->rewardTransactions->filter(function($tx) {
                        return $tx->member->role == 'salesman';
                    })->sum('points');
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: 600;">
                            <a href="{{ route('orders.show', $order->id) }}" style="color: #3b82f6; text-decoration: none;">{{ $order->order_number }}</a>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $order->created_at->format('Y-m-d') }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 500;">
                            <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($order->member->name) }}', '{{ addslashes($order->member->email) }}', '{{ addslashes($order->member->mobile) }}', '{{ addslashes($order->member->ref_code ?? '') }}', 'Dealer', '{{ addslashes(preg_replace('/\r|\n/', ' ', $order->member->address ?? '')) }}', '{{ addslashes($order->member->shop ?? '') }}', '{{ addslashes($order->member->city->city ?? '') }}', '{{ addslashes($order->member->gst_no ?? '') }}', '{{ $order->member->discount_percent ?? '' }}', '{{ addslashes($order->member->salesman->name ?? '') }}', '{{ addslashes($distributors->firstWhere('dist_id', $order->member->dist_id)->name ?? $order->member->dist_id ?? '') }}')" style="font-weight: 600; color: #3b82f6; text-decoration: none; border-bottom: 1px dashed rgba(59, 130, 246, 0.3);">
                                {{ $order->member->shop ?? $order->member->name }}
                            </a>
                        </div>
                        @if($order->member->shop)
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $order->member->name }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 500;">
                            @if(isset($order->member->salesman))
                                <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($order->member->salesman->name) }}', '{{ addslashes($order->member->salesman->email) }}', '{{ addslashes($order->member->salesman->mobile) }}', '{{ addslashes($order->member->salesman->ref_code) }}', 'Salesman', '', '', '{{ addslashes($order->member->salesman->city->city ?? '') }}', '', '', '', '')" style="font-weight: 600; color: #3b82f6; text-decoration: none; border-bottom: 1px dashed rgba(59, 130, 246, 0.3);">
                                    {{ $order->member->salesman->name }}
                                </a>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span style="color: var(--primary); font-weight: 600;">+{{ number_format($dealerPoints) }}</span>
                    </td>
                    <td>
                        <span style="color: var(--secondary); font-weight: 600;">+{{ number_format($salesmanPoints) }}</span>
                    </td>
                    <td>
                        <div class="action-menu-container">
                            <button class="action-btn" onclick="toggleActionMenu(this, event)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="action-dropdown">
                                <button type="button" onclick="openAddPointsModal('{{ $order->id }}', {{ $dealerPoints }}, {{ $salesmanPoints }})">
                                    <i class="fas fa-{{ ($dealerPoints > 0 || $salesmanPoints > 0) ? 'eye' : 'plus-circle' }}"></i> {{ ($dealerPoints > 0 || $salesmanPoints > 0) ? 'View Points' : 'Reward Points' }}
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">No orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div id="paginationContainer" style="padding: 20px 0;">
            {{ $history->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Add Points Modal -->
<div id="addPointsModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin-bottom: 50px; width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 22px; font-weight: 700;">Add Reward Points</h3>
            <div onclick="closeAddPointsModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 25px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Order Number</label>
            <select id="orderNumber" class="form-control" onchange="autoFillOrderDetails()" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                <!-- Populated by JS -->
            </select>
        </div>

        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Shop Name</label>
                <input type="text" id="dealerName" class="form-control" readonly style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: var(--text-muted);">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Shop Point</label>
                <input type="number" id="dealerPoints" class="form-control" placeholder="0" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Salesman Name</label>
                <input type="text" id="salesmanName" class="form-control" readonly style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: var(--text-muted);">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Salesman Point</label>
                <input type="number" id="salesmanPoints" class="form-control" placeholder="0" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Unlock days count</label>
            <input type="number" id="unlockDaysCount" class="form-control" placeholder="0" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 40px;">
            <button class="btn glass" onclick="closeAddPointsModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 12px 30px;">Cancel</button>
            <button id="submitPointsBtn" class="btn btn-primary" onclick="submitPoints()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Add Points</button>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const orders = @json($orders);

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
                        
                        var newStats = $(response).find('#statsGrid').html();
                        $('#statsGrid').html(newStats);
                    }
                });
            }, 300);
        }

        $('#searchInput').on('input', function() {
            applyFilters();
        });

        $('#dateTypeSelect, input[name="single_date"], input[name="date_from"], input[name="date_to"]').on('change', function() {
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

    function toggleActionMenu(btn, event) {
        event.stopPropagation();
        const isShowing = btn.classList.contains('active');
        closeAllActionMenus();

        if (!isShowing) {
            const dropdown = btn.nextElementSibling;
            dropdown.classList.add('show');
            btn.classList.add('active');
            
            document.body.appendChild(dropdown);
            dropdown.btnRef = btn;
            
            const btnRect = btn.getBoundingClientRect();
            const dropdownRect = dropdown.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            
            let leftPos = btnRect.right - dropdownRect.width;
            if (leftPos < 0) leftPos = 10;
            dropdown.style.left = leftPos + 'px';
            
            if (btnRect.bottom + dropdownRect.height > windowHeight && btnRect.top > dropdownRect.height) {
                dropdown.style.top = (btnRect.top - dropdownRect.height - 5) + 'px';
            } else {
                dropdown.style.top = (btnRect.bottom + 5) + 'px';
            }
        }
    }

    function closeAllActionMenus(event) {
        if (event && event.type === 'click' && event.target.closest('.action-btn')) {
            return; 
        }
        document.querySelectorAll('body > .action-dropdown.show').forEach(function(menu) {
            menu.classList.remove('show');
            if (menu.btnRef) {
                menu.btnRef.classList.remove('active');
                menu.btnRef.parentNode.appendChild(menu);
                menu.btnRef = null;
            }
        });
    }

    document.addEventListener('click', closeAllActionMenus);
    document.addEventListener('scroll', closeAllActionMenus, true);

    function openAddPointsModal(orderId, dPoints = 0, sPoints = 0) {
        document.getElementById('addPointsModal').style.display = 'flex';
        populateOrders();
        resetFields();
        
        const dInput = document.getElementById('dealerPoints');
        const sInput = document.getElementById('salesmanPoints');
        const submitBtn = document.getElementById('submitPointsBtn');
        const modalTitle = document.querySelector('#addPointsModal h3');
        
        if (orderId) {
            document.getElementById('orderNumber').value = orderId;
            autoFillOrderDetails();
            // Optional: disable the select to prevent changing order
            document.getElementById('orderNumber').style.pointerEvents = 'none';
            document.getElementById('orderNumber').style.opacity = '0.7';

            if (dPoints > 0 || sPoints > 0) {
                dInput.value = dPoints;
                sInput.value = sPoints;
                dInput.readOnly = true;
                sInput.readOnly = true;
                dInput.style.background = 'rgba(255,255,255,0.01)';
                sInput.style.background = 'rgba(255,255,255,0.01)';
                if(submitBtn) submitBtn.style.display = 'none';
                if(modalTitle) modalTitle.innerText = 'View Reward Points';
            } else {
                dInput.readOnly = false;
                sInput.readOnly = false;
                dInput.style.background = 'rgba(255,255,255,0.03)';
                sInput.style.background = 'rgba(255,255,255,0.03)';
                if(submitBtn) submitBtn.style.display = 'inline-block';
                if(modalTitle) modalTitle.innerText = 'Add Reward Points';
            }
        } else {
            document.getElementById('orderNumber').style.pointerEvents = 'auto';
            document.getElementById('orderNumber').style.opacity = '1';
            dInput.readOnly = false;
            sInput.readOnly = false;
            dInput.style.background = 'rgba(255,255,255,0.03)';
            sInput.style.background = 'rgba(255,255,255,0.03)';
            if(submitBtn) submitBtn.style.display = 'inline-block';
            if(modalTitle) modalTitle.innerText = 'Add Reward Points';
        }
    }

    function closeAddPointsModal() {
        document.getElementById('addPointsModal').style.display = 'none';
    }

    function resetFields() {
        document.getElementById('orderNumber').value = '';
        document.getElementById('dealerName').value = '';
        document.getElementById('dealerPoints').value = '';
        document.getElementById('salesmanName').value = '';
        document.getElementById('salesmanPoints').value = '';
    }

    function populateOrders() {
        const select = document.getElementById('orderNumber');
        select.innerHTML = '<option value="" style="background: #1e293b;">Select Order</option>';
        orders.forEach(ord => {
            const option = document.createElement('option');
            option.value = ord.id;
            option.text = ord.order_number;
            option.style.background = '#1e293b';
            select.appendChild(option);
        });
    }

    function autoFillOrderDetails() {
        const orderId = document.getElementById('orderNumber').value;
        const order = orders.find(o => String(o.id) === String(orderId));
        
        if (order) {
            document.getElementById('dealerName').value = order.dealer;
            document.getElementById('salesmanName').value = order.salesman;
        } else {
            document.getElementById('dealerName').value = '';
            document.getElementById('salesmanName').value = '';
        }
    }

    function submitPoints() {
        const orderId = document.getElementById('orderNumber').value;
        const dPoints = document.getElementById('dealerPoints').value;
        const sPoints = document.getElementById('salesmanPoints').value;
        const unlockDays = document.getElementById('unlockDaysCount').value;
        const submitBtn = document.querySelector('button[onclick="submitPoints()"]');

        if (!orderId || (!dPoints && !sPoints)) {
            alert('Please select an order and enter points');
            return;
        }

        const data = {
            order_id: orderId,
            dealer_points: dPoints || 0,
            salesman_points: sPoints || 0,
            unlock_days: unlockDays || null,
            _token: '{{ csrf_token() }}'
        };

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

        fetch(`${window.BASE_PATH}/rewards/store`, {
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
                submitBtn.innerHTML = 'Add Points';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Add Points';
        });
    }

    window.onclick = function(event) {
        if (event.target.id == 'addPointsModal') {
            closeAddPointsModal();
        }
        if (event.target.id == 'memberDetailsModal') {
            closeMemberModal();
        }
    }
</script>
@endsection
