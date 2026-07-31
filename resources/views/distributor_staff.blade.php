@extends('layouts.app')

@section('title', 'Manage Staff for ' . $distributor->name)

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('distributors') }}" class="btn glass" style="padding: 6px 12px; font-size: 14px;"><i class="fas fa-arrow-left"></i></a>
            <div style="width: 4px; height: 24px; background: var(--primary); border-radius: 2px;"></div>
            <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #fff;">Staff: {{ $distributor->name }} ({{ $distributor->dist_id }})</h3>
        </div>
        <button class="btn btn-primary" onclick="openStaffModal()" style="box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">
            <i class="fas fa-user-plus"></i> Add Staff
        </button>
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
        <input type="text" id="searchInput" autocomplete="off" class="form-control" placeholder="Search by name, email, phone..." style="flex: 1; min-width: 200px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        <select id="filterStatus" class="form-control" style="width: 150px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <div class="table-container">
        <table id="staffTable">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Email Address</th>
                    <th>Phone Number</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="staffTableBody">
                @forelse($staffMembers as $staff)
                <tr class="staff-row" data-status="{{ strtolower($staff->status) }}">
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>{{ $staff->mobile }}</td>
                    <td>
                        <span class="badge badge-status-{{ Str::slug($staff->status) }}" style="padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                            {{ $staff->status }}
                        </span>
                    </td>
                    <td>
                        <button class="btn glass" onclick="editStaff('{{ $staff->id }}', '{{ addslashes($staff->name) }}', '{{ addslashes($staff->email) }}', '{{ addslashes($staff->mobile) }}', '{{ $staff->status }}', '{{ $staff->city_id }}', '{{ addslashes($staff->city->city ?? '') }}')" style="padding: 5px 10px; font-size: 12px;" title="Edit Staff"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No staff members found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div id="paginationContainer" style="padding: 20px 0;">
            {{ $staffMembers->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Add/Edit Staff Modal -->
<div id="staffModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: 20px; width: 100%; max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 700;">Add New Staff</h3>
            <div onclick="closeStaffModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <input type="hidden" id="staffDistId" value="{{ $distributor->dist_id }}">
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Staff Name</label>
            <input type="text" id="staffName" class="form-control" placeholder="Enter full name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            <span class="text-danger" id="err-name" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Email Address</label>
            <input type="email" id="staffEmail" class="form-control" placeholder="email@example.com" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            <span class="text-danger" id="err-email" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Phone Number</label>
            <input type="text" id="staffPhone" class="form-control" placeholder="+91 00000 00000" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            <span class="text-danger" id="err-phone" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Assign City</label>
            <input type="hidden" id="staffCity" value="">
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
            <span class="text-danger" id="err-city" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Password</label>
            <div style="position: relative;">
                <input type="password" id="staffPassword" class="form-control" placeholder="Create secure password..." autocomplete="new-password" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); padding-right: 40px;">
                <i class="fas fa-eye" id="toggleStaffPassword" onclick="toggleStaffPasswordVisibility()" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; font-size: 14px; z-index: 10;"></i>
            </div>
            <span class="text-danger" id="err-password" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Status</label>
            <select id="staffStatus" class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <button class="btn glass" onclick="closeStaffModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 12px 30px;">Cancel</button>
            <button id="submitBtn" class="btn btn-primary" onclick="submitStaff()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Save Staff</button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.form-control:focus { outline: none; border-color: var(--primary); }

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
.city-select-trigger.input-error { border-color: #f87171 !important; }
</style>
@endpush

@section('scripts')
<script>
    let currentStaffId = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Search and Filter logic
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('filterStatus').addEventListener('change', filterTable);

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

    var filterTimeout;
    function filterTable() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            $.ajax({
                url: window.location.pathname,
                data: {
                    search: $('#searchInput').val(),
                    status: $('#filterStatus').val()
                },
                success: function(response) {
                    var newTable = $(response).find('.table-container').html();
                    $('.table-container').html(newTable);
                }
            });
        }, 300);
    }

    function clearErrors() {
        document.querySelectorAll('.text-danger').forEach(el => el.innerText = '');
    }

    function openStaffModal() {
        currentStaffId = null;
        clearErrors();
        document.getElementById('modalTitle').innerText = 'Add New Staff';
        document.getElementById('submitBtn').innerText = 'Save Staff';
        document.getElementById('staffName').value = '';
        document.getElementById('staffEmail').value = '';
        document.getElementById('staffPhone').value = '';
        document.getElementById('staffPassword').value = '';
        document.getElementById('staffPassword').placeholder = 'Create secure password...';
        document.getElementById('staffStatus').value = 'Active';
        document.getElementById('staffCity').value = '';
        selectCity('', 'Select City');
        document.getElementById('staffModal').style.display = 'flex';
        setTimeout(() => {
            if (currentStaffId === null) {
                document.getElementById('staffPassword').value = '';
            }
        }, 50);
    }

    function editStaff(id, name, email, phone, status, cityId, cityName) {
        currentStaffId = id;
        clearErrors();
        document.getElementById('modalTitle').innerText = 'Edit Staff: ' + name;
        document.getElementById('submitBtn').innerText = 'Update Staff';
        document.getElementById('staffName').value = name;
        document.getElementById('staffEmail').value = email;
        document.getElementById('staffPhone').value = phone;
        document.getElementById('staffPassword').value = '';
        document.getElementById('staffPassword').placeholder = 'Leave blank to keep current password';
        document.getElementById('staffStatus').value = status;
        document.getElementById('staffCity').value = cityId || '';
        selectCity(cityId || '', cityName || 'Select City');
        document.getElementById('staffModal').style.display = 'flex';
        setTimeout(() => {
            if (currentStaffId !== null) {
                document.getElementById('staffPassword').value = '';
            }
        }, 50);
    }

    function closeStaffModal() {
        document.getElementById('staffModal').style.display = 'none';
    }

    function toggleStaffPasswordVisibility() {
        const passInput = document.getElementById('staffPassword');
        const icon = document.getElementById('toggleStaffPassword');
        
        if (passInput.type === 'password') {
            passInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function submitStaff() {
        const isEdit = currentStaffId !== null;
        const url = isEdit ? `${window.BASE_PATH}/distributors/staff/${currentStaffId}` : `${window.BASE_PATH}/distributors/staff`;
        const method = isEdit ? 'PUT' : 'POST';

        const data = {
            name: document.getElementById('staffName').value,
            email: document.getElementById('staffEmail').value,
            phone: document.getElementById('staffPhone').value,
            password: document.getElementById('staffPassword').value,
            status: document.getElementById('staffStatus').value,
            city_id: document.getElementById('staffCity').value,
            dist_id: document.getElementById('staffDistId').value,
            _token: '{{ csrf_token() }}'
        };

        clearErrors();

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const result = await response.json();
            if (response.ok) {
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Something went wrong'));
                }
            } else if (response.status === 422) {
                if (result.errors) {
                    for (const key in result.errors) {
                        const errSpan = document.getElementById('err-' + key);
                        if (errSpan) {
                            errSpan.innerText = result.errors[key][0];
                        }
                    }
                } else {
                    alert('Validation error: ' + (result.message || 'Check your inputs.'));
                }
            } else {
                alert('An error occurred.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
    // City Dropdown Logic
    function toggleCityDropdown() {
        const dropdown = document.getElementById('citySelectDropdown');
        const trigger  = document.getElementById('citySelectTrigger');
        const chevron  = document.getElementById('cityChevron');
        
        const isOpen = dropdown.classList.contains('open');
        
        if (isOpen) {
            dropdown.classList.remove('open');
            trigger.classList.remove('open');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            dropdown.classList.add('open');
            trigger.classList.add('open');
            chevron.style.transform = 'rotate(180deg)';
            document.getElementById('citySearchInput').focus();
            
            document.getElementById('citySearchInput').value = '';
            filterCities();
        }
    }

    function filterCities() {
        const q = document.getElementById('citySearchInput').value.toLowerCase();
        document.querySelectorAll('#cityOptionsList .city-option').forEach(opt => {
            if (opt.getAttribute('data-value') === '') return;
            const text = opt.innerText.toLowerCase();
            if (text.includes(q)) {
                opt.classList.remove('hidden');
            } else {
                opt.classList.add('hidden');
            }
        });
    }

    function selectCity(val, label) {
        document.getElementById('staffCity').value = val;
        document.getElementById('citySelectText').innerText = label;
        document.getElementById('citySelectText').style.color = val ? '#fff' : 'rgba(255,255,255,0.4)';
        
        document.querySelectorAll('#cityOptionsList .city-option').forEach(opt => opt.classList.remove('selected'));
        if (val) {
            const selectedOpt = document.querySelector(`#cityOptionsList .city-option[data-value="${val}"]`);
            if (selectedOpt) selectedOpt.classList.add('selected');
        }
        
        const dropdown = document.getElementById('citySelectDropdown');
        if (dropdown.classList.contains('open')) {
            toggleCityDropdown();
        }
    }

    // Close city dropdown on outside click
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('citySelectWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            const dropdown = document.getElementById('citySelectDropdown');
            if (dropdown && dropdown.classList.contains('open')) {
                toggleCityDropdown();
            }
        }
    });
    // Modals do not close on outside click; user must click X icon or Close button
</script>
@endsection
