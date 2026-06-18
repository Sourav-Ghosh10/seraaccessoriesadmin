@extends('layouts.app')

@section('content')
    @php
        $backUrl = route('orders.index');
        $from = request('from');
        
        if ($from === 'requests') {
            $backUrl = route('order-requests');
        } elseif (url()->previous() && str_contains(url()->previous(), url('/'))) {
            // If there's a valid previous URL within our application, use it
            // This ensures clicking "Back" from Rewards takes you back to Rewards
            $backUrl = url()->previous();
        }
    @endphp
    <div style="margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
        <a href="{{ $backUrl }}" class="btn glass"
            style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 0;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h3 style="margin: 0;">Order Details: {{ $order['id'] }}</h3>
    </div>

    <div class="card"
        style="padding: 40px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <!-- Header Info -->
        <div
            style="display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 30px;">
            <div>
                <p
                    style="margin: 0; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                    Dealer Information</p>
                <h2 style="margin: 10px 0 5px 0; color: var(--primary);">{{ $order->member->name }}</h2>
                <p style="margin: 0; color: var(--text-muted);">Status: <span
                        class="badge {{ $order->status == 'Confirmed' ? 'badge-success' : 'badge-warning' }}">{{ $order->status }}</span>
                </p>
            </div>
            <div style="text-align: right;">
                <p
                    style="margin: 0; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                    Order Date</p>
                <h3 style="margin: 10px 0 0 0;">{{ $order->created_at->format('d M, Y') }}</h3>
                <p style="margin: 5px 0 0 0; color: var(--text-muted);">Reference: {{ $order->order_number }}</p>
            </div>
        </div>

        <!-- Delivery & Transport Details Section -->
        <div
            style="background: rgba(154, 90, 58, 0.05); border: 1px solid rgba(154, 90, 58, 0.2); border-radius: 15px; padding: 25px; margin-bottom: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; align-items: start;">
            <div>
                <p
                    style="margin: 0; color: var(--primary); font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; margin-bottom: 8px;">
                    Delivery Information</p>
                @if($order->delivery)
                    <div style="margin-bottom: 12px;">
                        <span style="font-size: 12px; color: var(--text-muted); display: block;">Expected Delivery:</span>
                        <strong style="color: #fff; font-size: 15px;">{{ \Carbon\Carbon::parse($order->delivery->expected_delivery_at)->format('d M, Y \a\t h:i A') }}</strong>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <span style="font-size: 12px; color: var(--text-muted); display: block;">Vehicle Details:</span>
                        <strong style="color: #fff; font-size: 14px;">{{ $order->delivery->vehicle_no }} ({{ $order->delivery->vehicle_type }})</strong>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: var(--text-muted); display: block;">Driver Phone:</span>
                        <strong style="color: #fff; font-size: 14px;">{{ $order->delivery->driver_phone }}</strong>
                    </div>
                @else
                    <div style="margin-bottom: 12px;">
                        <span style="font-size: 12px; color: var(--text-muted); display: block;">Expected Delivery:</span>
                        <strong style="color: #fff; font-size: 15px;">{{ $order->created_at->addDays(3)->format('d M, Y') }}</strong>
                    </div>
                    <div style="padding: 10px 15px; background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1); border-radius: 8px; color: var(--text-muted); font-size: 12px; margin-bottom: 12px;">
                        <i class="fas fa-shipping-fast" style="margin-right: 5px;"></i> Transport details not yet scheduled.
                    </div>
                @endif

                @if($order->distributor_id)
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <span style="font-size: 12px; color: var(--text-muted); display: block;">Assigned Distributor:</span>
                        <strong style="color: var(--primary); font-size: 14px; display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-truck-loading"></i>
                            @if($order->distributor)
                                {{ $order->distributor->name }} (ID: {{ $order->distributor->id }})
                                @if($order->distributor->mobile)
                                    <span style="color: var(--text-muted); font-weight: normal; font-size: 12px;"> - {{ $order->distributor->mobile }}</span>
                                @endif
                            @else
                                Distributor ID: {{ $order->distributor_id }}
                            @endif
                        </strong>
                    </div>
                @endif
            </div>
            <div style="border-left: 1px solid rgba(255,255,255,0.1); padding-left: 30px;">
                <p
                    style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; margin-bottom: 8px;">
                    Delivery Remarks / Transport Details</p>
                <p style="margin: 0; color: #cbd5e1; line-height: 1.6; font-size: 14px;">
                    @if($order->delivery && $order->delivery->remarks)
                        {{ $order->delivery->remarks }}
                    @else
                        {{ $order->description ?? 'No specific delivery remarks provided.' }}
                    @endif
                </p>
            </div>
        </div>

        <!-- Dealer Acknowledgment / Confirmation Section -->
        <div
            style="background: {{ $order->received_at ? 'rgba(34, 197, 94, 0.04)' : 'rgba(234, 179, 8, 0.04)' }}; border: 1px solid {{ $order->received_at ? 'rgba(34, 197, 94, 0.2)' : 'rgba(234, 179, 8, 0.2)' }}; border-radius: 15px; padding: 25px; margin-bottom: 40px; display: grid; grid-template-columns: 250px 1fr; gap: 30px; align-items: center; transition: all 0.3s ease;">
            <div>
                <p
                    style="margin: 0; color: {{ $order->received_at ? '#4ade80' : '#eab308' }}; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; margin-bottom: 10px;">
                    Dealer Receipt Status</p>
                @if($order->received_at)
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="badge badge-success" style="background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); font-size: 13px; padding: 6px 14px; border-radius: 20px;">
                            <i class="fas fa-check-circle" style="margin-right: 5px;"></i> Order Received
                        </span>
                    </div>
                @else
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="badge badge-warning" style="background: rgba(234, 179, 8, 0.15); color: #fbbf24; border: 1px solid rgba(234, 179, 8, 0.2); font-size: 13px; padding: 6px 14px; border-radius: 20px;">
                            <i class="fas fa-clock" style="margin-right: 5px;"></i> Pending Confirmation
                        </span>
                    </div>
                @endif
            </div>
            <div style="border-left: 1px solid rgba(255,255,255,0.1); padding-left: 30px;">
                <p
                    style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; margin-bottom: 8px;">
                    Acknowledgment Details</p>
                <p style="margin: 0; color: #cbd5e1; line-height: 1.6; font-size: 14px;">
                    @if($order->received_at)
                        The dealer confirmed the receipt of this order on <strong style="color: #fff;">{{ $order->received_at->format('d M, Y \a\t h:i A') }}</strong>.
                    @else
                        This order has been shipped or scheduled, but has not yet been marked as received by the dealer via their mobile app.
                    @endif
                </p>
            </div>
        </div>

        <!-- Items Table and Summary removed as requested -->

        <!-- Footer Actions -->
        <div
            style="margin-top: 50px; display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px;">
            @if($order->challan_file)
                <a href="{{ asset('uploads/' . $order->challan_file) }}" download="Challan_{{ $order->order_number }}"
                    class="btn glass"
                    style="padding: 12px 25px; border: 1px solid var(--primary); background: rgba(154, 90, 58, 0.1); color: var(--primary);">
                    <i class="fas fa-file-download"></i> Download Challan
                </a>
            @else
                <div
                    style="padding: 10px 20px; border: 1px dashed rgba(255,255,255,0.1); border-radius: 8px; color: var(--text-muted); font-size: 13px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle"></i> No Challan Uploaded
                </div>
            @endif
            @if($order->invoice_file)
                <a href="{{ asset('uploads/' . $order->invoice_file) }}" target="_blank" class="btn btn-primary"
                    style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">

                    <i class="fas fa-file-invoice"></i> Download Invoice
                </a>
            @endif
        </div>

        <script>
            function uploadChallan(input, orderId) {
                if (input.files && input.files[0]) {
                    const formData = new FormData();
                    formData.append('challan_file', input.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch(`${window.BASE_PATH}/orders/${orderId}/upload-challan`, {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                alert('Challan uploaded successfully!');
                                location.reload();
                            } else {
                                alert('Upload failed: ' + result.message);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('An error occurred during upload.');
                        });
                }
            }
        </script>
    </div>
@endsection