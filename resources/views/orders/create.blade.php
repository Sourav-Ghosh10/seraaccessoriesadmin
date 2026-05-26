@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="{{ route('order-requests') }}" class="btn glass" style="padding: 8px 12px;"><i
                    class="fas fa-arrow-left"></i></a>
            <h3>Generate New Order</h3>
        </div>
        <span style="font-size: 14px; color: var(--text-muted);">Reference Request:
            <strong>{{ request('from_req', 'Manual Entry') }}</strong></span>
    </div>


    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h4 style="margin-bottom: 0;">Order Details</h4>
        <span style="font-size: 14px; color: var(--text-muted);">Order ID: <strong>ORD-5582</strong> (Auto)</span>
    </div>

    <div class="grid" id="orderGrid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <input type="hidden" id="fromRequestId" value="{{ request('from_req') }}">
        <div class="form-group">
            <label class="form-label">Select Dealer</label>
            <select class="form-control" id="orderDealerId" {{ request('dealer') ? 'disabled' : '' }}>
                @foreach($dealers as $dealer)
                    <option value="{{ $dealer->id }}" {{ request('dealer') == $dealer->id ? 'selected' : '' }}>
                        {{ $dealer->name }} - {{ $dealer->shop }}
                    </option>
                @endforeach
            </select>
            @if(request('dealer'))
                <input type="hidden" id="orderDealerIdHidden" value="{{ request('dealer') }}">
            @endif
        </div>
        <div class="form-group">
            <label class="form-label">Delivery Type</label>
            <select class="form-control" id="deliveryTypeSelect" onchange="toggleDistributor()">
                <option value="Company Self Delivery">Company Self Delivery</option>
                <option value="Distributor Delivery">Distributor Delivery</option>
            </select>
        </div>
        <div class="form-group" id="distributorGroup">
            <label class="form-label">Select Distributor</label>
            <select class="form-control" id="distributorId">
                @foreach($distributors as $distributor)
                    <option value="{{ $distributor->id }}">{{ $distributor->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Expected Delivery Date</label>
            <input type="date" id="deliveryDate" class="form-control" value="2024-05-12">
        </div>
        <div class="form-group">
            <label class="form-label">Attach Challan (PDF)</label>
            <div class="glass"
                style="padding: 5px; border-radius: 10px; display: flex; align-items: center; gap: 10px; border: 1px dashed var(--glass-border);">
                <input type="file" id="pdfUpload" hidden accept="application/pdf">
                <label for="pdfUpload"
                    style="cursor: pointer; background: var(--glass-bg); padding: 5px 15px; border-radius: 8px; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-file-invoice" style="color: var(--primary);"></i>
                    <span>Upload Challan</span>
                </label>
                <span id="fileName"
                    style="font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px;">No
                    file selected</span>
            </div>
        </div>
    </div>

    <div class="form-group" style="margin-top: 20px;">
        <label class="form-label">Delivery Address</label>
        <textarea id="deliveryAddress" class="form-control" style="height: 60px;" placeholder="Enter full delivery address..."></textarea>
    </div>


    <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div style="max-width: 300px; width: 100%;">
            <label class="form-label">Remarks</label>
            <textarea id="orderRemarks" class="form-control" style="height: 80px;"
                placeholder="Any special instructions..."></textarea>
        </div>
        <div style="text-align: right;">
            <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn glass">Save Draft</button>
                <button class="btn btn-primary" style="padding: 12px 30px;"
                    onclick="submitOrder()">Confirm & Generate Order</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        document.getElementById('pdfUpload').addEventListener('change', function (e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file...';
            document.getElementById('fileName').textContent = fileName;
        });

        function toggleDistributor() {
            const select = document.getElementById('deliveryTypeSelect');
            const distGroup = document.getElementById('distributorGroup');

            if (select.value === 'Distributor Delivery') {
                distGroup.style.display = 'block';
            } else {
                distGroup.style.display = 'none';
            }
        }

        function submitOrder() {
            const submitBtn = document.querySelector('button[onclick="submitOrder()"]');
            if (submitBtn.disabled) return;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating Order...';

            const formData = new FormData();
            const dealerSelect = document.getElementById('orderDealerId');
            const dealerId = (dealerSelect.disabled && document.getElementById('orderDealerIdHidden'))
                ? document.getElementById('orderDealerIdHidden').value
                : dealerSelect.value;
            const deliveryType = document.getElementById('deliveryTypeSelect').value;
            formData.append('member_id', dealerId);
            formData.append('delivery_type', deliveryType);
            if (deliveryType === 'Distributor Delivery') {
                formData.append('distributor_id', document.getElementById('distributorId').value);
            }
            formData.append('delivery_date', document.getElementById('deliveryDate').value);
            formData.append('address', document.getElementById('deliveryAddress').value);
            formData.append('remarks', document.getElementById('orderRemarks').value);
            formData.append('from_request_id', document.getElementById('fromRequestId').value);
            formData.append('_token', '{{ csrf_token() }}');
            
            const fileInput = document.getElementById('pdfUpload');
            if (fileInput.files.length > 0) {
                formData.append('challan_file', fileInput.files[0]);
            }

            fetch('{{ route('orders.store') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        window.location.href = '{{ route('orders.index') }}';
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error'));
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Confirm & Generate Order';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong!');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Confirm & Generate Order';
                });
        }

        // File name display logic
        document.getElementById('pdfUpload').onchange = function() {
            document.getElementById('fileName').innerText = this.files[0].name;
        };

        // Initial call
        toggleDistributor();
    </script>
@endsection
@stop