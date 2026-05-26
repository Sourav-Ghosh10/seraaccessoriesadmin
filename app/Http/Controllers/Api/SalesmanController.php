<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\Member;
use App\Models\RewardTransaction;
use App\Models\OrderRequest;
use App\Models\Estimate;
use OpenApi\Attributes as OA;

class SalesmanController extends Controller
{
    /**
     * Helper to verify if the authenticated member has a 'salesman' role.
     */
    protected function verifySalesman(Member $member): bool
    {
        return strtolower($member->role) === 'salesman';
    }

    #[OA\Get(
        path: "/salesman/my-dealers",
        summary: "Get assigned dealers",
        description: "Fetches a paginated list of all dealers assigned to the authenticated salesman. Supports searching by name, shop name, email, or mobile number.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman"],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Search by dealer name, shop, email, or mobile",
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
                description: "Dealers fetched successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "shop", type: "string"),
                                new OA\Property(property: "email", type: "string"),
                                new OA\Property(property: "mobile", type: "string"),
                                new OA\Property(property: "address", type: "string", nullable: true),
                                new OA\Property(property: "status", type: "string"),
                                new OA\Property(property: "points_balance", type: "integer"),
                                new OA\Property(property: "total_orders", type: "integer"),
                                new OA\Property(property: "created_at", type: "string", format: "date-time")
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
    public function myDealers(Request $request): JsonResponse
    {
        /** @var Member $salesman */
        $salesman = $request->user();

        if (!$this->verifySalesman($salesman)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $dealersQuery = Member::where('salesman_id', $salesman->id)
            ->where('role', 'dealer')
            ->withCount('orders')
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('shop', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc');

        $dealers = $dealersQuery->paginate($perPage);

        $data = collect($dealers->items())->map(function ($dealer) {
            return [
                'id' => $dealer->id,
                'name' => $dealer->name,
                'shop' => $dealer->shop,
                'email' => $dealer->email,
                'mobile' => $dealer->mobile,
                'address' => $dealer->address,
                'status' => $dealer->status,
                'points_balance' => (int) $dealer->points_balance,
                'total_orders' => (int) $dealer->orders_count,
                'created_at' => $dealer->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $dealers->currentPage(),
                'last_page' => $dealers->lastPage(),
                'per_page' => $dealers->perPage(),
                'total' => $dealers->total(),
            ]
        ], 200);
    }

    #[OA\Get(
        path: "/salesman/my-orders",
        summary: "Get order history of assigned dealers",
        description: "Fetches a unified, paginated list of Estimates, Order Requests, and Orders placed by the dealers assigned to the authenticated salesman.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman"],
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
                description: "Search by ID, Date, or Dealer Name",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Number of records per page",
                required: false,
                schema: new OA\Schema(type: "integer", default: 10)
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
        /** @var Member $salesman */
        $salesman = $request->user();

        if (!$this->verifySalesman($salesman)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $tab = $request->query('tab', 'All'); // All, Pending, Confirmed, Order Placed
        $search = $request->query('search');

        // Fetch assigned dealer IDs
        $dealerIds = Member::where('salesman_id', $salesman->id)
            ->where('role', 'dealer')
            ->pluck('id');

        $merged = collect();

        // 1. Estimates
        if (in_array($tab, ['All', 'Pending'])) {
            $estimates = Estimate::whereIn('member_id', $dealerIds)
                ->with('member')
                ->when($search, function ($query) use ($search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('request_number', 'like', "%$search%")
                            ->orWhere('created_at', 'like', "%$search%")
                            ->orWhereHas('member', function ($mq) use ($search) {
                                $mq->where('name', 'like', "%$search%")
                                   ->orWhere('shop', 'like', "%$search%");
                            });
                    });
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
                        'dealer' => [
                            'id' => $item->member->id ?? null,
                            'name' => $item->member->name ?? null,
                            'shop' => $item->member->shop ?? null,
                            'mobile' => $item->member->mobile ?? null,
                            'email' => $item->member->email ?? null,
                        ],
                    ];
                });
            $merged = $merged->concat($estimates);
        }

        // 2. Order Requests
        if (in_array($tab, ['All', 'Pending', 'Confirmed'])) {
            $orderRequests = OrderRequest::whereIn('member_id', $dealerIds)
                ->with('member')
                ->when($tab === 'Pending', function ($query) {
                    return $query->where('status', 'Pending');
                })
                ->when($tab === 'Confirmed', function ($query) {
                    return $query->where('status', 'Processed');
                })
                ->when($search, function ($query) use ($search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('request_number', 'like', "%$search%")
                            ->orWhere('created_at', 'like', "%$search%")
                            ->orWhereHas('member', function ($mq) use ($search) {
                                $mq->where('name', 'like', "%$search%")
                                   ->orWhere('shop', 'like', "%$search%");
                            });
                    });
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
                        'dealer' => [
                            'id' => $item->member->id ?? null,
                            'name' => $item->member->name ?? null,
                            'shop' => $item->member->shop ?? null,
                            'mobile' => $item->member->mobile ?? null,
                            'email' => $item->member->email ?? null,
                        ],
                    ];
                });
            $merged = $merged->concat($orderRequests);
        }

        // 3. Orders
        if (in_array($tab, ['All', 'Order Placed'])) {
            $orders = Order::whereIn('member_id', $dealerIds)
                ->with('member')
                ->when($search, function ($query) use ($search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('order_number', 'like', "%$search%")
                            ->orWhere('created_at', 'like', "%$search%")
                            ->orWhereHas('member', function ($mq) use ($search) {
                                $mq->where('name', 'like', "%$search%")
                                   ->orWhere('shop', 'like', "%$search%");
                            });
                    });
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
                        'dealer' => [
                            'id' => $item->member->id ?? null,
                            'name' => $item->member->name ?? null,
                            'shop' => $item->member->shop ?? null,
                            'mobile' => $item->member->mobile ?? null,
                            'email' => $item->member->email ?? null,
                        ],
                    ];
                });
            $merged = $merged->concat($orders);
        }

        // Sort by date DESC
        $sorted = $merged->sortByDesc('raw_date')->values();

        // Manual Pagination
        $perPage = (int) $request->query('per_page', 10);
        $page = (int) $request->query('page', 1);
        $paginatedData = $sorted->forPage($page, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
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
        path: "/salesman/my-points",
        summary: "Get salesman points balance & history",
        description: "Fetches the authenticated salesman's current reward points balance and a paginated list of point transactions.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman"],
        parameters: [
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
                description: "Points data fetched successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "total_points", type: "integer"),
                            new OA\Property(property: "history", type: "array", items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "title", type: "string"),
                                    new OA\Property(property: "points", type: "string"),
                                    new OA\Property(property: "date", type: "string"),
                                    new OA\Property(property: "type", type: "string"),
                                    new OA\Property(property: "raw_points", type: "integer")
                                ]
                            )),
                            new OA\Property(property: "meta", type: "object", properties: [
                                new OA\Property(property: "current_page", type: "integer"),
                                new OA\Property(property: "last_page", type: "integer"),
                                new OA\Property(property: "per_page", type: "integer"),
                                new OA\Property(property: "total", type: "integer")
                            ])
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
    public function myPoints(Request $request): JsonResponse
    {
        /** @var Member $salesman */
        $salesman = $request->user();

        if (!$this->verifySalesman($salesman)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $totalPoints = (int) $salesman->points_balance;
        $perPage = (int) $request->query('per_page', 15);

        $transactions = $salesman->rewardTransactions()
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

        return response()->json([
            'success' => true,
            'data' => [
                'total_points' => $totalPoints,
                'history' => $history,
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ]
        ], 200);
    }

    #[OA\Get(
        path: "/salesman/dealer/passbook",
        summary: "Get assigned dealer's passbook and payment history",
        description: "Allows the authenticated salesman to fetch the total billed, paid, due, and transaction history for an assigned dealer.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman"],
        parameters: [
            new OA\Parameter(
                name: "dealer_id",
                in: "query",
                description: "The ID of the dealer to retrieve the passbook for",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
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
                                new OA\Property(property: "total_amount", type: "number", format: "float"),
                                new OA\Property(property: "paid_amount", type: "number", format: "float"),
                                new OA\Property(property: "due_amount", type: "number", format: "float"),
                                new OA\Property(property: "history", type: "array", items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: "id", type: "integer"),
                                        new OA\Property(property: "date", type: "string"),
                                        new OA\Property(property: "amount", type: "string"),
                                        new OA\Property(property: "raw_amount", type: "number", format: "float"),
                                        new OA\Property(property: "type", type: "string"),
                                        new OA\Property(property: "ref", type: "string"),
                                        new OA\Property(property: "status", type: "string"),
                                        new OA\Property(property: "managed_by", type: "string")
                                    ]
                                )),
                                new OA\Property(property: "meta", type: "object")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Unauthorized or not assigned to this salesman",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Dealer not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Dealer not found.")
                    ]
                )
            )
        ]
    )]
    public function dealerPassbook(Request $request): JsonResponse
    {
        /** @var Member $salesman */
        $salesman = $request->user();

        if (!$this->verifySalesman($salesman)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'dealer_id' => 'required|exists:members,id',
        ]);

        $dealerId = $request->input('dealer_id');

        // Ensure the dealer is assigned to this salesman
        $dealer = Member::where('id', $dealerId)
            ->where('salesman_id', $salesman->id)
            ->where('role', 'dealer')
            ->first();

        if (!$dealer) {
            return response()->json([
                'success' => false,
                'message' => 'Dealer not found or not assigned to you.',
            ], 404);
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
        ], 200);
    }

    // =========================================================================
    // GET /salesman/my-orders/details
    // Salesman only. Fetch full details of an Order, Order Request, or Estimate
    // belonging to a dealer assigned to this salesman.
    // =========================================================================
    #[OA\Get(
        path: "/salesman/my-orders/details",
        summary: "Get order details for an assigned dealer's order",
        description: "Fetches detailed information for a specific Order, Order Request, or Estimate that belongs to a dealer assigned to the authenticated salesman.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman"],
        parameters: [
            new OA\Parameter(
                name: "order_id",
                in: "query",
                description: "The order number or numeric ID of the record",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                description: "Type of record: Order, Order Request, or Estimate",
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
                description: "Record not found or not accessible",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Order not found.")
                    ]
                )
            )
        ]
    )]
    public function orderDetails(Request $request): JsonResponse
    {
        /** @var Member $salesman */
        $salesman = $request->user();

        if (!$this->verifySalesman($salesman)) {
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

        // IDs of dealers assigned to this salesman
        $dealerIds = Member::where('salesman_id', $salesman->id)
            ->where('role', 'dealer')
            ->pluck('id');

        // ── Order ─────────────────────────────────────────────────────────────
        if ($type === 'order') {
            $order = Order::whereIn('member_id', $dealerIds)
                ->where(function ($q) use ($orderId) {
                    $q->where('order_number', $orderId)
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

        // ── Order Request ──────────────────────────────────────────────────────
        if ($type === 'order request' || $type === 'order_request') {
            $orderRequest = OrderRequest::whereIn('member_id', $dealerIds)
                ->where(function ($q) use ($orderId) {
                    $q->where('request_number', $orderId)
                      ->orWhere('id', $orderId);
                })
                ->with('member')
                ->first();

            if (!$orderRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order request not found.',
                ], 404);
            }

            $files = [];
            if ($orderRequest->file_path) {
                $paths = is_array($orderRequest->file_path)
                    ? $orderRequest->file_path
                    : json_decode($orderRequest->file_path, true);
                foreach ((array) $paths as $path) {
                    $files[] = asset('uploads/' . $path);
                }
            }

            $displayStatus = ($orderRequest->status === 'Processed') ? 'Confirmed' : $orderRequest->status;

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'              => $orderRequest->id,
                    'order_id'        => $orderRequest->request_number,
                    'date'            => $orderRequest->created_at->format('d M Y'),
                    'status'          => ucfirst($displayStatus),
                    'type'            => 'Order Request',
                    'submission_type' => $orderRequest->type,
                    'description'     => $orderRequest->description,
                    'files'           => $files,
                    'created_at'      => $orderRequest->created_at,
                    'dealer'          => [
                        'id'     => $orderRequest->member->id,
                        'name'   => $orderRequest->member->name,
                        'shop'   => $orderRequest->member->shop,
                        'mobile' => $orderRequest->member->mobile,
                        'email'  => $orderRequest->member->email,
                    ],
                ],
            ]);
        }

        // ── Estimate ──────────────────────────────────────────────────────────
        if ($type === 'estimate') {
            $estimate = Estimate::whereIn('member_id', $dealerIds)
                ->where(function ($q) use ($orderId) {
                    $q->where('request_number', $orderId)
                      ->orWhere('id', $orderId);
                })
                ->with('member')
                ->first();

            if (!$estimate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estimate not found.',
                ], 404);
            }

            $files = [];
            if ($estimate->file_path) {
                $paths = is_array($estimate->file_path)
                    ? $estimate->file_path
                    : json_decode($estimate->file_path, true);
                foreach ((array) $paths as $path) {
                    $files[] = asset('uploads/' . $path);
                }
            }

            $reqNo         = $estimate->request_number ?? 'EST-' . str_pad($estimate->id, 4, '0', STR_PAD_LEFT);
            $revertDetails = null;
            if ($estimate->response_description || $estimate->response_file_path) {
                $revertDetails = [
                    'response_description' => $estimate->response_description,
                    'response_file_path'   => $estimate->response_file_path
                        ? asset('uploads/' . $estimate->response_file_path)
                        : null,
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'              => $estimate->id,
                    'order_id'        => $reqNo,
                    'date'            => $estimate->created_at->format('d M Y'),
                    'status'          => $estimate->status,
                    'type'            => 'Estimate',
                    'submission_type' => $estimate->type,
                    'description'     => $estimate->description,
                    'files'           => $files,
                    'revert_details'  => $revertDetails,
                    'created_at'      => $estimate->created_at,
                    'dealer'          => [
                        'id'     => $estimate->member->id,
                        'name'   => $estimate->member->name,
                        'shop'   => $estimate->member->shop,
                        'mobile' => $estimate->member->mobile,
                        'email'  => $estimate->member->email,
                    ],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid type. Use Order, Order Request, or Estimate.',
        ], 422);
    }
}
