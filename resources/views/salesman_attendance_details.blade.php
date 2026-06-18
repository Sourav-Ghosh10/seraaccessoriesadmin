@extends('layouts.app')

@section('title', 'Attendance Details & History')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('salesman.attendance') }}" class="btn glass" style="text-decoration: none;">
        <i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back to Attendance
    </a>
</div>

<div class="grid-2" style="margin-bottom: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            Salesman Information
        </h3>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div class="avatar" style="width: 50px; height: 50px; font-size: 20px; background: rgba(255,255,255,0.1); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                {{ substr($attendance->member->name ?? 'S', 0, 1) }}
            </div>
            <div>
                <h4 style="margin: 0; font-size: 18px;">{{ $attendance->member->name ?? 'N/A' }}</h4>
                <p style="margin: 5px 0 0 0; color: var(--text-muted); font-size: 13px;">
                    <i class="fas fa-phone-alt"></i> {{ $attendance->member->mobile ?? 'No Mobile' }} <br>
                    <i class="fas fa-envelope"></i> {{ $attendance->member->email ?? 'No Email' }}
                </p>
            </div>
        </div>
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: var(--text-muted);">Date:</span>
                <strong>{{ $attendance->date->format('d M, Y (l)') }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Total Hours Worked:</span>
                <strong style="color: var(--primary);">{{ $attendance->total_hours ?? 'N/A' }} hrs</strong>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            Clock In / Out Details
        </h3>
        
        <div style="margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-start;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(34, 197, 94, 0.1); color: var(--success); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <div>
                <div style="font-weight: 600; margin-bottom: 5px;">Clock In</div>
                @if($attendance->clock_in_time)
                    <div style="font-size: 14px; color: var(--success); margin-bottom: 5px;">{{ $attendance->clock_in_time->format('h:i A') }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); line-height: 1.4;">
                        {{ $attendance->clock_in_address ?? 'Location not available' }}
                        <br>
                        <small style="opacity: 0.7;"><i class="fas fa-map-marker-alt"></i> {{ $attendance->clock_in_latitude }}, {{ $attendance->clock_in_longitude }}</small>
                    </div>
                @else
                    <div style="color: var(--text-muted); font-size: 13px;">No clock-in record</div>
                @endif
            </div>
        </div>

        <div style="display: flex; gap: 15px; align-items: flex-start;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: var(--danger); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <div>
                <div style="font-weight: 600; margin-bottom: 5px;">Clock Out</div>
                @if($attendance->clock_out_time)
                    <div style="font-size: 14px; color: var(--danger); margin-bottom: 5px;">{{ $attendance->clock_out_time->format('h:i A') }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); line-height: 1.4;">
                        {{ $attendance->clock_out_address ?? 'Location not available' }}
                        <br>
                        <small style="opacity: 0.7;"><i class="fas fa-map-marker-alt"></i> {{ $attendance->clock_out_latitude }}, {{ $attendance->clock_out_longitude }}</small>
                    </div>
                @else
                    <div style="color: var(--warning); font-size: 13px; padding: 4px 8px; background: rgba(245, 158, 11, 0.1); border-radius: 4px; display: inline-block;">Currently Working / Pending</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 25px;">
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
        <i class="fas fa-store" style="color: var(--primary); margin-right: 8px;"></i> Dealer Visits Today
    </h3>
    
    @if($visits->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Dealer / Shop</th>
                        <th>Location</th>
                        <th>Notes</th>
                        <th>Photo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visits as $visit)
                        <tr>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.05);">{{ $visit->visit_time->format('h:i A') }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 500;">{{ $visit->dealer->name ?? 'Unknown Dealer' }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $visit->dealer->shop ?? '' }}</div>
                            </td>
                            <td style="max-width: 250px;">
                                <div style="font-size: 12px; line-height: 1.4;">{{ $visit->address }}</div>
                                <div style="font-size: 10px; color: var(--text-muted);"><i class="fas fa-map-marker-alt"></i> {{ $visit->latitude }}, {{ $visit->longitude }}</div>
                            </td>
                            <td style="max-width: 200px;">
                                <div style="font-size: 13px; color: var(--text-muted); white-space: pre-wrap;">{{ $visit->notes ?? '-' }}</div>
                            </td>
                            <td>
                                @if($visit->photo_path)
                                    <a href="{{ url($visit->photo_path) }}" target="_blank">
                                        <img src="{{ url($visit->photo_path) }}" alt="Visit Photo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1);">
                                    </a>
                                @else
                                    <span style="color: var(--text-muted); font-size: 12px;">No Photo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 30px; color: var(--text-muted);">
            <div style="font-size: 30px; margin-bottom: 10px; opacity: 0.5;"><i class="fas fa-store-slash"></i></div>
            <div>No dealer visits recorded on this day.</div>
        </div>
    @endif
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 20px;">
        <h3 style="margin: 0; font-size: 18px;">
            <i class="fas fa-route" style="color: var(--secondary); margin-right: 8px;"></i> Location History Logs
        </h3>
        @if($locations->count() > 0)
        <div style="display: flex; background: rgba(255,255,255,0.05); border-radius: 6px; padding: 3px;">
            <button type="button" id="btnTabMap" style="padding: 6px 15px; border-radius: 4px; font-size: 13px; border: none; background: var(--primary); color: #fff; cursor: pointer; transition: 0.2s;">
                <i class="fas fa-map"></i> Map
            </button>
            <button type="button" id="btnTabList" style="padding: 6px 15px; border-radius: 4px; font-size: 13px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: 0.2s;">
                <i class="fas fa-list"></i> List
            </button>
        </div>
        @endif
    </div>

    @if($locations->count() > 0)
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        
        <div id="paneMap">
            <!-- Map Container -->
            <div id="locationMap" style="height: 350px; width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); z-index: 0;"></div>
        </div>

        <div id="paneList" style="display: none;">
            <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                <table>
                <thead style="position: sticky; top: 0; background: var(--card); z-index: 1;">
                    <tr>
                        <th>Timestamp</th>
                        <th>Location</th>
                        <th>Coordinates</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $log)
                        <tr>
                            <td>{{ $log->timestamp->format('h:i A') }}</td>
                            <td>
                                <div class="geo-coord" data-lat="{{ $log->latitude }}" data-lng="{{ $log->longitude }}" style="font-size: 13px; line-height: 1.4; max-width: 300px;">
                                    <i class="fas fa-spinner fa-spin" style="color: var(--text-muted); margin-right: 5px;"></i> <span style="color: var(--text-muted);">Fetching location...</span>
                                </div>
                            </td>
                            <td>
                                <a href="https://maps.google.com/?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank" style="color: var(--secondary); text-decoration: none; font-size: 12px;">
                                    <i class="fas fa-map-marker-alt"></i> {{ $log->latitude }}, {{ $log->longitude }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
    @else
        <div style="text-align: center; padding: 30px; color: var(--text-muted);">
            <div style="font-size: 30px; margin-bottom: 10px; opacity: 0.5;"><i class="fas fa-map-marked-alt"></i></div>
            <div>No location history logs available for this day.</div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Map if we have locations
    const locationsData = @json($locations);
    
    if (locationsData && locationsData.length > 0) {
        // Find center (first location)
        const firstLoc = locationsData[0];
        const map = L.map('locationMap').setView([firstLoc.latitude, firstLoc.longitude], 13);
        
        // Add OpenStreetMap tiles (darker theme variant preferred if available, else standard)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        const latlngs = [];
        
        locationsData.forEach((log, index) => {
            const point = [log.latitude, log.longitude];
            latlngs.push(point);
            
            // Format timestamp for popup
            const date = new Date(log.timestamp);
            const timeStr = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            // Marker
            L.circleMarker(point, {
                radius: 6,
                fillColor: "var(--primary)",
                color: "#fff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map).bindPopup(`<b>${timeStr}</b><br>Lat: ${log.latitude}<br>Lng: ${log.longitude}`);
        });

        // Draw polyline connecting the dots
        if (latlngs.length > 1) {
            const polyline = L.polyline(latlngs, {color: 'var(--primary)', weight: 3, opacity: 0.7, dashArray: '5, 5'}).addTo(map);
            // Zoom the map to the polyline
            map.fitBounds(polyline.getBounds(), {padding: [30, 30]});
        }
        
        // Tab Switching Logic
        const btnTabMap = document.getElementById('btnTabMap');
        const btnTabList = document.getElementById('btnTabList');
        const paneMap = document.getElementById('paneMap');
        const paneList = document.getElementById('paneList');

        if(btnTabMap && btnTabList) {
            btnTabMap.addEventListener('click', function() {
                // Update buttons
                btnTabMap.style.background = 'var(--primary)';
                btnTabMap.style.color = '#fff';
                btnTabList.style.background = 'transparent';
                btnTabList.style.color = 'var(--text-muted)';
                
                // Show/Hide panes
                paneMap.style.display = 'block';
                paneList.style.display = 'none';
                
                // Fix map rendering issue when unhidden
                setTimeout(() => { map.invalidateSize(); }, 100);
            });

            btnTabList.addEventListener('click', function() {
                // Update buttons
                btnTabList.style.background = 'var(--primary)';
                btnTabList.style.color = '#fff';
                btnTabMap.style.background = 'transparent';
                btnTabMap.style.color = 'var(--text-muted)';
                
                // Show/Hide panes
                paneList.style.display = 'block';
                paneMap.style.display = 'none';
            });
        }
    }

    const coordsElements = document.querySelectorAll('.geo-coord');
    if (coordsElements.length === 0) return;

    const cache = {};
    let index = 0;

    function fetchNextAddress() {
        if (index >= coordsElements.length) return;

        const el = coordsElements[index];
        const lat = el.getAttribute('data-lat');
        const lng = el.getAttribute('data-lng');
        const key = `${lat},${lng}`;

        if (cache[key]) {
            el.innerHTML = cache[key];
            index++;
            fetchNextAddress();
        } else {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                headers: {
                    'Accept-Language': 'en'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    const address = `<i class="fas fa-map-marker-alt" style="color: var(--text-muted); margin-right: 5px;"></i> ${data.display_name}`;
                    cache[key] = address;
                    el.innerHTML = address;
                } else {
                    el.innerHTML = '<span style="color: var(--text-muted);">Location unavailable</span>';
                }
                index++;
                // Add a delay to respect Nominatim's usage policy (1 request per second)
                setTimeout(fetchNextAddress, 1100);
            })
            .catch(err => {
                console.error("Geocoding error:", err);
                el.innerHTML = '<span style="color: var(--danger);">Error fetching</span>';
                index++;
                setTimeout(fetchNextAddress, 1100);
            });
        }
    }

    fetchNextAddress();
});
</script>
@endsection
