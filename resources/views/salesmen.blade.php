@extends('layouts.app')

@section('title', 'Sales Registration')

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
            <input type="text" id="autoRefCode" class="form-control" placeholder="ABC123" maxlength="6" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); text-transform: uppercase;">
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
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Points Balance</label>
            <input type="number" id="quickEditPointsInput" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            <small id="err-quick-points" style="color: var(--danger); display: block; margin-top: 5px;"></small>
        </div>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button class="btn glass" onclick="closeEditPointsModal()" style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
            <button class="btn btn-primary" onclick="submitEditPoints()">Save Points</button>
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
</style>

@endpush

@section('scripts')
<script>
    let currentSalesmanId = null;
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
        document.getElementById('salesmanModal').style.display = 'flex';
    }

    function openEditSalesmanModal(id, name, mobile, email, ref_code, status, target) {
        currentSalesmanId = id;
        document.getElementById('salesmanName').value = name;
        document.getElementById('salesmanMobile').value = mobile;
        document.getElementById('salesmanEmail').value = email;
        document.getElementById('autoRefCode').value = ref_code;
        document.getElementById('salesmanStatus').value = status;
        document.getElementById('salesmanMonthlyTarget').value = target || '';
        document.getElementById('salesmanModal').style.display = 'flex';
    }

    function resetSalesmanForm() {
        document.getElementById('salesmanName').value = '';
        document.getElementById('salesmanMobile').value = '';
        document.getElementById('salesmanEmail').value = '';
        document.getElementById('salesmanPassword').value = '';
        document.getElementById('autoRefCode').value = '';
        document.getElementById('salesmanStatus').value = 'Active';
        document.getElementById('salesmanMonthlyTarget').value = '';
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

        const data = {
            name: document.getElementById('salesmanName').value,
            mobile: document.getElementById('salesmanMobile').value,
            email: document.getElementById('salesmanEmail').value,
            password: document.getElementById('salesmanPassword').value,
            ref_code: document.getElementById('autoRefCode').value.toUpperCase(),
            status: document.getElementById('salesmanStatus').value,
            monthly_target: document.getElementById('salesmanMonthlyTarget').value,
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

    let currentEditPointsSalesmanId = null;

    function openEditPointsModal(id, pointsBalance) {
        currentEditPointsSalesmanId = id;
        document.getElementById('quickEditPointsInput').value = pointsBalance !== undefined ? pointsBalance : '0';
        document.getElementById('err-quick-points').innerText = '';
        document.getElementById('editPointsModal').style.display = 'flex';
    }

    function closeEditPointsModal() {
        document.getElementById('editPointsModal').style.display = 'none';
        currentEditPointsSalesmanId = null;
    }

    function submitEditPoints() {
        const points = document.getElementById('quickEditPointsInput').value;
        if (!currentEditPointsSalesmanId) return;

        fetch(`${window.BASE_PATH}/salesmen/${currentEditPointsSalesmanId}/update-points`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ points: points })
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

    window.onclick = function(event) {
        if (event.target.id == 'salesmanModal') closeSalesmanModal();
        if (event.target.id == 'performanceModal') closePerformanceModal();
        if (event.target.id == 'editPointsModal') closeEditPointsModal();
    }

    document.addEventListener('click', closeAllActionMenus);
    document.addEventListener('scroll', closeAllActionMenus, true);
</script>
@endsection
