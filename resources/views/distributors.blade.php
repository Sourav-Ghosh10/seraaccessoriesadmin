@extends('layouts.app')

@section('title', 'Distributor Registration')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Custom styling for dark mode select2 */
.select2-container--default .select2-selection--single {
    background-color: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1);
    height: 42px;
    display: flex;
    align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff;
    line-height: normal;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
}
.select2-dropdown {
    background-color: #0f172a;
    border: 1px solid rgba(255,255,255,0.1);
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

    <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
        <input type="text" id="searchInput" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" class="form-control" placeholder="Search by name, email, phone, ID..." style="flex: 1; min-width: 200px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        
        <select id="filterCity" class="form-control" multiple="multiple" style="width: 200px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
            <option value="all">Select All</option>
            @foreach($cities as $c)
                <option value="{{ strtolower($c->city) }}">{{ $c->city }}</option>
            @endforeach
        </select>

        <select id="filterStatus" class="form-control" style="width: 150px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <div class="table-container">
        <table id="distributorTable">
            <thead>
                <tr>
                    <th>Distributor ID</th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>GST No.</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="distributorTableBody">
                @foreach($distributors as $distributor)
                <tr class="distributor-row" data-city="{{ strtolower($distributor->city ? $distributor->city->city : '') }}" data-status="{{ strtolower($distributor->status) }}">
                    <td>{{ $distributor->dist_id ?? 'N/A' }}</td>
                    <td>{{ $distributor->name }}</td>
                    <td>{{ $distributor->email }}</td>
                    <td>{{ $distributor->mobile }}</td>
                    <td>{{ $distributor->address ?? 'N/A' }}</td>
                    <td>{{ $distributor->city ? $distributor->city->city : 'N/A' }}</td>
                    <td>{{ $distributor->gst_no ?? 'N/A' }}</td>
                    <td><span class="badge badge-success">{{ $distributor->status }}</span></td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn glass" onclick="editDistributor('{{ $distributor->id }}', '{{ addslashes($distributor->name) }}', '{{ addslashes($distributor->email) }}', '{{ addslashes($distributor->mobile) }}', '{{ $distributor->status }}', '{{ addslashes($distributor->dist_id) }}', '{{ addslashes($distributor->address) }}', '{{ addslashes($distributor->gst_no) }}', '{{ $distributor->city_id }}')" style="padding: 5px 10px; font-size: 12px;" title="Edit Distributor"><i class="fas fa-edit"></i></button>
                            <a href="{{ route('distributors.staff', $distributor->id) }}" class="btn glass" style="padding: 5px 10px; font-size: 12px; color: #fff;" title="Manage Staff"><i class="fas fa-users"></i></a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div id="paginationContainer" style="padding: 20px 0;">
            {{ $distributors->appends(request()->query())->links() }}
        </div>
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
        
        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Distributor ID (6 chars)</label>
                <input type="text" id="distIdInput" class="form-control" placeholder="e.g. DST123" maxlength="6" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                <span class="text-danger" id="err-dist_id" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">GST No.</label>
                <input type="text" id="distGst" class="form-control" placeholder="Enter GST Number" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                <span class="text-danger" id="err-gst_no" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Distributor Name</label>
            <input type="text" id="distName" class="form-control" placeholder="Enter full name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            <span class="text-danger" id="err-name" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
        </div>

        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Email Address</label>
                <input type="email" id="distEmail" class="form-control" placeholder="email@example.com" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                <span class="text-danger" id="err-email" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Phone Number</label>
                <input type="text" id="distPhone" class="form-control" placeholder="+91 00000 00000" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                <span class="text-danger" id="err-phone" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Address (Optional)</label>
                <input type="text" id="distAddress" class="form-control" placeholder="Enter Address" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                <span class="text-danger" id="err-address" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">City</label>
                <select id="distCity" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;">
                    <option value="" style="color: #000;">Select a city...</option>
                    @foreach($cities as $c)
                        <option value="{{ $c->id }}" style="color: #000;">{{ $c->city }}</option>
                    @endforeach
                </select>
                <span class="text-danger" id="err-city_id" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Password</label>
            <div style="position: relative;">
                <input type="password" id="distPassword" class="form-control" placeholder="Create secure password..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); padding-right: 40px;">
                <i class="fas fa-eye" id="toggleDistPassword" onclick="toggleDistPasswordVisibility()" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; font-size: 14px; z-index: 10;"></i>
            </div>
            <span class="text-danger" id="err-password" style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;"></span>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let currentDistributorId = null;

    $(document).ready(function() {
        $('#distCity').select2({
            dropdownParent: $('#distributorModal'),
            placeholder: "Select a city...",
            width: '100%'
        });

        $('#filterCity').select2({
            placeholder: "Select Cities",
            width: '250px',
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
            
            if (count > 1) {
                $container.addClass('has-multiple');
                $container[0].style.setProperty('--selected-text', '"' + count + ' cities selected"');
            } else {
                $container.removeClass('has-multiple');
            }
        }

        $('#filterCity').on('change select2:close', function() {
            updatePlaceholder();
            applyFilters();
        });

        // Search and Filter logic
        $('#searchInput, #filterStatus').on('input change', function() {
            applyFilters();
        });

        // AJAX Pagination
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
    function applyFilters() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            $.ajax({
                url: window.location.pathname,
                data: {
                    search: $('#searchInput').val(),
                    city: $('#filterCity').val() || [],
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

    function openDistributorModal() {
        currentDistributorId = null;
        clearErrors();
        document.getElementById('modalTitle').innerText = 'Add New Distributor';
        document.getElementById('submitBtn').innerText = 'Register Distributor';
        document.getElementById('distName').value = '';
        document.getElementById('distEmail').value = '';
        document.getElementById('distPhone').value = '';
        document.getElementById('distIdInput').value = '';
        document.getElementById('distGst').value = '';
        document.getElementById('distAddress').value = '';
        $('#distCity').val('').trigger('change');
        document.getElementById('distPassword').value = '';
        document.getElementById('distStatus').value = 'Active';
        document.getElementById('distributorModal').style.display = 'flex';
    }

    function editDistributor(id, name, email, phone, status, distId, address, gst, cityId) {
        currentDistributorId = id;
        clearErrors();
        document.getElementById('modalTitle').innerText = 'Edit Distributor: ' + name;
        document.getElementById('submitBtn').innerText = 'Update Distributor';
        document.getElementById('distName').value = name;
        document.getElementById('distEmail').value = email;
        document.getElementById('distPhone').value = phone;
        document.getElementById('distIdInput').value = distId;
        document.getElementById('distGst').value = gst;
        document.getElementById('distAddress').value = address;
        $('#distCity').val(cityId ? cityId : '').trigger('change');
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
            dist_id: document.getElementById('distIdInput').value,
            gst_no: document.getElementById('distGst').value,
            address: document.getElementById('distAddress').value,
            city_id: document.getElementById('distCity').value,
            password: document.getElementById('distPassword').value,
            status: document.getElementById('distStatus').value,
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
        if (event.target.id == 'distributorModal') {
            closeDistributorModal();
        }
    }
</script>
@endsection
