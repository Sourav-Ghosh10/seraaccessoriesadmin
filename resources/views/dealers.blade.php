@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Dealer Registration</h3>
        <button class="btn btn-primary" onclick="openDealerModal()"><i class="fas fa-plus"></i> Register New Dealer</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dealer Name</th>
                    <th>Shop Name</th>
                    <th>Mobile</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dealers as $dealer)
                <tr>
                    <td>#{{ $dealer['id'] }}</td>
                    <td>{{ $dealer['name'] }}</td>
                    <td>{{ $dealer['shop'] }}</td>
                    <td>{{ $dealer['mobile'] }}</td>
                    <td>
                        <span class="badge {{ $dealer['status'] == 'Active' ? 'badge-success' : ($dealer['status'] == 'Pending' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $dealer['status'] }}
                        </span>
                    </td>
                    <td>
                        <button class="btn glass" style="padding: 5px 10px; font-size: 12px;" onclick="openEditModal('{{ $dealer['id'] }}', '{{ $dealer['name'] }}', '{{ $dealer['shop'] }}', '{{ $dealer['mobile'] }}', '{{ $dealer['status'] }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn glass" style="padding: 5px 10px; font-size: 12px;" onclick="openViewModal('{{ $dealer['id'] }}', '{{ $dealer['name'] }}', '{{ $dealer['shop'] }}', '{{ $dealer['mobile'] }}', '{{ $dealer['status'] }}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
    </div>
</div>

<!-- Dealer Modal (Used for Register, Edit, and View) -->
<div id="dealerModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; padding-top: 50px; overflow-y: auto;">
    <div class="card" style="width: 100%; max-width: 600px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin-bottom: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 22px; font-weight: 700;">Register New Dealer</h3>
            <div onclick="closeDealerModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div id="formFields">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Dealer Name</label>
                    <input type="text" id="dealerName" class="form-control" placeholder="Full name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Shop Name</label>
                    <input type="text" id="shopName" class="form-control" placeholder="Business name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Mobile Number</label>
                    <input type="tel" id="mobileNumber" class="form-control" placeholder="10 digit mobile..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Email Address</label>
                    <input type="email" id="emailAddress" class="form-control" placeholder="email@example.com" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Full Address</label>
                <textarea id="fullAddress" class="form-control" style="height: 80px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); resize: none;" placeholder="Shop location details..."></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1.5fr; gap: 20px; margin-top: 20px;">
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
                        <option value="" style="background: #1e293b;">Select Salesman</option>
                        <option value="S001" style="background: #1e293b;">Rahul Kumar (S001)</option>
                        <option value="S002" style="background: #1e293b;">Anita Singh (S002)</option>
                        <option value="S003" style="background: #1e293b;">Vikram Patel (S003)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Set Password</label>
                    <input type="password" id="dealerPassword" class="form-control" placeholder="Min. 8 characters" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
            </div>
        </div>

        <div id="viewContainer" style="display: none; animation: fadeIn 0.3s ease-out;">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding: 20px; background: rgba(255,255,255,0.02); border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: 700; color: #fff;">
                    <span id="initials">JD</span>
                </div>
                <div>
                    <h2 id="viewDealerName" style="margin: 0; font-size: 24px;">John Doe</h2>
                    <p id="viewShopName" style="margin: 5px 0 0 0; color: var(--primary); font-weight: 600;">JD Accessories</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div>
                    <p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Mobile Number</p>
                    <p id="dispMobile" style="margin: 5px 0 0 0; font-size: 16px; font-weight: 600; color: #cbd5e1;"></p>
                </div>
                <div>
                    <p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Current Status</p>
                    <div id="dispStatus" style="margin-top: 5px;"></div>
                </div>
                <div style="grid-column: span 2;">
                    <p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Office Address</p>
                    <p id="dispAddress" style="margin: 5px 0 0 0; font-size: 15px; line-height: 1.6; color: #cbd5e1;"></p>
                </div>
                <div>
                    <p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Mapped Salesman</p>
                    <p id="dispSalesman" style="margin: 5px 0 0 0; font-size: 15px; font-weight: 600; color: var(--secondary);"></p>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 40px;">
            <button class="btn glass" onclick="closeDealerModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 12px 30px;">Close</button>
            <button id="submitBtn" class="btn btn-primary" onclick="submitDealer()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Register Dealer</button>
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
</style>

@endsection

@section('scripts')
<script>
    function openDealerModal() {
        resetForm();
        document.getElementById('modalTitle').innerText = 'Register New Dealer';
        document.getElementById('submitBtn').innerText = 'Register Dealer';
        document.getElementById('submitBtn').style.display = 'block';
        document.getElementById('formFields').style.display = 'block';
        document.getElementById('viewContainer').style.display = 'none';
        enableInputs(true);
        document.getElementById('dealerModal').style.display = 'flex';
    }

    function openEditModal(id, name, shop, mobile, status) {
        resetForm();
        document.getElementById('modalTitle').innerText = 'Edit Dealer: #' + id;
        document.getElementById('submitBtn').innerText = 'Update Dealer';
        document.getElementById('submitBtn').style.display = 'block';
        document.getElementById('formFields').style.display = 'block';
        document.getElementById('viewContainer').style.display = 'none';
        
        document.getElementById('dealerName').value = name;
        document.getElementById('shopName').value = shop;
        document.getElementById('mobileNumber').value = mobile;
        document.getElementById('dealerStatus').value = status;
        
        enableInputs(true);
        document.getElementById('dealerModal').style.display = 'flex';
    }

    function openViewModal(id, name, shop, mobile, status) {
        resetForm();
        document.getElementById('modalTitle').innerText = 'Dealer Profile: #' + id;
        document.getElementById('submitBtn').style.display = 'none';
        document.getElementById('formFields').style.display = 'none';
        document.getElementById('viewContainer').style.display = 'block';
        
        // Populate view container
        document.getElementById('viewDealerName').innerText = name;
        document.getElementById('viewShopName').innerText = shop;
        document.getElementById('dispMobile').innerText = mobile;
        document.getElementById('dispAddress').innerText = '123, Street Name, Business District, City, State - 123456';
        document.getElementById('dispSalesman').innerText = 'Rahul Kumar (S001)';
        document.getElementById('initials').innerText = name.split(' ').map(n => n[0]).join('').toUpperCase();
        
        const badgeClass = status === 'Active' ? 'badge-success' : (status === 'Pending' ? 'badge-warning' : 'badge-danger');
        document.getElementById('dispStatus').innerHTML = `<span class="badge ${badgeClass}">${status}</span>`;
        
        document.getElementById('dealerModal').style.display = 'flex';
    }

    function enableInputs(enabled) {
        const inputs = document.querySelectorAll('#dealerModal .form-control');
        inputs.forEach(input => {
            input.disabled = !enabled;
        });
    }

    function resetForm() {
        const inputs = document.querySelectorAll('#dealerModal .form-control');
        inputs.forEach(input => {
            input.value = '';
            if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            }
        });
    }

    function closeDealerModal() {
        document.getElementById('dealerModal').style.display = 'none';
    }

    function submitDealer() {
        const isEdit = document.getElementById('submitBtn').innerText.includes('Update');
        alert(isEdit ? 'Dealer updated successfully!' : 'Dealer registered successfully!');
        closeDealerModal();
    }

    window.onclick = function(event) {
        const modal = document.getElementById('dealerModal');
        if (event.target == modal) {
            closeDealerModal();
        }
    }
</script>
@endsection
