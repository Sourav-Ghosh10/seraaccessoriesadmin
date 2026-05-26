@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 4px; height: 24px; background: var(--primary); border-radius: 2px;"></div>
            <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #fff;">System Users</h3>
        </div>
        <button class="btn btn-primary" onclick="openUserModal()">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>#{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge" style="background: rgba(79, 172, 254, 0.1); color: #4facfe;">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $user->status == 'Active' ? 'badge-success' : 'badge-warning' }}">
                            {{ $user->status }}
                        </span>
                    </td>
                    <td>
                        <button class="btn glass" onclick="editUser('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role }}', '{{ $user->status }}')" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('modals')
<!-- Add/Edit User Modal -->
<div id="userModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 id="userModalTitle" style="margin: 0; font-size: 22px; font-weight: 700;">Add New User</h3>
            <div onclick="closeUserModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Full Name</label>
            <input type="text" id="userName" class="form-control" placeholder="Enter full name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Email Address</label>
                <input type="email" id="userEmail" class="form-control" placeholder="email@shera.com" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Assign Role</label>
                <select id="userRole" class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                    <option value="Admin">Admin</option>
                    <option value="Operations">Operations</option>
                    <option value="Account">Account</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">User Status</label>
                <select id="userStatus" class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Password</label>
            <div style="position: relative;">
                <input type="password" id="userPassword" class="form-control" placeholder="Create secure password..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); padding-right: 40px;">
                <i class="fas fa-eye" id="toggleUserPassword" onclick="toggleUserPasswordVisibility()" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; font-size: 14px; z-index: 10;"></i>
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <button class="btn glass" onclick="closeUserModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 12px 30px;">Cancel</button>
            <button id="userSubmitBtn" class="btn btn-primary" onclick="submitUser()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Create User</button>
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
    let currentUserId = null;

    function openUserModal() {
        currentUserId = null;
        document.getElementById('userModalTitle').innerText = 'Add New User';
        document.getElementById('userSubmitBtn').innerText = 'Create User';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userRole').value = 'Admin';
        document.getElementById('userStatus').value = 'Active';
        document.getElementById('userPassword').value = '';
        document.getElementById('userModal').style.display = 'flex';
    }

    function editUser(id, name, email, role, status) {
        currentUserId = id;
        document.getElementById('userModalTitle').innerText = 'Edit User: ' + name;
        document.getElementById('userSubmitBtn').innerText = 'Update User';
        document.getElementById('userName').value = name;
        document.getElementById('userEmail').value = email;
        document.getElementById('userRole').value = role;
        document.getElementById('userStatus').value = status;
        document.getElementById('userPassword').value = '';
        document.getElementById('userModal').style.display = 'flex';
    }

    function closeUserModal() {
        document.getElementById('userModal').style.display = 'none';
    }

    function toggleUserPasswordVisibility() {
        const passInput = document.getElementById('userPassword');
        const icon = document.getElementById('toggleUserPassword');
        
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

    function submitUser() {
        const isEdit = currentUserId !== null;
        const url = isEdit ? `${window.BASE_PATH}/users/${currentUserId}` : `${window.BASE_PATH}/users`;
        const method = isEdit ? 'PUT' : 'POST';

        const data = {
            name: document.getElementById('userName').value,
            email: document.getElementById('userEmail').value,
            role: document.getElementById('userRole').value,
            status: document.getElementById('userStatus').value,
            password: document.getElementById('userPassword').value,
            _token: '{{ csrf_token() }}'
        };

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
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

    window.onclick = function(event) {
        if (event.target.id == 'userModal') {
            closeUserModal();
        }
    }
</script>
@endsection
