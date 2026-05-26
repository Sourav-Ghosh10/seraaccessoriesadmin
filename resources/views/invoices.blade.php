@extends('layouts.app')

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Invoice Management</h3>
            @php $role = session('role', 'Admin'); @endphp
            @if($role == 'Admin' || $role == 'Account')
                <button class="btn btn-primary" onclick="openUploadModal()"><i class="fas fa-upload"></i> Upload
                    Invoice</button>
            @endif
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Order #</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td>{{ $invoice->order->order_number }}</td>
                            <td>₹ {{ number_format($invoice->amount, 2) }}</td>
                            <td>
                                <a href="{{ asset('uploads/' . $invoice->file_path) }}" target="_blank" class="btn glass"
                                    style="padding: 5px 10px; font-size: 12px;">

                                    <i class="fas fa-file-pdf"></i> View PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('modals')
        <!-- Upload Invoice Modal -->
        <div id="uploadModal"
            style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(10px); overflow-y: auto; align-items: center; justify-content: center; padding: 20px;">
            <div class="card"
                style="width: 100%; max-width: 500px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalIn 0.3s ease-out; margin: auto;">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Upload New Invoice</h3>
                    <div onclick="closeUploadModal()"
                        style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                    </div>
                </div>

                <form id="invoiceForm" onsubmit="event.preventDefault(); submitInvoice();">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Link
                            to Order</label>
                        <select id="invOrderId" class="form-control"
                            style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff; cursor: pointer;"
                            required>
                            <option value="">Select Order Number</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}">{{ $order->order_number }} ({{ $order->member->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Invoice
                            Number</label>
                        <input type="text" id="invNumber" class="form-control" placeholder="INV-2026-XXXX"
                            style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Invoice
                            Document (PDF/Image)</label>
                        <div style="border: 2px dashed rgba(255,255,255,0.1); border-radius: 12px; padding: 30px; text-align: center; background: rgba(255,255,255,0.02); cursor: pointer;"
                            onclick="document.getElementById('invoiceFile').click()">
                            <i class="fas fa-cloud-upload-alt"
                                style="font-size: 30px; color: var(--primary); margin-bottom: 10px;"></i>
                            <p id="fileNameDisplay" style="margin: 0; font-size: 13px; color: #cbd5e1;">Click to browse or drag
                                and drop invoice</p>
                            <input type="file" id="invoiceFile" style="display: none;" accept=".pdf,.jpg,.png" required
                                onchange="updateFileName(this)">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label class="form-label"
                            style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Invoice
                            Amount</label>
                        <input type="number" id="invAmount" class="form-control" placeholder="Enter amount..."
                            style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1);" step="0.01"
                            required>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
                        <button type="button" class="btn glass" onclick="closeUploadModal()"
                            style="border: none; background: rgba(255,255,255,0.05);">Cancel</button>
                        <button type="submit" id="submitBtn" class="btn btn-primary"
                            style="padding: 12px 30px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">Upload &
                            Save</button>
                    </div>
                </form>
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

            .form-control:focus {
                outline: none;
                border-color: var(--primary);
            }
        </style>
    @endpush

@endsection

@section('scripts')
    <script>
        function openUploadModal() {
            document.getElementById('uploadModal').style.display = 'flex';
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
        }

        function updateFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('fileNameDisplay').innerText = input.files[0].name;
                document.getElementById('fileNameDisplay').style.color = 'var(--primary)';
            }
        }

        function submitInvoice() {
            const submitBtn = document.getElementById('submitBtn');
            const formData = new FormData();

            formData.append('order_id', document.getElementById('invOrderId').value);
            formData.append('invoice_number', document.getElementById('invNumber').value);
            formData.append('amount', document.getElementById('invAmount').value);
            formData.append('invoice_file', document.getElementById('invoiceFile').files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

            fetch('{{ route('invoices.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error'));
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Upload & Save';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong!');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Upload & Save';
                });
        }

        window.onclick = function (event) {
            const modal = document.getElementById('uploadModal');
            if (event.target == modal) {
                closeUploadModal();
            }
        }
    </script>
@endsection