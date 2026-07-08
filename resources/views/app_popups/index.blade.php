@extends('layouts.app')

@section('title', 'App Popup Management')

@section('styles')
<style>
/* Switch CSS */
.switch { position: relative; display: inline-block; width: 38px; height: 22px; margin: 0; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; border: 1px solid rgba(255,255,255,0.1); }
.slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: #fff; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
input:checked + .slider { background-color: rgba(255,255,255,0.1); border-color: var(--primary); }
input:checked + .slider:before { transform: translateX(16px); background-color: var(--primary); }

.filter-select {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
}
.filter-select:focus {
    border-color: var(--primary);
}
.search-input {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
    border-radius: 8px;
    padding: 8px 14px 8px 36px;
    font-size: 13px;
    width: 250px;
    outline: none;
    transition: all 0.2s;
}
.search-input:focus {
    border-color: var(--primary);
    background: rgba(255,255,255,0.05);
    width: 280px;
}
</style>
@endsection

@section('content')
<div class="card animate-fade">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 4px; height: 28px; background: var(--primary); border-radius: 2px;"></div>
            <div>
                <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #fff;">App Popup Management</h3>
                <p style="margin: 2px 0 0 0; font-size: 13px; color: var(--text-muted);">Manage mobile app announcements, promotions, and maintenance alerts</p>
            </div>
        </div>
        @if(\App\Models\AppPopup::count() == 0)
        <div>
            <a href="{{ route('app-popups.create') }}" class="btn btn-primary" style="padding: 10px 20px; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3); display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                <i class="fas fa-plus"></i> Create New Popup
            </a>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #22c55e; padding: 14px 18px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle" style="font-size: 18px;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filters and Search Bar --}}
    <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 16px; margin-bottom: 25px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; justify-content: space-between;">
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px;"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Search by title..." oninput="debounceSearch()">
            </div>

            <select id="statusFilter" class="filter-select" onchange="filterPopups()">
                <option value="all">All Statuses</option>
                <option value="1">Active (On)</option>
                <option value="0">Inactive (Off)</option>
            </select>
        </div>

        <div>
            <button type="button" onclick="resetFilters()" class="btn glass" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; color: var(--text-muted);">
                <i class="fas fa-redo-alt"></i> Reset Filters
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-container" style="position: relative; min-height: 200px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Title & Description</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 150px;">Created At</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody id="popupsTableBody">
                @include('app_popups.table')
            </tbody>
        </table>
        <div id="paginationContainer" style="padding: 20px 0; display: flex; justify-content: flex-end;">
            {{ $popups->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let searchTimeout = null;

    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterPopups();
        }, 400);
    }

    function filterPopups(url = null) {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;

        let fetchUrl = url || `${window.BASE_PATH}/app-popups?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`;

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('popupsTableBody').innerHTML = data.html;
            document.getElementById('paginationContainer').innerHTML = data.pagination;
            attachPaginationLinks();
        })
        .catch(err => {
            console.error('Error filtering popups:', err);
        });
    }

    function attachPaginationLinks() {
        const container = document.getElementById('paginationContainer');
        const links = container.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                filterPopups(this.getAttribute('href'));
            });
        });
    }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = 'all';
        filterPopups();
    }

    function togglePopupStatus(id, checkbox) {
        const url = `${window.BASE_PATH}/app-popups/${id}/toggle-status`;
        
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Error updating status');
                checkbox.checked = !checkbox.checked;
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred while updating status.');
            checkbox.checked = !checkbox.checked;
        });
    }

    function deletePopup(id, title) {
        if (!confirm(`Are you sure you want to delete the popup "${title}"? This action cannot be undone.`)) {
            return;
        }

        const url = `${window.BASE_PATH}/app-popups/${id}`;
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                filterPopups();
            } else {
                alert(data.message || 'Error deleting popup');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred while deleting the popup.');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        attachPaginationLinks();
    });
</script>
@endsection
