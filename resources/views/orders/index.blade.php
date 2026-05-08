@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Finalized Orders</h3>
        <a href="{{ route('orders.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Order</a>
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
                    <td><strong>{{ $order['id'] }}</strong></td>
                    <td>{{ $order['dealer'] }}</td>
                    <td>{{ $order['amount'] }}</td>
                    <td>{{ $order['date'] }}</td>
                    <td>
                        <span class="badge {{ $order['status'] == 'Confirmed' ? 'badge-success' : 'badge-warning' }}">
                            {{ $order['status'] }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('orders.show', $order['id']) }}" class="btn glass" style="padding: 5px 12px; font-size: 11px;">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button class="btn glass" style="padding: 5px 12px; font-size: 11px;"><i class="fas fa-download"></i> PDF</button>
                        <button class="btn glass" style="padding: 5px 12px; font-size: 11px;" onclick="openDeliveryModal('{{ $order['id'] }}')">
                            <i class="fas fa-truck"></i> Delivery
                        </button>
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
<div id="deliveryModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; padding-top: 80px; overflow-y: auto;">
    <div class="card" style="width: 100%; max-width: 500px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin-bottom: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Update Delivery Status</h3>
            <div onclick="closeModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Order Reference</label>
            <input type="text" id="modalOrderId" class="form-control" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); cursor: not-allowed;" readonly>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Expected Delivery Date</label>
            <input type="date" class="form-control" id="deliveryDate" value="{{ date('Y-m-d') }}" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Delivery Remarks</label>
            <textarea class="form-control" id="deliveryRemarks" style="height: 120px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); resize: none;" placeholder="Enter any specific delivery instructions or current status..."></textarea>
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
            <button class="btn glass" onclick="closeModal()" style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
            <button class="btn btn-primary" onclick="submitDelivery()" style="padding: 12px 30px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Submit Update</button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
    function openDeliveryModal(orderId) {
        document.getElementById('modalOrderId').value = orderId;
        const modal = document.getElementById('deliveryModal');
        modal.style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('deliveryModal').style.display = 'none';
    }

    function submitDelivery() {
        const orderId = document.getElementById('modalOrderId').value;
        const date = document.getElementById('deliveryDate').value;
        const remarks = document.getElementById('deliveryRemarks').value;
        
        if (!date) {
            alert('Please select an expected delivery date.');
            return;
        }

        // Only frontend logic
        alert(`Delivery status updated for ${orderId}\nExpected Date: ${date}\nRemarks: ${remarks}`);
        closeModal();
    }

    // Close modal on click outside
    window.onclick = function(event) {
        const deliveryModal = document.getElementById('deliveryModal');
        if (event.target == deliveryModal) {
            closeModal();
        }
    }
</script>
@endsection
