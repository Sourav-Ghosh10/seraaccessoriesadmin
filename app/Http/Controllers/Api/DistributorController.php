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
use App\Models\RedeemRequest;
use App\Services\FcmService;
use OpenApi\Attributes as OA;

class DistributorController extends Controller
{
    /**
     * Helper to verify if the authenticated member has a 'distributor' role.
     */
    protected function verifyDistributor(Member $member): bool
    {
        return in_array(strtolower($member->role), ['distributor', 'distributor_staff', 'staff']);
    }

    protected function getDistributorId(Member $member): ?int
    {
        if (strtolower($member->role) === 'distributor') {
            return $member->id;
        }
        if (in_array(strtolower($member->role), ['distributor_staff', 'staff']) && $member->dist_id) {
            $dist = Member::where('dist_id', $member->dist_id)->where('role', 'distributor')->first();
            return $dist ? $dist->id : null;
        }
        return $member->id;
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

        // Distributor users only see fully confirmed Orders from their dealers.
        // Estimate requests (EST-*) and Order requests (ORD-date-*) are NOT shown.
        $tab = $request->query('tab', $request->query('status', 'All'));
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $distributorId = $this->getDistributorId($distributor);
        $dealerIds = Member::where(function ($q) use ($distributor, $distributorId) {
            $q->where('dist_id', $distributor->dist_id ?: $distributorId)
              ->orWhere('dist_id', $distributorId);
        })
        ->where('id', '!=', $distributorId)
        ->pluck('id');

        // Only show orders that have been confirmed or are in a later stage
        $confirmedStatuses = ['Confirmed', 'Out for Delivery', 'Dispatched', 'Delivered', 'Returned', 'Invoiced'];

        $orders = Order::where(function ($q) use ($distributorId, $dealerIds) {
            $q->where('distributor_id', $distributorId)
              ->orWhere(function ($sq) use ($dealerIds) {
                  $sq->whereIn('member_id', $dealerIds)
                     ->whereNull('distributor_id');
              });
        })
        ->where('member_id', '!=', $distributorId)
        ->whereIn('status', $confirmedStatuses)
        ->with(['member', 'delivery', 'invoice', 'items', 'creditNote'])
        ->when($tab && $tab !== 'All', function ($query) use ($tab) {
            $statusLower = strtolower(trim($tab));
            if (in_array($statusLower, ['not yet dispatch', 'not yet dispatched', 'to dispatch'])) {
                return $query->whereDoesntHave('delivery');
            }
            if ($statusLower === 'dispatched') {
                return $query->whereHas('delivery');
            }
            if ($tab === 'Delivered') {
                return $query->where(function ($q) {
                    $q->where('status', 'Delivered')
                      ->orWhere(function ($sq) {
                          $sq->where('status', 'Invoiced')
                             ->whereNotNull('received_at');
                      });
                });
            }
            if ($tab === 'Returned') {
                return $query->where('status', 'Returned');
            }
            return $query->where('status', $tab);
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
        ->get()
        ->map(function ($order) {
            $delivery = null;
            if ($order->delivery && !empty($order->delivery->status)) {
                $delivery = [
                    'vehicle_no' => $order->delivery->vehicle_no,
                    'vehicle_type' => $order->delivery->vehicle_type,
                    'driver_phone' => $order->delivery->driver_phone,
                    'expected_delivery_at' => $order->delivery->expected_delivery_at,
                    'actual_delivery_at' => $order->received_at ? $order->received_at->format('Y-m-d H:i:s') : ($order->status === 'Delivered' ? $order->updated_at->format('Y-m-d H:i:s') : null),
                    'received_at' => $order->received_at ? $order->received_at->format('Y-m-d H:i:s') : null,
                    'remarks' => $order->delivery->remarks,
                    'status' => $order->delivery->status,
                    'document_url' => $order->delivery->document_path ? asset('uploads/' . $order->delivery->document_path) : null,
                    'document_path' => $order->delivery->document_path,
                ];
            }

            $calculatedAmount = $order->items->sum(function($i) { return $i->qty * $i->price; });
            $finalAmount = $order->amount > 0 ? $order->amount : ($order->invoice && $order->invoice->amount > 0 ? $order->invoice->amount : $calculatedAmount);

            $cnPath = null;
            if ($order->creditNote) {
                $cnPath = $order->creditNote->distributor_file_path;
                if (!$cnPath && $order->creditNote->file_path && strpos($order->creditNote->file_path, 'dealer') === false && $order->creditNote->file_path !== $order->creditNote->dealer_file_path) {
                    $cnPath = $order->creditNote->file_path;
                }
            }
            $creditNoteFile = $cnPath ? asset('uploads/' . $cnPath) : null;

            return [
                'id' => $order->id,
                'order_id' => $order->order_number,
                'order_number' => $order->order_number,
                'request_number' => $order->order_number,
                'amount' => (float) $finalAmount,
                'status' => $order->status ?: 'Confirmed',
                'type' => 'Order',
                'date' => $order->created_at->format('d M Y'),
                'received_at' => $order->received_at ? $order->received_at->format('Y-m-d H:i:s') : null,
                'created_at' => $order->created_at,
                'raw_date' => $order->created_at,
                'dealer' => [
                    'id' => $order->member->id ?? null,
                    'name' => $order->member->name ?? null,
                    'shop' => $order->member->shop ?? null,
                    'mobile' => $order->member->mobile ?? null,
                    'email' => $order->member->email ?? null,
                ],
                'delivery' => $delivery,
                'invoice_number' => $order->invoice ? $order->invoice->invoice_number : null,
                'challan_file' => $order->challan_file ? asset('uploads/' . $order->challan_file) : null,
                'invoice_file' => $order->invoice_file ? asset('uploads/' . $order->invoice_file) : ($order->invoice && $order->invoice->file_path ? asset('uploads/' . $order->invoice->file_path) : null),
                'credit_note_file' => $creditNoteFile,
                'credit_note_url' => $creditNoteFile,
                'credit_note' => $order->creditNote ? [
                    'id' => $order->creditNote->id,
                    'credit_note_number' => $order->creditNote->credit_note_number,
                    'file_path' => $creditNoteFile,
                ] : null,
                'has_invoice' => ($order->invoice_file || $order->challan_file || ($order->invoice && $order->invoice->file_path)) ? true : false,
            ];
        });

        $sorted = $orders->sortByDesc('raw_date')->values();
        $paginatedData = $sorted->forPage($page, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'data' => array_values($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
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
        ]);

        $orderId = $request->query('order_id');
        $type    = strtolower(trim($request->query('type', 'order')));
        if ($type === 'undefined' || empty($type)) {
            $type = 'order';
        }

        if ($type !== 'order') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type provided. Must be: Order',
            ], 422);
        }

        $distributorId = $this->getDistributorId($distributor);
        $dealerIds = Member::where(function ($q) use ($distributor, $distributorId) {
            $q->where('dist_id', $distributor->dist_id ?: $distributorId)
              ->orWhere('dist_id', $distributorId);
        })
        ->where('id', '!=', $distributorId)
        ->pluck('id');

        $num = intval(preg_replace('/^.*-(\d+)$/', '$1', $orderId));
        $order = Order::where(function ($q) use ($distributorId, $dealerIds) {
            $q->where('distributor_id', $distributorId)
              ->orWhere(function ($sq) use ($dealerIds) {
                  $sq->whereIn('member_id', $dealerIds)
                     ->whereNull('distributor_id');
              });
        })
            ->where(function ($query) use ($orderId, $num) {
                $query->where('order_number', $orderId)
                    ->orWhere('id', $orderId)
                    ->orWhere('id', $num)
                    ->orWhere('order_number', 'ORD-' . str_pad($num, 4, '0', STR_PAD_LEFT));
            })
            ->with(['member', 'items', 'delivery', 'invoice', 'creditNote'])
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
        if ($order->delivery && !empty(trim($order->delivery->status ?? ''))) {
            $delivery = [
                'id'                   => $order->delivery->id,
                'vehicle_no'           => $order->delivery->vehicle_no,
                'vehicle_type'         => $order->delivery->vehicle_type,
                'driver_phone'         => $order->delivery->driver_phone,
                'expected_delivery_at' => $order->delivery->expected_delivery_at,
                'actual_delivery_at'   => $order->received_at ? $order->received_at->format('Y-m-d H:i:s') : ($order->status === 'Delivered' ? $order->updated_at->format('Y-m-d H:i:s') : null),
                'received_at'          => $order->received_at ? $order->received_at->format('Y-m-d H:i:s') : null,
                'remarks'              => $order->delivery->remarks,
                'status'               => $order->delivery->status,
                'document_url'         => $order->delivery->document_path ? asset('uploads/' . $order->delivery->document_path) : null,
                'document_path'        => $order->delivery->document_path,
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

        $cnPath = null;
        if ($order->creditNote) {
            $cnPath = $order->creditNote->distributor_file_path;
            if (!$cnPath && $order->creditNote->file_path && strpos($order->creditNote->file_path, 'dealer') === false && $order->creditNote->file_path !== $order->creditNote->dealer_file_path) {
                $cnPath = $order->creditNote->file_path;
            }
        }
        $creditNoteFile = $cnPath ? asset('uploads/' . $cnPath) : null;

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
                    : ($order->invoice && $order->invoice->file_path ? asset('uploads/' . $order->invoice->file_path) : null),
                'credit_note_file' => $creditNoteFile,
                'credit_note_url'  => $creditNoteFile,
                'credit_note' => $order->creditNote ? [
                    'id' => $order->creditNote->id,
                    'credit_note_number' => $order->creditNote->credit_note_number,
                    'file_path' => $creditNoteFile,
                ] : null,
                'received_at'  => $order->received_at ? $order->received_at->format('Y-m-d H:i:s') : null,
                'created_at'   => $order->created_at,
                'dealer'       => [
                    'id'     => $order->member->id,
                    'name'   => $order->member->name,
                    'shop'   => $order->member->shop,
                    'mobile' => $order->member->mobile,
                    'email'  => $order->member->email,
                    'role'   => $order->member->role,
                ],
                'is_assigned_dispatcher' => ($order->distributor_id === $distributorId),
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
        $distributorId = $this->getDistributorId($distributor);
        $num = intval(preg_replace('/^.*-(\d+)$/', '$1', $id));
        $order = Order::where('distributor_id', $distributorId)
            ->where(function($q) use ($id, $num) {
                $q->where('id', $id)
                  ->orWhere('id', $num)
                  ->orWhere('order_number', $id)
                  ->orWhere('order_number', 'ORD-' . str_pad($num, 4, '0', STR_PAD_LEFT));
            })
            ->with('member')
            ->first();
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not assigned to you.',
            ], 404);
        }

        if (in_array(strtolower($order->status), ['delivered', 'returned'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update delivery details for an order that is already delivered.',
            ], 403);
        }

        $request->validate([
            'vehicle_no'             => 'required|string|max:50',
            'vehicle_type'           => 'required|string|max:50',
            'driver_phone'           => 'required|string|max:20',
            'expected_delivery_date' => 'required|date',
            'expected_delivery_time' => 'required|string',
            'delivery_remarks'       => 'required|string|max:1000',
            'dispatch_documents'     => 'required_without:dispatch_document|array|max:3',
            'dispatch_documents.*'   => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'dispatch_document'      => 'required_without:dispatch_documents|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Normalise time to HH:MM:SS
        $time        = date('H:i', strtotime($request->expected_delivery_time));
        $expectedAt  = $request->expected_delivery_date . ' ' . $time . ':00';

        // Handle dispatch document upload
        $documentPaths = "";
        if ($request->hasFile('dispatch_documents')) {
            foreach ($request->file('dispatch_documents') as $file) {
                if ($file->isValid()) {
                    $documentPaths = $file->store('deliveries/documents', 'public');
                }
            }
        } elseif ($request->hasFile('dispatch_document') && $request->file('dispatch_document')->isValid()) {
            $documentPaths = $request->file('dispatch_document')->store('deliveries/documents', 'public');
        } elseif ($request->hasFile('upload_documents') && $request->file('upload_documents')->isValid()) {
            $documentPaths = $request->file('upload_documents')->store('deliveries/documents', 'public');
        }

        $documentPath = !empty($documentPaths) ? $documentPaths : null;

        $deliveryData = [
            'vehicle_no'           => $request->vehicle_no,
            'vehicle_type'         => $request->vehicle_type,
            'driver_phone'         => $request->driver_phone,
            'expected_delivery_at' => $expectedAt,
            'remarks'              => $request->delivery_remarks,
            'status'               => 'Out for Delivery',
        ];
        if ($documentPath) {
            $deliveryData['document_path'] = $documentPath;
        }

        $delivery = Delivery::updateOrCreate(
            ['order_id' => $order->id],
            $deliveryData
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
                'document_url'         => $delivery->document_path ? asset('uploads/' . $delivery->document_path) : null,
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

        $distributorId = $this->getDistributorId($distributor);

        $estimate = Estimate::create([
            'member_id' => $distributorId,
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

        $distributorId = $this->getDistributorId($distributor);

        $orderRequest = OrderRequest::create([
            'member_id' => $distributorId,
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

    public function redeemRequests(Request $request): JsonResponse
    {
        /** @var Member $distributor */
        $distributor = $request->user();

        if (!$this->verifyDistributor($distributor)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $status = $request->query('status');
        $search = $request->query('search');

        $orderDealerIds = Order::where('distributor_id', $distributor->id)->pluck('member_id');
        $distDealerIds = Member::where(function ($q) {
                $q->where('role', 'dealer')
                  ->orWhere('role', 'Dealer')
                  ->orWhere('role', 'like', '%dealer%');
            })
            ->where(function ($q) use ($distributor) {
                if ($distributor->dist_id) {
                    $q->where('dist_id', $distributor->dist_id);
                }
                $q->orWhere('dist_id', (string) $distributor->id);
            })
            ->pluck('id');

        $dealerIds = $orderDealerIds->merge($distDealerIds)->unique()->filter()->values();

        $requests = RedeemRequest::whereIn('member_id', $dealerIds)
            ->with('member')
            ->when($status && $status !== 'All', function ($query) use ($status) {
                return $query->where('status', strtolower($status))
                             ->orWhere('status', ucfirst($status))
                             ->orWhere('status', $status);
            })
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('member', function ($mq) use ($search) {
                    $mq->where('name', 'like', "%{$search}%")
                       ->orWhere('shop', 'like', "%{$search}%")
                       ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'request_id' => '#RDM' . str_pad($req->id, 5, '0', STR_PAD_LEFT),
                    'req_id' => '#RDM' . str_pad($req->id, 5, '0', STR_PAD_LEFT),
                    'title' => '#RDM' . str_pad($req->id, 5, '0', STR_PAD_LEFT),
                    'dealer_name' => $req->member->name ?? 'Dealer',
                    'shop_name' => $req->member->shop ?? '',
                    'points' => (int) $req->Points,
                    'raw_points' => (int) $req->Points,
                    'status' => ucfirst($req->status ?? 'Pending'),
                    'date' => $req->created_at ? $req->created_at->format('d M Y') : 'N/A',
                    'credit_note' => $req->Credit_note ?? 'Pending',
                    'remarks' => $req->notes ?? '',
                    'note' => $req->notes ?? '',
                    'dealer_document_url' => $req->dealer_file_path ? asset('uploads/' . $req->dealer_file_path) : null,
                    'distributor_document_url' => $req->distributor_file_path ? asset('uploads/' . $req->distributor_file_path) : null,
                    'salesman_document_url' => $req->salesman_file_path ? asset('uploads/' . $req->salesman_file_path) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function updateRedeemRequestStatus(Request $request, $id): JsonResponse
    {
        /** @var Member $distributor */
        $distributor = $request->user();

        if (!$this->verifyDistributor($distributor)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $redeemRequest = RedeemRequest::findOrFail($id);

        $redeemRequest->status = $validated['status'];
        if (isset($validated['remarks'])) {
            $redeemRequest->notes = $validated['remarks'];
        }
        $redeemRequest->save();

        return response()->json([
            'success' => true,
            'message' => "Redeem request updated to {$validated['status']}.",
            'data' => $redeemRequest,
        ]);
    }

    #[OA\Post(
        path: "/distributor/update-fcm-token",
        summary: "Update FCM Push Token",
        description: "Registers or updates the FCM token for the distributor's device.",
        security: [["bearerAuth" => []]],
        tags: ["Distributor"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["fcm_token"],
                properties: [
                    new OA\Property(property: "fcm_token", type: "string", description: "The FCM push token received from Firebase SDK"),
                    new OA\Property(property: "device_type", type: "string", description: "Optional. 'android' or 'ios'", example: "android")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Token updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "FCM Token registered successfully.")
                    ]
                )
            )
        ]
    )]
    public function updateFcmToken(Request $request): JsonResponse
    {
        /** @var Member $distributor */
        $distributor = $request->user();

        // Distributor-only guard
        if (strtolower($distributor->role) !== 'distributor') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        \App\Models\MemberDevice::updateOrCreate(
            ['fcm_token' => $request->fcm_token],
            ['member_id' => $distributor->id, 'device_type' => $request->device_type ?? 'android']
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM Token registered successfully.',
        ]);
    }

    #[OA\Get(
        path: "/distributor/notifications",
        summary: "Get notifications history",
        description: "Fetches a paginated history list of all stored notifications sent to the authenticated distributor.",
        security: [["bearerAuth" => []]],
        tags: ["Distributor"],
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
                        new OA\Property(property: "success", type: "boolean", example: true)
                    ]
                )
            )
        ]
    )]
    public function getNotifications(Request $request): JsonResponse
    {
        /** @var Member $distributor */
        $distributor = $request->user();

        if (strtolower($distributor->role) !== 'distributor') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $notifications = $distributor->notifications()
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
        path: "/distributor/notifications/read-all",
        summary: "Mark all notifications as read",
        description: "Marks all stored notifications for the authenticated distributor as read.",
        security: [["bearerAuth" => []]],
        tags: ["Distributor"],
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
        /** @var Member $distributor */
        $distributor = $request->user();

        if (strtolower($distributor->role) !== 'distributor') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $distributor->notifications()->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.'
        ]);
    }
}
