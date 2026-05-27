@extends('layouts.app')

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Incoming Order Requests</h3>
            <div style="display: flex; gap: 10px;">
                <button class="btn glass" style="font-size: 13px;"><i class="fas fa-filter"></i> Filter</button>
                <button class="btn btn-primary" onclick="openRequestModal()"><i class="fas fa-plus"></i> Create Manual
                    Request</button>
            </div>
        </div>

        @push('modals')
            <div id="requestModal"
                style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
                <div class="card modal-content"
                    style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); width: 400px;">
                    <h3 style="margin-bottom: 20px;">Create Manual Request</h3>

                    <div class="form-group">
                        <label class="form-label">Select Dealer</label>
                        <select id="reqDealerId" class="form-control">
                            @foreach(\App\Models\Member::where('role', 'dealer')->get() as $dealer)
                                <option value="{{ $dealer->id }}">{{ $dealer->name }} - {{ $dealer->shop }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Request Type</label>
                        <select id="reqType" class="form-control">
                            <option value="Call">Call</option>
                            <option value="Text">Text Request</option>
                            <option value="Voice">Voice Message</option>
                            <option value="Photo">Photo/Document</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea id="reqDescription" class="form-control" style="height: 80px; resize: none;"
                            placeholder="Enter details..."></textarea>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
                        <button class="btn glass" onclick="closeRequestModal()">Cancel</button>
                        <button class="btn btn-primary" onclick="submitRequest()">Save Request</button>
                    </div>
                </div>
            </div>
        @endpush

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Dealer</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        @php
                            $orderFileUrls = collect($order->file_path ?? [])
                                ->filter()
                                ->map(fn ($path) => asset('uploads/' . ltrim(str_replace('\\', '/', (string) $path), '/')))
                                ->values()
                                ->all();
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #fff;">{{ $order->request_number }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: #fff;">{{ $order->member->name }}</div>
                                @if($order->description)
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                                        <i class="fas fa-info-circle"></i> {{ Str::limit($order->description, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($order->type == 'Voice')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-microphone" style="color: #a855f7;"></i>
                                        <span>Voice Message</span>
                                    </div>
                                @elseif($order->type == 'Photo')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-image" style="color: #3b82f6;"></i>
                                        <span>Photo</span>
                                    </div>
                                @elseif($order->type == 'Document' || $order->type == 'Pdf')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-file-pdf" style="color: #ef4444;"></i>
                                        <span>Document / PDF</span>
                                    </div>
                                @elseif($order->type == 'Call')
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-phone-alt" style="color: #10b981;"></i>
                                        <span>Call Request</span>
                                    </div>
                                @else
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-font" style="color: var(--primary);"></i>
                                        <span>Text Request</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 12px; color: var(--text-muted);">
                                    {{ $order->created_at->format('d M, Y') }}
                                </div>
                            </td>
                            <td>
                                @if($order->status == 'Pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($order->status == 'Processed')
                                    <span class="badge badge-success">Processed</span>
                                @else
                                    <span class="badge badge-danger">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn glass" style="padding: 5px 12px; font-size: 11px;"
                                        data-type="{{ $order->type }}" data-description="{{ $order->description }}"
                                        data-file-urls='@json($orderFileUrls)' data-id="{{ $order->id }}"
                                        data-sender="{{ $order->member->name ?? 'Unknown' }}"
                                        data-senderid="{{ $order->member_id }}"
                                        data-phone="{{ $order->member->mobile ?? '' }}"
                                        onclick="initViewRequest(this)">
                                        <i class="fas fa-eye"></i> View
                                    </button>

                                    @if($order->status == 'Pending')
                                        <a href="{{ route('orders.create', ['from_req' => $order->id, 'dealer' => $order->member_id]) }}"
                                            class="btn btn-primary" style="padding: 5px 12px; font-size: 11px;">
                                            <i class="fas fa-check"></i> Create Order
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('modals')
        <!-- View Content Modal -->
        <div id="viewContentModal"
            style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(15px); align-items: center; justify-content: center;">
            <div class="card modal-content"
                style="padding: 30px; background: #0f172a; border: 1px solid var(--glass-border); width: 500px; animation: modalIn 0.3s ease-out;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 id="viewModalTitle" style="margin: 0; font-size: 18px;">Request Content</h3>
                    <div onclick="closeViewModal()" style="cursor: pointer; color: var(--text-muted);"><i
                            class="fas fa-times"></i></div>
                </div>

                <div id="contentBody"
                    style="min-height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(255,255,255,0.02); border-radius: 15px; padding: 20px; border: 1px solid rgba(255,255,255,0.05);">
                    <!-- Dynamic Content Loaded Here -->
                </div>

                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Sender: <strong id="viewSenderName"
                            style="color: #fff;"></strong></p>
                    <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: flex-end;">
                        <button class="btn glass" onclick="closeViewModal()">Close</button>
                        <button class="btn btn-primary" id="processBtn">Process to Order</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox Zoom Overlay -->
        <style>
            .attachment-thumb-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                justify-content: flex-start;
                width: 100%;
            }

            .attachment-thumb {
                width: 96px;
                height: 96px;
                border-radius: 10px;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.12);
                cursor: zoom-in;
                position: relative;
                background: rgba(255, 255, 255, 0.03);
                transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
                flex-shrink: 0;
            }

            .attachment-thumb:hover {
                transform: translateY(-2px);
                border-color: rgba(255, 255, 255, 0.25);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
            }

            .attachment-thumb img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .attachment-thumb-overlay {
                position: absolute;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(15, 23, 42, 0.45);
                opacity: 0;
                transition: opacity 0.2s ease;
                color: #fff;
                font-size: 18px;
            }

            .attachment-thumb:hover .attachment-thumb-overlay {
                opacity: 1;
            }

            .lightbox-overlay {
                display: none;
                position: fixed;
                z-index: 100001;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(2, 6, 23, 0.96);
                backdrop-filter: blur(20px);
                align-items: center;
                justify-content: center;
                cursor: zoom-out;
                animation: fadeIn 0.25s ease-out;
            }

            .lightbox-overlay img {
                max-width: 90%;
                max-height: 85vh;
                border-radius: 8px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
                transition: transform 0.25s cubic-bezier(0.1, 0.8, 0.3, 1);
                transform-origin: center center;
                cursor: grab;
            }

            .lightbox-overlay img:active {
                cursor: grabbing;
            }

            .lightbox-close {
                position: absolute;
                top: 25px;
                right: 25px;
                color: #fff;
                font-size: 20px;
                cursor: pointer;
                background: rgba(255, 255, 255, 0.06);
                width: 45px;
                height: 45px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .lightbox-close:hover {
                background: rgba(255, 255, 255, 0.15);
                transform: scale(1.05);
            }

            .lightbox-controls {
                position: absolute;
                bottom: 35px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(15, 23, 42, 0.85);
                border: 1px solid rgba(255, 255, 255, 0.15);
                padding: 8px 18px;
                border-radius: 30px;
                display: flex;
                gap: 12px;
                align-items: center;
                backdrop-filter: blur(10px);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            }

            .lightbox-btn {
                color: #fff;
                background: none;
                border: none;
                cursor: pointer;
                font-size: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                transition: all 0.2s;
                text-decoration: none;
            }

            .lightbox-btn:hover {
                background: rgba(255, 255, 255, 0.1);
                color: var(--primary);
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }
        </style>
        <div id="imageLightbox" class="lightbox-overlay" onclick="closeLightbox()">
            <span class="lightbox-close"><i class="fas fa-times"></i></span>
            <img id="lightboxImg" src="" onclick="event.stopPropagation();" style="transform: scale(1);">
            <div class="lightbox-controls" onclick="event.stopPropagation();">
                <button class="lightbox-btn" onclick="zoomImg(-0.2)" title="Zoom Out"><i
                        class="fas fa-search-minus"></i></button>
                <button class="lightbox-btn" onclick="resetZoom()" title="Reset Zoom"><i class="fas fa-redo"></i></button>
                <button class="lightbox-btn" onclick="zoomImg(0.2)" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                <span style="color: rgba(255,255,255,0.2); margin: 0 4px;">|</span>
                <a id="lightboxDownload" href="" download class="lightbox-btn" title="Download Image"><i
                        class="fas fa-download"></i></a>
            </div>
        </div>
    @endpush

@endsection

@section('scripts')
    <script>
        function initViewRequest(btn) {
            const type = btn.getAttribute('data-type');
            const description = btn.getAttribute('data-description');
            const fileUrlsRaw = btn.getAttribute('data-file-urls');
            const id = btn.getAttribute('data-id');
            const senderName = btn.getAttribute('data-sender');
            const senderId = btn.getAttribute('data-senderid');
            const phone = btn.getAttribute('data-phone');
            viewRequestContent(type, description, fileUrlsRaw, id, senderName, senderId, phone);
        }

        function openRequestModal() {

            document.getElementById('requestModal').style.display = 'flex';
        }

        function closeRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
        }

        let currentScale = 1;

        function openLightbox(src) {
            const lightbox = document.getElementById('imageLightbox');
            const img = document.getElementById('lightboxImg');
            const downloadLink = document.getElementById('lightboxDownload');
            img.src = src;
            downloadLink.href = src;
            currentScale = 1;
            img.style.transform = `scale(${currentScale})`;
            lightbox.style.display = 'flex';
        }

        function closeLightbox() {
            document.getElementById('imageLightbox').style.display = 'none';
        }

        function zoomImg(amount) {
            const img = document.getElementById('lightboxImg');
            currentScale = Math.max(0.4, Math.min(4, currentScale + amount));
            img.style.transform = `scale(${currentScale})`;
        }

        function resetZoom() {
            const img = document.getElementById('lightboxImg');
            currentScale = 1;
            img.style.transform = `scale(${currentScale})`;
        }

        function parseFileUrls(raw) {
            if (!raw || raw === 'null' || raw === 'undefined') {
                return [];
            }
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
            } catch (e) {
                return [];
            }
        }

        function buildImageThumbnails(fileUrls) {
            if (!fileUrls.length) {
                return '<p style="font-size: 13px; color: var(--text-muted); margin: 0;">No attachment file found for this request.</p>';
            }

            let html = '<div class="attachment-thumb-grid">';
            fileUrls.forEach(url => {
                const safeUrl = url.replace(/'/g, "\\'");
                if (url.toLowerCase().includes('.pdf')) {
                    html += `
                        <a href="${url}" target="_blank" class="btn glass" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-size: 13px; padding: 10px 20px; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none;">
                            <i class="fas fa-file-pdf" style="font-size: 20px; color: #ef4444;"></i>
                            <span>View PDF</span>
                        </a>
                    `;
                } else {
                    html += `
                        <div class="attachment-thumb" onclick="openLightbox('${safeUrl}')" title="Click to view full size">
                            <img src="${url}" alt="Request attachment">
                            <div class="attachment-thumb-overlay"><i class="fas fa-search-plus"></i></div>
                        </div>
                    `;
                }
            });
            html += '</div>';
            return html;
        }

        function viewRequestContent(type, description, fileUrlsRaw, id, senderName, senderId, phone) {
            const body = document.getElementById('contentBody');
            const title = document.getElementById('viewModalTitle');
            const senderNameEl = document.getElementById('viewSenderName');
            const processBtn = document.getElementById('processBtn');

            senderNameEl.innerText = senderName;
            title.innerText = type + ' Content';
            processBtn.onclick = () => {
                window.location.href = `${window.APP_URL}/orders/create?from_req=${id}&dealer=${senderId}`;
            };

            let content = '';
            const fileUrls = parseFileUrls(fileUrlsRaw);

            if (type === 'Voice') {
                const storagePath = fileUrls.length > 0 ? fileUrls[0] : '#';
                content = `
                                                <div style="text-align: center; width: 100%;">
                                                    <i class="fas fa-microphone" style="font-size: 40px; color: var(--secondary); margin-bottom: 15px;"></i>
                                                    <p style="font-size: 14px; margin-bottom: 20px;">Voice Message Received</p>
                                                    <audio controls style="width: 100%; filter: invert(1) hue-rotate(180deg);">
                                                        <source src="${storagePath}" type="audio/mpeg">
                                                        Your browser does not support the audio element.
                                                    </audio>
                                                </div>
                                            `;
            } else if (type === 'Photo' || type === 'Document' || type === 'Pdf') {
                const imagesHtml = buildImageThumbnails(fileUrls);

                content = `
                    <div style="width: 100%;">
                        ${imagesHtml}
                        <p style="font-size: 13px; color: var(--text-muted); margin: 14px 0 0;">${description || 'Attachment'}</p>
                    </div>
                `;
            } else if (type === 'Call') {
                content = `
                                                <div style="text-align: center; width: 100%;">
                                                    <i class="fas fa-phone-alt" style="font-size: 40px; color: #10b981; margin-bottom: 15px;"></i>
                                                    <p style="font-size: 15px; font-weight: 500; margin-bottom: 15px;">Dealer Requested a Call Back</p>
                                                    ${phone ? `
                                                        <a href="tel:${phone}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; padding: 10px 20px; text-decoration: none; border-radius: 12px;">
                                                            <i class="fas fa-phone"></i> Call ${phone}
                                                        </a>
                                                    ` : '<p style="color: var(--text-muted); font-size: 13px;">No phone number available</p>'}
                                                </div>
                                            `;
            } else {
                content = `
                                                <div style="width: 100%;">
                                                    <p style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px;">Message Content</p>
                                                    <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); line-height: 1.6;">
                                                        ${description || 'No additional details provided.'}
                                                    </div>
                                                </div>
                                            `;
            }


            body.innerHTML = content;
            document.getElementById('viewContentModal').style.display = 'flex';
        }

        function closeViewModal() {
            document.getElementById('viewContentModal').style.display = 'none';
            closeLightbox();
        }

        function submitRequest() {
            const dealerId = document.getElementById('reqDealerId').value;
            const type = document.getElementById('reqType').value;
            const description = document.getElementById('reqDescription').value;

            fetch('{{ route('order-requests.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    member_id: dealerId,
                    type: type,
                    description: description
                })
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong!');
                });
        }

        window.onclick = function (event) {
            const reqModal = document.getElementById('requestModal');
            const viewModal = document.getElementById('viewContentModal');
            const lightboxModal = document.getElementById('imageLightbox');
            if (event.target == reqModal) closeRequestModal();
            if (event.target == viewModal) closeViewModal();
            if (event.target == lightboxModal) closeLightbox();
        }
    </script>
@endsection