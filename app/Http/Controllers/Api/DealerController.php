<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Estimate;
use App\Models\OrderRequest;
use App\Models\Order;
use App\Models\Member;
use App\Models\MemberDevice;
use App\Models\Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;


class DealerController extends Controller
{
    // =========================================================================
    // GET ESTIMATE — POST /api/dealer/estimate
    // Dealer only. Supports: Text | Voice | Photo
    // =========================================================================
    #[OA\Post(
        path: "/dealer/estimate",
        summary: "Submit an estimation request",
        description: "Allows a dealer to submit an estimate request via text description, voice recording, or photo upload. Only one submission type is accepted per request.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["type"],
                    properties: [
                        new OA\Property(
                            property: "type",
                            type: "string",
                            enum: ["Text", "Voice", "Photo"],
                            description: "Submission type — determines which field is required",
                            example: "Text"
                        ),
                        new OA\Property(
                            property: "description",
                            type: "string",
                            description: "Required when type is Text",
                            example: "I need 10 units of product XYZ"
                        ),
                        new OA\Property(
                            property: "file",
                            type: "string",
                            format: "binary",
                            description: "Required when type is Voice (audio file)"
                        ),
                        new OA\Property(
                            property: "files[]",
                            type: "array",
                            items: new OA\Items(type: "string", format: "binary"),
                            description: "Required when type is Photo (multiple images supported)"
                        ),

                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Estimate submitted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Estimate request submitted successfully."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "member_id", type: "integer", example: 5),
                                new OA\Property(property: "type", type: "string", example: "Text"),
                                new OA\Property(property: "file_paths", type: "array", items: new OA\Items(type: "string"), example: ["estimates/photos/abc.jpg"]),
                                new OA\Property(property: "status", type: "string", example: "Pending"),
                                new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-05-19T14:00:00Z"),

                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Only dealers can submit estimates",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Only dealers can submit estimate requests."),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Validation failed."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function submitEstimate(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Only dealers can submit estimate requests.',
            ], 403);
        }

        $request->validate([
            'type' => 'required|string|in:Text,Voice,Photo,Document,Pdf,text,voice,photo,document,pdf',
            'description' => 'required_if:type,Text,text|nullable|string|max:2000',
            'file' => 'required_if:type,Voice,voice,Document,document,Pdf,pdf|nullable|file|max:20480',
            'files' => 'required_if:type,Photo,photo|nullable|array',
            'files.*' => 'file|max:20480',
        ]);

        $filePaths = [];

        // Single file (for Voice, Document, PDF, or legacy Photo)
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $typeLower = strtolower($request->type);
            if ($typeLower === 'voice') {
                $folder = 'estimates/voice';
            } elseif ($typeLower === 'document' || $typeLower === 'pdf') {
                $folder = 'estimates/documents';
            } else {
                $folder = 'estimates/photos';
            }
            $filePaths[] = $request->file('file')->store($folder, 'public');
        }

        // Multiple files (for Photo)
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $filePaths[] = $file->store('estimates/photos', 'public');
                }
            }
        }

        // Auto-generate unique estimate number: EST-{id padded}
        $nextId = (Estimate::max('id') ?? 0) + 1;
        $requestNumber = 'EST-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $estimate = Estimate::create([
            'member_id' => $dealer->id,
            'request_number' => $requestNumber,
            'type' => ucfirst(strtolower($request->type)),
            'description' => $request->description,
            'file_path' => $filePaths, // Saved as JSON array due to model cast
            'status' => 'Pending',
        ]);




        return response()->json([
            'success' => true,
            'message' => 'Estimate request submitted successfully.',
            'data' => [
                'id' => $estimate->id,
                'request_number' => $estimate->request_number,
                'member_id' => $estimate->member_id,
                'type' => $estimate->type,
                'description' => $estimate->description,

                'file_paths' => $estimate->file_path,

                'status' => $estimate->status,
                'created_at' => $estimate->created_at,
            ],
        ], 201);
    }

    // =========================================================================
    // PLACE ORDER REQUEST — POST /api/dealer/order-request
    // Dealer only. Supports: Text | Voice | Photo
    // =========================================================================
    #[OA\Post(
        path: "/dealer/order-request",
        summary: "Submit a place order request",
        description: "Allows a dealer to place an order request via text, voice, photo, or call. For type=Call, no additional fields are needed — it simply logs the call intent.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["type"],
                    properties: [
                        new OA\Property(
                            property: "type",
                            type: "string",
                            enum: ["Text", "Voice", "Photo", "Call"],
                            description: "Submission type. For Call, no extra fields are needed.",
                            example: "Text"
                        ),
                        new OA\Property(
                            property: "description",
                            type: "string",
                            description: "Required when type is Text",
                            example: "Please deliver 20 units of item ABC"
                        ),
                        new OA\Property(
                            property: "file",
                            type: "string",
                            format: "binary",
                            description: "Required when type is Voice (audio)"
                        ),
                        new OA\Property(
                            property: "files[]",
                            type: "array",
                            items: new OA\Items(type: "string", format: "binary"),
                            description: "Required when type is Photo (multiple images supported)"
                        ),

                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Order request submitted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Order request submitted successfully."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 12),
                                new OA\Property(property: "request_number", type: "string", example: "ORD-20260519-0012"),
                                new OA\Property(property: "member_id", type: "integer", example: 5),
                                new OA\Property(property: "type", type: "string", example: "Photo"),
                                new OA\Property(property: "description", type: "string", nullable: true, example: null),
                                new OA\Property(property: "file_paths", type: "array", items: new OA\Items(type: "string"), example: ["order-requests/photos/abc.jpg"]),
                                new OA\Property(property: "status", type: "string", example: "Pending"),

                                new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-05-19T14:00:00Z"),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Only dealers can place order requests",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Only dealers can place order requests."),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Validation failed."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function placeOrderRequest(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Only dealers can place order requests.',
            ], 403);
        }

        $request->validate([
            'type' => 'required|string|in:Text,Voice,Photo,Call,Document,Pdf,text,voice,photo,call,document,pdf',
            'description' => 'required_if:type,Text,text|nullable|string|max:2000',
            'file' => 'required_if:type,Voice,voice,Document,document,Pdf,pdf|nullable|file|max:20480',
            'files' => 'required_if:type,Photo,photo|nullable|array',
            'files.*' => 'file|max:20480',
        ]);

        $filePaths = [];

        // Handle single file (Voice, Document, PDF, or legacy Photo)
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $typeLower = strtolower($request->type);
            if ($typeLower === 'voice') {
                $folder = 'order-requests/voice';
            } elseif ($typeLower === 'document' || $typeLower === 'pdf') {
                $folder = 'order-requests/documents';
            } else {
                $folder = 'order-requests/photos';
            }
            $filePaths[] = $request->file('file')->store($folder, 'public');
        }

        // Handle multiple files (Photo)
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $filePaths[] = $file->store('order-requests/photos', 'public');
                }
            }
        }

        // Auto-generate unique request number: ORD-YYYYMMDD-{id padded}
        $requestNumber = 'ORD-' . now()->format('Ymd') . '-' . str_pad(
            (OrderRequest::max('id') ?? 0) + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        $orderRequest = OrderRequest::create([
            'member_id' => $dealer->id,
            'request_number' => $requestNumber,
            'type' => ucfirst(strtolower($request->type)),
            'description' => $request->description,
            'file_path' => $filePaths,
            'status' => 'Pending',
        ]);



        return response()->json([
            'success' => true,
            'message' => 'Order request submitted successfully.',
            'data' => [
                'id' => $orderRequest->id,
                'request_number' => $orderRequest->request_number,
                'member_id' => $orderRequest->member_id,
                'type' => $orderRequest->type,
                'description' => $orderRequest->description,
                'file_paths' => $orderRequest->file_path,

                'status' => $orderRequest->status,
                'created_at' => $orderRequest->created_at,
            ],
        ], 201);
    }

    #[OA\Get(
        path: "/dealer/my-orders",
        summary: "Get dealer order history",
        description: "Fetches a unified, paginated list of Estimates, Order Requests, and Orders for the authenticated dealer.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        parameters: [
            new OA\Parameter(
                name: "tab",
                in: "query",
                description: "Filter by tab: All, Pending, Confirmed, Order Placed",
                required: false,
                schema: new OA\Schema(type: "string", default: "All")
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Search by ID or Date",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Page number",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "order_id", type: "string"),
                                new OA\Property(property: "date", type: "string"),
                                new OA\Property(property: "status", type: "string"),
                                new OA\Property(property: "type", type: "string")
                            ]
                        )),
                        new OA\Property(property: "meta", type: "object", properties: [
                            new OA\Property(property: "current_page", type: "integer"),
                            new OA\Property(property: "last_page", type: "integer"),
                            new OA\Property(property: "per_page", type: "integer"),
                            new OA\Property(property: "total", type: "integer")
                        ])
                    ]
                )
            )
        ]
    )]
    public function myOrders(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $tab = $request->query('tab', 'All'); // All, Pending, Confirmed, Order Placed
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $merged = collect();

        // 1. Estimates
        if (in_array($tab, ['All', 'Pending'])) {
            $estimates = Estimate::where('member_id', $dealer->id)
                ->when($search, function ($query) use ($search) {
                    return $query->where('request_number', 'like', "%$search%")
                        ->orWhere('created_at', 'like', "%$search%");
                })
                ->get()
                ->map(function ($item) {
                    $reqNo = $item->request_number ?? 'EST-' . str_pad($item->id, 4, '0', STR_PAD_LEFT);
                    return [
                        'id' => $item->id,
                        'order_id' => $reqNo,
                        'request_number' => $reqNo,
                        'date' => $item->created_at->format('d M Y'),
                        'status' => $item->status,
                        'type' => 'Estimate',
                        'raw_date' => $item->created_at,
                        'response_description' => $item->response_description,
                        'response_file_path' => $item->response_file_path ? asset('uploads/' . $item->response_file_path) : null,
                    ];
                });
            $merged = $merged->concat($estimates);
        }

        // 2. Order Requests
        if (in_array($tab, ['All', 'Pending', 'Confirmed'])) {
            $orderRequests = OrderRequest::where('member_id', $dealer->id)
                ->when($tab === 'Pending', function ($query) {
                    return $query->where('status', 'Pending');
                })
                ->when($tab === 'Confirmed', function ($query) {
                    return $query->where('status', 'Processed');
                })
                ->when($search, function ($query) use ($search) {
                    return $query->where('request_number', 'like', "%$search%")
                        ->orWhere('created_at', 'like', "%$search%");
                })
                ->get()
                ->map(function ($item) {
                    $displayStatus = ($item->status === 'Processed') ? 'Confirmed' : $item->status;
                    return [
                        'id' => $item->id,
                        'order_id' => $item->request_number,
                        'request_number' => $item->request_number,
                        'date' => $item->created_at->format('d M Y'),
                        'status' => ucfirst($displayStatus),
                        'type' => 'Order Request',
                        'raw_date' => $item->created_at,
                    ];
                });
            $merged = $merged->concat($orderRequests);
        }

        // 3. Orders
        if (in_array($tab, ['All', 'Order Placed'])) {
            $orders = Order::where('member_id', $dealer->id)
                ->with(['invoice', 'items'])
                ->when($search, function ($query) use ($search) {
                    return $query->where('order_number', 'like', "%$search%")
                        ->orWhere('created_at', 'like', "%$search%");
                })
                ->when($startDate, function ($query) use ($startDate) {
                    return $query->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($query) use ($endDate) {
                    return $query->whereDate('created_at', '<=', $endDate);
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'order_id' => $item->order_number,
                        'request_number' => $item->order_number,
                        'date' => $item->created_at->format('d M Y'),
                        'status' => 'Order Placed',
                        'type' => 'Order',
                        'raw_date' => $item->created_at,
                        'amount' => $item->amount > 0 ? $item->amount : ($item->invoice && $item->invoice->amount > 0 ? $item->invoice->amount : $item->items->sum(function($i) { return $i->qty * $i->price; })),
                        'invoice_number' => $item->invoice ? $item->invoice->invoice_number : null,
                        'has_invoice' => ($item->invoice_file || $item->challan_file || ($item->invoice && $item->invoice->file_path)) ? true : false,
                    ];
                });
            $merged = $merged->concat($orders);
        }

        // Sort by date DESC
        $sorted = $merged->sortByDesc('raw_date')->values();

        // Manual Pagination
        $perPage = 10;
        $page = $request->query('page', 1);
        $paginatedData = $sorted->forPage($page, $perPage);

        $paginator = new LengthAwarePaginator(
            $paginatedData,
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    #[OA\Get(
        path: "/dealer/my-orders/details",
        summary: "Get my orders details",
        description: "Fetches detailed information for a specific Order, Order Request, or Estimate.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        parameters: [
            new OA\Parameter(
                name: "order_id",
                in: "query",
                description: "The unique identifier of the order, order request, or estimate (or numeric ID)",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                description: "Type of the record (Order, Order Request, Estimate)",
                required: true,
                schema: new OA\Schema(type: "string", enum: ["Order", "Order Request", "Estimate"])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Details fetched successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Record not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Record not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation failed",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Validation failed."),
                        new OA\Property(property: "errors", type: "object")
                    ]
                )
            )
        ]
    )]
    public function orderDetails(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'order_id' => 'required',
            'type' => 'required|string'
        ]);

        $orderId = $request->query('order_id');
        $type = strtolower($request->query('type'));

        if ($type === 'order') {
            $order = Order::where('member_id', $dealer->id)
                ->where(function ($query) use ($orderId) {
                    $query->where('order_number', $orderId)
                        ->orWhere('id', $orderId);
                })
                ->with(['items', 'delivery', 'invoice'])
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            // Map order items with totals
            $items = $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->qty * $item->price,
                ];
            });

            // Map delivery if exists
            $delivery = null;
            if ($order->delivery) {
                $delivery = [
                    'id' => $order->delivery->id,
                    'vehicle_no' => $order->delivery->vehicle_no,
                    'vehicle_type' => $order->delivery->vehicle_type,
                    'driver_phone' => $order->delivery->driver_phone,
                    'expected_delivery_at' => $order->delivery->expected_delivery_at,
                    'remarks' => $order->delivery->remarks,
                    'status' => $order->delivery->status,
                ];
            }

            // Map invoice if exists
            $invoice = null;
            if ($order->invoice) {
                $invoice = [
                    'id' => $order->invoice->id,
                    'invoice_number' => $order->invoice->invoice_number,
                    'amount' => $order->invoice->amount,
                    'file_path' => $order->invoice->file_path ? asset('uploads/' . $order->invoice->file_path) : null,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'order_id' => $order->order_number,
                    'date' => $order->created_at->format('d M Y'),
                    'status' => 'Order Placed',
                    'type' => 'Order',
                    'description' => $order->description,
                    'amount' => $order->amount,
                    'challan_file' => $order->challan_file ? asset('uploads/' . $order->challan_file) : null,
                    'invoice_file' => $order->invoice_file ? asset('uploads/' . $order->invoice_file) : null,
                    'received_at' => $order->received_at,
                    'created_at' => $order->created_at,
                    'items' => $items,
                    'delivery' => $delivery,
                    'invoice' => $invoice,
                ],
            ]);
        }

        if ($type === 'order request' || $type === 'order_request') {
            $orderRequest = OrderRequest::where('member_id', $dealer->id)
                ->where(function ($query) use ($orderId) {
                    $query->where('request_number', $orderId)
                        ->orWhere('id', $orderId);
                })
                ->first();

            if (!$orderRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order request not found.',
                ], 404);
            }

            // Decode file path array
            $files = [];
            if ($orderRequest->file_path) {
                $filePaths = is_array($orderRequest->file_path) ? $orderRequest->file_path : json_decode($orderRequest->file_path, true);
                if (is_array($filePaths)) {
                    foreach ($filePaths as $path) {
                        $files[] = asset('uploads/' . $path);
                    }
                }
            }

            $displayStatus = ($orderRequest->status === 'Processed') ? 'Confirmed' : $orderRequest->status;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $orderRequest->id,
                    'order_id' => $orderRequest->request_number,
                    'date' => $orderRequest->created_at->format('d M Y'),
                    'status' => ucfirst($displayStatus),
                    'type' => 'Order Request',
                    'submission_type' => $orderRequest->type,
                    'description' => $orderRequest->description,
                    'files' => $files,
                    'created_at' => $orderRequest->created_at,
                ],
            ]);
        }

        if ($type === 'estimate') {
            $estimate = Estimate::where('member_id', $dealer->id)
                ->where(function ($query) use ($orderId) {
                    $query->where('request_number', $orderId)
                        ->orWhere('id', $orderId)
                        ->when(str_starts_with(strtoupper($orderId), 'EST-'), function ($q) use ($orderId) {
                            $num = (int) substr($orderId, 4);
                            $q->orWhere('id', $num);
                        });
                })
                ->first();

            if (!$estimate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estimate not found.',
                ], 404);
            }

            // Decode file path array
            $files = [];
            if ($estimate->file_path) {
                $filePaths = is_array($estimate->file_path) ? $estimate->file_path : json_decode($estimate->file_path, true);
                if (is_array($filePaths)) {
                    foreach ($filePaths as $path) {
                        $files[] = asset('uploads/' . $path);
                    }
                }
            }

            $reqNo = $estimate->request_number ?? 'EST-' . str_pad($estimate->id, 4, '0', STR_PAD_LEFT);

            // Revert details mapping
            $revertDetails = null;
            if ($estimate->response_description || $estimate->response_file_path) {
                $revertDetails = [
                    'response_description' => $estimate->response_description,
                    'response_file_path' => $estimate->response_file_path ? asset('uploads/' . $estimate->response_file_path) : null,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $estimate->id,
                    'order_id' => $reqNo,
                    'date' => $estimate->created_at->format('d M Y'),
                    'status' => $estimate->status,
                    'type' => 'Estimate',
                    'submission_type' => $estimate->type,
                    'description' => $estimate->description,
                    'files' => $files,
                    'revert_details' => $revertDetails,
                    'created_at' => $estimate->created_at,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid type provided. Must be one of: Order, Order Request, Estimate',
        ], 422);
    }

    #[OA\Get(
        path: "/dealer/my-orders/download-invoice",
        summary: "Download invoice for an order",
        description: "Fetches the direct download link for the invoice or challan of a specific order.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        parameters: [
            new OA\Parameter(
                name: "order_id",
                in: "query",
                description: "The unique identifier of the order",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                description: "Type of the record (Order)",
                required: true,
                schema: new OA\Schema(type: "string", enum: ["Order"])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Link fetched successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "download_link", type: "string", example: "https://example.com/uploads/invoices/123.pdf")
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Invoice not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Invoice or challan not found for this order.")
                    ]
                )
            )
        ]
    )]
    public function downloadInvoice(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'order_id' => 'required',
            'type' => 'required|string'
        ]);

        $orderId = $request->query('order_id');
        $type = strtolower($request->query('type'));

        if ($type === 'order') {
            $order = Order::where('member_id', $dealer->id)
                ->where(function ($query) use ($orderId) {
                    $query->where('order_number', $orderId)
                        ->orWhere('id', $orderId);
                })
                ->with('invoice')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            $downloadLink = null;
            if ($order->invoice && $order->invoice->file_path) {
                $downloadLink = asset('uploads/' . $order->invoice->file_path);
            } elseif ($order->invoice_file) {
                $downloadLink = asset('uploads/' . $order->invoice_file);
            } elseif ($order->challan_file) {
                $downloadLink = asset('uploads/' . $order->challan_file);
            }

            if (!$downloadLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice or challan not found for this order.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'download_link' => $downloadLink,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Download not supported for this type.',
        ], 400);
    }

    #[OA\Post(
        path: "/dealer/update-fcm-token",
        summary: "Register or update FCM device token",
        description: "Registers an FCM token for the authenticated dealer's current device, enabling push notifications to be sent to this device.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["fcm_token"],
                properties: [
                    new OA\Property(property: "fcm_token", type: "string", description: "The FCM push token received from Firebase SDK")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Token registered successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "FCM Token registered successfully.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function updateFcmToken(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $dealer->devices()->updateOrCreate(
            ['fcm_token' => $request->fcm_token]
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM Token registered successfully.',
        ]);
    }

    #[OA\Get(
        path: "/dealer/notifications",
        summary: "Get notifications history",
        description: "Fetches a paginated history list of all stored notifications sent to the authenticated dealer.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        parameters: [
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Page number",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Notifications fetched successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "title", type: "string"),
                                new OA\Property(property: "body", type: "string"),
                                new OA\Property(property: "data", type: "object", nullable: true),
                                new OA\Property(property: "is_read", type: "boolean"),
                                new OA\Property(property: "created_at", type: "string", format: "date-time")
                            ]
                        ))
                    ]
                )
            )
        ]
    )]
    public function getNotifications(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $notifications = $dealer->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]
        ]);
    }

    #[OA\Post(
        path: "/dealer/notifications/read-all",
        summary: "Mark all notifications as read",
        description: "Marks all stored notifications for the authenticated dealer as read.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Notifications marked as read successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "All notifications marked as read.")
                    ]
                )
            )
        ]
    )]
    public function readAllNotifications(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $dealer->notifications()->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    #[OA\Get(
        path: "/dealer/my-points",
        summary: "Get dealer reward points and transaction history",
        description: "Fetches the total points balance and history of points earned or redeemed for the authenticated dealer.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        parameters: [
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Page number to retrieve",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Number of records to retrieve per page",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Points details retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_points", type: "integer", example: 2450),
                                new OA\Property(
                                    property: "history",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "title", type: "string", example: "Order #ORD12345"),
                                            new OA\Property(property: "points", type: "string", example: "+150"),
                                            new OA\Property(property: "date", type: "string", example: "20 May 2026"),
                                            new OA\Property(property: "type", type: "string", example: "Order Points"),
                                            new OA\Property(property: "raw_points", type: "integer", example: 150)
                                        ]
                                    )
                                ),
                                new OA\Property(
                                    property: "meta",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "current_page", type: "integer", example: 1),
                                        new OA\Property(property: "last_page", type: "integer", example: 5),
                                        new OA\Property(property: "per_page", type: "integer", example: 15),
                                        new OA\Property(property: "total", type: "integer", example: 75)
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Unauthorized - Only dealers can access points balance",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized.")
                    ]
                )
            )
        ]
    )]
    public function myPoints(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $totalPoints = (int) $dealer->points_balance;
        $lockedPoints = (int) $dealer->rewardTransactions()->where('count_days', '>', 0)->sum('points');
        $redeemedPoints = (int) \App\Models\RedeemRequest::where('member_id', $dealer->id)->whereIn('status', ['Pending', 'Approved', 'Processed'])->sum('Points');
        $redeemablePoints = max(0, $totalPoints - $lockedPoints - $redeemedPoints);

        $perPage = (int) $request->query('per_page', 15);
        $transactions = $dealer->rewardTransactions()
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $history = collect($transactions->items())->map(function ($tx) {
            $title = $tx->type;
            if ($tx->order_id) {
                $title = 'Order #' . ($tx->order->order_number ?? $tx->order_id);
            }

            return [
                'id' => $tx->id,
                'title' => $title,
                'points' => ($tx->points >= 0 ? '+' : '') . $tx->points,
                'date' => $tx->created_at->format('d M Y'),
                'type' => $tx->type,
                'raw_points' => $tx->points,
            ];
        });

        $redeemRequests = \App\Models\RedeemRequest::where('member_id', $dealer->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'title' => '#RDM' . str_pad($req->id, 5, '0', STR_PAD_LEFT),
                    'points' => '-' . $req->Points,
                    'date' => $req->created_at ? $req->created_at->format('d M Y') : 'N/A',
                    'type' => 'Redemption',
                    'raw_points' => -((int) $req->Points),
                    'status' => ucfirst($req->status ?? 'Pending'),
                    'credit_note' => $req->Credit_note ?? 'Pending',
                    'note' => $req->notes ?? 'Redemption request submitted.',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_points' => $totalPoints,
                'redeemable_points' => $redeemablePoints,
                'locked_points' => $lockedPoints,
                'history' => $history,
                'redeem_requests' => $redeemRequests,
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ]
        ]);
    }

    #[OA\Post(
        path: "/dealer/redeem-request",
        summary: "Submit a points redeem request",
        description: "Stores a redeem request for the authenticated dealer.",
        security: [["bearerAuth" => []]]
    )]
    public function submitRedeemRequest(Request $request): JsonResponse
    {
        $dealer = $request->user();

        $points = (int) $request->input('points', $request->input('Points', 0));
        $notes = $request->input('notes', $request->input('note', $request->input('remarks', '')));

        if ($points <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid points amount to redeem.',
            ], 422);
        }

        $totalPoints = (int) $dealer->points_balance;
        $lockedPoints = (int) $dealer->rewardTransactions()->where('count_days', '>', 0)->sum('points');
        $redeemedPoints = (int) \App\Models\RedeemRequest::where('member_id', $dealer->id)->whereIn('status', ['Pending', 'Approved', 'Processed'])->sum('Points');
        $redeemablePoints = max(0, $totalPoints - $lockedPoints - $redeemedPoints);

        if ($points > $redeemablePoints) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have enough redeemable points.',
            ], 422);
        }

        \App\Models\RedeemRequest::create([
            'member_id' => $dealer->id,
            'Points' => $points,
            'notes' => $notes,
            'status' => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Redeem request submitted successfully.',
        ]);
    }

    #[OA\Get(
        path: "/dealer/price-list",
        summary: "Get latest dynamic price list",
        description: "Fetches the details and download URL of the currently active price list PDF for the authenticated dealer.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Latest price list fetched successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            nullable: true,
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "version", type: "string", example: "v2.5"),
                                new OA\Property(property: "file_name", type: "string", example: "PriceList.pdf"),
                                new OA\Property(property: "file_size", type: "string", example: "1.2 MB"),
                                new OA\Property(property: "url", type: "string", example: "http://localhost/storage/pricelists/price_list_123.pdf"),
                                new OA\Property(property: "uploaded_at", type: "string", example: "2026-05-22 15:30:00")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized.")
                    ]
                )
            )
        ]
    )]
    public function getLatestPriceList(Request $request): JsonResponse
    {
        /** @var Member $user */
        $user = $request->user();



        $latest = \App\Models\PriceList::orderBy('id', 'desc')->first();

        if (!$latest) {
            return response()->json([
                'success' => true,
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $latest->id,
                'version' => $latest->version,
                'file_name' => $latest->file_name,
                'file_size' => $latest->file_size,
                'url' => asset('uploads/' . $latest->file_path),
                'uploaded_at' => $latest->created_at->format('Y-m-d H:i:s')
            ]
        ]);
    }

    #[OA\Get(
        path: "/dealer/my-passbook",
        summary: "Get dealer passbook and payment history",
        description: "Fetches the total billed amount, total paid amount, outstanding due, and transaction history ledger for the authenticated dealer.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        parameters: [
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Page number to retrieve",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Number of records to retrieve per page",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Passbook details fetched successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_amount", type: "number", format: "float", example: 125000.00),
                                new OA\Property(property: "paid_amount", type: "number", format: "float", example: 75000.00),
                                new OA\Property(property: "due_amount", type: "number", format: "float", example: 50000.00),
                                new OA\Property(
                                    property: "history",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "date", type: "string", example: "12 May, 2026"),
                                            new OA\Property(property: "amount", type: "string", example: "+ ₹ 15,000.00"),
                                            new OA\Property(property: "raw_amount", type: "number", format: "float", example: 15000.00),
                                            new OA\Property(property: "type", type: "string", example: "Order"),
                                            new OA\Property(property: "ref", type: "string", example: "ORD-5580"),
                                            new OA\Property(property: "status", type: "string", example: "Confirmed"),
                                            new OA\Property(property: "managed_by", type: "string", example: "Alice Smith")
                                        ]
                                    )
                                ),
                                new OA\Property(
                                    property: "meta",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "current_page", type: "integer", example: 1),
                                        new OA\Property(property: "last_page", type: "integer", example: 5),
                                        new OA\Property(property: "per_page", type: "integer", example: 15),
                                        new OA\Property(property: "total", type: "integer", example: 75)
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized.")
                    ]
                )
            )
        ]
    )]
    public function myPassbook(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if (!$dealer->is_passbook_visible) {
            return response()->json([
                'success' => false,
                'message' => 'Passbook is currently hidden by admin.',
            ], 403);
        }

        $balance = $dealer->dealerBalance;
        $totalAmount = $balance ? (float) $balance->total_amount : 0.00;
        $paidAmount = $balance ? (float) $balance->paid_amount : 0.00;
        $dueAmount = $balance ? (float) $balance->due_amount : 0.00;

        $perPage = (int) $request->query('per_page', 15);
        $transactions = $dealer->passbookTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $history = collect($transactions->items())->map(function ($txn) {
            $prefix = ($txn->type === 'Order' || $txn->type === 'Adjustment') ? '+' : '-';
            
            return [
                'id' => $txn->id,
                'date' => $txn->created_at->format('d M, Y'),
                'amount' => $prefix . ' ₹ ' . number_format($txn->amount, 2),
                'raw_amount' => (float) $txn->amount,
                'type' => $txn->type,
                'ref' => $txn->ref,
                'status' => $txn->status,
                'managed_by' => $txn->managed_by,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'history' => $history,
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ]
        ]);
    }

    #[OA\Post(
        path: "/dealer/upload-payment",
        summary: "Upload payment details & receipt",
        description: "Allows a dealer to upload payment details including amount and a file copy of the payment receipt.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["amount", "receipt"],
                    properties: [
                        new OA\Property(
                            property: "amount",
                            type: "number",
                            format: "float",
                            description: "Amount of payment paid",
                            example: 15000.00
                        ),
                        new OA\Property(
                            property: "receipt",
                            type: "string",
                            format: "binary",
                            description: "Payment receipt file (PDF, JPG, PNG, JPEG)"
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Payment uploaded successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Payment receipt uploaded successfully!"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "amount", type: "string", example: "15000.00"),
                                new OA\Property(property: "receipt_url", type: "string", example: "http://localhost/storage/payments/receipts/abc.jpg"),
                                new OA\Property(property: "status", type: "string", example: "Pending"),
                                new OA\Property(property: "created_at", type: "string", format: "date-time")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Only dealers can upload payment details.")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            )
        ]
    )]
    public function uploadPayment(Request $request): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Only dealers can upload payment details.',
            ], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'receipt' => 'required|file|mimes:pdf,jpg,png,jpeg|max:20480',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt') && $request->file('receipt')->isValid()) {
            $receiptPath = $request->file('receipt')->store('payments/receipts', 'public');
        }

        $submission = \App\Models\PaymentSubmission::create([
            'member_id' => $dealer->id,
            'amount' => $request->amount,
            'receipt_path' => $receiptPath,
            'status' => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment receipt uploaded successfully!',
            'data' => [
                'id' => $submission->id,
                'amount' => $submission->amount,
                'receipt_url' => asset('uploads/' . $submission->receipt_path),
                'status' => $submission->status,
                'created_at' => $submission->created_at,
            ],
        ], 201);
    }

    #[OA\Post(
        path: "/dealer/order/{id}/receive",
        summary: "Mark an order as received by the dealer",
        description: "Allows a dealer to mark their order as successfully received. Updates both the order status and delivery status to 'Delivered' and logs the received timestamp.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Numeric ID of the order",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Order marked as received successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Order marked as received successfully!"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 3),
                                new OA\Property(property: "order_number", type: "string", example: "ORD-0003"),
                                new OA\Property(property: "status", type: "string", example: "Delivered"),
                                new OA\Property(property: "received_at", type: "string", format: "date-time")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Order not found"
            )
        ]
    )]
    public function markOrderReceived(Request $request, $id): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        // Dealer-only guard
        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $order = Order::where('member_id', $dealer->id)->findOrFail($id);

        $order->update([
            'status' => 'Delivered',
            'received_at' => now(),
        ]);

        // If there is an associated delivery, update its status too!
        if ($order->delivery) {
            $order->delivery->update([
                'status' => 'Delivered',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order marked as received successfully!',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'received_at' => $order->received_at,
            ],
        ], 200);
    }

    #[OA\Post(
        path: "/dealer/estimate/{id}/confirm",
        summary: "Confirm an estimate and convert to order request",
        description: "Confirms a Responded estimate and automatically generates an Order Request from it.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Estimate ID", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Estimate confirmed and converted"),
            new OA\Response(response: 400, description: "Invalid state"),
            new OA\Response(response: 404, description: "Estimate not found")
        ]
    )]
    public function confirmEstimate(Request $request, $id): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json(['success' => false, 'message' => 'Only dealers can perform this action.'], 403);
        }

        $estimate = Estimate::where('id', $id)->where('member_id', $dealer->id)->first();

        if (!$estimate) {
            return response()->json(['success' => false, 'message' => 'Estimate request not found.'], 404);
        }

        if (in_array($estimate->status, ['Cancelled', 'Confirmed'])) {
            return response()->json(['success' => false, 'message' => 'Estimate request cannot be confirmed in its current state.'], 400);
        }

        $requestNumber = 'ORD-' . now()->format('Ymd') . '-' . str_pad(
            (OrderRequest::max('id') ?? 0) + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        $description = 'Converted from Estimate ' . $estimate->request_number;
        if ($estimate->description) {
            $description .= "\nOriginal: " . $estimate->description;
        }
        if ($estimate->response_description) {
            $description .= "\nAdmin Response: " . $estimate->response_description;
        }

        $mergedPaths = [];
        if (!empty($estimate->file_path) && is_array($estimate->file_path)) {
            $mergedPaths = array_merge($mergedPaths, $estimate->file_path);
        }
        if (!empty($estimate->response_file_path)) {
            if (is_array($estimate->response_file_path)) {
                $mergedPaths = array_merge($mergedPaths, $estimate->response_file_path);
            } else {
                $mergedPaths[] = $estimate->response_file_path;
            }
        }

        $orderRequest = OrderRequest::create([
            'member_id' => $dealer->id,
            'request_number' => $requestNumber,
            'type' => 'Text', // Or we could use the original type, but this encapsulates the details
            'description' => $description,
            'file_path' => $mergedPaths,
            'status' => 'Pending',
        ]);

        $estimate->update(['status' => 'Confirmed']);

        return response()->json([
            'success' => true,
            'message' => 'Estimate converted into Order Request successfully.',
            'data' => [
                'order_request_id' => $orderRequest->id,
                'request_number' => $orderRequest->request_number
            ]
        ], 200);
    }

    #[OA\Post(
        path: "/dealer/estimate/{id}/cancel",
        summary: "Cancel an estimate",
        description: "Cancels an estimate.",
        security: [["bearerAuth" => []]],
        tags: ["Dealer"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Estimate ID", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Estimate cancelled successfully"),
            new OA\Response(response: 400, description: "Already cancelled"),
            new OA\Response(response: 404, description: "Estimate not found")
        ]
    )]
    public function cancelEstimate(Request $request, $id): JsonResponse
    {
        /** @var Member $dealer */
        $dealer = $request->user();

        if (strtolower($dealer->role) !== 'dealer') {
            return response()->json(['success' => false, 'message' => 'Only dealers can perform this action.'], 403);
        }

        $estimate = Estimate::where('id', $id)->where('member_id', $dealer->id)->first();

        if (!$estimate) {
            return response()->json(['success' => false, 'message' => 'Estimate request not found.'], 404);
        }

        if ($estimate->status === 'Cancelled') {
            return response()->json(['success' => false, 'message' => 'Estimate request is already cancelled.'], 400);
        }

        if ($estimate->status === 'Confirmed') {
            return response()->json(['success' => false, 'message' => 'Cannot cancel an already confirmed estimate.'], 400);
        }

        $estimate->update(['status' => 'Cancelled']);

        return response()->json([
            'success' => true, 
            'message' => 'Estimate request cancelled successfully.',
            'data' => [
                'id' => $estimate->id,
                'status' => $estimate->status
            ]
        ], 200);
    }
}


