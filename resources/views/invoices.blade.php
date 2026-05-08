@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Invoice Management</h3>
        @php $role = session('role', 'Admin'); @endphp
        @if($role == 'Admin' || $role == 'Account')
        <button class="btn btn-primary" onclick="openUploadModal()"><i class="fas fa-upload"></i> Upload Invoice</button>
        @endif
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Order #</th>
                    <th>Amount</th>
                    <th>GST</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>INV-2024-001</td>
                    <td>ORD1001</td>
                    <td>₹ 15,000</td>
                    <td>₹ 2,700</td>
                    <td><span class="badge badge-success">Paid</span></td>
                    <td>
                        <button class="btn glass" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-download"></i> PDF</button>
                    </td>
                </tr>
                <tr>
                    <td>INV-2024-002</td>
                    <td>ORD1002</td>
                    <td>₹ 8,500</td>
                    <td>₹ 1,530</td>
                    <td><span class="badge badge-warning">Due</span></td>
                    <td>
                        <button class="btn glass" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-download"></i> PDF</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
    </div>
</div>

<!-- Upload Invoice Modal -->
<div id="uploadModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; padding-top: 80px; overflow-y: auto;">
    <div class="card" style="width: 100%; max-width: 500px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin-bottom: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Upload New Invoice</h3>
            <div onclick="closeUploadModal()" style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Link to Order</label>
            <select class="form-control" style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff; cursor: pointer;">
                <option value="" style="background: #1e293b; color: #fff;">Select Order Number</option>
                <option value="ORD-5580" style="background: #1e293b; color: #fff;">ORD-5580 (John Doe)</option>
                <option value="ORD-5581" style="background: #1e293b; color: #fff;">ORD-5581 (Jane Smith)</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Invoice Document</label>
            <div style="border: 2px dashed rgba(255,255,255,0.1); border-radius: 12px; padding: 30px; text-align: center; background: rgba(255,255,255,0.02); cursor: pointer;" onclick="document.getElementById('invoiceFile').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 30px; color: var(--primary); margin-bottom: 10px;"></i>
                <p style="margin: 0; font-size: 13px; color: #cbd5e1;">Click to browse or drag and drop invoice PDF</p>
                <input type="file" id="invoiceFile" style="display: none;" accept=".pdf,.jpg,.png">
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Invoice Amount (Incl. GST)</label>
            <input type="number" class="form-control" placeholder="Enter amount..." style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Payment Status</label>
            <div style="display: flex; gap: 15px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="radio" name="pay_status" value="Paid" checked> Paid
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="radio" name="pay_status" value="Due"> Due
                </label>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <button class="btn glass" onclick="closeUploadModal()" style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
            <button class="btn btn-primary" onclick="submitInvoice()" style="padding: 12px 30px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Upload & Save</button>
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
    function openUploadModal() {
        document.getElementById('uploadModal').style.display = 'flex';
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
    }

    function submitInvoice() {
        // Frontend logic
        alert('Invoice uploaded successfully! (Simulation)');
        closeUploadModal();
    }

    // Close modal on click outside
    window.onclick = function(event) {
        const modal = document.getElementById('uploadModal');
        if (event.target == modal) {
            closeUploadModal();
        }
    }
</script>
@endsection
