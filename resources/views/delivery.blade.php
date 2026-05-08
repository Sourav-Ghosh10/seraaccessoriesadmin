@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Delivery Status Management</h3>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Expected Delivery</th>
                    <th>Transport Details (Remarks)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ORD-5580</td>
                    <td>10 May, 2024</td>
                    <td>Bus No: AR-01-2234, Driver: 9876543210</td>
                    <td><span class="badge badge-warning">Out for Delivery</span></td>
                    <td>
                        <button class="btn glass" style="padding: 5px 12px; font-size: 11px;" onclick="openDeliveryModal('ORD-5580')">
                            <i class="fas fa-edit"></i> Update
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>ORD-5581</td>
                    <td>08 May, 2024</td>
                    <td>Self Pickup - Contact: John Doe</td>
                    <td><span class="badge badge-success">Delivered</span></td>
                    <td>
                        <button class="btn glass" style="padding: 5px 12px; font-size: 11px;" onclick="openDeliveryModal('ORD-5581')">
                            <i class="fas fa-edit"></i> Update
                        </button>
                    </td>
                </tr>
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
        const modal = document.getElementById('deliveryModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
@endsection
