@extends('layouts.app')

@section('title', 'City Management')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 4px; height: 24px; background: var(--primary); border-radius: 2px;"></div>
            <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #fff;">City List</h3>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <div style="display: flex; gap: 10px; margin: 0;">
                <input type="text" id="searchInput" onkeyup="clearTimeout(window.searchTimeout); window.searchTimeout = setTimeout(() => { searchCities(this.value); }, 500);" class="form-control" placeholder="Search cities..." style="width: 250px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;">
            </div>
            <button class="btn btn-primary" onclick="openCityModal()" style="box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">
                <i class="fas fa-plus"></i> Add City
            </button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>City Name</th>
                    <th>State Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="citiesTableBody">
                @foreach($cities as $city)
                <tr>
                    <td>#{{ $city->id }}</td>
                    <td>{{ $city->city }}</td>
                    <td>{{ $city->state ? $city->state->name : 'N/A' }}</td>
                    <td>
                        <label class="switch">
                            <input type="checkbox" onchange="toggleCityStatus('{{ $city->id }}', this)" {{ $city->status ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <td>
                        <button class="btn glass" onclick="editCity('{{ $city->id }}', '{{ addslashes($city->city) }}', '{{ $city->state_id }}')" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                @endforeach
                @if(count($cities) == 0)
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">No cities found.</td>
                </tr>
                @endif
            </tbody>
        </table>
        <div id="paginationContainer" style="padding: 20px 0;">
            {{ $cities->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Add/Edit City Modal -->
<div id="cityModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: 20px; width: 100%; max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 22px; font-weight: 700;">Add New City</h3>
            <div onclick="closeCityModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">City Name</label>
            <input type="text" id="cityName" class="form-control" placeholder="Enter city name..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">State Name (Optional)</label>
            <select id="stateId" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;">
                <option value="" style="color: #000;">Select a state...</option>
                @foreach($states as $state)
                    <option value="{{ $state->id }}" style="color: #000;">{{ $state->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <button class="btn glass" onclick="closeCityModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 12px 30px;">Cancel</button>
            <button id="submitBtn" class="btn btn-primary" onclick="submitCity()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Save City</button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.form-control:focus { outline: none; border-color: var(--primary); }

/* Switch CSS */
.switch { position: relative; display: inline-block; width: 34px; height: 20px; margin: 0; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: #fff; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: rgba(255,255,255,0.1); }
input:checked + .slider:before { transform: translateX(14px); background-color: var(--primary); }

</style>
@endpush

@section('scripts')
<script>
    let currentCityId = null;

    function searchCities(query) {
        const url = `${window.BASE_PATH}/cities?search=${encodeURIComponent(query)}`;
        fetchCities(url);
    }

    function fetchCities(url) {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('citiesTableBody');
            tbody.innerHTML = '';
            if (data.cities.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 20px;">No cities found.</td></tr>`;
                document.getElementById('paginationContainer').innerHTML = '';
                return;
            }
            data.cities.forEach(city => {
                const stateName = city.state ? city.state.name : 'N/A';
                const checked = city.status ? 'checked' : '';
                const escapedCityName = city.city.replace(/'/g, "\\'");
                const tr = `
                    <tr>
                        <td>#${city.id}</td>
                        <td>${city.city}</td>
                        <td>${stateName}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" onchange="toggleCityStatus('${city.id}', this)" ${checked}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <button class="btn glass" onclick="editCity('${city.id}', '${escapedCityName}', '${city.state_id !== null ? city.state_id : ''}')" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += tr;
            });
            document.getElementById('paginationContainer').innerHTML = data.pagination;
        })
        .catch(error => {
            console.error('Error fetching search results:', error);
        });
    }

    function openCityModal() {
        currentCityId = null;
        document.getElementById('modalTitle').innerText = 'Add New City';
        document.getElementById('submitBtn').innerText = 'Save City';
        document.getElementById('cityName').value = '';
        document.getElementById('stateId').value = '';
        document.getElementById('cityModal').style.display = 'flex';
    }

    function editCity(id, name, state_id) {
        currentCityId = id;
        document.getElementById('modalTitle').innerText = 'Edit City: ' + name;
        document.getElementById('submitBtn').innerText = 'Update City';
        document.getElementById('cityName').value = name;
        document.getElementById('stateId').value = state_id !== 'null' ? state_id : '';
        document.getElementById('cityModal').style.display = 'flex';
    }

    function closeCityModal() {
        document.getElementById('cityModal').style.display = 'none';
    }

    function submitCity() {
        const isEdit = currentCityId !== null;
        const url = isEdit ? `${window.BASE_PATH}/cities/${currentCityId}` : `${window.BASE_PATH}/cities`;
        const method = isEdit ? 'PUT' : 'POST';

        const data = {
            city: document.getElementById('cityName').value,
            state_id: document.getElementById('stateId').value,
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

    function toggleCityStatus(id, checkbox) {
        const url = `${window.BASE_PATH}/cities/${id}/toggle-status`;
        
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                alert('Error: ' + (result.message || 'Something went wrong'));
                checkbox.checked = !checkbox.checked; // Revert
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
            checkbox.checked = !checkbox.checked; // Revert
        });
    }

    window.onclick = function(event) {
        if (event.target.id == 'cityModal') {
            closeCityModal();
        }
    }
</script>
@endsection
