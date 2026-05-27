@extends('layouts.app')

@section('title', 'Sales Registration')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: flex-end; margin-bottom: 25px;">
        <button class="btn btn-primary" onclick="openSalesmanModal()"><i class="fas fa-plus"></i> Add Salesman</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Referral Code</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesmen as $salesman)
                <tr>
                    <td>{{ $salesman->name }}</td>
                    <td><code>{{ $salesman->ref_code }}</code></td>
                    <td><span class="badge badge-success">{{ $salesman->status }}</span></td>
                    <td>
                        <button class="btn glass" style="padding: 5px 10px; font-size: 12px;" onclick="openEditSalesmanModal('{{ $salesman->id }}', '{{ $salesman->name }}', '{{ $salesman->mobile }}', '{{ $salesman->email }}', '{{ $salesman->ref_code }}', '{{ $salesman->status }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn glass" style="padding: 5px 10px; font-size: 12px;" onclick="openPerformanceModal('{{ $salesman->id }}', '{{ $salesman->name }}')">
                            <i class="fas fa-chart-line"></i> Performance
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
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
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Employee Name</label>
            <input type="text" id="salesmanName" class="form-control" placeholder="Enter name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Mobile Number</label>
            <input type="tel" id="salesmanMobile" class="form-control" placeholder="10 digit number..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Email Address</label>
            <input type="email" id="salesmanEmail" class="form-control" placeholder="email@example.com" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Password</label>
            <div style="position: relative;">
                <input type="password" id="salesmanPassword" class="form-control" placeholder="Create password..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); padding-right: 40px;">
                <i class="fas fa-eye" id="togglePassword" onclick="toggleSalesmanPassword()" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; font-size: 14px;"></i>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Status</label>
            <select id="salesmanStatus" class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Referral Code</label>
            <div style="display: flex; gap: 10px;">
                <input type="text" id="autoRefCode" class="form-control" placeholder="GENERATE123" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                <button class="btn glass" onclick="generateCode()" style="white-space: nowrap; font-size: 12px;">Generate</button>
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
                <p style="margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Total Revenue</p>
                <h4 id="perfRevenue" style="margin: 10px 0 0 0; font-size: 18px; color: var(--success);">₹0</h4>
            </div>
            <div style="background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); text-align: center;">
                <p style="margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Orders</p>
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
    let currentSalesmanId = null;

    function openSalesmanModal() {
        currentSalesmanId = null;
        resetSalesmanForm();
        document.getElementById('salesmanModal').style.display = 'flex';
    }

    function openEditSalesmanModal(id, name, mobile, email, ref_code, status) {
        currentSalesmanId = id;
        document.getElementById('salesmanName').value = name;
        document.getElementById('salesmanMobile').value = mobile;
        document.getElementById('salesmanEmail').value = email;
        document.getElementById('autoRefCode').value = ref_code;
        document.getElementById('salesmanStatus').value = status;
        document.getElementById('salesmanModal').style.display = 'flex';
    }

    function resetSalesmanForm() {
        document.getElementById('salesmanName').value = '';
        document.getElementById('salesmanMobile').value = '';
        document.getElementById('salesmanEmail').value = '';
        document.getElementById('salesmanPassword').value = '';
        document.getElementById('autoRefCode').value = '';
        document.getElementById('salesmanStatus').value = 'Active';
    }

    function closeSalesmanModal() {
        document.getElementById('salesmanModal').style.display = 'none';
    }

    function generateCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for(let i=0; i<8; i++) code += chars[Math.floor(Math.random()*chars.length)];
        document.getElementById('autoRefCode').value = code;
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

        const data = {
            name: document.getElementById('salesmanName').value,
            mobile: document.getElementById('salesmanMobile').value,
            email: document.getElementById('salesmanEmail').value,
            password: document.getElementById('salesmanPassword').value,
            ref_code: document.getElementById('autoRefCode').value,
            status: document.getElementById('salesmanStatus').value,
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

    window.onclick = function(event) {
        if (event.target.id == 'salesmanModal') closeSalesmanModal();
        if (event.target.id == 'performanceModal') closePerformanceModal();
    }
</script>
@endsection
