@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Incoming Order Requests</h3>
        <div style="display: flex; gap: 10px;">
             <button class="btn glass" style="font-size: 13px;"><i class="fas fa-filter"></i> Filter</button>
             <button class="btn btn-primary"><i class="fas fa-plus"></i> Create Manual Order</button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Dealer</th>
                    <th>Type</th>
                    <th>Date/Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>{{ $order['id'] }}</td>
                    <td>{{ $order['dealer'] }}</td>
                    <td>
                        @if($order['type'] == 'Voice')
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-microphone" style="color: var(--secondary);"></i>
                                <div class="glass" style="padding: 2px 10px; border-radius: 10px; font-size: 10px; display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-play" style="font-size: 8px;"></i>
                                    <span>0:45</span>
                                </div>
                            </div>
                        @elseif($order['type'] == 'Photo')
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-camera" style="color: var(--accent);"></i>
                                <div class="glass" style="width: 30px; height: 30px; border-radius: 4px; overflow: hidden;">
                                    <div style="background: #333; width: 100%; height: 100%;"></div>
                                </div>
                            </div>
                        @elseif($order['type'] == 'Call')
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-phone" style="color: #10b981;"></i>
                                <span>Phone Call</span>
                            </div>
                        @else
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-font" style="color: var(--primary);"></i>
                                <span>Text Request</span>
                            </div>
                        @endif
                    </td>
                    <td><span style="font-size: 12px; color: var(--text-muted);">{{ $order['date'] }}</span></td>
                    <td>
                        <span class="badge {{ $order['status'] == 'Processing' ? 'badge-warning' : 'badge-danger' }}">
                            {{ $order['status'] }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('orders.create', ['from_req' => $order['id'], 'dealer' => $order['dealer']]) }}" class="btn btn-primary" style="padding: 5px 12px; font-size: 11px;"><i class="fas fa-file-invoice"></i> Create Order</a>
                        <button class="btn glass" style="padding: 5px 12px; font-size: 11px; color: var(--accent);"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
