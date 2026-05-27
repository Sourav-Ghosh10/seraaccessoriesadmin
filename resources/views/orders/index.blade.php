@extends('layouts.app')

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Finalized Orders</h3>
            {{-- <a href="{{ route('orders.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Order</a> --}}
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Dealer Name</th>
                        <th>Total Amount</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($finalOrders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->member->name }}</td>
                            <td>₹ {{ number_format($order->amount, 2) }}</td>
                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                            <td>
                                @if($order->status == 'Confirmed')
                                    <span class="badge badge-success">Confirmed</span>
                                @elseif($order->status == 'Out for Delivery')
                                    <span class="badge badge-primary"
                                        style="background: rgba(59, 130, 246, 0.2); color: #3b82f6;">Out for Delivery</span>
                                @elseif($order->status == 'Delivered')
                                    <span class="badge badge-success">Delivered</span>
                                @elseif($order->status == 'Processing')
                                    <span class="badge badge-warning">Processing</span>
                                @else
                                    <span class="badge badge-danger">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('orders.show', $order->id) }}" class="btn glass"
                                    style="padding: 5px 12px; font-size: 11px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($order->challan_file)
                                    <a href="{{ asset('uploads/' . $order->challan_file) }}" target="_blank" class="btn glass"
                                        style="padding: 5px 12px; font-size: 11px; color: var(--primary);">

                                        <i class="fas fa-file-download"></i> Challan
                                    </a>
                                @endif
                                @php $role = session('role', 'Admin'); @endphp
                                @if($role == 'Admin' || $role == 'Operations')
                                    <button class="btn glass" style="padding: 5px 12px; font-size: 11px;"
                                        onclick="openDeliveryModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ $order->delivery->vehicle_no ?? '' }}', '{{ $order->delivery->vehicle_type ?? '' }}', '{{ $order->delivery->driver_phone ?? '' }}', '{{ $order->delivery ? \Carbon\Carbon::parse($order->delivery->expected_delivery_at)->format('Y-m-d') : date('Y-m-d') }}', '{{ $order->delivery ? \Carbon\Carbon::parse($order->delivery->expected_delivery_at)->format('H:i') : date('H:i') }}', '{{ addslashes($order->delivery->remarks ?? '') }}')">
                                        <i class="fas fa-truck"></i> Delivery
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('scripts')
    <!-- Delivery Modal -->
    <div id="deliveryModal"
        style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; padding-top: 80px; overflow-y: auto;">
        <div class="card"
            style="width: 100%; max-width: 500px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin-bottom: 50px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Update Delivery Status</h3>
                <div onclick="closeModal()"
                    style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                </div>
            </div>

            <input type="hidden" id="realOrderId">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label"
                    style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Order
                    Reference</label>
                <input type="text" id="modalOrderId" class="form-control"
                    style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); cursor: not-allowed;"
                    readonly>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label"
                        style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Vehicle
                        No</label>
                    <input type="text" id="vehicleNo" class="form-control" placeholder="AR-01-XXXX"
                        style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
                <div class="form-group">
                    <label class="form-label"
                        style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Vehicle
                        Type</label>
                    <input type="text" id="vehicleType" class="form-control" placeholder="e.g. Truck, Van"
                        style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label"
                    style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Phone
                    No</label>
                <input type="tel" id="phoneNo" class="form-control" placeholder="Enter phone number"
                    style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label"
                        style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Expected
                        Delivery Date</label>
                    <input type="date" class="form-control" id="deliveryDate"
                        style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
                <div class="form-group">
                    <label class="form-label"
                        style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Time</label>
                    <input type="time" class="form-control" id="deliveryTime"
                        style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label"
                    style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Delivery
                    Remarks</label>
                <textarea class="form-control" id="deliveryRemarks"
                    style="height: 120px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); resize: none;"
                    placeholder="Enter any specific delivery instructions or current status..."></textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
                <button class="btn glass" onclick="closeModal()"
                    style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
                <button class="btn btn-primary" id="submitBtn" onclick="submitDelivery()"
                    style="padding: 12px 30px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Submit Update</button>
            </div>
        </div>
    </div>

    <style>
        @keyframes modalIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        function openDeliveryModal(id, orderNum, vNo, vType, phone, date, time, remarks) {
            document.getElementById('modalOrderId').value = orderNum;
            document.getElementById('realOrderId').value = id;
            document.getElementById('vehicleNo').value = vNo || '';
            document.getElementById('vehicleType').value = vType || '';
            document.getElementById('phoneNo').value = phone || '';
            document.getElementById('deliveryDate').value = date;
            document.getElementById('deliveryTime').value = time;
            document.getElementById('deliveryRemarks').value = remarks || '';

            document.getElementById('deliveryModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('deliveryModal').style.display = 'none';
        }

        function submitDelivery() {
            const id = document.getElementById('realOrderId').value;
            const submitBtn = document.getElementById('submitBtn');

            const data = {
                vehicle_no: document.getElementById('vehicleNo').value,
                vehicle_type: document.getElementById('vehicleType').value,
                driver_phone: document.getElementById('phoneNo').value,
                expected_delivery_date: document.getElementById('deliveryDate').value,
                expected_delivery_time: document.getElementById('deliveryTime').value,
                delivery_remarks: document.getElementById('deliveryRemarks').value,
                _token: '{{ csrf_token() }}'
            };

            if (!data.expected_delivery_date) {
                alert('Please select an expected delivery date.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            fetch(`${window.BASE_PATH}/orders/${id}/update-delivery`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error'));
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Submit Update';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong!');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit Update';
                });
        }

        // Close modal on click outside
        window.onclick = function (event) {
            const deliveryModal = document.getElementById('deliveryModal');
            if (event.target == deliveryModal) {
                closeModal();
            }
        }
    </script>
@endsection