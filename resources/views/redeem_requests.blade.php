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

/* Action Dropdown */
.action-dropdown {
    position: relative;
    display: inline-block;
}
.action-btn {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 5px;
    font-size: 16px;
    transition: color 0.2s;
}
.action-btn:hover {
    color: #fff;
}
.action-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: #0f172a;
    min-width: 160px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.5);
    border-radius: 8px;
    z-index: 100;
    border: 1px solid rgba(255,255,255,0.1);
    overflow: hidden;
}
.action-menu.show {
    display: block;
    animation: fadeIn 0.2s ease;
}
.action-menu a, .action-menu button {
    display: flex;
    align-items: center;
    width: 100%;
    text-align: left;
    padding: 10px 15px;
    color: #fff;
    text-decoration: none;
    font-size: 13px;
    background: none;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}
.action-menu a:hover, .action-menu button:hover {
    background: rgba(255,255,255,0.05);
}
.action-menu i {
    width: 16px;
    text-align: center;
    margin-right: 8px;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
@endsection

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Redeem Requests</h3>
        </div>

        <form id="filterForm" method="GET" action="" style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05);">
            <div class="grid-4" style="gap: 15px; margin-bottom: 15px;">
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Search</label>
                    <input type="text" name="search" id="searchInput" class="form-control" placeholder="ID, Member or Shop Name" value="{{ request('search') }}" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
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

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Member / Shop Name</th>
                        <th>Role</th>
                        <th>Total Earned Points</th>
                        <th>Requested Points</th>
                        <th>Credit Note</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #fff;">RDM-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td>
                                @if($req->member)
                                    <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($req->member->name ?? '') }}', '{{ addslashes($req->member->email ?? '') }}', '{{ addslashes($req->member->mobile ?? '') }}', '{{ addslashes($req->member->ref_code ?? $req->member->emp_id ?? '') }}', '{{ ucfirst($req->member->role ?? 'Member') }}', '{{ addslashes(preg_replace('/\r|\n/', ' ', $req->member->address ?? '')) }}', '{{ addslashes($req->member->shop ?? '') }}', '{{ addslashes($req->member->city->city ?? '') }}', '{{ addslashes($req->member->gst_no ?? '') }}', '{{ $req->member->discount_percent ?? '' }}', '{{ addslashes($req->member->salesman->name ?? '') }}', '{{ addslashes($distributors->firstWhere('dist_id', $req->member->dist_id)->name ?? $req->member->dist_id ?? '') }}')" style="font-weight: 500; color: #3b82f6; text-decoration: none;">
                                        {{ $req->member->shop ?? $req->member->name }}
                                    </a>
                                @else
                                    <span style="color: var(--text-muted);">Deleted Member</span>
                                @endif
                            </td>
                            <td>
                                <span style="color: #cbd5e1;">{{ ucfirst($req->member->role ?? 'N/A') }}</span>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #60a5fa;">{{ number_format($req->member->reward_transactions_sum_points ?? 0) }} pts</span>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #fbbf24;">{{ number_format($req->Points ?? 0) }} pts</span>
                            </td>
                            <td>
                                <span style="color: var(--text-muted);">{{ $req->Credit_note ? $req->Credit_note : '-' }}</span>
                            </td>
                            <td>
                                <div style="font-size: 12px; color: var(--text-muted);">
                                    {{ $req->created_at ? $req->created_at->format('d M, Y') : '-' }}
                                </div>
                            </td>
                            <td>
                                @if($req->status == 'Pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($req->status == 'Approved')
                                    <span class="badge badge-info" style="background: rgba(59,130,246,0.2); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 500;">Approved</span>
                                @elseif($req->status == 'Processed')
                                    <span class="badge badge-success">Processed</span>
                                @else
                                    <span class="badge badge-danger">{{ $req->status }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="action-btn" onclick="toggleActionMenu(this, event)">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="action-menu">
                                        <button data-id="{{ $req->id }}"
                                            data-points="{{ number_format($req->Points ?? 0) }}"
                                            data-total="{{ number_format($req->member->reward_transactions_sum_points ?? 0) }}"
                                            data-notes="{{ addslashes($req->notes ?? '') }}"
                                            data-credit="{{ addslashes($req->Credit_note ?? '') }}"
                                            data-sender="{{ addslashes($req->member->shop ?? $req->member->name ?? 'Unknown') }}"
                                            data-status="{{ $req->status }}"
                                            data-dealer-doc="{{ $req->dealer_file_path ? asset('uploads/' . $req->dealer_file_path) : '' }}"
                                            data-distributor-doc="{{ $req->distributor_file_path ? asset('uploads/' . $req->distributor_file_path) : '' }}"
                                            onclick="initViewRedeem(this)">
                                            <i class="fas fa-eye"></i> Manage / View
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 30px; color: var(--text-muted);">No redeem requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div id="paginationContainer" style="padding: 20px 0;">
                {{ $requests->appends(request()->query())->links() }}
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

        <!-- Redeem Manage Modal -->
        <div id="viewRedeemModal"
            style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(15px); align-items: center; justify-content: center;">
            <div class="card modal-content"
                style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); width: 500px; animation: modalIn 0.3s ease-out;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-coins" style="color: #fbbf24;"></i>
                        <span>Manage Redeem Request</span>
                    </h3>
                    <div onclick="closeRedeemModal()" style="cursor: pointer; color: var(--text-muted);"><i class="fas fa-times"></i></div>
                </div>

                <div style="background: rgba(255,255,255,0.02); border-radius: 15px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; gap: 15px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                        <span style="color: var(--text-muted); font-size: 13px;">Member Name:</span>
                        <strong id="redeemSenderName" style="color: #fff;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                        <span style="color: var(--text-muted); font-size: 13px;">Total Earned Points:</span>
                        <strong id="redeemTotalPoints" style="color: #60a5fa;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                        <span style="color: var(--text-muted); font-size: 13px;">Requested Redeem Points:</span>
                        <strong id="redeemReqPoints" style="color: #fbbf24; font-size: 16px;">-</strong>
                    </div>
                    <div>
                        <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Notes</label>
                        <div id="redeemNotes" style="color: #e2e8f0; font-size: 13px; margin-top: 4px; background: rgba(0,0,0,0.25); padding: 12px; border-radius: 8px; line-height: 1.4;">-</div>
                    </div>
                    <div>
                        <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Credit Note / Challan No.</label>
                        <input type="text" id="redeemCreditInput" class="form-control" style="margin-top: 4px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;" placeholder="Enter reference number...">
                    </div>
                    <div>
                        <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Update Status</label>
                        <select id="redeemStatusSelect" class="form-control" style="margin-top: 4px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Processed">Processed</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Dealer Document -->
                    <div class="form-group" style="margin-top: 10px;">
                        <label class="form-label" style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-user" style="color: #ef4444; margin-right: 5px;"></i> Dealer Document (PDF/Image)
                        </label>
                        <div id="existingRedeemDealerDoc" style="display: none; margin-bottom: 8px;">
                            <a id="redeemDealerDocLink" href="#" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; font-size: 12px; color: #ef4444; text-decoration: none; background: rgba(239,68,68,0.1); padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(239,68,68,0.2);">
                                <i class="fas fa-file-download"></i> View Uploaded Dealer Document
                            </a>
                        </div>
                        <div style="border: 2px dashed rgba(239,68,68,0.3); border-radius: 12px; padding: 18px; text-align: center; background: rgba(239,68,68,0.03); cursor: pointer;"
                            onclick="document.getElementById('redeemDealerFile').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 22px; color: #ef4444; margin-bottom: 6px;"></i>
                            <p id="redeemDealerFileNameDisplay" style="margin: 0; font-size: 13px; color: #cbd5e1;">Click to browse dealer credit note</p>
                            <input type="file" id="redeemDealerFile" style="display: none;" accept=".pdf,.jpg,.png" onchange="updateRedeemDealerFileName(this)">
                        </div>
                    </div>

                    <!-- Distributor Document -->
                    <div class="form-group" style="margin-top: 10px;">
                        <label class="form-label" style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-truck" style="color: #f59e0b; margin-right: 5px;"></i> Distributor Document (PDF/Image)
                        </label>
                        <div id="existingRedeemDistributorDoc" style="display: none; margin-bottom: 8px;">
                            <a id="redeemDistributorDocLink" href="#" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; font-size: 12px; color: #f59e0b; text-decoration: none; background: rgba(245,158,11,0.1); padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(245,158,11,0.2);">
                                <i class="fas fa-file-download"></i> View Uploaded Distributor Document
                            </a>
                        </div>
                        <div style="border: 2px dashed rgba(245,158,11,0.3); border-radius: 12px; padding: 18px; text-align: center; background: rgba(245,158,11,0.03); cursor: pointer;"
                            onclick="document.getElementById('redeemDistributorFile').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 22px; color: #f59e0b; margin-bottom: 6px;"></i>
                            <p id="redeemDistributorFileNameDisplay" style="margin: 0; font-size: 13px; color: #cbd5e1;">Click to browse distributor credit note</p>
                            <input type="file" id="redeemDistributorFile" style="display: none;" accept=".pdf,.jpg,.png" onchange="updateRedeemDistributorFileName(this)">
                        </div>
                    </div>
                </div>

                <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button class="btn glass" onclick="closeRedeemModal()">Cancel</button>
                    <button class="btn btn-primary" onclick="saveRedeemChanges()">Approved</button>
                </div>
            </div>
        </div>
    @endpush
@endsection

@section('scripts')
    <script>
        function toggleActionMenu(button, event) {
            event.stopPropagation();
            document.querySelectorAll('.action-menu.show').forEach(menu => {
                if (menu !== button.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });
            const menu = button.nextElementSibling;
            menu.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            document.querySelectorAll('.action-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        });

        function viewMemberDetails(name, email, mobile, code, role, address, shop, city, gst, discount, salesman, distributor) {
            document.getElementById('memberModalTitle').innerHTML = '<i class="fas fa-user-circle" style="color: var(--primary); font-size: 24px;"></i> <span>' + role + ' Details</span>';
            document.getElementById('memberModalName').innerText = name || 'N/A';
            document.getElementById('memberModalEmail').innerText = email || 'N/A';
            document.getElementById('memberModalPhone').innerText = mobile || 'N/A';
            document.getElementById('memberModalCode').innerText = code || 'N/A';
            
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

        let activeRedeemId = null;

        function initViewRedeem(btn) {
            activeRedeemId = btn.getAttribute('data-id');
            document.getElementById('redeemSenderName').innerText = btn.getAttribute('data-sender');
            document.getElementById('redeemTotalPoints').innerText = btn.getAttribute('data-total') + ' pts';
            document.getElementById('redeemReqPoints').innerText = btn.getAttribute('data-points') + ' pts';
            document.getElementById('redeemNotes').innerText = btn.getAttribute('data-notes') || 'No notes provided.';
            document.getElementById('redeemCreditInput').value = btn.getAttribute('data-credit') || '';
            document.getElementById('redeemStatusSelect').value = btn.getAttribute('data-status') || 'Pending';
            
            document.getElementById('redeemDealerFile').value = '';
            document.getElementById('redeemDistributorFile').value = '';
            document.getElementById('redeemDealerFileNameDisplay').innerText = 'Click to browse dealer credit note';
            document.getElementById('redeemDealerFileNameDisplay').style.color = '#cbd5e1';
            document.getElementById('redeemDistributorFileNameDisplay').innerText = 'Click to browse distributor credit note';
            document.getElementById('redeemDistributorFileNameDisplay').style.color = '#cbd5e1';

            const dealerDoc = btn.getAttribute('data-dealer-doc');
            const distDoc = btn.getAttribute('data-distributor-doc');
            
            if (dealerDoc) {
                document.getElementById('existingRedeemDealerDoc').style.display = 'block';
                document.getElementById('redeemDealerDocLink').href = dealerDoc;
            } else {
                document.getElementById('existingRedeemDealerDoc').style.display = 'none';
            }

            if (distDoc) {
                document.getElementById('existingRedeemDistributorDoc').style.display = 'block';
                document.getElementById('redeemDistributorDocLink').href = distDoc;
            } else {
                document.getElementById('existingRedeemDistributorDoc').style.display = 'none';
            }

            document.getElementById('viewRedeemModal').style.display = 'flex';
        }

        function closeRedeemModal() {
            document.getElementById('viewRedeemModal').style.display = 'none';
        }

        function updateRedeemDealerFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('redeemDealerFileNameDisplay').innerText = input.files[0].name;
                document.getElementById('redeemDealerFileNameDisplay').style.color = '#ef4444';
            }
        }

        function updateRedeemDistributorFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('redeemDistributorFileNameDisplay').innerText = input.files[0].name;
                document.getElementById('redeemDistributorFileNameDisplay').style.color = '#f59e0b';
            }
        }

        function saveRedeemChanges() {
            if (!activeRedeemId) return;
            
            const status = document.getElementById('redeemStatusSelect').value;
            const credit = document.getElementById('redeemCreditInput').value;
            
            const formData = new FormData();
            formData.append('status', status);
            formData.append('credit_note', credit);
            
            const dealerFile = document.getElementById('redeemDealerFile').files[0];
            const distFile = document.getElementById('redeemDistributorFile').files[0];
            
            if (dealerFile) formData.append('dealer_file', dealerFile);
            if (distFile) formData.append('distributor_file', distFile);

            fetch(`${window.BASE_PATH}/redeem-requests/${activeRedeemId}/status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error updating status');
                }
            })
            .catch(err => alert('Network error occurred'));
        }

        function toggleDateInputs() {
            const type = document.getElementById('dateTypeSelect').value;
            const single = document.getElementById('singleDateWrapper');
            const range1 = document.getElementById('rangeDateWrapper1');
            const range2 = document.getElementById('rangeDateWrapper2');

            single.style.display = type === 'individual' ? 'block' : 'none';
            range1.style.display = type === 'range' ? 'block' : 'none';
            range2.style.display = type === 'range' ? 'block' : 'none';
        }
    </script>
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
                
                var selectedCities = $(this).val();
                var currentSalesman = $('#filterSalesman').val();
                var currentDistributor = $('#filterDistributor').val();

                $.ajax({
                    url: `${window.BASE_PATH}/api/dependent-members`,
                    method: 'GET',
                    data: { city_ids: selectedCities },
                    success: function(response) {
                        var salesmanSelect = $('#filterSalesman');
                        salesmanSelect.empty().append('<option value="">All Salesmen</option>');
                        response.salesmen.forEach(function(salesman) {
                            var selected = (currentSalesman == salesman.id) ? 'selected' : '';
                            salesmanSelect.append('<option value="' + salesman.id + '" ' + selected + '>' + salesman.name + '</option>');
                        });
                        salesmanSelect.trigger('change.select2');

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

            $('#filterSalesman').select2({
                placeholder: "All Salesmen",
                allowClear: true
            });
            $('#filterDistributor').select2({
                placeholder: "All Distributors",
                allowClear: true
            });
            
            updatePlaceholder();

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

            $('#dateTypeSelect, input[name="single_date"], input[name="date_from"], input[name="date_to"]').on('change', function() {
                applyFilters();
            });

            $('#filterSalesman, #filterDistributor').on('change', function() {
                applyFilters();
            });

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
    </script>
@endsection
