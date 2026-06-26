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
use App\Models\SalesmanAttendance;
use App\Models\SalesmanVisit;
use App\Models\SalesmanLocationLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
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

    /**
     * Helper to fetch address from coordinates using Nominatim API.
     */
    protected function getAddressFromCoordinates($latitude, $longitude, $default = null): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'SeraAccessoriesApp/1.0',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $latitude,
                'lon' => $longitude,
            ]);

            if ($response->successful() && $response->json('display_name')) {
                return $response->json('display_name');
            }
        } catch (\Exception $e) {
            Log::error('Nominatim API error: ' . $e->getMessage());
        }

        return $default;
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
                                new OA\Property(property: "distributor_name", type: "string", nullable: true),
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
            ->with('distributor')
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
                'distributor_name' => $dealer->distributor ? $dealer->distributor->name : null,
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

    // =========================================================================
    // PLACE ORDER REQUEST — POST /api/salesman/order-request
    // Salesman can place on behalf of dealer. Supports: Text | Voice | Photo | Call
    // =========================================================================
    #[OA\Post(
        path: "/salesman/order-request",
        summary: "Submit an order request on behalf of a dealer",
        description: "Allows a salesman to place an order request for an assigned dealer via text, voice, photo, or call.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["type", "dealer_id"],
                    properties: [
                        new OA\Property(property: "dealer_id", type: "integer", description: "ID of the dealer"),
                        new OA\Property(
                            property: "type",
                            type: "string",
                            enum: ["Text", "Voice", "Photo", "Call"],
                            description: "Submission type."
                        ),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "file", type: "string", format: "binary"),
                        new OA\Property(property: "files[]", type: "array", items: new OA\Items(type: "string", format: "binary")),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Order request submitted successfully")
        ]
    )]
    public function placeOrderRequest(Request $request): JsonResponse
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
            'dealer_id' => 'required|integer|exists:members,id',
            'type' => 'required|string|in:Text,Voice,Photo,Call,Document,Pdf,text,voice,photo,call,document,pdf',
            'description' => 'required_if:type,Text,text|nullable|string|max:2000',
            'file' => 'required_if:type,Voice,voice,Document,document,Pdf,pdf|nullable|file|max:20480',
            'files' => 'required_if:type,Photo,photo|nullable|array',
            'files.*' => 'file|max:20480',
        ]);

        $dealerId = $request->dealer_id;

        // Verify dealer belongs to salesman
        $isAssigned = Member::where('id', $dealerId)
            ->where('role', 'dealer')
            ->where('salesman_id', $salesman->id)
            ->exists();

        if (!$isAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'Dealer is not assigned to you.',
            ], 403);
        }

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
            'member_id' => $dealerId,
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
        $dealerIdParam = $request->query('dealer_id');

        // Fetch assigned dealer IDs
        $dealerIds = Member::where('salesman_id', $salesman->id)
            ->where('role', 'dealer')
            ->pluck('id');
            
        if ($dealerIdParam) {
            $dealerIds = $dealerIds->filter(fn($id) => $id == $dealerIdParam);
        }

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
        $lockedPoints = (int) $salesman->rewardTransactions()->where('count_days', '>', 0)->sum('points');
        $redeemedPoints = (int) \App\Models\RedeemRequest::where('member_id', $salesman->id)->whereIn('status', ['Pending', 'Approved', 'Processed'])->sum('Points');
        $redeemablePoints = max(0, $totalPoints - $lockedPoints - $redeemedPoints);
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

        $redeemRequests = \App\Models\RedeemRequest::where('member_id', $salesman->id)
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
        ], 200);
    }

    #[OA\Post(
        path: "/salesman/redeem-request",
        summary: "Submit a points redeem request",
        description: "Stores a redeem request for the authenticated salesman.",
        security: [["bearerAuth" => []]]
    )]
    public function submitRedeemRequest(Request $request): JsonResponse
    {
        $salesman = $request->user();

        if (!$this->verifySalesman($salesman)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $points = (int) $request->input('points', $request->input('Points', 0));
        $notes = $request->input('notes', $request->input('note', $request->input('remarks', '')));

        if ($points <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid points amount to redeem.',
            ], 422);
        }

        $totalPoints = (int) $salesman->points_balance;
        $lockedPoints = (int) $salesman->rewardTransactions()->where('count_days', '>', 0)->sum('points');
        $redeemedPoints = (int) \App\Models\RedeemRequest::where('member_id', $salesman->id)->whereIn('status', ['Pending', 'Approved', 'Processed'])->sum('Points');
        $redeemablePoints = max(0, $totalPoints - $lockedPoints - $redeemedPoints);

        if ($points > $redeemablePoints) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have enough redeemable points.',
            ], 422);
        }

        \App\Models\RedeemRequest::create([
            'member_id' => $salesman->id,
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

        if (!$dealer->is_passbook_visible) {
            return response()->json([
                'success' => false,
                'message' => 'Dealer passbook is currently hidden by admin.',
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

    // =========================================================================
    // GET /salesman/attendance-status
    // =========================================================================
    #[OA\Get(
        path: "/salesman/attendance-status",
        summary: "Check attendance status",
        description: "Check if the salesman is already clocked in today, so the timer can resume.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Attendance"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "is_clocked_in", type: "boolean"),
                        new OA\Property(property: "clock_in_time", type: "string", nullable: true),
                        new OA\Property(property: "today_total_hours", type: "string", nullable: true)
                    ])
                ])
            )
        ]
    )]
    public function attendanceStatus(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $today = Carbon::today()->format('Y-m-d');
        $attendance = SalesmanAttendance::where('member_id', $salesman->id)
            ->where('date', $today)
            ->first();

        $isClockedIn = false;
        $clockInTime = null;
        $todayTotalHours = '00:00';

        if ($attendance) {
            $clockInTime = $attendance->clock_in_time->toIso8601String();
            if ($attendance->clock_out_time) {
                // Already clocked out
                $isClockedIn = false;
                $todayTotalHours = $attendance->total_hours;
            } else {
                // Currently clocked in
                $isClockedIn = true;
                $diffInMinutes = Carbon::now()->diffInMinutes($attendance->clock_in_time);
                $hours = floor($diffInMinutes / 60);
                $mins = $diffInMinutes % 60;
                $todayTotalHours = sprintf('%02d:%02d', $hours, $mins);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_clocked_in' => $isClockedIn,
                'clock_in_time' => $clockInTime,
                'today_total_hours' => $todayTotalHours,
            ]
        ]);
    }

    // =========================================================================
    // POST /salesman/clock-in
    // =========================================================================
    #[OA\Post(
        path: "/salesman/clock-in",
        summary: "Clock in",
        description: "Clock in for the day.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Attendance"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "latitude", type: "number"),
                new OA\Property(property: "longitude", type: "number"),
                new OA\Property(property: "address", type: "string", nullable: true)
            ])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "message", type: "string"),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "clock_in_time", type: "string")
                    ])
                ])
            )
        ]
    )]
    public function clockIn(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $today = Carbon::today()->format('Y-m-d');
        $attendance = SalesmanAttendance::where('member_id', $salesman->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Already clocked in today.'
            ], 400);
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $address = $request->input('address');

        // Reverse Geocode if address is empty or override
        $fetchedAddress = $this->getAddressFromCoordinates($lat, $lng);
        $address = $fetchedAddress ?: $address;

        $now = Carbon::now();
        $attendance = SalesmanAttendance::create([
            'member_id' => $salesman->id,
            'date' => $today,
            'clock_in_time' => $now,
            'clock_in_latitude' => $lat,
            'clock_in_longitude' => $lng,
            'clock_in_address' => $address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully',
            'data' => [
                'clock_in_time' => $now->toIso8601String(),
            ]
        ]);
    }

    // =========================================================================
    // POST /salesman/clock-out
    // =========================================================================
    #[OA\Post(
        path: "/salesman/clock-out",
        summary: "Clock out",
        description: "Clock out at the end of the day.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Attendance"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "latitude", type: "number"),
                new OA\Property(property: "longitude", type: "number"),
                new OA\Property(property: "address", type: "string", nullable: true)
            ])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "message", type: "string"),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "clock_out_time", type: "string"),
                        new OA\Property(property: "total_hours_today", type: "string")
                    ])
                ])
            )
        ]
    )]
    public function clockOut(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $today = Carbon::today()->format('Y-m-d');
        $attendance = SalesmanAttendance::where('member_id', $salesman->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'You have not clocked in today.'
            ], 400);
        }

        if ($attendance->clock_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Already clocked out today.'
            ], 400);
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $address = $request->input('address');

        $fetchedAddress = $this->getAddressFromCoordinates($lat, $lng);
        $address = $fetchedAddress ?: $address;

        $now = Carbon::now();
        $diffInMinutes = $now->diffInMinutes($attendance->clock_in_time);
        $hours = floor($diffInMinutes / 60);
        $mins = $diffInMinutes % 60;
        $totalHours = sprintf('%02d:%02d', $hours, $mins);

        $attendance->update([
            'clock_out_time' => $now,
            'clock_out_latitude' => $lat,
            'clock_out_longitude' => $lng,
            'clock_out_address' => $address,
            'total_hours' => $totalHours,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully',
            'data' => [
                'clock_out_time' => $now->toIso8601String(),
                'total_hours_today' => $totalHours,
            ]
        ]);
    }
    // =========================================================================
    // GET /salesman/attendance-history
    // =========================================================================
    #[OA\Get(
        path: "/salesman/attendance-history",
        summary: "Get attendance history",
        description: "Fetch paginated attendance logs.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Attendance"],
        parameters: [
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "month",
                in: "query",
                description: "Filter by month (1-12)",
                required: false,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "year",
                in: "query",
                description: "Filter by year (e.g. 2024)",
                required: false,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "date", type: "string"),
                        new OA\Property(property: "raw_date", type: "string"),
                        new OA\Property(property: "clock_in_time", type: "string", nullable: true),
                        new OA\Property(property: "clock_in_address", type: "string", nullable: true),
                        new OA\Property(property: "clock_out_time", type: "string", nullable: true),
                        new OA\Property(property: "clock_out_address", type: "string", nullable: true),
                        new OA\Property(property: "total_hours", type: "string", nullable: true)
                    ])),
                    new OA\Property(property: "meta", type: "object")
                ])
            )
        ]
    )]
    public function attendanceHistory(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $perPage = (int) $request->query('per_page', 15);
        
        $query = SalesmanAttendance::where('member_id', $salesman->id)
            ->orderBy('date', 'desc');

        if ($request->has('month')) {
            $query->whereMonth('date', $request->query('month'));
        }

        if ($request->has('year')) {
            $query->whereYear('date', $request->query('year'));
        }

        $attendances = $query->paginate($perPage);

        $data = collect($attendances->items())->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'date' => $attendance->date->format('d M Y'),
                'raw_date' => $attendance->date->format('Y-m-d'),
                'clock_in_time' => $attendance->clock_in_time ? $attendance->clock_in_time->format('h:i A') : null,
                'clock_in_address' => $attendance->clock_in_address,
                'clock_out_time' => $attendance->clock_out_time ? $attendance->clock_out_time->format('h:i A') : null,
                'clock_out_address' => $attendance->clock_out_address,
                'total_hours' => $attendance->total_hours,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ]
        ]);
    }

    // =========================================================================
    // POST /salesman/visits
    // =========================================================================
    #[OA\Post(
        path: "/salesman/visits",
        summary: "Submit a new visit",
        description: "Save a visit log with a photo from a dealer's shop.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Visits"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(properties: [
                    new OA\Property(property: "dealer_id", type: "integer"),
                    new OA\Property(property: "latitude", type: "number"),
                    new OA\Property(property: "longitude", type: "number"),
                    new OA\Property(property: "address", type: "string", nullable: true),
                    new OA\Property(property: "notes", type: "string", nullable: true),
                    new OA\Property(property: "photo", type: "string", format: "binary", nullable: true)
                ])
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "message", type: "string")
                ])
            )
        ]
    )]
    public function storeVisit(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'dealer_id' => 'required|exists:members,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|max:5120', // Max 5MB
        ]);

        $dealer = Member::where('id', $request->dealer_id)
            ->where('role', 'dealer')
            ->where('salesman_id', $salesman->id)
            ->first();

        if (!$dealer) {
            return response()->json(['success' => false, 'message' => 'Dealer not found or not assigned to you.'], 404);
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $address = $request->input('address');

        $fetchedAddress = $this->getAddressFromCoordinates($lat, $lng);
        $address = $fetchedAddress ?: $address;

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/visits'), $filename);
            $photoPath = 'visits/' . $filename;
        }

        SalesmanVisit::create([
            'salesman_id' => $salesman->id,
            'dealer_id' => $dealer->id,
            'visit_time' => Carbon::now(),
            'latitude' => $lat,
            'longitude' => $lng,
            'address' => $address,
            'notes' => $request->input('notes'),
            'photo_path' => $photoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visit logged successfully.',
        ]);
    }

    // =========================================================================
    // GET /salesman/visits
    // =========================================================================
    #[OA\Get(
        path: "/salesman/visits",
        summary: "Get visit history",
        description: "Fetch paginated visit logs.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Visits"],
        parameters: [
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "dealer_name", type: "string"),
                        new OA\Property(property: "date", type: "string"),
                        new OA\Property(property: "time", type: "string"),
                        new OA\Property(property: "location", type: "string", nullable: true),
                        new OA\Property(property: "coordinates", type: "string"),
                        new OA\Property(property: "photo_url", type: "string", nullable: true),
                        new OA\Property(property: "notes", type: "string", nullable: true)
                    ])),
                    new OA\Property(property: "meta", type: "object")
                ])
            )
        ]
    )]
    public function getVisits(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $perPage = (int) $request->query('per_page', 15);

        $visits = SalesmanVisit::where('salesman_id', $salesman->id)
            ->with('dealer')
            ->orderBy('visit_time', 'desc')
            ->paginate($perPage);

        $data = collect($visits->items())->map(function ($visit) {
            return [
                'id' => $visit->id,
                'dealer_name' => $visit->dealer ? ($visit->dealer->shop ?? $visit->dealer->name) : 'Unknown Dealer',
                'date' => $visit->visit_time->format('d M Y'),
                'time' => $visit->visit_time->format('h:i A'),
                'location' => $visit->address,
                'coordinates' => "{$visit->latitude}° N, {$visit->longitude}° E",
                'photo_url' => $visit->photo_path ? asset('uploads/' . $visit->photo_path) : null,
                'notes' => $visit->notes,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $visits->currentPage(),
                'last_page' => $visits->lastPage(),
                'per_page' => $visits->perPage(),
                'total' => $visits->total(),
            ]
        ]);
    }

    // =========================================================================
    // POST /salesman/location-ping
    // =========================================================================
    #[OA\Post(
        path: "/salesman/location-ping",
        summary: "Background location ping",
        description: "Log background location and battery status.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Attendance"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "latitude", type: "number"),
                new OA\Property(property: "longitude", type: "number"),
                new OA\Property(property: "timestamp", type: "string"),
                new OA\Property(property: "battery_level", type: "integer", nullable: true)
            ])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "message", type: "string")
                ])
            )
        ]
    )]
    public function locationPing(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'timestamp' => 'required|date',
            'battery_level' => 'nullable|integer|min:0|max:100',
        ]);

        SalesmanLocationLog::create([
            'salesman_id' => $salesman->id,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'timestamp' => Carbon::parse($request->input('timestamp')),
            'battery_level' => $request->input('battery_level'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location logged successfully.',
        ]);
    }

    // =========================================================================
    // GET /salesman/expense-categories
    // =========================================================================
    #[OA\Get(
        path: "/salesman/expense-categories",
        summary: "Get expense categories",
        description: "Fetches a list of active expense categories.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Expenses"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "name", type: "string")
                    ]))
                ])
            )
        ]
    )]
    public function getExpenseCategories(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $categories = ExpenseCategory::where('status', 'active')->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // =========================================================================
    // POST /salesman/expenses
    // =========================================================================
    #[OA\Post(
        path: "/salesman/expenses",
        summary: "Submit a new expense",
        description: "Allows a salesman to submit an expense with a photo.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Expenses"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(properties: [
                    new OA\Property(property: "expense_category_id", type: "integer"),
                    new OA\Property(property: "amount", type: "number"),
                    new OA\Property(property: "description", type: "string", nullable: true),
                    new OA\Property(property: "receipt_photo", type: "string", format: "binary", nullable: true)
                ])
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "message", type: "string")
                ])
            )
        ]
    )]
    public function storeExpense(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'receipt_photo' => 'nullable|image|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('receipt_photo')) {
            $file = $request->file('receipt_photo');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/expenses'), $fileName);
            $photoPath = 'expenses/' . $fileName;
        }

        Expense::create([
            'salesman_id' => $salesman->id,
            'expense_category_id' => $request->input('expense_category_id'),
            'amount' => $request->input('amount'),
            'description' => $request->input('description'),
            'receipt_photo_path' => $photoPath,
            'status' => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense submitted successfully and is pending approval.'
        ]);
    }

    // =========================================================================
    // GET /salesman/expenses
    // =========================================================================
    #[OA\Get(
        path: "/salesman/expenses",
        summary: "Get expense history",
        description: "Fetches a paginated list of the authenticated salesman's expenses.",
        security: [["bearerAuth" => []]],
        tags: ["Salesman Expenses"],
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
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean"),
                    new OA\Property(property: "data", type: "array", items: new OA\Items())
                ])
            )
        ]
    )]
    public function getExpenses(Request $request): JsonResponse
    {
        $salesman = $request->user();
        if (!$this->verifySalesman($salesman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $perPage = (int) $request->query('per_page', 15);

        $expenses = Expense::where('salesman_id', $salesman->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $data = collect($expenses->items())->map(function ($expense) {
            return [
                'id' => $expense->id,
                'category' => $expense->category ? $expense->category->name : 'Unknown',
                'amount' => (float) $expense->amount,
                'description' => $expense->description,
                'receipt_photo_url' => $expense->receipt_photo_path ? asset('uploads/' . $expense->receipt_photo_path) : null,
                'status' => $expense->status,
                'date' => $expense->created_at->format('d M, Y h:i A'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ]
        ]);
    }
}
