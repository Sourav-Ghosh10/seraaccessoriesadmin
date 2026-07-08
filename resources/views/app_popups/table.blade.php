@foreach($popups as $popup)
<tr>
    <td>#{{ $popup->id }}</td>
    <td>
        <div style="font-weight: 600; color: #fff; font-size: 14px;">{{ $popup->title }}</div>
        @if($popup->description)
            <div style="color: var(--text-muted); font-size: 12px; max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">
                {{ $popup->description }}
            </div>
        @endif
    </td>
    <td>
        <label class="switch" style="margin: 0;">
            <input type="checkbox" onchange="togglePopupStatus('{{ $popup->id }}', this)" {{ $popup->status ? 'checked' : '' }}>
            <span class="slider round"></span>
        </label>
    </td>
    <td>
        <span style="color: var(--text-muted); font-size: 12px;">{{ $popup->created_at ? $popup->created_at->format('d M Y') : '—' }}</span>
    </td>
    <td>
        <div style="display: flex; gap: 8px; align-items: center;">
            <a href="{{ route('app-popups.edit', $popup->id) }}" class="btn glass" style="padding: 6px 12px; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; color: #fff;" title="Edit Popup">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </td>
</tr>
@endforeach

@if(count($popups) == 0)
<tr>
    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
            <i class="fas fa-bullhorn" style="font-size: 32px; opacity: 0.3;"></i>
            <span style="font-size: 14px;">No popup announcements found matching your criteria.</span>
        </div>
    </td>
</tr>
@endif
