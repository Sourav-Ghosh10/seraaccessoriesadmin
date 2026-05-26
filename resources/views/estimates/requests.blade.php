@extends('layouts.app')

@section('content')
    <div class="card animate-fade">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Incoming Estimate Requests</h3>
            <div style="display: flex; gap: 10px;">
                <button class="btn glass" style="font-size: 13px;"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Dealer</th>
                        <th>Type</th>
                        <th>Date/Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estimates as $estimate)
                        <tr>
                            <td>{{ $estimate->request_number ?? 'EST-' . str_pad($estimate->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $estimate->member->name }}</td>
                            <td>
                                @if($estimate->type == 'Voice')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-microphone" style="color: var(--secondary);"></i>
                                        <span>Voice Note</span>
                                    </div>
                                @elseif($estimate->type == 'Photo')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-camera" style="color: var(--accent);"></i>
                                        <span>Photo</span>
                                    </div>
                                @elseif($estimate->type == 'Document' || $estimate->type == 'Pdf')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-file-pdf" style="color: #ef4444;"></i>
                                        <span>Document / PDF</span>
                                    </div>
                                @else
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-font" style="color: var(--primary);"></i>
                                        <span>Text Request</span>
                                    </div>
                                @endif
                            </td>
                            <td><span
                                    style="font-size: 12px; color: var(--text-muted);">{{ $estimate->created_at->format('Y-m-d H:i A') }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $estimate->status == 'Responded' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $estimate->status }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn glass" style="padding: 5px 12px; font-size: 11px;"
                                        data-id="{{ $estimate->request_number ?? 'EST-' . str_pad($estimate->id, 4, '0', STR_PAD_LEFT) }}" 
                                        data-type="{{ $estimate->type }}"
                                        data-member="{{ $estimate->member->name }}" 
                                        data-desc="{{ $estimate->description }}"
                                        data-path="{{ json_encode($estimate->file_path) }}" 
                                        data-response-desc="{{ $estimate->response_description }}"
                                        data-response-file="{{ $estimate->response_file_path }}"
                                        onclick="initViewEstimate(this)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
 
                                    <button class="btn btn-primary" style="padding: 5px 12px; font-size: 11px;"
                                        onclick="openEstimateModal('{{ $estimate->id }}', '{{ $estimate->request_number ?? 'EST-' . str_pad($estimate->id, 4, '0', STR_PAD_LEFT) }}', '{{ $estimate->response_description }}')">
                                        <i class="fas fa-reply"></i> Revert Estimate
                                    </button>
                                    <button class="btn glass"
                                        style="padding: 5px 12px; font-size: 11px; color: var(--accent);"><i
                                            class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Revert Estimate Modal -->
    <div id="estimateModal"
        style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
        <div class="card"
            style="width: 100%; max-width: 500px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Revert Estimate</h3>
                <div onclick="closeModal()"
                    style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                </div>
            </div>

            <form id="revertForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="modalEstimateId" name="estimate_id">

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Request ID</label>
                    <input type="text" id="modalRequestId" class="form-control" readonly
                        style="background: rgba(255,255,255,0.03);">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Estimate Details / Price Breakdown</label>
                    <textarea name="response_description" id="modalResponseDesc" class="form-control" style="height: 120px; background: rgba(255,255,255,0.03);"
                        placeholder="Enter the estimated prices and details for the dealer..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label class="form-label">Upload Estimate Document (PDF / Image)</label>
                    <input type="file" name="estimate_pdf" accept=".pdf,image/*" class="form-control" style="background: rgba(255,255,255,0.03); color: var(--text-muted);">
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn glass" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Estimate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Request Modal -->
    <div id="viewModal"
        style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
        <div class="card"
            style="width: 100%; max-width: 600px; padding: 30px; background: #0f172a; border: 1px solid var(--glass-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-size: 20px; font-weight: 700;">Dealer Request Details</h3>
                <div onclick="closeModal()"
                    style="width: 30px; height: 30px; border-radius: 50%; background: var(--glass); display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="fas fa-times" style="color: var(--text-muted); font-size: 14px;"></i>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Request ID</div>
                        <div id="viewRequestId" style="font-weight: 700; color: var(--primary);"></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Dealer</div>
                        <div id="viewDealerName" style="font-weight: 700;"></div>
                    </div>
                </div>

                <div class="glass" style="padding: 20px; border-radius: 12px; margin-top: 10px;">
                    <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; margin-bottom: 10px;">
                        Request Content</div>
                    <div id="viewRequestContent"></div>
                </div>

                <!-- Reverted Response Area -->
                <div id="viewResponseContainer" class="glass" style="padding: 20px; border-radius: 12px; margin-top: 15px; border-color: rgba(34, 197, 94, 0.2); display: none;">
                    <div style="color: #22c55e; font-size: 12px; text-transform: uppercase; margin-bottom: 10px; font-weight: 600;">
                        Reverted Estimate Details</div>
                    <div id="viewResponseDesc" style="margin-bottom: 15px; color: var(--text-muted);"></div>
                    <div id="viewResponseFile"></div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button class="btn btn-primary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function initViewEstimate(btn) {
            const id = btn.getAttribute('data-id');
            const type = btn.getAttribute('data-type');
            const member = btn.getAttribute('data-member');
            const desc = btn.getAttribute('data-desc');
            const pathRaw = btn.getAttribute('data-path');
            const responseDesc = btn.getAttribute('data-response-desc');
            const responseFile = btn.getAttribute('data-response-file');
            viewRequest(id, type, member, desc, pathRaw, responseDesc, responseFile);
        }

        function openEstimateModal(dbId, displayId, currentResponseDesc) {
            document.getElementById('modalEstimateId').value = dbId;
            document.getElementById('modalRequestId').value = displayId;
            document.getElementById('modalResponseDesc').value = currentResponseDesc || '';
            document.getElementById('revertForm').action = `${window.APP_URL}/estimates/${dbId}/revert`;
            document.getElementById('estimateModal').style.display = 'flex';
        }

        function viewRequest(id, type, dealer, description, filePathRaw, responseDesc, responseFile) {
            document.getElementById('viewRequestId').textContent = id;
            document.getElementById('viewDealerName').textContent = dealer;

            const contentArea = document.getElementById('viewRequestContent');

            // Normalize filePath to an array
            let filePaths = [];
            try {
                filePaths = JSON.parse(filePathRaw);
                if (!Array.isArray(filePaths)) filePaths = filePathRaw ? [filePathRaw] : [];
            } catch (e) {
                filePaths = filePathRaw ? [filePathRaw] : [];
            }




            if (type === 'Voice') {
                const storagePath = filePaths.length > 0 ? `${window.APP_URL}/uploads/${filePaths[0]}` : '#';
                contentArea.innerHTML = `
                                                    <div style="text-align: center; width: 100%;">
                                                        <i class="fas fa-microphone" style="font-size: 40px; color: var(--secondary); margin-bottom: 15px;"></i>
                                                        <audio controls style="width: 100%; filter: invert(1) hue-rotate(180deg);">
                                                            <source src="${storagePath}" type="audio/mpeg">
                                                            Your browser does not support the audio element.
                                                        </audio>
                                                        <p style="margin-top: 15px; font-size: 14px; color: var(--text-muted); font-style: italic;">${description || 'Voice Note'}</p>
                                                    </div>
                                                `;
            } else if (type === 'Photo' || type === 'Document' || type === 'Pdf') {
                let imagesHtml = '';
                filePaths.forEach(path => {
                    const storagePath = `${window.APP_URL}/uploads/${path}`;
                    if (path.toLowerCase().endsWith('.pdf')) {
                        imagesHtml += `
                            <div style="margin-bottom: 20px; text-align: center;">
                                <a href="${storagePath}" target="_blank" class="btn glass" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-size: 13px; padding: 10px 20px; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none;">
                                    <i class="fas fa-file-pdf" style="font-size: 20px; color: #ef4444;"></i>
                                    <span>View Uploaded PDF Document</span>
                                </a>
                            </div>
                        `;
                    } else {
                        imagesHtml += `
                                            <div style="width: 100%; max-height: 420px; background: rgba(255,255,255,0.03); border-radius: 12px; overflow: hidden; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.1);">
                                                <img src="${storagePath}" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                                            </div>
                                        `;
                    }
                });

                contentArea.innerHTML = `
                                                    <div style="text-align: center; width: 100%;">
                                                        ${imagesHtml}
                                                        <p style="font-size: 14px; color: var(--text-muted);">${description || 'Attachment'}</p>
                                                    </div>
                                                `;
            } else {
                contentArea.innerHTML = `
                                                    <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                                                        <p style="font-size: 15px; line-height: 1.6; margin: 0;">${description || 'No description provided.'}</p>
                                                    </div>
                                                `;
            }

            // Handle response display
            const respContainer = document.getElementById('viewResponseContainer');
            const respDesc = document.getElementById('viewResponseDesc');
            const respFile = document.getElementById('viewResponseFile');

            if (responseDesc || responseFile) {
                respContainer.style.display = 'block';
                respDesc.innerHTML = responseDesc ? `<p style="font-size: 14px; margin: 0; line-height: 1.5;">${responseDesc}</p>` : '<p style="font-style: italic; color: var(--text-muted); font-size: 13px; margin: 0;">No written details provided.</p>';
                
                if (responseFile) {
                    const fileUrl = `${window.APP_URL}/uploads/${responseFile}`;
                    if (responseFile.toLowerCase().endsWith('.pdf')) {
                        respFile.innerHTML = `
                            <a href="${fileUrl}" target="_blank" class="btn glass" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-size: 12px; padding: 8px 15px;">
                                <i class="fas fa-file-pdf" style="font-size: 16px; color: #ef4444;"></i>
                                <span>View Reverted Estimate PDF</span>
                            </a>
                        `;
                    } else {
                        respFile.innerHTML = `
                            <div style="margin-top: 10px; border-radius: 8px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); max-height: 250px;">
                                <img src="${fileUrl}" style="width: 100%; max-height: 250px; object-fit: contain;">
                            </div>
                        `;
                    }
                } else {
                    respFile.innerHTML = '';
                }
            } else {
                respContainer.style.display = 'none';
            }


            document.getElementById('viewModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('estimateModal').style.display = 'none';
            document.getElementById('viewModal').style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target == document.getElementById('estimateModal') || event.target == document.getElementById('viewModal')) {
                closeModal();
            }
        }
    </script>
@endsection