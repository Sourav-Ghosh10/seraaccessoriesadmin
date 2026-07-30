@extends('layouts.app')

@section('title', 'Sales Registration')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<style>
    .cropper-view-box,
    .cropper-face {
      border-radius: 50%;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <form id="searchFilterForm" style="display: flex; gap: 10px; align-items: center; flex-wrap: nowrap;" onsubmit="return false;">
            <input type="text" id="searchInput" name="search" class="form-control" placeholder="Search by name, email, ID..." value="{{ request('search') }}" style="width: 250px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            <select id="statusFilter" name="status" class="form-control" style="width: 150px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                <option value="">All Statuses</option>
                <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="button" id="clearBtn" class="btn glass" style="color: var(--danger); display: none; white-space: nowrap;"><i class="fas fa-times"></i> Clear</button>
        </form>
        <button class="btn btn-primary" onclick="openSalesmanModal()"><i class="fas fa-plus"></i> Add Salesman</button>
    </div>

    <div class="table-container">
        <table id="tableBodyContent">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Points</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            @include('salesmen_table')
        </table>
    </div>
</div>
@endsection

@push('modals')
<!-- Add Salesman Modal -->
<div id="salesmanModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin-bottom: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Add New Salesman</h3>
            <div onclick="closeSalesmanModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">ID (6 Digit Alphanumeric)</label>
            <input type="text" id="autoRefCode" class="form-control" placeholder="ABC123" maxlength="6" autocomplete="off" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); text-transform: uppercase;">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Employee Name</label>
            <input type="text" id="salesmanName" class="form-control" placeholder="Enter name..." autocomplete="off" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Profile Picture (Optional)</label>
            <input type="file" id="salesmanProfileImage" accept="image/*" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); padding: 8px;">
            <div id="salesmanProfileImagePreview" style="margin-top: 10px; display: none; position: relative; width: 100px; height: 100px;">
                <img id="profileImagePreviewImg" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 2px solid var(--primary);">
                <button type="button" onclick="clearProfileImage()" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Mobile Number</label>
            <input type="tel" id="salesmanMobile" class="form-control" placeholder="10 digit number..." autocomplete="off" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Email Address</label>
            <input type="email" id="salesmanEmail" class="form-control" placeholder="email@example.com" autocomplete="off" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
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
            <input type="hidden" id="salesmanCity" value="">
            <p class="field-error" id="err-city"></p>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Password</label>
            <div style="position: relative;">
                <input type="password" id="salesmanPassword" class="form-control" placeholder="Create password..." autocomplete="new-password" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); padding-right: 40px;">
                <i class="fas fa-eye" id="togglePassword" onclick="toggleSalesmanPassword()" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; font-size: 14px;"></i>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Status</label>
                <select id="salesmanStatus" class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Monthly Target (₹)</label>
                <input type="number" id="salesmanMonthlyTarget" class="form-control" placeholder="100000" min="0" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
        </div>


        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <button class="btn glass" onclick="closeSalesmanModal()" style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
            <button class="btn btn-primary" onclick="submitSalesman()" style="padding: 12px 30px;">Save Salesman</button>
        </div>
    </div>
</div>

<!-- Performance Modal -->
<div id="performanceModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin-bottom: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div>
                <h3 id="perfName" style="margin: 0; font-size: 20px; font-weight: 700;">Performance Analytics</h3>
            </div>
            <div onclick="closePerformanceModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="grid-3" style="gap: 15px; margin-bottom: 30px;">
            <div style="background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); text-align: center;">
                <p style="margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase;">This Month Revenue</p>
                <h4 id="perfRevenue" style="margin: 10px 0 0 0; font-size: 18px; color: var(--success);">₹0</h4>
            </div>
            <div style="background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); text-align: center;">
                <p style="margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase;">This Month Orders</p>
                <h4 id="perfOrders" style="margin: 10px 0 0 0; font-size: 18px; color: var(--secondary);">0</h4>
            </div>
            <div style="background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); text-align: center;">
                <p style="margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Dealers</p>
                <h4 id="perfDealers" style="margin: 10px 0 0 0; font-size: 18px; color: var(--primary);">0</h4>
            </div>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <h4 style="font-size: 14px; margin-bottom: 15px;">Target Completion</h4>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-size: 12px; color: var(--text-muted);">Monthly Sales Target</span>
                <span id="perfTargetText" style="font-size: 12px; color: #fff;">0%</span>
            </div>
            <div class="glass" style="height: 8px; border-radius: 4px; overflow: hidden;">
                <div id="perfProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary)); transition: width 0.5s ease-in-out;"></div>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 40px;">
            <button class="btn btn-primary" onclick="closePerformanceModal()" style="padding: 12px 40px;">Close Analytics</button>
        </div>
    </div>
</div>

<!-- Edit Points Modal -->
<div id="editPointsModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); width: 400px; animation: modalIn 0.3s ease-out;">
        <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 600;">Edit Salesman Points</h3>
        <div class="form-group" style="margin-bottom: 15px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Select Order <span style="color: var(--danger);">*</span></label>
            <select id="quickEditPointsOrder" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;" onchange="handleOrderPointSelection(this)">
                <option value="">Admin Adjustment (Total Balance)</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" id="pointsInputLabel" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Points Balance</label>
            <input type="number" id="quickEditPointsInput" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            <small id="err-quick-points" style="color: var(--danger); display: block; margin-top: 5px;"></small>
        </div>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button class="btn glass" onclick="closeEditPointsModal()" style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
            <button class="btn btn-primary" onclick="submitEditPoints()">Save Points</button>
        </div>
    </div>
</div>

<!-- Cropper Modal -->
<div id="cropperModal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.95); align-items: center; justify-content: center;">
    <div class="card modal-content" style="padding: 20px; background: #0f172a; border: 1px solid var(--glass-border); max-width: 500px; width: 100%; border-radius: 12px;">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px;">Crop Profile Picture</h3>
        <div style="width: 100%; max-height: 400px; overflow: hidden; background: #000; border-radius: 8px; margin-bottom: 20px;">
            <img id="cropperImage" src="" style="max-width: 100%; display: block;">
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn glass" onclick="closeCropperModal()" style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="applyCrop()">Apply Crop</button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.form-control:focus { outline: none; border-color: var(--primary); }
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
</style>

@endpush

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let currentSalesmanId = null;
    let croppedProfileImageBlob = null;
    let cropperInstance = null;
    let searchTimer = null;

    function fetchSalesmen(pageUrl = null) {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const clearBtn = document.getElementById('clearBtn');
        
        if (search || status) {
            clearBtn.style.display = 'inline-block';
        } else {
            clearBtn.style.display = 'none';
        }

        let url = pageUrl || '{{ route("salesmen") }}';
        let separator = url.includes('?') ? '&' : '?';
        url = url + separator + `search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Because the partial contains tbody and tfoot, we can't just set innerHTML on a table element safely in all browsers if it expects full table structure. 
            // Wait, actually setting innerHTML on table might fail or skip the thead.
            // Better to wrap the whole table in a div. 
            // But let's just do:
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = `<table>${html}</table>`;
            
            // Remove existing tbody and tfoot
            const table = document.getElementById('tableBodyContent');
            const existingTbody = table.querySelector('tbody');
            const existingTfoot = table.querySelector('tfoot');
            if (existingTbody) existingTbody.remove();
            if (existingTfoot) existingTfoot.remove();
            
            // Append new ones
            const newTbody = tempDiv.querySelector('tbody');
            const newTfoot = tempDiv.querySelector('tfoot');
            if (newTbody) table.appendChild(newTbody);
            if (newTfoot) table.appendChild(newTfoot);
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    document.getElementById('searchInput').addEventListener('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            fetchSalesmen();
        }, 500); // 500ms delay for typing
    });

    document.getElementById('statusFilter').addEventListener('change', function() {
        fetchSalesmen();
    });

    document.getElementById('clearBtn').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        fetchSalesmen();
    });

    // Handle pagination clicks using Ajax
    document.addEventListener('click', function(e) {
        if (e.target.closest('#paginationContainer a')) {
            e.preventDefault();
            const url = e.target.closest('a').href;
            fetchSalesmen(url);
        }
    });

    function openSalesmanModal() {
        currentSalesmanId = null;
        resetSalesmanForm();
        const pwdInput = document.getElementById('salesmanPassword');
        pwdInput.placeholder = 'Create password...';
        if (pwdInput.type !== 'password') {
            pwdInput.type = 'password';
            document.getElementById('togglePassword').classList.replace('fa-eye-slash', 'fa-eye');
        }
        document.getElementById('salesmanModal').style.display = 'flex';
        setTimeout(() => {
            if (currentSalesmanId === null) {
                document.getElementById('salesmanPassword').value = '';
            }
        }, 50);
    }

    function openEditSalesmanModal(id, name, mobile, email, ref_code, status, target, city_id, city_name, profile_image) {
        currentSalesmanId = id;
        document.getElementById('salesmanName').value = name;
        document.getElementById('salesmanMobile').value = mobile;
        document.getElementById('salesmanEmail').value = email;
        document.getElementById('autoRefCode').value = ref_code;
        document.getElementById('salesmanStatus').value = status;
        document.getElementById('salesmanMonthlyTarget').value = target || '';
        document.getElementById('salesmanCity').value = city_id || '';
        if (city_id && city_name) {
            selectCity(city_id, city_name);
        } else {
            resetCityDropdown();
        }
        
        if (profile_image) {
            document.getElementById('profileImagePreviewImg').src = profile_image;
            document.getElementById('salesmanProfileImagePreview').style.display = 'block';
        } else {
            document.getElementById('profileImagePreviewImg').src = '';
            document.getElementById('salesmanProfileImagePreview').style.display = 'none';
        }
        
        const pwdInput = document.getElementById('salesmanPassword');
        pwdInput.value = '';
        pwdInput.placeholder = 'Leave blank to keep current password';
        if (pwdInput.type !== 'password') {
            pwdInput.type = 'password';
            document.getElementById('togglePassword').classList.replace('fa-eye-slash', 'fa-eye');
        }

        document.getElementById('salesmanModal').style.display = 'flex';
        setTimeout(() => {
            if (currentSalesmanId !== null) {
                document.getElementById('salesmanPassword').value = '';
            }
        }, 50);
    }

    function resetSalesmanForm() {
        document.getElementById('salesmanName').value = '';
        document.getElementById('salesmanMobile').value = '';
        document.getElementById('salesmanEmail').value = '';
        document.getElementById('salesmanPassword').value = '';
        document.getElementById('autoRefCode').value = '';
        document.getElementById('salesmanStatus').value = 'Active';
        document.getElementById('salesmanMonthlyTarget').value = '';
        document.getElementById('salesmanCity').value = '';
        resetCityDropdown();
        clearProfileImage();
    }
    
    function clearProfileImage() {
        document.getElementById('salesmanProfileImage').value = '';
        document.getElementById('profileImagePreviewImg').src = '';
        document.getElementById('salesmanProfileImagePreview').style.display = 'none';
        croppedProfileImageBlob = null;
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
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        } else {
            dropdown.classList.add('open');
            trigger.classList.add('open');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
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
        document.getElementById('salesmanCity').value = value;
        const textEl = document.getElementById('citySelectText');
        if (textEl) {
            textEl.innerText = label || 'Select City';
            textEl.style.color = value ? '#fff' : 'rgba(255,255,255,0.4)';
        }
        // Mark selected
        document.querySelectorAll('.city-option').forEach(opt => {
            opt.classList.toggle('selected', opt.dataset.value == value);
        });
        // Close dropdown
        const dropdown = document.getElementById('citySelectDropdown');
        if (dropdown) dropdown.classList.remove('open');
        const trigger = document.getElementById('citySelectTrigger');
        if (trigger) trigger.classList.remove('open');
        const chevron = document.getElementById('cityChevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }

    function resetCityDropdown() {
        const hiddenInput = document.getElementById('salesmanCity');
        if (hiddenInput) hiddenInput.value = '';
        const textEl = document.getElementById('citySelectText');
        if (textEl) {
            textEl.innerText = 'Select City';
            textEl.style.color = 'rgba(255,255,255,0.4)';
        }
        document.querySelectorAll('.city-option').forEach(opt => opt.classList.remove('selected'));
        const dropdown = document.getElementById('citySelectDropdown');
        if (dropdown) dropdown.classList.remove('open');
        const trigger = document.getElementById('citySelectTrigger');
        if (trigger) trigger.classList.remove('open');
        const chevron = document.getElementById('cityChevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }

    function closeSalesmanModal() {
        document.getElementById('salesmanModal').style.display = 'none';
    }

    function toggleSalesmanPassword() {
        const passwordInput = document.getElementById('salesmanPassword');
        const toggleIcon = document.getElementById('togglePassword');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function submitSalesman() {
        const isEdit = currentSalesmanId !== null;
        const url = isEdit ? `${window.BASE_PATH}/salesmen/${currentSalesmanId}` : `${window.BASE_PATH}/salesmen`;
        const method = isEdit ? 'PUT' : 'POST';

        const formData = new FormData();
        formData.append('name', document.getElementById('salesmanName').value);
        formData.append('mobile', document.getElementById('salesmanMobile').value);
        formData.append('email', document.getElementById('salesmanEmail').value);
        
        const cityVal = document.getElementById('salesmanCity').value;
        if (cityVal) formData.append('city_id', cityVal);
        
        formData.append('password', document.getElementById('salesmanPassword').value);
        formData.append('ref_code', document.getElementById('autoRefCode').value.toUpperCase());
        formData.append('status', document.getElementById('salesmanStatus').value);
        formData.append('monthly_target', document.getElementById('salesmanMonthlyTarget').value);
        formData.append('_token', '{{ csrf_token() }}');

        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        if (croppedProfileImageBlob) {
            formData.append('profile_image', croppedProfileImageBlob, 'profile.jpg');
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Error: ' + (result.message || 'Something went wrong'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }

    function openPerformanceModal(id, name) {
        document.getElementById('perfName').innerText = name + ' Performance';
        
        // Show loading state
        document.getElementById('perfRevenue').innerText = '...';
        document.getElementById('perfOrders').innerText = '...';
        document.getElementById('perfDealers').innerText = '...';
        document.getElementById('perfTargetText').innerText = '...';
        document.getElementById('perfProgressBar').style.width = '0%';
        
        document.getElementById('performanceModal').style.display = 'flex';
        
        // Fetch performance data
        fetch(`${window.BASE_PATH}/salesmen/${id}/performance`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    document.getElementById('perfRevenue').innerText = '₹' + parseFloat(data.total_revenue).toLocaleString('en-IN');
                    document.getElementById('perfOrders').innerText = data.orders_count;
                    document.getElementById('perfDealers').innerText = data.dealers_count;
                    document.getElementById('perfTargetText').innerText = data.target_completion + '%';
                    document.getElementById('perfProgressBar').style.width = data.target_completion + '%';
                } else {
                    alert('Error: Could not retrieve performance metrics');
                    closePerformanceModal();
                }
            })
            .catch(error => {
                console.error('Error fetching performance metrics:', error);
                alert('An error occurred. Please try again.');
                closePerformanceModal();
            });
    }

    function closePerformanceModal() {
        document.getElementById('performanceModal').style.display = 'none';
    }

    let currentEditPointsSalesmanId = null;
    let currentEditPointsGlobalBalance = 0;
    let currentSalesmanOrders = [];

    function openEditPointsModal(id, pointsBalance) {
        currentEditPointsSalesmanId = id;
        currentEditPointsGlobalBalance = pointsBalance !== undefined ? pointsBalance : '0';
        
        const orderSelect = document.getElementById('quickEditPointsOrder');
        if (orderSelect) {
            orderSelect.innerHTML = '<option value="">Loading orders...</option>';
            orderSelect.disabled = true;
            fetch(`${window.BASE_PATH}/api/members/${id}/reward-orders`)
                .then(r => r.json())
                .then(result => {
                    currentSalesmanOrders = result.data || [];
                    orderSelect.innerHTML = '<option value="">Admin Adjustment (Total Balance)</option>';
                    currentSalesmanOrders.forEach(order => {
                        const disabledAttr = order.editable === false ? 'disabled' : '';
                        const labelText = order.editable === false ? `Order ${order.order_number} (${order.date}) - Redeemed` : `Order ${order.order_number} (${order.date})`;
                        orderSelect.innerHTML += `<option value="${order.id}" ${disabledAttr}>${labelText}</option>`;
                    });
                    orderSelect.disabled = false;
                })
                .catch(() => {
                    orderSelect.innerHTML = '<option value="">Failed to load orders</option>';
                });
        }

        const input = document.getElementById('quickEditPointsInput');
        input.value = currentEditPointsGlobalBalance;
        
        const label = document.getElementById('pointsInputLabel');
        if (label) label.innerText = 'Points Balance';

        document.getElementById('err-quick-points').innerText = '';
        document.getElementById('editPointsModal').style.display = 'flex';
    }

    function handleOrderPointSelection(select) {
        const orderId = select.value;
        const input = document.getElementById('quickEditPointsInput');
        const label = document.getElementById('pointsInputLabel');
        
        if (!orderId) {
            input.value = currentEditPointsGlobalBalance;
            if (label) label.innerText = 'Points Balance';
            return;
        }
        
        const order = currentSalesmanOrders.find(o => o.id == orderId);
        if (order) {
            input.value = order.points || 0;
            if (label) label.innerText = `Points for Order ${order.order_number}`;
        }
    }

    function closeEditPointsModal() {
        document.getElementById('editPointsModal').style.display = 'none';
        currentEditPointsSalesmanId = null;
    }

    function submitEditPoints() {
        const points = document.getElementById('quickEditPointsInput').value;
        const orderId = document.getElementById('quickEditPointsOrder') ? document.getElementById('quickEditPointsOrder').value : '';
        
        if (!orderId) {
            alert('Please select an order before saving points.');
            return;
        }

        if (!currentEditPointsSalesmanId) return;

        fetch(`${window.BASE_PATH}/salesmen/${currentEditPointsSalesmanId}/update-points`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ points: points, order_id: orderId })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                if (result.errors && result.errors.points) {
                    document.getElementById('err-quick-points').innerText = result.errors.points[0];
                } else {
                    alert('Error: ' + (result.message || 'An error occurred.'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Server error occurred.');
        });
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

    // Modals do not close on outside click; user must click X icon or Close button

    document.addEventListener('click', function(event) {
        closeAllActionMenus(event);
        const wrapper = document.getElementById('citySelectWrapper');
        if (wrapper && !wrapper.contains(event.target)) {
            const dropdown = document.getElementById('citySelectDropdown');
            if (dropdown && dropdown.classList.contains('open')) {
                dropdown.classList.remove('open');
                const trigger = document.getElementById('citySelectTrigger');
                if (trigger) trigger.classList.remove('open');
                const chevron = document.getElementById('cityChevron');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        }
    });
    document.addEventListener('scroll', closeAllActionMenus, true);

    // Profile Image Cropper Logic
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('salesmanProfileImage');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        document.getElementById('cropperImage').src = event.target.result;
                        document.getElementById('cropperModal').style.display = 'flex';
                        
                        if (cropperInstance) cropperInstance.destroy();
                        cropperInstance = new Cropper(document.getElementById('cropperImage'), {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.8,
                            restore: false,
                            guides: false,
                            center: false,
                            highlight: false,
                            cropBoxMovable: false,
                            cropBoxResizable: false,
                            toggleDragModeOnDblclick: false,
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    function closeCropperModal() {
        document.getElementById('cropperModal').style.display = 'none';
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        if (!croppedProfileImageBlob && !document.getElementById('profileImagePreviewImg').src) {
            document.getElementById('salesmanProfileImage').value = '';
        }
    }

    function applyCrop() {
        if (cropperInstance) {
            const canvas = cropperInstance.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });
            
            document.getElementById('profileImagePreviewImg').src = canvas.toDataURL('image/jpeg');
            document.getElementById('salesmanProfileImagePreview').style.display = 'block';
            
            canvas.toBlob(function(blob) {
                croppedProfileImageBlob = blob;
            }, 'image/jpeg', 0.9);
            
            closeCropperModal();
        }
    }
</script>
@endsection
