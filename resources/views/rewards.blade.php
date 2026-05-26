@extends('layouts.app')

@section('title', 'Reward Points')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: flex-end; margin-bottom: 25px;">
        <button class="btn btn-primary" onclick="openAddPointsModal()">
            <i class="fas fa-plus"></i> Add Points
        </button>
    </div>

    <div class="grid">
        <div class="card">
            <h4>Dealer Points</h4>
            <div style="font-size: 24px; font-weight: 700; color: var(--primary); margin-top: 10px;">
                {{ number_format($dealerPointsSum) }} pts
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">Total distributed this month</div>
        </div>
        <div class="card">
            <h4>Salesman Points</h4>
            <div style="font-size: 24px; font-weight: 700; color: var(--secondary); margin-top: 10px;">
                {{ number_format($salesmanPointsSum) }} pts
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">Total distributed this month</div>
        </div>
    </div>

    <div class="table-container" style="margin-top: 30px;">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entity</th>
                    <th>Order #</th>
                    <th>Points Earned</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $tx)
                <tr>
                    <td>{{ $tx->created_at->format('Y-m-d') }}</td>
                    <td>
                        {{ $tx->member->name }}
                        @if($tx->member->role == 'salesman')
                            <span style="font-size: 11px; color: var(--text-muted);"> (Salesman)</span>
                        @else
                            <span style="font-size: 11px; color: var(--text-muted);"> (Dealer)</span>
                        @endif
                    </td>
                    <td>{{ $tx->order ? $tx->order->order_number : 'N/A' }}</td>
                    <td>+{{ number_format($tx->points) }}</td>
                    <td><span class="badge badge-success" style="background: rgba(16, 185, 129, 0.2); color: #10b981;">{{ $tx->type }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">No points transactions recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Points Modal -->
<div id="addPointsModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; overflow-y: auto;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin-bottom: 50px; width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 22px; font-weight: 700;">Add Reward Points</h3>
            <div onclick="closeAddPointsModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 25px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Order Number</label>
            <select id="orderNumber" class="form-control" onchange="autoFillOrderDetails()" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;">
                <!-- Populated by JS -->
            </select>
        </div>

        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Dealer Name</label>
                <input type="text" id="dealerName" class="form-control" readonly style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: var(--text-muted);">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Dealer Point</label>
                <input type="number" id="dealerPoints" class="form-control" placeholder="0" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Salesman Name</label>
                <input type="text" id="salesmanName" class="form-control" readonly style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: var(--text-muted);">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Salesman Point</label>
                <input type="number" id="salesmanPoints" class="form-control" placeholder="0" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 40px;">
            <button class="btn glass" onclick="closeAddPointsModal()" style="border: none; background: rgba(255,255,255,0.05); padding: 12px 30px;">Cancel</button>
            <button class="btn btn-primary" onclick="submitPoints()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Add Points</button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.form-control:focus { outline: none; border-color: var(--primary); }
</style>

@endsection

@section('scripts')
<script>
    const orders = @json($orders);

    function openAddPointsModal() {
        document.getElementById('addPointsModal').style.display = 'flex';
        populateOrders();
        resetFields();
    }

    function closeAddPointsModal() {
        document.getElementById('addPointsModal').style.display = 'none';
    }

    function resetFields() {
        document.getElementById('orderNumber').value = '';
        document.getElementById('dealerName').value = '';
        document.getElementById('dealerPoints').value = '';
        document.getElementById('salesmanName').value = '';
        document.getElementById('salesmanPoints').value = '';
    }

    function populateOrders() {
        const select = document.getElementById('orderNumber');
        select.innerHTML = '<option value="" style="background: #1e293b;">Select Order</option>';
        orders.forEach(ord => {
            const option = document.createElement('option');
            option.value = ord.id;
            option.text = ord.order_number;
            option.style.background = '#1e293b';
            select.appendChild(option);
        });
    }

    function autoFillOrderDetails() {
        const orderId = document.getElementById('orderNumber').value;
        const order = orders.find(o => String(o.id) === String(orderId));
        
        if (order) {
            document.getElementById('dealerName').value = order.dealer;
            document.getElementById('salesmanName').value = order.salesman;
        } else {
            document.getElementById('dealerName').value = '';
            document.getElementById('salesmanName').value = '';
        }
    }

    function submitPoints() {
        const orderId = document.getElementById('orderNumber').value;
        const dPoints = document.getElementById('dealerPoints').value;
        const sPoints = document.getElementById('salesmanPoints').value;
        const submitBtn = document.querySelector('button[onclick="submitPoints()"]');

        if (!orderId || (!dPoints && !sPoints)) {
            alert('Please select an order and enter points');
            return;
        }

        const data = {
            order_id: orderId,
            dealer_points: dPoints || 0,
            salesman_points: sPoints || 0,
            _token: '{{ csrf_token() }}'
        };

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

        fetch(`${window.BASE_PATH}/rewards/store`, {
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
                submitBtn.innerHTML = 'Add Points';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Add Points';
        });
    }

    window.onclick = function(event) {
        if (event.target.id == 'addPointsModal') {
            closeAddPointsModal();
        }
    }
</script>
@endsection
