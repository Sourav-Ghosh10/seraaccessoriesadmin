@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--multiple {
    background-color: #1e293b !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 6px !important;
    height: 42px !important;
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
.select2-dropdown {
    background-color: #0f172a;
    border: 1px solid rgba(255,255,255,0.1);
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

/* Single Select Styling */
.select2-container--default .select2-selection--single {
    background-color: #1e293b !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 6px !important;
    height: 42px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff !important;
    line-height: 40px !important;
    padding-left: 14px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    right: 6px !important;
}
.select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: #0f172a;
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
}
.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    outline: none;
    border-color: var(--primary);
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
</style>
@endsection

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Dealer Registration</h3>
        <button class="btn btn-primary" onclick="openDealerModal()"><i class="fas fa-plus"></i> Register New Dealer</button>
    </div>
    <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
        <input type="text" id="filterSearch" class="form-control" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" placeholder="Search ID, Name, Shop, City..." style="width: 250px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);" oninput="filterTable()">
        
        <select id="filterSalesman" class="form-control" style="width: 180px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;" onchange="filterTable()">
            <option value="">All Salesmen</option>
            @foreach($salesmen as $salesman)
                <option value="{{ $salesman->id }}">{{ $salesman->name }}</option>
            @endforeach
        </select>
        
        <select id="filterDistributor" class="form-control" style="width: 180px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;" onchange="filterTable()">
            <option value="">All Distributors</option>
            @foreach($distributors as $dist)
                <option value="{{ $dist->dist_id }}">{{ $dist->name }}</option>
            @endforeach
        </select>
        
        <select id="filterCity" class="form-control" multiple="multiple" style="width: 200px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
            <option value="all">Select All</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}">{{ $city->city }}</option>
            @endforeach
        </select>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Dealer ID</th>
                    <th>Shop Name</th>
                    <th>Dealer Name</th>
                    <th>Mobile</th>
                    <th>City</th>
                    <th>Salesman</th>
                    <th>Distributor</th>
                    <th>Discount</th>
                    <th>Points</th>
                    <th>Passbook</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dealers as $dealer)
                <tr class="dealer-row" 
                    data-search="{{ strtolower($dealer->ref_code . ' ' . $dealer->name . ' ' . $dealer->shop . ' ' . ($dealer->city->city ?? '')) }}"
                    data-salesman="{{ $dealer->salesman_id }}"
                    data-distributor="{{ $dealer->dist_id }}"
                    data-city="{{ $dealer->city_id }}">
                    <td><code>{{ $dealer->ref_code ?? '—' }}</code></td>
                    <td>{{ $dealer->shop }}</td>
                    <td>{{ $dealer->name }}</td>
                    <td>{{ $dealer->mobile }}</td>
                    <td>{{ $dealer->city->city ?? '—' }}</td>
                    <td>{{ $dealer->salesman->name ?? '—' }}</td>
                    <td>{{ $distributors->firstWhere('dist_id', $dealer->dist_id)->name ?? ($dealer->dist_id ?: '—') }}</td>
                    <td>{{ $dealer->discount_percent ? $dealer->discount_percent.'%' : '—' }}</td>
                    <td style="color: var(--primary); font-weight: 600;">{{ number_format($dealer->points_balance) }}</td>
                    <td>
                        <label class="switch" style="position: relative; display: inline-block; width: 40px; height: 22px; margin: 0;">
                            <input type="checkbox" onchange="togglePassbookVisibility({{ $dealer->id }}, this.checked)" {{ $dealer->is_passbook_visible ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <td>
                        <span class="badge {{ $dealer->status == 'Active' ? 'badge-success' : ($dealer->status == 'Pending' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $dealer->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-menu-container">
                            <button class="action-btn" onclick="toggleActionMenu(this, event)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="action-dropdown">
                                <button type="button" onclick="openEditModal({{ json_encode($dealer) }})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" onclick="openEditPointsModal({{ json_encode($dealer) }}, {{ $dealer->points_balance ?? 0 }})">
                                    <i class="fas fa-star" style="color: #f59e0b;"></i> Edit Points
                                </button>
                                <button type="button" onclick="openViewModal({{ json_encode($dealer) }})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button type="button" style="color: #f87171;" onclick="deleteDealer({{ $dealer->id }}, '{{ addslashes($dealer->name) }}')">
                                    <i class="fas fa-trash" style="color: #f87171;"></i> Delete
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div id="paginationContainer" style="padding: 20px 0;">
            {{ $dealers->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Dealer Modal -->
<div id="dealerModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: 30px 0 50px 0; max-width: 720px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 22px; font-weight: 700;">Register New Dealer</h3>
            <div onclick="closeDealerModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>

        <div id="formFields">
            {{-- Row 1: ID + Name --}}
            <div class="grid-2" style="margin-bottom: 18px;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">ID (Manual Entry)</label>
                    <input type="text" id="dealerRefCode" class="form-control" placeholder="Dealer ID..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                    <p class="field-error" id="err-refcode"></p>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Dealer Name</label>
                    <input type="text" id="dealerName" class="form-control" placeholder="Full name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                    <p class="field-error" id="err-name"></p>
                </div>
            </div>

            {{-- Row 2: Shop Name + Mobile --}}
            <div class="grid-2" style="margin-bottom: 18px;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Shop Name</label>
                    <input type="text" id="shopName" class="form-control" placeholder="Business name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                    <p class="field-error" id="err-shop"></p>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Mobile Number</label>
                    <input type="tel" id="mobileNumber" class="form-control" placeholder="10 digit mobile..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                    <p class="field-error" id="err-mobile"></p>
                </div>
            </div>

            {{-- Row 3: Email + City --}}
            <div class="grid-2" style="margin-bottom: 18px;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Email Address</label>
                    <input type="email" id="emailAddress" class="form-control" placeholder="email@example.com" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                    <p class="field-error" id="err-email"></p>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">City</label>
                    <div class="city-select-wrapper" id="citySelectWrapper">
                        <div class="city-select-trigger" id="citySelectTrigger" onclick="toggleCityDropdown()">
                            <span id="citySelectText" style="color: rgba(255,255,255,0.4);">Select City</span>
                            <i class="fas fa-chevron-down" id="cityChevron" style="font-size: 11px; color: rgba(255,255,255,0.5); transition: transform 0.2s;"></i>
                        </div>
                        <div class="city-select-dropdown" id="citySelectDropdown">
                            <div class="city-search-box">
                                <i class="fas fa-search" style="color: rgba(255,255,255,0.3); font-size: 12px;"></i>
                                <input type="text" id="citySearchInput" placeholder="Search city..." oninput="filterCities()" onclick="event.stopPropagation()" autocomplete="off">
                            </div>
                            <div class="city-options-list" id="cityOptionsList">
                                <div class="city-option" data-value="" onclick="selectCity('', 'Select City')" style="color: rgba(255,255,255,0.4);">Select City</div>
                                @foreach($cities as $city)
                                <div class="city-option" data-value="{{ $city->id }}" data-label="{{ $city->city }}" onclick="selectCity('{{ $city->id }}', '{{ $city->city }}')">{{ $city->city }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="dealerCity" value="">
                    <p class="field-error" id="err-city"></p>
                </div>
            </div>

            {{-- Address --}}
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Full Address</label>
                <textarea id="fullAddress" class="form-control" style="height: 80px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); resize: none;" placeholder="Shop location details..."></textarea>
            </div>

            {{-- Row 4: GST + Discount --}}
            <div class="grid-2" style="margin-bottom: 18px;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">GST Number</label>
                    <input type="text" id="dealerGst" class="form-control" placeholder="22AAAAA0000A1Z5..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                    <p class="field-error" id="err-gst"></p>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Discount (%)</label>
                    <input type="number" id="dealerDiscount" class="form-control" placeholder="0.00" min="0" max="100" step="0.01" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                    <p class="field-error" id="err-discount"></p>
                </div>
            </div>

            {{-- Row 5: Status + Assign Salesman --}}
            <div class="grid-2" style="margin-bottom: 18px;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Status</label>
                    <select id="dealerStatus" class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                        <option value="Active" style="background: #1e293b;">Active</option>
                        <option value="Pending" style="background: #1e293b;">Pending</option>
                        <option value="Inactive" style="background: #1e293b;">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Assign Salesman</label>
                    <select id="assignSalesman" class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                        <option value="">Select Salesman</option>
                        @foreach($salesmen as $salesman)
                            <option value="{{ $salesman->id }}">{{ $salesman->name }} ({{ strtoupper($salesman->ref_code) }})</option>
                        @endforeach
                    </select>
                    <p class="field-error" id="err-salesman"></p>
                </div>
            </div>

            {{-- Row 6: Assign Distributor + Password --}}
            <div class="grid-2" style="margin-bottom: 18px;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Assign Distributor</label>
                    <select id="assignDistributor" class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                        <option value="">Select Distributor</option>
                        @foreach($distributors as $dist)
                            <option value="{{ $dist->dist_id }}">{{ $dist->name }} ({{ $dist->dist_id }})</option>
                        @endforeach
                    </select>
                    <p class="field-error" id="err-distributor"></p>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Password</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="password" id="dealerPassword" class="form-control" placeholder="Min. 6 characters" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); padding-right: 40px; width: 100%;">
                        <button type="button" onclick="togglePasswordVisibility()" style="position: absolute; right: 10px; background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 0 5px; outline: none;">
                            <i class="fas fa-eye" id="passwordEyeIcon"></i>
                        </button>
                    </div>
                    <p class="field-error" id="err-password"></p>
                </div>
            </div>

            {{-- Row 7: Passbook Visibility --}}
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Passbook Visibility</label>
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                    <label class="switch" style="position: relative; display: inline-block; width: 40px; height: 22px;">
                        <input type="checkbox" id="dealerPassbookVisible" checked style="opacity: 0; width: 0; height: 0;" onchange="document.getElementById('passbookVisibleLabel').innerText = this.checked ? 'Visible' : 'Hidden'">
                        <span class="slider round"></span>
                    </label>
                    <span id="passbookVisibleLabel" style="font-size: 14px; color: #fff;">Visible</span>
                </div>
            </div>
        </div>

        {{-- View-only container --}}
        <div id="viewContainer" style="display: none; animation: fadeIn 0.3s ease-out;">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding: 20px; background: rgba(255,255,255,0.02); border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: 700; color: #fff;">
                    <span id="initials">JD</span>
                </div>
                <div>
                    <h2 id="viewDealerName" style="margin: 0; font-size: 24px;"></h2>
                    <p id="viewShopName" style="margin: 5px 0 0 0; color: var(--primary); font-weight: 600;"></p>
                </div>
            </div>
            <div class="grid-2" style="gap: 20px;">
                <div><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">ID</p><p id="vRefCode" style="margin: 5px 0 0 0; font-size: 15px; font-weight: 600;"></p></div>
                <div><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Mobile</p><p id="vMobile" style="margin: 5px 0 0 0; font-size: 15px; font-weight: 600;"></p></div>
                <div><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Email</p><p id="vEmail" style="margin: 5px 0 0 0; font-size: 15px;"></p></div>
                <div><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">City</p><p id="vCity" style="margin: 5px 0 0 0; font-size: 15px;"></p></div>
                <div><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">GST Number</p><p id="vGst" style="margin: 5px 0 0 0; font-size: 15px;"></p></div>
                <div><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Discount</p><p id="vDiscount" style="margin: 5px 0 0 0; font-size: 15px;"></p></div>
                <div><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Salesman</p><p id="vSalesman" style="margin: 5px 0 0 0; font-size: 15px; color: var(--secondary);"></p></div>
                <div><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Distributor ID</p><p id="vDistributor" style="margin: 5px 0 0 0; font-size: 15px;"></p></div>
                <div style="grid-column: span 2;"><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Address</p><p id="vAddress" style="margin: 5px 0 0 0; font-size: 15px; line-height: 1.6;"></p></div>
                <div><p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Status</p><div id="vStatus" style="margin-top: 5px;"></div></div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <button class="btn glass" onclick="closeDealerModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 12px 30px;">Close</button>
            <button id="submitBtn" class="btn btn-primary" onclick="submitDealer()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Register Dealer</button>
        </div>
    </div>
</div>

<!-- Edit Points Modal -->
<div id="editPointsModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: 30px 0; max-width: 400px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Edit Points</h3>
            <div onclick="closeEditPointsModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Points Balance</label>
            <input type="number" id="quickEditPointsInput" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); font-size: 18px; font-weight: bold; color: var(--primary);">
            <p class="field-error" id="err-quick-points"></p>
        </div>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button class="btn glass" onclick="closeEditPointsModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 10px 20px;">Cancel</button>
            <button class="btn btn-primary" onclick="submitEditPoints()" style="padding: 10px 25px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Save Points</button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.form-control:focus { outline: none; border-color: var(--primary); }
.form-control:disabled { opacity: 0.7; cursor: not-allowed; background: rgba(255,255,255,0.05) !important; }

/* Switch Toggle CSS */
.switch .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 22px; }
.switch .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
.switch input:checked + .slider { background-color: var(--primary); }
.switch input:checked + .slider:before { transform: translateX(18px); }

/* Fix for browser autofill background in dark mode */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active{
    -webkit-box-shadow: 0 0 0 30px #1e293b inset !important;
    -webkit-text-fill-color: white !important;
    transition: background-color 5000s ease-in-out 0s;
}

/* Searchable City Dropdown */
.city-select-wrapper { position: relative; }
.city-select-trigger {
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 6px; padding: 10px 14px; cursor: pointer; min-height: 42px;
    transition: border-color 0.2s;
}
.city-select-trigger:hover { border-color: rgba(255,255,255,0.25); }
.city-select-trigger.open { border-color: var(--primary); }
.city-select-dropdown {
    display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: #1e293b; border: 1px solid rgba(255,255,255,0.15);
    border-radius: 8px; z-index: 10000; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    overflow: hidden;
}
.city-select-dropdown.open { display: block; }
.city-search-box {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 12px; border-bottom: 1px solid rgba(255,255,255,0.08);
}
.city-search-box input {
    background: transparent; border: none; outline: none;
    color: #fff; font-size: 13px; width: 100%;
}
.city-search-box input::placeholder { color: rgba(255,255,255,0.3); }
.city-options-list { max-height: 220px; overflow-y: auto; }
.city-option {
    padding: 9px 14px; font-size: 13px; color: #cbd5e1; cursor: pointer;
    transition: background 0.15s;
}
.city-option:hover { background: rgba(255,255,255,0.07); }
.city-option.selected { background: rgba(var(--primary-rgb, 154,90,58), 0.2); color: #fff; }
.city-option.hidden { display: none; }

/* Validation */
.field-error { color: #f87171; font-size: 11px; margin-top: 4px; display: none; }
.input-error { border-color: #f87171 !important; }
.city-select-trigger.input-error { border-color: #f87171 !important; }
</style>

@endpush

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let currentDealerId = null;

    function openDealerModal() {
        currentDealerId = null;
        resetForm();
        document.getElementById('modalTitle').innerText = 'Register New Dealer';
        document.getElementById('submitBtn').innerText = 'Register Dealer';
        document.getElementById('submitBtn').style.display = 'inline-block';
        document.getElementById('formFields').style.display = 'block';
        document.getElementById('viewContainer').style.display = 'none';
        document.getElementById('dealerModal').style.display = 'flex';
    }

    function openEditModal(dealer) {
        currentDealerId = dealer.id;
        resetForm();
        document.getElementById('modalTitle').innerText = 'Edit Dealer: ' + (dealer.ref_code || '#'+dealer.id);
        document.getElementById('submitBtn').innerText = 'Update Dealer';
        document.getElementById('submitBtn').style.display = 'inline-block';
        document.getElementById('formFields').style.display = 'block';
        document.getElementById('viewContainer').style.display = 'none';

        document.getElementById('dealerRefCode').value   = dealer.ref_code || '';
        document.getElementById('dealerName').value      = dealer.name || '';
        document.getElementById('shopName').value        = dealer.shop || '';
        document.getElementById('mobileNumber').value   = dealer.mobile || '';
        document.getElementById('emailAddress').value   = dealer.email || '';
        document.getElementById('dealerCity').value      = dealer.city_id || '';
        // Update the custom city dropdown display
        if (dealer.city_id && dealer.city) {
            selectCity(dealer.city_id, dealer.city.city || dealer.city);
        } else {
            resetCityDropdown();
        }
        document.getElementById('fullAddress').value     = dealer.address || '';
        document.getElementById('dealerGst').value       = dealer.gst_no || '';
        document.getElementById('dealerDiscount').value  = dealer.discount_percent || '';
        document.getElementById('dealerStatus').value    = dealer.status || 'Active';
        document.getElementById('assignSalesman').value  = dealer.salesman_id || '';
        document.getElementById('assignDistributor').value = dealer.dist_id || '';
        
        const isVisible = dealer.is_passbook_visible !== undefined ? dealer.is_passbook_visible : true;
        document.getElementById('dealerPassbookVisible').checked = isVisible;
        document.getElementById('passbookVisibleLabel').innerText = isVisible ? 'Visible' : 'Hidden';

        document.getElementById('dealerModal').style.display = 'flex';
    }

    function openViewModal(dealer) {
        resetForm();
        document.getElementById('modalTitle').innerText = 'Dealer Profile: ' + (dealer.ref_code || '#'+dealer.id);
        document.getElementById('submitBtn').style.display = 'none';
        document.getElementById('formFields').style.display = 'none';
        document.getElementById('viewContainer').style.display = 'block';

        document.getElementById('viewDealerName').innerText = dealer.name;
        document.getElementById('viewShopName').innerText   = dealer.shop || '';
        document.getElementById('initials').innerText       = dealer.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0,2);
        document.getElementById('vRefCode').innerText       = dealer.ref_code || ('# ' + dealer.id);
        document.getElementById('vMobile').innerText        = dealer.mobile || '—';
        document.getElementById('vEmail').innerText         = dealer.email || '—';
        document.getElementById('vCity').innerText          = dealer.city ? dealer.city.city : '—';
        document.getElementById('vGst').innerText           = dealer.gst_no || '—';
        document.getElementById('vDiscount').innerText      = dealer.discount_percent ? dealer.discount_percent + '%' : '—';
        document.getElementById('vSalesman').innerText      = dealer.salesman ? dealer.salesman.name : '—';
        document.getElementById('vDistributor').innerText   = dealer.dist_id || '—';
        document.getElementById('vAddress').innerText       = dealer.address || 'No address provided';

        const badgeClass = dealer.status === 'Active' ? 'badge-success' : (dealer.status === 'Pending' ? 'badge-warning' : 'badge-danger');
        document.getElementById('vStatus').innerHTML = `<span class="badge ${badgeClass}">${dealer.status}</span>`;

        document.getElementById('dealerModal').style.display = 'flex';
    }

    function resetForm() {
        const inputs = document.querySelectorAll('#formFields .form-control');
        inputs.forEach(input => {
            if (input.tagName === 'SELECT') { input.selectedIndex = 0; }
            else { input.value = ''; }
        });
        const pwdInput = document.getElementById('dealerPassword');
        const eyeIcon = document.getElementById('passwordEyeIcon');
        if (pwdInput) { pwdInput.type = 'password'; }
        if (eyeIcon) { eyeIcon.className = 'fas fa-eye'; }
        
        document.getElementById('dealerPassbookVisible').checked = true;
        document.getElementById('passbookVisibleLabel').innerText = 'Visible';
        
        resetCityDropdown();
    }

    // ---- Searchable City Dropdown ----
    function toggleCityDropdown() {
        const dropdown = document.getElementById('citySelectDropdown');
        const trigger  = document.getElementById('citySelectTrigger');
        const chevron  = document.getElementById('cityChevron');
        const isOpen   = dropdown.classList.contains('open');
        if (isOpen) {
            dropdown.classList.remove('open');
            trigger.classList.remove('open');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            dropdown.classList.add('open');
            trigger.classList.add('open');
            chevron.style.transform = 'rotate(180deg)';
            document.getElementById('citySearchInput').focus();
            // Reset search
            document.getElementById('citySearchInput').value = '';
            filterCities();
        }
    }

    function filterCities() {
        const q = document.getElementById('citySearchInput').value.toLowerCase();
        document.querySelectorAll('.city-option').forEach(opt => {
            const label = (opt.dataset.label || opt.innerText).toLowerCase();
            opt.classList.toggle('hidden', q !== '' && !label.includes(q));
        });
    }

    function selectCity(value, label) {
        document.getElementById('dealerCity').value = value;
        const textEl = document.getElementById('citySelectText');
        textEl.innerText = label || 'Select City';
        textEl.style.color = value ? '#fff' : 'rgba(255,255,255,0.4)';
        // Mark selected
        document.querySelectorAll('.city-option').forEach(opt => {
            opt.classList.toggle('selected', opt.dataset.value == value);
        });
        // Close dropdown
        document.getElementById('citySelectDropdown').classList.remove('open');
        document.getElementById('citySelectTrigger').classList.remove('open');
        document.getElementById('cityChevron').style.transform = 'rotate(0deg)';
    }

    function resetCityDropdown() {
        document.getElementById('dealerCity').value = '';
        const textEl = document.getElementById('citySelectText');
        textEl.innerText = 'Select City';
        textEl.style.color = 'rgba(255,255,255,0.4)';
        document.querySelectorAll('.city-option').forEach(opt => opt.classList.remove('selected'));
        document.getElementById('citySelectDropdown').classList.remove('open');
        document.getElementById('citySelectTrigger').classList.remove('open');
        document.getElementById('cityChevron').style.transform = 'rotate(0deg)';
    }

    function togglePasswordVisibility() {
        const input = document.getElementById('dealerPassword');
        const icon  = document.getElementById('passwordEyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    function closeDealerModal() {
        document.getElementById('dealerModal').style.display = 'none';
        clearErrors();
    }

    function clearErrors() {
        document.querySelectorAll('.field-error').forEach(el => { el.style.display = 'none'; el.innerText = ''; });
        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
    }

    function showError(fieldId, errorId, message) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(errorId);
        if (field) field.classList.add('input-error');
        if (error) { error.innerText = message; error.style.display = 'block'; }
    }

    function submitDealer() {
        clearErrors();
        const isEdit = currentDealerId !== null;
        let valid = true;

        const refCode   = document.getElementById('dealerRefCode').value.trim();
        const name      = document.getElementById('dealerName').value.trim();
        const shop      = document.getElementById('shopName').value.trim();
        const mobile    = document.getElementById('mobileNumber').value.trim();
        const email     = document.getElementById('emailAddress').value.trim();
        const discount  = document.getElementById('dealerDiscount').value.trim();
        const password  = document.getElementById('dealerPassword').value;
        const cityVal   = document.getElementById('dealerCity').value;
        const passbookVisible = document.getElementById('dealerPassbookVisible').checked ? 1 : 0;

        if (!refCode) { showError('dealerRefCode', 'err-refcode', 'ID is required.'); valid = false; }
        if (!name)    { showError('dealerName', 'err-name', 'Dealer name is required.'); valid = false; }
        if (!shop)    { showError('shopName', 'err-shop', 'Shop name is required.'); valid = false; }

        if (!mobile) {
            showError('mobileNumber', 'err-mobile', 'Mobile number is required.'); valid = false;
        } else if (!/^[0-9]{10}$/.test(mobile)) {
            showError('mobileNumber', 'err-mobile', 'Enter a valid 10-digit mobile number.'); valid = false;
        }

        if (!email) {
            showError('emailAddress', 'err-email', 'Email address is required.'); valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('emailAddress', 'err-email', 'Enter a valid email address.'); valid = false;
        }

        if (discount !== '' && (isNaN(discount) || parseFloat(discount) < 0 || parseFloat(discount) > 100)) {
            showError('dealerDiscount', 'err-discount', 'Discount must be between 0 and 100.'); valid = false;
        }

        const gst = document.getElementById('dealerGst').value.trim();
        if (!gst) { showError('dealerGst', 'err-gst', 'GST number is required.'); valid = false; }

        if (!cityVal) { showError(null, 'err-city', 'City is required.'); valid = false; }

        const salesmanVal = document.getElementById('assignSalesman').value;
        if (!salesmanVal) { showError('assignSalesman', 'err-salesman', 'Assigning a salesman is required.'); valid = false; }

        const distributorVal = document.getElementById('assignDistributor').value;
        if (!distributorVal) { showError('assignDistributor', 'err-distributor', 'Assigning a distributor is required.'); valid = false; }

        if (!isEdit && !password) {
            showError('dealerPassword', 'err-password', 'Password is required.'); valid = false;
        } else if (password && password.length < 6) {
            showError('dealerPassword', 'err-password', 'Password must be at least 6 characters.'); valid = false;
        }

        if (!valid) return;

        const url    = isEdit ? `${window.BASE_PATH}/dealers/${currentDealerId}` : `${window.BASE_PATH}/dealers`;
        const method = isEdit ? 'PUT' : 'POST';

        const data = {
            ref_code:         refCode,
            name:             name,
            shop:             shop,
            mobile:           mobile,
            email:            email,
            city_id:          cityVal || null,
            address:          document.getElementById('fullAddress').value,
            gst_no:           gst,
            discount_percent: discount || null,
            status:           document.getElementById('dealerStatus').value,
            salesman_id:      salesmanVal || null,
            dist_id:          distributorVal || null,
            is_passbook_visible: passbookVisible,
            _token:           '{{ csrf_token() }}'
        };
        if (password) data.password = password;

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(result => {
            submitBtn.disabled = false;
            submitBtn.innerText = isEdit ? 'Update Dealer' : 'Register Dealer';
            if (result.success) {
                alert(result.message);
                location.reload();
            } else if (result.errors) {
                // Map server validation errors back to fields
                const map = {
                    ref_code: ['dealerRefCode', 'err-refcode'],
                    name: ['dealerName', 'err-name'],
                    shop: ['shopName', 'err-shop'],
                    mobile: ['mobileNumber', 'err-mobile'],
                    email: ['emailAddress', 'err-email'],
                    city_id: [null, 'err-city'],
                    gst_no: ['dealerGst', 'err-gst'],
                    discount_percent: ['dealerDiscount', 'err-discount'],
                    salesman_id: ['assignSalesman', 'err-salesman'],
                    dist_id: ['assignDistributor', 'err-distributor'],
                    password: ['dealerPassword', 'err-password'],
                };
                Object.entries(result.errors).forEach(([key, msgs]) => {
                    if (map[key]) showError(map[key][0], map[key][1], msgs[0]);
                });
            } else {
                alert('Error: ' + (result.message || 'Something went wrong'));
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerText = isEdit ? 'Update Dealer' : 'Register Dealer';
            console.error(err);
            alert('An error occurred. Please try again.');
        });
    }

    function filterTable() {
        const search = document.getElementById('filterSearch').value.toLowerCase();
        const salesman = document.getElementById('filterSalesman').value;
        const distributor = document.getElementById('filterDistributor').value;
        
        // Handle Select2 multiple values
        let city = $('#filterCity').val() || [];
        if (!Array.isArray(city)) city = [city];
        // If "all" is selected, clear the filter
        if (city.includes('all')) city = [];

        document.querySelectorAll('.dealer-row').forEach(row => {
            const matchesSearch = row.dataset.search.includes(search);
            const matchesSalesman = salesman === "" || row.dataset.salesman === salesman;
            const matchesDistributor = distributor === "" || row.dataset.distributor === distributor;
            
            const rowCity = row.dataset.city;
            const matchesCity = city.length === 0 || city.includes(rowCity);

            if (matchesSearch && matchesSalesman && matchesDistributor && matchesCity) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function deleteDealer(id, name) {
        if (!confirm('Are you sure you want to delete dealer "' + name + '"?')) return;
        
        fetch(`${window.BASE_PATH}/dealers/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Error: ' + (result.message || 'Could not delete dealer'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred. Please try again.');
        });
    }

    window.onclick = function(event) {
        if (event.target.id === 'dealerModal') closeDealerModal();
        // Close city dropdown on outside click
        const wrapper = document.getElementById('citySelectWrapper');
        if (wrapper && !wrapper.contains(event.target)) {
            document.getElementById('citySelectDropdown').classList.remove('open');
            document.getElementById('citySelectTrigger').classList.remove('open');
            document.getElementById('cityChevron').style.transform = 'rotate(0deg)';
        }
    }

    $(document).ready(function() {
        $('#filterCity').select2({
            placeholder: "Select Cities",
            width: '250px',
            closeOnSelect: false,
            templateResult: function(state) {
                if (!state.id) { return state.text; }
                return $('<span><input type="checkbox" ' + (state.selected ? 'checked' : '') + ' style="margin-right:8px; pointer-events:none; accent-color: var(--primary);" /> ' + state.text + '</span>');
            }
        });

        $('#filterSalesman').select2({
            width: '180px'
        });

        $('#filterDistributor').select2({
            width: '180px'
        });

        $('#filterSalesman, #filterDistributor').on('change', function() {
            filterTable();
        });

        function syncCheckboxes() {
            setTimeout(function() {
                var selectedVals = $('#filterCity').val() || [];
                var selectedTexts = [];
                $('#filterCity option:selected').each(function() {
                    selectedTexts.push($(this).text().trim());
                });

                $('.select2-results__option').each(function() {
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
                    // Deselect all
                    $this.val([]).trigger('change');
                } else {
                    // Select all
                    var allVals = [];
                    $this.find('option').each(function() {
                        if ($(this).val() && $(this).val() !== 'all') {
                            allVals.push($(this).val());
                        }
                    });
                    $this.val(allVals).trigger('change');
                }
                
                // Select2 closes dropdown if not prevented, but we have closeOnSelect: false.
                // However, triggering 'change' might close it in some versions, so let's ensure it stays open.
                // Actually, closeOnSelect: false handles this.
                syncCheckboxes();
            }
        });

        // For individual selections, we just need to sync checkboxes.
        $('#filterCity').on('select2:select select2:unselect', function (e) {
            syncCheckboxes();
        });

        $('#filterCity').on('select2:open', function() {
            syncCheckboxes();
        });

        function updatePlaceholder() {
            var count = $('#filterCity').val() ? $('#filterCity').val().length : 0;
            var $container = $('#filterCity').next('.select2-container');
            
            if (count > 1) {
                $container.addClass('has-multiple');
                $container[0].style.setProperty('--selected-text', '"' + count + ' cities selected"');
            } else {
                $container.removeClass('has-multiple');
            }
        }

        $('#filterCity').on('change select2:close', function() {
            updatePlaceholder();
            filterTable();
        });
    });

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

    let currentEditPointsDealerId = null;

    function openEditPointsModal(dealer, pointsBalance) {
        currentEditPointsDealerId = dealer.id;
        document.getElementById('quickEditPointsInput').value = pointsBalance !== undefined ? pointsBalance : '0';
        document.getElementById('err-quick-points').innerText = '';
        document.getElementById('editPointsModal').style.display = 'flex';
        closeAllActionMenus();
    }

    function closeEditPointsModal() {
        document.getElementById('editPointsModal').style.display = 'none';
        currentEditPointsDealerId = null;
    }

    function submitEditPoints() {
        const points = document.getElementById('quickEditPointsInput').value;
        if (!currentEditPointsDealerId) return;

        showLoader();
        fetch(`${window.BASE_PATH}/dealers/${currentEditPointsDealerId}/update-points`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ points: points })
        })
        .then(response => response.json())
        .then(result => {
            hideLoader();
            if (result.success) {
                alert(result.message);
                setTimeout(() => window.location.reload(), 500);
            } else {
                if (result.errors && result.errors.points) {
                    showError('quickEditPointsInput', 'err-quick-points', result.errors.points[0]);
                } else {
                    alert('Error: ' + (result.message || 'An error occurred.'));
                }
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Error:', error);
            alert('Server error occurred.');
        });
    }

    function togglePassbookVisibility(id, isVisible) {
        fetch(`${window.BASE_PATH}/dealers/${id}/toggle-passbook`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_passbook_visible: isVisible })
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                // Optional toast notification can be added here
                console.log(result.message);
            } else {
                alert('Error updating passbook visibility: ' + (result.message || 'Unknown error'));
                // Revert switch visually
                location.reload();
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while updating passbook visibility.');
            location.reload();
        });
    }

    document.addEventListener('click', closeAllActionMenus);
    document.addEventListener('scroll', closeAllActionMenus, true);
</script>
@endsection
