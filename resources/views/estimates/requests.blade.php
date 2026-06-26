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
    <div class="card animate-fade">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Incoming Estimate Requests</h3>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <a href="{{ route('estimate-requests', array_merge(request()->query(), ['tab' => 'dealer'])) }}" 
               class="btn {{ request('tab', 'dealer') == 'dealer' ? 'btn-primary' : 'glass' }}"
               style="padding: 8px 20px;">Dealer Requests</a>
            <a href="{{ route('estimate-requests', array_merge(request()->query(), ['tab' => 'distributor'])) }}" 
               class="btn {{ request('tab') == 'distributor' ? 'btn-primary' : 'glass' }}"
               style="padding: 8px 20px;">Distributor Requests</a>
        </div>

        <form id="filterForm" method="GET" action="{{ route('estimate-requests') }}" style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05);">
            <div class="grid-4" style="gap: 15px; align-items: end;">
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
                @if(request('tab', 'dealer') !== 'distributor')
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Salesman</label>
                    <select name="salesman_id" id="filterSalesman" class="form-control select2" style="width: 100%;">
                        <option value="">All Salesmen</option>
                        @foreach($salesmen as $salesman)
                            <option value="{{ $salesman->id }}" {{ request('salesman_id') == $salesman->id ? 'selected' : '' }}>{{ $salesman->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="form-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Distributor</label>
                    <select name="dist_id" id="filterDistributor" class="form-control select2" style="width: 100%;">
                        <option value="">All Distributors</option>
                        @foreach($distributors as $dist)
                            <option value="{{ $dist->dist_id }}" {{ request('dist_id') == $dist->dist_id ? 'selected' : '' }}>{{ $dist->name }}</option>
                        @endforeach
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
                        <th>Request ID</th>
                        @if(request('tab', 'dealer') !== 'distributor')
                        <th>Shop Name</th>
                        <th>Salesman</th>
                        @endif
                        <th>Distributor</th>
                        <th>Type</th>
                        <th>Date/Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estimates as $estimate)
                        @php
                            $estimateFileUrls = collect($estimate->file_path ?? [])
                                ->filter()
                                ->map(fn ($path) => asset('uploads/' . ltrim(str_replace('\\', '/', (string) $path), '/')))
                                ->values()
                                ->all();
                        @endphp
                        <tr>
                            <td>{{ $estimate->request_number ?? 'EST-' . str_pad($estimate->id, 4, '0', STR_PAD_LEFT) }}</td>
                            @if(request('tab', 'dealer') !== 'distributor')
                            <td>
                                <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($estimate->member->name) }}', '{{ addslashes($estimate->member->email) }}', '{{ addslashes($estimate->member->mobile) }}', '{{ addslashes($estimate->member->ref_code ?? '') }}', 'Dealer', '{{ addslashes(preg_replace('/\r|\n/', ' ', $estimate->member->address ?? '')) }}', '{{ addslashes($estimate->member->shop ?? '') }}', '{{ addslashes($estimate->member->city->city ?? '') }}', '{{ addslashes($estimate->member->gst_no ?? '') }}', '{{ $estimate->member->discount_percent ?? '' }}', '{{ addslashes($estimate->member->salesman->name ?? '') }}', '{{ addslashes($distributors->firstWhere('dist_id', $estimate->member->dist_id)->name ?? $estimate->member->dist_id ?? '') }}')" style="font-weight: 500; color: #3b82f6; text-decoration: none;">
                                    {{ $estimate->member->shop ?? $estimate->member->name }}
                                </a>
                            </td>
                            <td>
                                @if(isset($estimate->member->salesman))
                                    <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($estimate->member->salesman->name) }}', '{{ addslashes($estimate->member->salesman->email) }}', '{{ addslashes($estimate->member->salesman->mobile) }}', '{{ addslashes($estimate->member->salesman->ref_code) }}', 'Salesman', '', '', '{{ addslashes($estimate->member->salesman->city->city ?? '') }}', '', '', '', '')" style="font-weight: 500; color: #3b82f6; text-decoration: none;">
                                        {{ $estimate->member->salesman->name }}
                                    </a>
                                @else
                                    <span style="color: var(--text-muted);">N/A</span>
                                @endif
                            </td>
                            @endif
                            <td>
                                @if(isset($estimate->member->distributor))
                                    <a href="javascript:void(0)" onclick="viewMemberDetails('{{ addslashes($estimate->member->distributor->name) }}', '{{ addslashes($estimate->member->distributor->email) }}', '{{ addslashes($estimate->member->distributor->mobile) }}', '{{ addslashes($estimate->member->distributor->dist_id) }}', 'Distributor', '{{ addslashes(preg_replace('/\r|\n/', ' ', $estimate->member->distributor->address ?? '')) }}', '', '{{ addslashes($estimate->member->distributor->city->city ?? '') }}', '', '', '', '')" style="font-weight: 500; color: #3b82f6; text-decoration: none;">
                                        {{ $estimate->member->distributor->name }}
                                    </a>
                                @else
                                    <span style="color: var(--text-muted);">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($estimate->type == 'Voice')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-microphone" style="color: #8b5cf6;"></i>
                                        <span>Voice Note</span>
                                    </div>
                                @elseif($estimate->type == 'Photo')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-camera" style="color: var(--accent);"></i>
                                        <span>Photo</span>
                                    </div>
                                @elseif($estimate->type == 'Document' || $estimate->type == 'Pdf')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-file-pdf" style="color: #ef4444;"></i>
                                        <span>Document / PDF</span>
                                    </div>
                                @else
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-font" style="color: var(--primary);"></i>
                                        <span>Text Request</span>
                                    </div>
                                @endif
                            </td>
                            <td><span
                                    style="font-size: 12px; color: var(--text-muted);">{{ $estimate->created_at->format('Y-m-d H:i A') }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $estimate->status == 'Responded' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $estimate->status }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn glass" style="padding: 5px 12px; font-size: 11px;"
                                        data-id="{{ $estimate->request_number ?? 'EST-' . str_pad($estimate->id, 4, '0', STR_PAD_LEFT) }}" 
                                        data-type="{{ $estimate->type }}"
                                        data-member="{{ $estimate->member->shop ?? $estimate->member->name }}" 
                                        data-desc="{{ $estimate->description }}"
                                        data-file-urls='@json($estimateFileUrls)'
                                        data-response-desc="{{ $estimate->response_description }}"
                                        data-response-file="{{ $estimate->response_file_path }}"
                                        onclick="initViewEstimate(this)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
 
                                    <button class="btn btn-primary" style="padding: 5px 12px; font-size: 11px;"
                                        onclick="openEstimateModal('{{ $estimate->id }}', '{{ $estimate->request_number ?? 'EST-' . str_pad($estimate->id, 4, '0', STR_PAD_LEFT) }}', '{{ $estimate->response_description }}')">
                                        <i class="fas fa-reply"></i> Revert Estimate
                                    </button>
                                    <button class="btn glass"
                                        style="padding: 5px 12px; font-size: 11px; color: var(--accent);"><i
                                            class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
        </table>
        <div id="paginationContainer" style="padding: 20px 0;">
            {{ $estimates->appends(request()->query())->links() }}
        </div>
    </div>
    </div>

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

    <!-- Revert Estimate Modal -->
    <div id="estimateModal"
        style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
        <div class="card"
            style="width: 100%; max-width: 500px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Revert Estimate</h3>
                <div onclick="closeModal()"
                    style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                </div>
            </div>

            <form id="revertForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="modalEstimateId" name="estimate_id">

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Request ID</label>
                    <input type="text" id="modalRequestId" class="form-control" readonly
                        style="background: rgba(255,255,255,0.03);">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Estimate Details / Price Breakdown</label>
                    <textarea name="response_description" id="modalResponseDesc" class="form-control" style="height: 120px; background: rgba(255,255,255,0.03);"
                        placeholder="Enter the estimated prices and details for the dealer..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label class="form-label">Upload Estimate Document (PDF / Image)</label>
                    <input type="file" name="estimate_pdf" accept=".pdf,image/*" class="form-control" style="background: rgba(255,255,255,0.03); color: var(--text-muted);">
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn glass" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Estimate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Request Modal -->
    <div id="viewModal"
        style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
        <div class="card"
            style="width: 100%; max-width: 600px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Dealer Request Details</h3>
                <div onclick="closeModal()"
                    style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Request ID</div>
                        <div id="viewRequestId" style="font-weight: 700; color: var(--primary);"></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Shop Name</div>
                        <div id="viewDealerName" style="font-weight: 700;"></div>
                    </div>
                </div>

                <div class="glass" style="padding: 20px; border-radius: 12px; margin-top: 10px;">
                    <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; margin-bottom: 10px;">
                        Request Content</div>
                    <div id="viewRequestContent"></div>
                </div>

                <!-- Reverted Response Area -->
                <div id="viewResponseContainer" class="glass" style="padding: 20px; border-radius: 12px; margin-top: 15px; border-color: rgba(34, 197, 94, 0.2); display: none;">
                    <div style="color: #22c55e; font-size: 12px; text-transform: uppercase; margin-bottom: 10px; font-weight: 600;">
                        Reverted Estimate Details</div>
                    <div id="viewResponseDesc" style="margin-bottom: 15px; color: var(--text-muted);"></div>
                    <div id="viewResponseFile"></div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button class="btn btn-primary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Lightbox for photo preview -->
    <style>
        .attachment-thumb-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: flex-start;
            width: 100%;
        }

        .attachment-thumb {
            width: 96px;
            height: 96px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.12);
            cursor: zoom-in;
            position: relative;
            background: rgba(255, 255, 255, 0.03);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            flex-shrink: 0;
        }

        .attachment-thumb:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
        }

        .attachment-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .attachment-thumb-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.45);
            opacity: 0;
            transition: opacity 0.2s ease;
            color: #fff;
            font-size: 18px;
        }

        .attachment-thumb:hover .attachment-thumb-overlay {
            opacity: 1;
        }

        .lightbox-overlay {
            display: none;
            position: fixed;
            z-index: 100001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(2, 6, 23, 0.96);
            backdrop-filter: blur(20px);
            align-items: center;
            justify-content: center;
            cursor: zoom-out;
            animation: fadeIn 0.25s ease-out;
        }

        .lightbox-overlay img {
            max-width: 90%;
            max-height: 85vh;
            border-radius: 8px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
            transition: transform 0.25s cubic-bezier(0.1, 0.8, 0.3, 1);
            transform-origin: center center;
            cursor: grab;
        }

        .lightbox-overlay img:active {
            cursor: grabbing;
        }

        .lightbox-close {
            position: absolute;
            top: 25px;
            right: 25px;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.06);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: scale(1.05);
        }

        .lightbox-controls {
            position: absolute;
            bottom: 35px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 8px 18px;
            border-radius: 30px;
            display: flex;
            gap: 12px;
            align-items: center;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .lightbox-btn {
            color: #fff;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            transition: all 0.2s;
            text-decoration: none;
        }

        .lightbox-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--primary);
        }
    </style>
    <div id="imageLightbox" class="lightbox-overlay" onclick="closeLightbox()">
        <span class="lightbox-close"><i class="fas fa-times"></i></span>
        <img id="lightboxImg" src="" onclick="event.stopPropagation();" style="transform: scale(1);">
        <div class="lightbox-controls" onclick="event.stopPropagation();">
            <button class="lightbox-btn" onclick="zoomImg(-0.2)" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
            <button class="lightbox-btn" onclick="resetZoom()" title="Reset Zoom"><i class="fas fa-redo"></i></button>
            <button class="lightbox-btn" onclick="zoomImg(0.2)" title="Zoom In"><i class="fas fa-search-plus"></i></button>
            <span style="color: rgba(255,255,255,0.2); margin: 0 4px;">|</span>
            <a id="lightboxDownload" href="" download class="lightbox-btn" title="Download Image"><i class="fas fa-download"></i></a>
        </div>
    </div>

    <script>
        function toggleDateInputs() {
            const type = document.getElementById('dateTypeSelect').value;
            const single = document.getElementById('singleDateWrapper');
            const range1 = document.getElementById('rangeDateWrapper1');
            const range2 = document.getElementById('rangeDateWrapper2');

            single.style.display = type === 'individual' ? 'block' : 'none';
            range1.style.display = type === 'range' ? 'block' : 'none';
            range2.style.display = type === 'range' ? 'block' : 'none';
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

        function initViewEstimate(btn) {
            const id = btn.getAttribute('data-id');
            const type = btn.getAttribute('data-type');
            const member = btn.getAttribute('data-member');
            const desc = btn.getAttribute('data-desc');
            const fileUrlsRaw = btn.getAttribute('data-file-urls');
            const responseDesc = btn.getAttribute('data-response-desc');
            const responseFile = btn.getAttribute('data-response-file');
            viewRequest(id, type, member, desc, fileUrlsRaw, responseDesc, responseFile);
        }

        function openEstimateModal(dbId, displayId, currentResponseDesc) {
            document.getElementById('modalEstimateId').value = dbId;
            document.getElementById('modalRequestId').value = displayId;
            document.getElementById('modalResponseDesc').value = currentResponseDesc || '';
            document.getElementById('revertForm').action = `${window.APP_URL}/estimates/${dbId}/revert`;
            document.getElementById('estimateModal').style.display = 'flex';
        }

        function parseFileUrls(raw) {
            if (!raw || raw === 'null' || raw === 'undefined') {
                return [];
            }
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
            } catch (e) {
                return [];
            }
        }

        let currentScale = 1;

        function openLightbox(src) {
            const lightbox = document.getElementById('imageLightbox');
            const img = document.getElementById('lightboxImg');
            const downloadLink = document.getElementById('lightboxDownload');
            img.src = src;
            downloadLink.href = src;
            currentScale = 1;
            img.style.transform = `scale(${currentScale})`;
            lightbox.style.display = 'flex';
        }

        function closeLightbox() {
            document.getElementById('imageLightbox').style.display = 'none';
        }

        function zoomImg(amount) {
            const img = document.getElementById('lightboxImg');
            currentScale = Math.max(0.4, Math.min(4, currentScale + amount));
            img.style.transform = `scale(${currentScale})`;
        }

        function resetZoom() {
            const img = document.getElementById('lightboxImg');
            currentScale = 1;
            img.style.transform = `scale(${currentScale})`;
        }

        function buildImageThumbnails(fileUrls) {
            if (!fileUrls.length) {
                return '<p style="font-size: 14px; color: var(--text-muted); margin: 0;">No attachment file found for this request.</p>';
            }

            let html = '<div class="attachment-thumb-grid">';
            fileUrls.forEach(url => {
                const safeUrl = url.replace(/'/g, "\\'");
                if (url.toLowerCase().includes('.pdf')) {
                    html += `
                        <a href="${url}" target="_blank" class="btn glass" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-size: 13px; padding: 10px 20px; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none;">
                            <i class="fas fa-file-pdf" style="font-size: 20px; color: #ef4444;"></i>
                            <span>View PDF</span>
                        </a>
                    `;
                } else {
                    html += `
                        <div class="attachment-thumb" onclick="openLightbox('${safeUrl}')" title="Click to view full size">
                            <img src="${url}" alt="Request attachment">
                            <div class="attachment-thumb-overlay"><i class="fas fa-search-plus"></i></div>
                        </div>
                    `;
                }
            });
            html += '</div>';
            return html;
        }

        function viewRequest(id, type, dealer, description, fileUrlsRaw, responseDesc, responseFile) {
            document.getElementById('viewRequestId').textContent = id;
            document.getElementById('viewDealerName').textContent = dealer;

            const contentArea = document.getElementById('viewRequestContent');
            const fileUrls = parseFileUrls(fileUrlsRaw);

            if (type === 'Voice') {
                const storagePath = fileUrls.length > 0 ? fileUrls[0] : '#';
                contentArea.innerHTML = `
                                                    <div style="text-align: center; width: 100%;">
                                                        <i class="fas fa-microphone" style="font-size: 40px; color: #8b5cf6; margin-bottom: 15px;"></i>
                                                        <audio controls style="width: 100%; filter: invert(1) hue-rotate(180deg);">
                                                            <source src="${storagePath}" type="audio/mpeg">
                                                            Your browser does not support the audio element.
                                                        </audio>
                                                        <p style="margin-top: 15px; font-size: 14px; color: var(--text-muted); font-style: italic;">${description || 'Voice Note'}</p>
                                                    </div>
                                                `;
            } else if (type === 'Photo' || type === 'Document' || type === 'Pdf') {
                const imagesHtml = buildImageThumbnails(fileUrls);

                contentArea.innerHTML = `
                    <div style="width: 100%;">
                        ${imagesHtml}
                        <p style="font-size: 14px; color: var(--text-muted); margin: 14px 0 0;">${description || 'Attachment'}</p>
                    </div>
                `;
            } else {
                contentArea.innerHTML = `
                                                    <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                                                        <p style="font-size: 15px; line-height: 1.6; margin: 0;">${description || 'No description provided.'}</p>
                                                    </div>
                                                `;
            }

            // Handle response display
            const respContainer = document.getElementById('viewResponseContainer');
            const respDesc = document.getElementById('viewResponseDesc');
            const respFile = document.getElementById('viewResponseFile');

            if (responseDesc || responseFile) {
                respContainer.style.display = 'block';
                respDesc.innerHTML = responseDesc ? `<p style="font-size: 14px; margin: 0; line-height: 1.5;">${responseDesc}</p>` : '<p style="font-style: italic; color: var(--text-muted); font-size: 13px; margin: 0;">No written details provided.</p>';
                
                if (responseFile) {
                    const fileUrl = responseFile.startsWith('http') ? responseFile : `${window.APP_URL}/uploads/${responseFile.replace(/^uploads\//, '')}`;
                    if (responseFile.toLowerCase().endsWith('.pdf')) {
                        respFile.innerHTML = `
                            <a href="${fileUrl}" target="_blank" class="btn glass" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-size: 12px; padding: 8px 15px;">
                                <i class="fas fa-file-pdf" style="font-size: 16px; color: #ef4444;"></i>
                                <span>View Reverted Estimate PDF</span>
                            </a>
                        `;
                    } else {
                        const safeUrl = fileUrl.replace(/'/g, "\\'");
                        respFile.innerHTML = `
                            <div class="attachment-thumb" onclick="openLightbox('${safeUrl}')" title="Click to view full size" style="margin-top: 10px;">
                                <img src="${fileUrl}" alt="Reverted estimate attachment">
                                <div class="attachment-thumb-overlay"><i class="fas fa-search-plus"></i></div>
                            </div>
                        `;
                    }
                } else {
                    respFile.innerHTML = '';
                }
            } else {
                respContainer.style.display = 'none';
            }


            document.getElementById('viewModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('estimateModal').style.display = 'none';
            document.getElementById('viewModal').style.display = 'none';
            closeLightbox();
        }

        window.onclick = function (event) {
            const lightboxModal = document.getElementById('imageLightbox');
            if (event.target == document.getElementById('estimateModal') || event.target == document.getElementById('viewModal')) {
                closeModal();
            }
            if (event.target == lightboxModal) {
                closeLightbox();
            }
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
                        
                        // Apply table filters now that dropdowns are updated
                        applyFilters();
                    },
                    error: function(xhr) {
                        console.error('Failed to load dependent members');
                        applyFilters(); // Apply filters anyway as a fallback
                    }
                });
            });
            updatePlaceholder();

            $('#filterSalesman').select2({
                placeholder: "All Salesmen",
                allowClear: true
            });
            $('#filterDistributor').select2({
                placeholder: "All Distributors",
                allowClear: true
            });

            // AJAX Filtering Logic
            var filterTimeout;
            function applyFilters() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    var form = $('#filterForm');
                    $.ajax({
                        url: form.attr('action'),
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
    </script>
@endsection