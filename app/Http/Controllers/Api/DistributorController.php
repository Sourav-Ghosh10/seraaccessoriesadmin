<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\Estimate;
use App\Models\OrderRequest;
use App\Models\Member;
use App\Models\Delivery;
use App\Services\FcmService;
use OpenApi\Attributes as OA;

class DistributorController extends Controller
{
    /**
     * Helper to verify if the authenticated member has a 'distributor' role.
     */
    protected function verifyDistributor(Member $member): bool
    {
        return strtolower($member->role) === 'distributor';
    }

    #[OA\Get(
        path: "/distributor/my-orders",
        summary: "Get assigned orders",
        description: "Fetches a paginated list of all orders assigned to the authenticated distributor. Supports searching by order number, dealer name, or shop name, and filtering by status.",
        security: [["bearerAuth" => []]],
        tags: ["Distributor"],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Search by order number, dealer name, or shop",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "status",
                in: "query",
                description: "Filter by order status (e.g., Confirmed, Out for Delivery, Delivered)",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Number of records per page",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
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
                description: "Orders fetched successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "order_number", type: "string"),
                                new OA\Property(property: "amount", type: "number", format: "float"),
                                new OA\Property(property: "status", type: "string"),
                                new OA\Property(property: "received_at", type: "string", format: "date-time", nullable: true),
                                new OA\Property(property: "created_at", type: "string", format: "date-time"),
                                new OA\Property(property: "dealer", type: "object", properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "name", type: "string"),
                                    new OA\Property(property: "shop", type: "string"),
                                    new OA\Property(property: "mobile", type: "string"),
                                    new OA\Property(property: "email", type: "string")
                                ]),
                                new OA\Property(property: "delivery", type: "object", nullable: true, properties: [
                                    new OA\Property(property: "vehicle_no", type: "string"),
                                    new OA\Property(property: "vehicle_type", type: "string"),
                                    new OA\Property(property: "driver_phone", type: "string"),
                                    new OA\Property(property: "expected_delivery_at", type: "string"),
                                    new OA\Property(property: "remarks", type: "string", nullable: true),
                                    new OA\Property(property: "status", type: "string")
                                ])
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
    public function myOrders(Request $request): JsonResponse
    {
        /** @var Member $distributor */
        $distributor = $request->user();

        if (!$this->verifyDistributor($distributor)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $ordersQuery = Order::where('distributor_id', $distributor->id)
            ->with(['member', 'delivery', 'invoice', 'items'])
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('member', function ($mq) use ($search) {
                          $mq->where('name', 'like', "%{$search}%")
                             ->orWhere('shop', 'like', "%{$search}%");
                      });
                });
            })
            ->when($startDate, function ($query) use ($startDate) {
                return $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                return $query->whereDate('created_at', '<=', $endDate);
            })
            ->orderBy('created_at', 'desc');

        $orders = $ordersQuery->paginate($perPage);

        $data = collect($orders->items())->map(function ($order) {
            $delivery = null;
            if ($order->delivery) {
                $delivery = [
                    'vehicle_no' => $order->delivery->vehicle_no,
                    'vehicle_type' => $order->delivery->vehicle_type,
                    'driver_phone' => $order->delivery->driver_phone,
                    'expected_delivery_at' => $order->delivery->expected_delivery_at,
                    'remarks' => $order->delivery->remarks,
                    'status' => $order->delivery->status,
                ];
            }

            $calculatedAmount = $order->items->sum(function($i) { return $i->qty * $i->price; });
            $finalAmount = $order->amount > 0 ? $order->amount : ($order->invoice && $order->invoice->amount > 0 ? $order->invoice->amount : $calculatedAmount);

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'amount' => (float) $finalAmount,
                'status' => $order->status,
                'received_at' => $order->received_at,
                'created_at' => $order->created_at,
                'dealer' => [
                    'id' => $order->member->id,
                    'name' => $order->member->name,
                    'shop' => $order->member->shop,
                    'mobile' => $order->member->mobile,
                    'email' => $order->member->email,
                ],
                'delivery' => $delivery,
                'invoice_number' => $order->invoice ? $order->invoice->invoice_number : null,
                'has_invoice' => ($order->invoice_file || $order->challan_file || ($order->invoice && $order->invoice->file_path)) ? true : false,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ], 200);
    }

    #[OA\Get(
        path: "/distributor/my-orders/details",
        summary: "Get assigned order details",
        description: "Fetches detailed information for a specific order assigned to the authenticated distributor.",
        security: [["bearerAuth" => []]],
        tags: ["Distributor"],
        parameters: [
            new OA\Parameter(
                name: "order_id",
                in: "query",
                description: "The order number or numeric ID of the order",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                description: "Type of record (must be Order)",
                required: true,
                schema: new OA\Schema(type: "string", enum: ["Order"])
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
                description: "Order not found or not assigned to this distributor",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Order not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation failed or invalid type",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function orderDetails(Request $request): JsonResponse
    {
        /** @var Member $distributor */
        $distributor = $request->user();

        if (!$this->verifyDistributor($distributor)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'order_id' => 'required',
            'type'     => 'required|string',
        ]);

        $orderId = $request->query('order_id');
        $type    = strtolower(trim($request->query('type')));

        if ($type !== 'order') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type provided. Must be: Order',
            ], 422);
        }

        $order = Order::where('distributor_id', $distributor->id)
            ->where(function ($query) use ($orderId) {
                $query->where('order_number', $orderId)
                    ->orWhere('id', $orderId);
            })
            ->with(['member', 'items', 'delivery', 'invoice'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $items = $order->items->map(fn ($item) => [
            'id'    => $item->id,
            'name'  => $item->name,
            'qty'   => $item->qty,
            'price' => $item->price,
            'total' => $item->qty * $item->price,
        ]);

        $delivery = null;
        if ($order->delivery) {
            $delivery = [
                'id'                   => $order->delivery->id,
                'vehicle_no'           => $order->delivery->vehicle_no,
                'vehicle_type'         => $order->delivery->vehicle_type,
                'driver_phone'         => $order->delivery->driver_phone,
                'expected_delivery_at' => $order->delivery->expected_delivery_at,
                'remarks'              => $order->delivery->remarks,
                'status'               => $order->delivery->status,
            ];
        }

        $invoice = null;
        if ($order->invoice) {
            $invoice = [
                'id'             => $order->invoice->id,
                'invoice_number' => $order->invoice->invoice_number,
                'amount'         => $order->invoice->amount,
                'file_path'      => $order->invoice->file_path
                    ? asset('uploads/' . $order->invoice->file_path)
                    : null,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $order->id,
                'order_id'     => $order->order_number,
                'date'         => $order->created_at->format('d M Y'),
                'status'       => $order->status,
                'type'         => 'Order',
                'description'  => $order->description,
                'amount'       => (float) $order->amount,
                'challan_file' => $order->challan_file
                    ? asset('uploads/' . $order->challan_file)
                    : null,
                'invoice_file' => $order->invoice_file
                    ? asset('uploads/' . $order->invoice_file)
                    : null,
                'received_at'  => $order->received_at,
                'created_at'   => $order->created_at,
                'dealer'       => [
                    'id'     => $order->member->id,
                    'name'   => $order->member->name,
                    'shop'   => $order->member->shop,
                    'mobile' => $order->member->mobile,
                    'email'  => $order->member->email,
                ],
                'items'    => $items,
                'delivery' => $delivery,
                'invoice'  => $invoice,
            ],
        ]);
    }

    #[OA\Post(
        path: "/distributor/order/{id}/delivery",
        summary: "Submit delivery details for an assigned order",
        description: "Allows the authenticated distributor to submit vehicle, driver, and schedule details for an order assigned to them. This sets the order status to 'Out for Delivery' and notifies the dealer via push notification.",
        security: [["bearerAuth" => []]],
        tags: ["Distributor"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Order ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["vehicle_no", "vehicle_type", "driver_phone", "expected_delivery_date", "expected_delivery_time"],
                    properties: [
                        new OA\Property(property: "vehicle_no",              type: "string",  example: "AR-01-XXXX"),
                        new OA\Property(property: "vehicle_type",            type: "string",  example: "Truck"),
                        new OA\Property(property: "driver_phone",            type: "string",  example: "9876543210"),
                        new OA\Property(property: "expected_delivery_date",  type: "string",  format: "date",  example: "2026-05-28"),
                        new OA\Property(property: "expected_delivery_time",  type: "string",  example: "09:44"),
                        new OA\Property(property: "delivery_remarks",        type: "string",  nullable: true, example: "Handle with care"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Delivery details submitted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string",  example: "Delivery details submitted successfully."),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "order_id",             type: "integer"),
                            new OA\Property(property: "order_number",         type: "string"),
                            new OA\Property(property: "order_status",         type: "string", example: "Out for Delivery"),
                            new OA\Property(property: "vehicle_no",           type: "string"),
                            new OA\Property(property: "vehicle_type",         type: "string"),
                            new OA\Property(property: "driver_phone",         type: "string"),
                            new OA\Property(property: "expected_delivery_at", type: "string", format: "date-time"),
                            new OA\Property(property: "delivery_remarks",     type: "string", nullable: true),
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "Unauthorized.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Order not found or not assigned to this distributor",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "Order not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation failed",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "Validation failed."),
                        new OA\Property(property: "errors",  type: "object")
                    ]
                )
            )
        ]
    )]
    public function updateDelivery(Request $request, $id): JsonResponse
    {
        /** @var Member $distributor */
        $distributor = $request->user();

        if (!$this->verifyDistributor($distributor)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Ensure the order is assigned to this distributor
        $order = Order::where('distributor_id', $distributor->id)->with('member')->find($id);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not assigned to you.',
            ], 404);
        }

        $request->validate([
            'vehicle_no'             => 'required|string|max:50',
            'vehicle_type'           => 'required|string|max:50',
            'driver_phone'           => 'required|string|max:20',
            'expected_delivery_date' => 'required|date',
            'expected_delivery_time' => 'required|string',
            'delivery_remarks'       => 'nullable|string|max:1000',
        ]);

        // Normalise time to HH:MM:SS
        $time        = date('H:i', strtotime($request->expected_delivery_time));
        $expectedAt  = $request->expected_delivery_date . ' ' . $time . ':00';

        $delivery = Delivery::updateOrCreate(
            ['order_id' => $order->id],
            [
                'vehicle_no'           => $request->vehicle_no,
                'vehicle_type'         => $request->vehicle_type,
                'driver_phone'         => $request->driver_phone,
                'expected_delivery_at' => $expectedAt,
                'remarks'              => $request->delivery_remarks,
                'status'               => 'Out for Delivery',
            ]
        );

        // Update the order status
        $order->update(['status' => 'Out for Delivery']);

        // Notify the dealer via push notification
        if ($order->member) {
            FcmService::sendPushNotification(
                $order->member,
                'Order Out for Delivery',
                "Your order {$order->order_number} is out for delivery! Vehicle: {$request->vehicle_no}.",
                [
                    'type'         => 'order',
                    'id'           => $order->id,
                    'order_number' => $order->order_number,
                    'status'       => 'Out for Delivery',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery details submitted successfully.',
            'data'    => [
                'order_id'             => $order->id,
                'order_number'         => $order->order_number,
                'order_status'         => $order->status,
                'vehicle_no'           => $delivery->vehicle_no,
                'vehicle_type'         => $delivery->vehicle_type,
                'driver_phone'         => $delivery->driver_phone,
                'expected_delivery_at' => $delivery->expected_delivery_at,
                'delivery_remarks'     => $delivery->remarks,
            ],
        ], 200);
    }

    public function submitEstimate(Request $request): JsonResponse
    {
        /** @var Member $distributor */
        $distributor = $request->user();

        if (!$this->verifyDistributor($distributor)) {
            return response()->json([
                'success' => false,
                'message' => 'Only distributors can submit estimate requests.',
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

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $filePaths[] = $file->store('estimates/photos', 'public');
                }
            }
        }

        $nextId = (Estimate::max('id') ?? 0) + 1;
        $requestNumber = 'EST-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $estimate = Estimate::create([
            'member_id' => $distributor->id,
            'request_number' => $requestNumber,
            'type' => ucfirst(strtolower($request->type)),
            'description' => $request->description,
            'file_path' => $filePaths,
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

    public function placeOrderRequest(Request $request): JsonResponse
    {
        /** @var Member $distributor */
        $distributor = $request->user();

        if (!$this->verifyDistributor($distributor)) {
            return response()->json([
                'success' => false,
                'message' => 'Only distributors can place order requests.',
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

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $filePaths[] = $file->store('order-requests/photos', 'public');
                }
            }
        }

        $requestNumber = 'ORD-' . now()->format('Ymd') . '-' . str_pad(
            (OrderRequest::max('id') ?? 0) + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        $orderRequest = OrderRequest::create([
            'member_id' => $distributor->id,
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
}
