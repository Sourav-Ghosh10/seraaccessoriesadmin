@extends('layouts.app')

@section('title', 'Price List Management')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3 style="margin: 0; font-size: 24px; font-weight: 700;">Price List Management</h3>
        <button class="btn btn-primary" onclick="openUploadModal()">
            <i class="fas fa-file-upload"></i> Upload New Price List
        </button>
    </div>

    <div class="grid">
        @if($latest)
        <div class="card" style="display: flex; align-items: center; gap: 20px; border: 1px solid rgba(244, 63, 94, 0.2); background: rgba(244, 63, 94, 0.02);">
            <div style="font-size: 44px; color: #f43f5e; filter: drop-shadow(0 0 8px rgba(244, 63, 94, 0.3));">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 5px 0; font-size: 18px; font-weight: 600; color: #fff;">Latest Price List {{ $latest->version }}</h4>
                <p style="margin: 0 0 4px 0; font-size: 12px; color: var(--text-muted);">Uploaded on: {{ $latest->created_at->format('Y-m-d') }}</p>
                <p style="margin: 0; font-size: 11px; color: rgba(255,255,255,0.4); font-family: monospace;">File: {{ $latest->file_name }} ({{ $latest->file_size }})</p>
            </div>
            <a href="{{ asset('uploads/' . $latest->file_path) }}" target="_blank" class="btn btn-primary" style="padding: 10px 15px; display: flex; align-items: center; justify-content: center; height: 42px; width: 42px; border-radius: 8px;">
                <i class="fas fa-download"></i>
            </a>
        </div>
        @else
        <div class="card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px; padding: 40px 20px; text-align: center; border: 1px dashed rgba(255,255,255,0.15); background: rgba(255,255,255,0.01);">
            <div style="font-size: 48px; color: var(--text-muted); opacity: 0.3;">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #fff;">No Price List Uploaded</h4>
                <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Please upload the first Price List PDF file to notify all dealers.</p>
            </div>
        </div>
        @endif
    </div>

    <h4 style="margin-top: 40px; margin-bottom: 20px; font-size: 18px; font-weight: 600;">Version History</h4>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Upload Date</th>
                    <th>Size</th>
                    <th>File Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($priceLists as $list)
                <tr>
                    <td style="font-weight: 600; color: #fff;">{{ $list->version }}</td>
                    <td>{{ $list->created_at->format('Y-m-d H:i') }}</td>
                    <td><span class="badge" style="background: rgba(255,255,255,0.08); color: #fff; padding: 3px 8px; font-size: 11px;">{{ $list->file_size }}</span></td>
                    <td style="font-size: 13px; color: var(--text-muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $list->file_name }}</td>
                    <td>
                        <a href="{{ asset('uploads/' . $list->file_path) }}" target="_blank" class="btn glass" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; border-radius: 6px;">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px 10px;">No price list logs available.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Upload Price List Modal -->
<div id="uploadModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: flex-start; justify-content: center; overflow-y: auto; padding-top: 100px;">
    <div class="card modal-content" style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); animation: modalIn 0.3s ease-out; width: 450px; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #fff;">Upload Price List</h3>
            <div onclick="closeUploadModal()" style="width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <i class="fas fa-times" style="color: var(--text-muted); font-size: 13px;"></i>
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label class="form-label" style="color: rgba(255,255,255,0.4); font-size: 11px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; display: block; margin-bottom: 8px;">Select Price List PDF File</label>
            <div style="border: 2px dashed rgba(255,255,255,0.15); border-radius: 8px; padding: 25px 20px; text-align: center; background: rgba(255,255,255,0.01); transition: all 0.3s; position: relative;" id="dropZone">
                <i class="fas fa-cloud-upload-alt" style="font-size: 36px; color: var(--primary); margin-bottom: 10px; opacity: 0.8;"></i>
                <p style="margin: 0 0 10px 0; font-size: 13px; color: #fff; font-weight: 500;">Click to select file</p>
                <p style="margin: 0; font-size: 11px; color: var(--text-muted);">Supports PDF files only (Max 50MB)</p>
                <input type="file" id="priceListFile" accept="application/pdf" onchange="handleFileSelect()" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
            </div>
            <div id="fileSelectionDetails" style="display: none; margin-top: 15px; padding: 10px 12px; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15); border-radius: 6px; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle" style="color: #10b981; font-size: 16px;"></i>
                <div style="flex: 1; overflow: hidden;">
                    <p id="selectedFileName" style="margin: 0 0 2px 0; font-size: 12px; color: #fff; font-weight: 500; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"></p>
                    <p id="selectedFileSize" style="margin: 0; font-size: 10px; color: rgba(255,255,255,0.4);"></p>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <button class="btn glass" onclick="closeUploadModal()" style="padding: 10px 20px; font-weight: 500; font-size: 13px; border-radius: 6px;">Cancel</button>
            <button class="btn btn-primary" id="submitUploadBtn" onclick="submitPriceList()" style="padding: 10px 24px; font-weight: 600; font-size: 13px; border-radius: 6px; box-shadow: 0 4px 12px rgba(244, 63, 94, 0.2);">Upload</button>
        </div>
    </div>
</div>

<style>
    @keyframes modalIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</style>

<script>
    function openUploadModal() {
        document.getElementById('priceListFile').value = '';
        document.getElementById('fileSelectionDetails').style.display = 'none';
        document.getElementById('dropZone').style.borderColor = 'rgba(255,255,255,0.15)';
        document.getElementById('uploadModal').style.display = 'flex';
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
    }

    function handleFileSelect() {
        const fileInput = document.getElementById('priceListFile');
        const detailsContainer = document.getElementById('fileSelectionDetails');
        const nameEl = document.getElementById('selectedFileName');
        const sizeEl = document.getElementById('selectedFileSize');
        const dropZone = document.getElementById('dropZone');

        if (fileInput.files && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            
            if (file.type !== 'application/pdf') {
                alert('Only PDF files are supported.');
                fileInput.value = '';
                detailsContainer.style.display = 'none';
                dropZone.style.borderColor = 'rgba(255,255,255,0.15)';
                return;
            }

            nameEl.innerText = file.name;
            
            // Format size
            let formattedSize = file.size + ' B';
            if (file.size > 1024 * 1024) {
                formattedSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
            } else if (file.size > 1024) {
                formattedSize = (file.size / 1024).toFixed(1) + ' KB';
            }
            sizeEl.innerText = formattedSize;
            
            detailsContainer.style.display = 'flex';
            dropZone.style.borderColor = 'rgba(16, 185, 129, 0.4)';
        } else {
            detailsContainer.style.display = 'none';
            dropZone.style.borderColor = 'rgba(255,255,255,0.15)';
        }
    }

    function submitPriceList() {
        const fileInput = document.getElementById('priceListFile');
        const submitBtn = document.getElementById('submitUploadBtn');

        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Please select a PDF file first.');
            return;
        }

        const formData = new FormData();
        formData.append('price_list_file', fileInput.files[0]);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

        fetch(`${window.BASE_PATH}/price-list/upload`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Error: ' + (result.message || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Upload';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong during the upload process.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Upload';
        });
    }

    window.onclick = function(event) {
        if (event.target.id == 'uploadModal') {
            closeUploadModal();
        }
    }
</script>
@endsection
