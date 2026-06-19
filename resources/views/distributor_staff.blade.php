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
                        <span class="badge {{ strtolower($staff->status) == 'active' ? 'badge-success' : 'badge-danger' }}" style="background: {{ strtolower($staff->status) == 'active' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; color: {{ strtolower($staff->status) == 'active' ? '#10b981' : '#ef4444' }}; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                            {{ $staff->status }}
                        </span>
                    </td>
                    <td>
                        <button class="btn glass" onclick="editStaff('{{ $staff->id }}', '{{ addslashes($staff->name) }}', '{{ addslashes($staff->email) }}', '{{ addslashes($staff->mobile) }}', '{{ $staff->status }}')" style="padding: 5px 10px; font-size: 12px;" title="Edit Staff"><i class="fas fa-edit"></i></button>
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
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Password</label>
            <div style="position: relative;">
                <input type="password" id="staffPassword" class="form-control" placeholder="Create secure password..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); padding-right: 40px;">
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
</style>
@endpush

@section('scripts')
<script>
    let currentStaffId = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Search and Filter logic
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('filterStatus').addEventListener('change', filterTable);
    });

    function filterTable() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('filterStatus').value;

        document.querySelectorAll('.staff-row').forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const rowStatus = row.getAttribute('data-status');

            const matchesSearch = rowText.includes(searchTerm);
            const matchesStatus = statusFilter === '' || rowStatus === statusFilter;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
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
        document.getElementById('staffStatus').value = 'Active';
        document.getElementById('staffModal').style.display = 'flex';
    }

    function editStaff(id, name, email, phone, status) {
        currentStaffId = id;
        clearErrors();
        document.getElementById('modalTitle').innerText = 'Edit Staff: ' + name;
        document.getElementById('submitBtn').innerText = 'Update Staff';
        document.getElementById('staffName').value = name;
        document.getElementById('staffEmail').value = email;
        document.getElementById('staffPhone').value = phone;
        document.getElementById('staffPassword').value = ''; 
        document.getElementById('staffStatus').value = status;
        document.getElementById('staffModal').style.display = 'flex';
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

    window.onclick = function(event) {
        if (event.target.id == 'staffModal') {
            closeStaffModal();
        }
    }
</script>
@endsection
