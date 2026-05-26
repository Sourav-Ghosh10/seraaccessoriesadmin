@extends('layouts.app')

@section('title', 'Distributor Registration')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 4px; height: 24px; background: var(--primary); border-radius: 2px;"></div>
            <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #fff;">Distributor List</h3>
        </div>
        <button class="btn btn-primary" onclick="openDistributorModal()" style="box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">
            <i class="fas fa-plus"></i> Add Distributor
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Phone Number</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($distributors as $distributor)
                <tr>
                    <td>#{{ $distributor->id }}</td>
                    <td>{{ $distributor->name }}</td>
                    <td>{{ $distributor->email }}</td>
                    <td>{{ $distributor->mobile }}</td>
                    <td><span class="badge badge-success">{{ $distributor->status }}</span></td>
                    <td>
                        <button class="btn glass" onclick="editDistributor('{{ $distributor->id }}', '{{ $distributor->name }}', '{{ $distributor->email }}', '{{ $distributor->mobile }}', '{{ $distributor->status }}')" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('modals')
<!-- Add Distributor Modal -->
<div id="distributorModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 22px; font-weight: 700;">Add New Distributor</h3>
            <div onclick="closeDistributorModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Distributor Name</label>
            <input type="text" id="distName" class="form-control" placeholder="Enter full name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Email Address</label>
                <input type="email" id="distEmail" class="form-control" placeholder="email@example.com" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Phone Number</label>
                <input type="text" id="distPhone" class="form-control" placeholder="+91 00000 00000" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Password</label>
            <div style="position: relative;">
                <input type="password" id="distPassword" class="form-control" placeholder="Create secure password..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); padding-right: 40px;">
                <i class="fas fa-eye" id="toggleDistPassword" onclick="toggleDistPasswordVisibility()" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; font-size: 14px; z-index: 10;"></i>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Status</label>
            <select id="distStatus" class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <button class="btn glass" onclick="closeDistributorModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 12px 30px;">Cancel</button>
            <button id="submitBtn" class="btn btn-primary" onclick="submitDistributor()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Register Distributor</button>
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
    let currentDistributorId = null;

    function openDistributorModal() {
        currentDistributorId = null;
        document.getElementById('modalTitle').innerText = 'Add New Distributor';
        document.getElementById('submitBtn').innerText = 'Register Distributor';
        document.getElementById('distName').value = '';
        document.getElementById('distEmail').value = '';
        document.getElementById('distPhone').value = '';
        document.getElementById('distPassword').value = '';
        document.getElementById('distStatus').value = 'Active';
        document.getElementById('distributorModal').style.display = 'flex';
    }

    function editDistributor(id, name, email, phone, status) {
        currentDistributorId = id;
        document.getElementById('modalTitle').innerText = 'Edit Distributor: ' + name;
        document.getElementById('submitBtn').innerText = 'Update Distributor';
        document.getElementById('distName').value = name;
        document.getElementById('distEmail').value = email;
        document.getElementById('distPhone').value = phone;
        document.getElementById('distPassword').value = ''; 
        document.getElementById('distStatus').value = status;
        document.getElementById('distributorModal').style.display = 'flex';
    }

    function closeDistributorModal() {
        document.getElementById('distributorModal').style.display = 'none';
    }

    function toggleDistPasswordVisibility() {
        const passInput = document.getElementById('distPassword');
        const icon = document.getElementById('toggleDistPassword');
        
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

    function submitDistributor() {
        const isEdit = currentDistributorId !== null;
        const url = isEdit ? `${window.BASE_PATH}/distributors/${currentDistributorId}` : `${window.BASE_PATH}/distributors`;
        const method = isEdit ? 'PUT' : 'POST';

        const data = {
            name: document.getElementById('distName').value,
            email: document.getElementById('distEmail').value,
            phone: document.getElementById('distPhone').value,
            password: document.getElementById('distPassword').value,
            status: document.getElementById('distStatus').value,
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
        if (event.target.id == 'distributorModal') {
            closeDistributorModal();
        }
    }
</script>
@endsection
