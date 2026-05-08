@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="{{ route('order-requests') }}" class="btn glass" style="padding: 8px 12px;"><i class="fas fa-arrow-left"></i></a>
            <h3>Generate New Order</h3>
        </div>
        <span style="font-size: 14px; color: var(--text-muted);">Reference Request: <strong>{{ request('from_req', 'Manual Entry') }}</strong></span>
    </div>

    <!-- AI/Request Reference Panel -->
    @if(request('from_req'))
    <div class="glass" style="padding: 20px; border-radius: var(--radius); margin-bottom: 30px; border-left: 4px solid var(--primary);">
        <h4 style="margin-bottom: 10px; font-size: 14px; color: var(--primary);">Request Analysis</h4>
        <div style="display: flex; gap: 20px; align-items: flex-start;">
            <div style="flex: 1;">
                <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                    "Hi, I need 10 units of the Premium Leather Case for my shop. Please deliver them by Friday."
                </p>
                <div style="margin-top: 10px; display: flex; gap: 10px;">
                    <span class="badge badge-success" style="font-size: 10px;">Detected: 10x Leather Case</span>
                    <span class="badge badge-warning" style="font-size: 10px;">Priority: High</span>
                </div>
            </div>
            <div class="glass" style="width: 150px; padding: 10px; border-radius: 8px; text-align: center;">
                <i class="fas fa-robot" style="font-size: 24px; color: var(--primary); margin-bottom: 8px;"></i>
                <div style="font-size: 10px; color: var(--text-muted);">AI Assistant Active</div>
            </div>
        </div>
    </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h4 style="margin-bottom: 0;">Order Details</h4>
        <span style="font-size: 14px; color: var(--text-muted);">Order ID: <strong>ORD-5582</strong> (Auto)</span>
    </div>
    
        <div class="grid" style="grid-template-columns: 1fr 1fr 1fr 1fr;">
            <div class="form-group">
                <label class="form-label">Select Dealer</label>
                <select class="form-control">
                    <option {{ request('dealer') == 'John Doe' ? 'selected' : '' }}>John Doe - JD Accessories</option>
                    <option>Jane Smith - Smith Stores</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Delivery Type</label>
                <select class="form-control">
                    <option>Distributor Delivery</option>
                    <option>Company Self Delivery</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Expected Delivery Date</label>
                <input type="date" class="form-control" value="2024-05-12">
            </div>
            <div class="form-group">
                <label class="form-label">Attach Challan (PDF)</label>
                <div class="glass" style="padding: 5px; border-radius: 10px; display: flex; align-items: center; gap: 10px; border: 1px dashed var(--glass-border);">
                    <input type="file" id="pdfUpload" hidden accept="application/pdf">
                    <label for="pdfUpload" style="cursor: pointer; background: var(--glass-bg); padding: 5px 15px; border-radius: 8px; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-file-invoice" style="color: var(--primary);"></i>
                        <span>Upload Challan</span>
                    </label>
                    <span id="fileName" style="font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px;">No file selected</span>
                </div>
            </div>
        </div>

    <h4 style="margin-top: 20px; margin-bottom: 15px;">Product List</h4>
    <div class="table-container glass" style="padding: 15px; border-radius: var(--radius);">
        <table id="productTable">
            <thead>
                <tr>
                    <th style="width: 40%;">Product Name</th>
                    <th>Quantity</th>
                    <th>Price (ea)</th>
                    <th>GST (18%)</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="productBody">
                <tr class="product-row">
                    <td>
                        <input type="text" class="form-control product-name" value="Premium Leather Case" placeholder="Enter product name">
                    </td>
                    <td><input type="number" value="10" min="1" class="form-control qty-input" style="width: 80px; padding: 5px;"></td>
                    <td><input type="number" value="500" min="0" class="form-control price-input" style="width: 100px; padding: 5px;"></td>
                    <td class="gst-display">₹ 90.00</td>
                    <td class="total-display">₹ 5,900.00</td>
                    <td><i class="fas fa-times remove-row" style="color: var(--accent); cursor: pointer;"></i></td>
                </tr>
            </tbody>
        </table>
        <button id="addItemBtn" class="btn glass" style="margin-top: 15px; font-size: 12px;"><i class="fas fa-plus"></i> Add Item</button>
    </div>

    <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div style="max-width: 300px; width: 100%;">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" style="height: 80px;" placeholder="Any special instructions...">As per request REQ-2001</textarea>
        </div>
        <div style="text-align: right;">
            <div style="margin-bottom: 10px;">
                <span style="color: var(--text-muted);">Subtotal:</span>
                <span id="subtotal" style="font-size: 18px; margin-left: 10px;">₹ 5,000.00</span>
            </div>
            <div style="margin-bottom: 20px;">
                <span style="color: var(--text-muted);">Total Tax (GST):</span>
                <span id="totalTax" style="font-size: 18px; margin-left: 10px;">₹ 900.00</span>
            </div>
            <div style="border-top: 1px solid var(--glass-border); padding-top: 10px;">
                <span style="font-weight: 700;">Grand Total:</span>
                <span id="grandTotal" style="font-size: 24px; font-weight: 800; color: var(--primary); margin-left: 10px;">₹ 5,900.00</span>
            </div>
            <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn glass">Save Draft</button>
                <button class="btn btn-primary" style="padding: 12px 30px;" onclick="alert('Order generated successfully!')">Confirm & Generate Order</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.getElementById('pdfUpload').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'No file...';
        document.getElementById('fileName').textContent = fileName;
    });

    const productBody = document.getElementById('productBody');
    const addItemBtn = document.getElementById('addItemBtn');
    
    // Formatting helper
    const formatCurrency = (amount) => {
        return '₹ ' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const calculateTotals = () => {
        let subtotal = 0;
        let totalTax = 0;
        
        document.querySelectorAll('.product-row').forEach(row => {
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const qty = parseInt(row.querySelector('.qty-input').value) || 0;
            
            const lineSubtotal = price * qty;
            const lineTax = lineSubtotal * 0.18;
            const lineTotal = lineSubtotal + lineTax;
            
            row.querySelector('.gst-display').textContent = formatCurrency(lineTax);
            row.querySelector('.total-display').textContent = formatCurrency(lineTotal);
            
            subtotal += lineSubtotal;
            totalTax += lineTax;
        });
        
        document.getElementById('subtotal').textContent = formatCurrency(subtotal);
        document.getElementById('totalTax').textContent = formatCurrency(totalTax);
        document.getElementById('grandTotal').textContent = formatCurrency(subtotal + totalTax);
    };

    addItemBtn.addEventListener('click', () => {
        const newRow = document.createElement('tr');
        newRow.className = 'product-row';
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control product-name" placeholder="Enter product name">
            </td>
            <td><input type="number" value="1" min="1" class="form-control qty-input" style="width: 80px; padding: 5px;"></td>
            <td><input type="number" value="0" min="0" class="form-control price-input" style="width: 100px; padding: 5px;"></td>
            <td class="gst-display">₹ 0.00</td>
            <td class="total-display">₹ 0.00</td>
            <td><i class="fas fa-times remove-row" style="color: var(--accent); cursor: pointer;"></i></td>
        `;
        productBody.appendChild(newRow);
        calculateTotals();
    });

    productBody.addEventListener('input', (e) => {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
            calculateTotals();
        }
    });

    productBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-row')) {
            if (document.querySelectorAll('.product-row').length > 1) {
                e.target.closest('tr').remove();
                calculateTotals();
            } else {
                alert('At least one item is required.');
            }
        }
    });

    // Initial calculation
    calculateTotals();
</script>
@endsection
@stop
