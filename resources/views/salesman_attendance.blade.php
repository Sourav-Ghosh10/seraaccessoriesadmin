@extends('layouts.app')

@section('title', 'Salesman Attendance')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <form method="GET" action="{{ route('salesman.attendance') }}" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;" id="attendanceFilterForm">
            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search salesman..." value="{{ request('search') }}" style="width: 200px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;" autocomplete="off">
            <button type="submit" style="display: none;"></button>
            
            <select name="salesman_id" class="form-control" style="width: 150px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;" onchange="this.form.submit()">
                <option value="">All Salesmen</option>
                @foreach($allSalesmen as $salesman)
                    <option value="{{ $salesman->id }}" {{ request('salesman_id') == $salesman->id ? 'selected' : '' }}>
                        {{ $salesman->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date" class="form-control" value="{{ $date }}" style="width: 150px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #fff;" onchange="this.form.submit()">
            
            @if(request('salesman_id') || request('date') != now()->toDateString() || request('search'))
                <a href="{{ route('salesman.attendance') }}" class="btn glass" style="color: var(--danger); text-decoration: none;"><i class="fas fa-times"></i> Clear</a>
            @endif
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Salesman</th>
                    <th>Date</th>
                    <th>Clock In Time</th>
                    <th>Clock In Location</th>
                    <th>Clock Out Time</th>
                    <th>Clock Out Location</th>
                    <th>Total Hours</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesmenList as $member)
                @php $record = $member->attendances->first(); @endphp
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar" style="width: 32px; height: 32px; font-size: 14px; background: rgba(255,255,255,0.1); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                {{ substr($member->name ?? 'S', 0, 1) }}
                            </div>
                            <div>
                                <div style="font-weight: 500;">{{ $member->name ?? 'N/A' }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $member->mobile ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</td>
                    
                    @if($record)
                        <td>
                            @if($record->clock_in_time)
                                <span class="badge badge-success" style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 5px 10px; border-radius: 6px;">
                                    <i class="fas fa-sign-in-alt" style="margin-right: 5px;"></i> {{ $record->clock_in_time->format('h:i A') }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td style="max-width: 250px;">
                            @if($record->clock_in_address)
                                <div style="font-size: 12px; line-height: 1.4;">{{ $record->clock_in_address }}</div>
                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 3px;">
                                    <i class="fas fa-map-marker-alt"></i> {{ $record->clock_in_latitude }}, {{ $record->clock_in_longitude }}
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($record->clock_out_time)
                                <span class="badge badge-danger" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 5px 10px; border-radius: 6px;">
                                    <i class="fas fa-sign-out-alt" style="margin-right: 5px;"></i> {{ $record->clock_out_time->format('h:i A') }}
                                </span>
                            @else
                                <span class="badge badge-warning" style="background: rgba(245, 158, 11, 0.1); color: var(--warning); padding: 5px 10px; border-radius: 6px;">
                                    <i class="fas fa-clock" style="margin-right: 5px;"></i> Working
                                </span>
                            @endif
                        </td>
                        <td style="max-width: 250px;">
                            @if($record->clock_out_address)
                                <div style="font-size: 12px; line-height: 1.4;">{{ $record->clock_out_address }}</div>
                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 3px;">
                                    <i class="fas fa-map-marker-alt"></i> {{ $record->clock_out_latitude }}, {{ $record->clock_out_longitude }}
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($record->total_hours)
                                <strong style="color: var(--primary);">{{ $record->total_hours }} hrs</strong>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('salesman.attendance.details', $record->id) }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">
                                <i class="fas fa-eye" style="margin-right: 5px;"></i> Details
                            </a>
                        </td>
                    @else
                        <td colspan="6" style="text-align: center; color: var(--text-muted);">
                            No attendance logged for this date
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <div style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;">
                            <i class="fas fa-users-slash"></i>
                        </div>
                        <div>No salesmen found matching the criteria.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($salesmenList->hasPages())
    <div style="margin-top: 20px; display: flex; justify-content: flex-end;" id="paginationContainer">
        {{ $salesmenList->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
    <style>
        .pagination { display: flex; list-style: none; gap: 5px; margin: 0; padding: 0; }
        .page-item .page-link { 
            background: rgba(255,255,255,0.03); 
            border: 1px solid rgba(255,255,255,0.1); 
            color: #fff; 
            padding: 8px 12px; 
            border-radius: 6px; 
            text-decoration: none;
            font-size: 13px;
        }
        .page-item.active .page-link { background: var(--primary); border-color: var(--primary); }
        .page-item.disabled .page-link { opacity: 0.5; pointer-events: none; }
        .page-item:not(.active):not(.disabled) .page-link:hover { background: rgba(255,255,255,0.1); }
    </style>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        let typingTimer;
        const doneTypingInterval = 600; // Time in ms

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    document.getElementById('attendanceFilterForm').submit();
                }, doneTypingInterval);
            });
            
            // Move cursor to the end of the input if there's a value (prevents cursor jumping to start on reload)
            if (searchInput.value) {
                const len = searchInput.value.length;
                searchInput.setSelectionRange(len, len);
                searchInput.focus();
            }
        }
    });
</script>
@endsection
