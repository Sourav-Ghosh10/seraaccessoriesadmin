<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\RewardTransaction;
use App\Models\RedeemRequest;
use App\Services\FcmService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function storeRequest(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $lastRequest = OrderRequest::orderBy('id', 'desc')->first();
        $nextNumber = $lastRequest ? (int)substr($lastRequest->request_number, 4) + 1 : 1;
        $orderNumber = 'REQ-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $order = OrderRequest::create([
            'member_id' => $request->member_id,
            'type' => $request->type,
            'description' => $request->description,
            'request_number' => $orderNumber,
            'status' => 'Pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Request created successfully!']);
    }

    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'distributor_id' => 'nullable|exists:members,id',
            'delivery_type' => 'required|string',
            'delivery_date' => 'required|date',
            'address' => 'required|string',
            'remarks' => 'nullable|string',
            'from_request_id' => 'nullable|exists:order_requests,id',
            'challan_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $challanPath = null;
        if ($request->hasFile('challan_file')) {
            $challanPath = $request->file('challan_file')->store('challans', 'public');
        }

        if ($request->from_request_id) {
            $req = OrderRequest::find($request->from_request_id);
            if ($req && $req->status !== 'Pending') {
                return response()->json(['success' => false, 'message' => 'An order has already been generated from this request!'], 400);
            }
        }

        $lastOrder = Order::where('order_number', 'like', 'ORD-%')->orderBy('id', 'desc')->first();
        $nextNumber = $lastOrder ? (int)substr($lastOrder->order_number, 4) + 1 : 1;
        $orderNumber = 'ORD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $order = Order::create([
            'member_id' => $validated['member_id'],
            'distributor_id' => $validated['distributor_id'] ?? null,
            'order_number' => $orderNumber,
            'type' => $validated['delivery_type'],
            'description' => $validated['remarks'],
            'challan_file' => $challanPath,
            'amount' => 0,
            'status' => 'Confirmed',
        ]);

        \App\Models\Delivery::create([
            'order_id' => $order->id,
            'expected_delivery_at' => $validated['delivery_date'] . ' 00:00:00',
            'status' => null
        ]);

        if ($request->from_request_id) {
            OrderRequest::where('id', $request->from_request_id)->update([
                'status' => 'Processed',
                'order_id' => $order->id
            ]);
        }

        // Send push notification to member (dealer or distributor) with deep link
        $notificationType = ($order->member->role === 'distributor') ? 'assigned_order' : 'order';
        FcmService::sendPushNotification(
            $order->member,
            'New Order Confirmed',
            "Your order {$orderNumber} has been confirmed and is being processed.",
            [
                'type' => $notificationType,
                'id' => $order->id,
                'order_number' => $orderNumber,
                'status' => 'Confirmed'
            ]
        );

        // Send push notification to distributor if assigned
        if ($order->distributor_id) {
            $distributor = \App\Models\Member::find($order->distributor_id);
            if ($distributor) {
                FcmService::sendPushNotification(
                    $distributor,
                    'New Order Assigned',
                    "You have been assigned order {$orderNumber} for delivery.",
                    [
                        'type' => 'assigned_order',
                        'id' => $order->id,
                        'order_number' => $orderNumber,
                        'status' => 'Confirmed'
                    ]
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Order generated successfully!', 'order_id' => $order->id]);
    }

    public function uploadChallan(Request $request, $id)
    {
        $request->validate([
            'challan_file' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $order = Order::findOrFail($id);

        if ($request->hasFile('challan_file')) {
            $path = $request->file('challan_file')->store('challans', 'public');
            $order->update(['challan_file' => $path]);

            // Send push notification to dealer with deep link
            FcmService::sendPushNotification(
                $order->member,
                'Challan Uploaded',
                "Challan has been uploaded for your order {$order->order_number}.",
                [
                    'type' => ($order->member->role === 'distributor') ? 'assigned_order' : 'order',
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status
                ]
            );

            if ($order->distributor_id && $order->distributor_id !== $order->member_id) {
                $distributor = \App\Models\Member::find($order->distributor_id);
                if ($distributor) {
                    FcmService::sendPushNotification(
                        $distributor,
                        'Challan Uploaded',
                        "Challan has been uploaded for assigned order {$order->order_number}.",
                        [
                            'type' => 'assigned_order',
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => $order->status
                        ]
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Challan uploaded successfully!']);
        }
        return response()->json(['success' => false, 'message' => 'No file uploaded.']);
    }

    public function updateDeliveryStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'vehicle_no' => 'required|string',
            'vehicle_type' => 'required|string',
            'driver_phone' => 'required|string',
            'expected_delivery_date' => 'required|date',
            'expected_delivery_time' => 'required|string',
            'delivery_remarks' => 'nullable|string',
            'upload_documents' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'dispatch_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $num = intval(preg_replace('/^.*-(\d+)$/', '$1', $id));
        $order = Order::where(function($q) use ($id, $num) {
            $q->where('id', $id)
              ->orWhere('id', $num)
              ->orWhere('order_number', $id)
              ->orWhere('order_number', 'ORD-' . str_pad($num, 4, '0', STR_PAD_LEFT));
        })->firstOrFail();

        if (in_array(strtolower($order->status), ['delivered', 'returned'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update delivery details for an order that is already delivered.'
            ], 403);
        }
        
        // Ensure time is in 24h format for database compatibility
        $time = date("H:i", strtotime($validated['expected_delivery_time']));
        $expectedAt = $validated['expected_delivery_date'] . ' ' . $time . ':00';

        $documentPath = null;
        if ($request->hasFile('upload_documents') && $request->file('upload_documents')->isValid()) {
            $documentPath = $request->file('upload_documents')->store('deliveries/documents', 'public');
        } elseif ($request->hasFile('dispatch_document') && $request->file('dispatch_document')->isValid()) {
            $documentPath = $request->file('dispatch_document')->store('deliveries/documents', 'public');
        }

        $deliveryData = [
            'vehicle_no' => $validated['vehicle_no'],
            'vehicle_type' => $validated['vehicle_type'],
            'driver_phone' => $validated['driver_phone'],
            'expected_delivery_at' => $expectedAt,
            'remarks' => $validated['delivery_remarks'] ?? null,
            'status' => 'Out for Delivery'
        ];
        if ($documentPath) {
            $deliveryData['document_path'] = $documentPath;
        }

        Delivery::updateOrCreate(
            ['order_id' => $order->id],
            $deliveryData
        );

        $order->update(['status' => 'Out for Delivery']);

        // Send push notification to dealer with deep link
        FcmService::sendPushNotification(
            $order->member,
            'Order Out for Delivery',
            "Your order {$order->order_number} is out for delivery! Vehicle: {$validated['vehicle_no']}.",
            [
                'type' => ($order->member->role === 'distributor') ? 'assigned_order' : 'order',
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => 'Out for Delivery'
            ]
        );

        if ($order->distributor_id && $order->distributor_id !== $order->member_id) {
            $distributor = \App\Models\Member::find($order->distributor_id);
            if ($distributor) {
                FcmService::sendPushNotification(
                    $distributor,
                    'Order Out for Delivery',
                    "Assigned order {$order->order_number} is out for delivery! Vehicle: {$validated['vehicle_no']}.",
                    [
                        'type' => 'assigned_order',
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => 'Out for Delivery'
                    ]
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Delivery status updated successfully!']);
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'amount' => 'required|numeric|min:0',
            'invoice_file' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        
        $totalAmount = $validated['amount'];

        if ($request->hasFile('invoice_file')) {
            $path = $request->file('invoice_file')->store('invoices', 'public');
            
            Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'order_id' => $order->id,
                'amount' => $totalAmount,
                'file_path' => $path
            ]);

            // Update Member Balance and Passbook (Dealer or Distributor)
            $dealer = $order->member;
            if ($dealer && in_array($dealer->role, ['dealer', 'distributor'])) {
                $balance = $dealer->dealerBalance;
                if (!$balance) {
                    $balance = \App\Models\DealerBalance::create([
                        'member_id' => $dealer->id,
                        'total_amount' => 0.00,
                        'paid_amount' => 0.00,
                        'due_amount' => 0.00,
                    ]);
                }
                
                $balance->total_amount += $totalAmount;
                $balance->due_amount = $balance->total_amount - $balance->paid_amount;
                $balance->save();

                // Record Passbook Transaction using actual Invoice Number
                $ref = $validated['invoice_number'];

                $managerName = auth()->user() ? auth()->user()->name : 'System Admin';
                
                \App\Models\PassbookTransaction::create([
                    'member_id' => $dealer->id,
                    'managed_by' => $managerName,
                    'type' => 'Order',
                    'amount' => $totalAmount,
                    'ref' => $ref,
                    'status' => 'Confirmed',
                ]);
            }

            $order->update(['status' => 'Invoiced']);

            // Send push notification to dealer with deep link
            FcmService::sendPushNotification(
                $order->member,
                'Invoice Generated',
                "Invoice {$validated['invoice_number']} of ₹{$totalAmount} has been generated for order {$order->order_number}.",
                [
                    'type' => ($order->member->role === 'distributor') ? 'assigned_order' : 'order',
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => 'Invoiced',
                    'invoice_number' => $validated['invoice_number']
                ]
            );

            if ($order->distributor_id && $order->distributor_id !== $order->member_id) {
                $distributor = \App\Models\Member::find($order->distributor_id);
                if ($distributor) {
                    FcmService::sendPushNotification(
                        $distributor,
                        'Invoice Generated',
                        "Invoice {$validated['invoice_number']} of ₹{$totalAmount} has been generated for assigned order {$order->order_number}.",
                        [
                            'type' => 'assigned_order',
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => 'Invoiced',
                            'invoice_number' => $validated['invoice_number']
                        ]
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Invoice uploaded successfully!']);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded.']);
    }

    public function revertEstimate(Request $request, $id)
    {
        $request->validate([
            'response_description' => 'nullable|string|max:5000',
            'estimate_pdf' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:20480',
        ]);

        $estimate = \App\Models\Estimate::findOrFail($id);

        if (strtolower($estimate->status) === 'confirmed') {
            return redirect()->back()->with('error', 'Cannot revert an estimate that is already confirmed.');
        }

        $filePath = $estimate->response_file_path;
        if ($request->hasFile('estimate_pdf')) {
            $filePath = $request->file('estimate_pdf')->store('estimates/responses', 'public');
        }

        $estimate->update([
            'response_description' => $request->response_description,
            'response_file_path' => $filePath,
            'status' => 'Responded',
        ]);

        // Trigger push notifications to all registered dealer devices & save history
        \App\Services\FcmService::sendPushNotification(
            $estimate->member,
            'Estimate Reverted',
            "Your estimate request {$estimate->request_number} has been reverted by the administrator.",
            [
                'type' => ($estimate->member->role === 'distributor') ? 'assigned_estimate' : 'estimate',
                'id' => $estimate->id,
                'status' => 'Responded'
            ]
        );

        return redirect()->back()->with('success', 'Estimate reverted successfully!');
    }

    public function storeRewardPoints(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'dealer_points' => 'nullable|integer|min:0',
            'salesman_points' => 'nullable|integer|min:0',
            'unlock_days' => 'nullable|integer|min:0',
        ]);

        $order = Order::with(['member.salesman'])->findOrFail($validated['order_id']);
        $dealer = $order->member;
        $salesman = $dealer ? $dealer->salesman : null;

        $hasAddedPoints = false;

        $unlockDays = $request->input('unlock_days');
        $countDays = null;
        if ($unlockDays !== null && $unlockDays !== '') {
            $countDays = $unlockDays;
        }

        // 1. Assign Dealer Points
        if (!empty($validated['dealer_points']) && $validated['dealer_points'] > 0) {
            RewardTransaction::create([
                'member_id' => $dealer->id,
                'order_id' => $order->id,
                'points' => $validated['dealer_points'],
                'type' => 'Order Points',
                'unlock_days' => $unlockDays,
                'count_days' => $countDays,
            ]);

            // Send push notification to Dealer
            FcmService::sendPushNotification(
                $dealer,
                'Reward Points Earned',
                "Congratulations! You have earned {$validated['dealer_points']} reward points for order {$order->order_number}.",
                [
                    'type' => 'rewards',
                    'deeplink' => 'my-points',
                    'deep_link' => 'my-points',
                    'points' => (string)$validated['dealer_points'],
                    'order_number' => $order->order_number,
                ]
            );

            $hasAddedPoints = true;
        }

        // 2. Assign Salesman Points
        if ($salesman && !empty($validated['salesman_points']) && $validated['salesman_points'] > 0) {
            RewardTransaction::create([
                'member_id' => $salesman->id,
                'order_id' => $order->id,
                'points' => $validated['salesman_points'],
                'type' => 'Order Points',
                'unlock_days' => $unlockDays,
                'count_days' => $countDays,
            ]);

            // Send push notification to Salesman
            FcmService::sendPushNotification(
                $salesman,
                'Reward Points Earned',
                "Congratulations! You have earned {$validated['salesman_points']} reward points for order {$order->order_number}.",
                [
                    'type' => 'rewards',
                    'deeplink' => 'my-points',
                    'deep_link' => 'my-points',
                    'points' => (string)$validated['salesman_points'],
                    'order_number' => $order->order_number,
                ]
            );

            $hasAddedPoints = true;
        }

        if ($hasAddedPoints) {
            return response()->json([
                'success' => true,
                'message' => 'Reward points assigned successfully!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No points were entered or processed.',
        ]);
    }

    public function markReturned(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $request->validate([
            'note' => 'nullable|string|max:5000',
            'credit_note_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $order->update(['status' => 'Returned']);

        if ($request->hasFile('credit_note_file') || $request->filled('note')) {
            $lastCN = \App\Models\CreditNote::orderBy('id', 'desc')->first();
            $nextNumber = 1;
            if ($lastCN) {
                $parts = explode('-', $lastCN->credit_note_number);
                $lastSeq = end($parts);
                $nextNumber = is_numeric($lastSeq) ? (int)$lastSeq + 1 : 1;
            }
            $creditNoteNumber = 'CN-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            while (\App\Models\CreditNote::where('credit_note_number', $creditNoteNumber)->exists()) {
                $nextNumber++;
                $creditNoteNumber = 'CN-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }

            $path = null;
            if ($request->hasFile('credit_note_file')) {
                $path = $request->file('credit_note_file')->store('credit_notes/dealer', 'public');
            }

            \App\Models\CreditNote::create([
                'credit_note_number' => $creditNoteNumber,
                'order_id' => $order->id,
                'amount' => 0.00,
                'file_path' => $path,
                'dealer_file_path' => $path,
                'note' => $request->note ?? 'Order Returned'
            ]);
        }

        try {
            FcmService::sendPushNotification(
                $order->member,
                'Order Returned',
                "Your order {$order->order_number} has been marked as returned.",
                [
                    'type' => ($order->member->role === 'distributor') ? 'assigned_order' : 'order',
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => 'Returned'
                ]
            );

            if ($order->distributor_id && $order->distributor_id !== $order->member_id) {
                $distributor = \App\Models\Member::find($order->distributor_id);
                if ($distributor) {
                    FcmService::sendPushNotification(
                        $distributor,
                        'Order Returned',
                        "Assigned order {$order->order_number} has been marked as returned.",
                        [
                            'type' => 'assigned_order',
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => 'Returned'
                        ]
                    );
                }
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Order status updated to Returned successfully!'
        ]);
    }

    public function storeCreditNote(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'note' => 'required|string|max:5000',
            'amount' => 'nullable|numeric|min:0',
            'dealer_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'distributor_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ], [
            'note.required' => 'Please enter credit notes/remarks.',
            'dealer_file.mimes' => 'Dealer Document must be a PDF or Image (JPG/PNG).',
            'distributor_file.mimes' => 'Distributor Document must be a PDF or Image (JPG/PNG).'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        if (!$request->hasFile('dealer_file') && !$request->hasFile('distributor_file')) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload at least one document (Dealer or Distributor).'
            ]);
        }

        $validated = $validator->validated();

        $order = Order::findOrFail($validated['order_id']);

        $amount = $request->has('amount') && is_numeric($request->amount) ? (float) $request->amount : 0.00;

        // Auto-generate unique credit note number
        $lastCN = \App\Models\CreditNote::orderBy('id', 'desc')->first();
        if ($lastCN) {
            $parts = explode('-', $lastCN->credit_note_number);
            $lastSeq = end($parts);
            $nextNumber = is_numeric($lastSeq) ? (int)$lastSeq + 1 : 1;
        } else {
            $nextNumber = 1;
        }
        $creditNoteNumber = 'CN-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        while (\App\Models\CreditNote::where('credit_note_number', $creditNoteNumber)->exists()) {
            $nextNumber++;
            $creditNoteNumber = 'CN-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        $dealerPath = null;
        $distributorPath = null;

        if ($request->hasFile('dealer_file')) {
            $dealerPath = $request->file('dealer_file')->store('credit_notes/dealer', 'public');
        }

        if ($request->hasFile('distributor_file')) {
            $distributorPath = $request->file('distributor_file')->store('credit_notes/distributor', 'public');
        }

        \App\Models\CreditNote::create([
            'credit_note_number' => $creditNoteNumber,
            'order_id' => $order->id,
            'amount' => $amount,
            'file_path' => $dealerPath ?? $distributorPath,
            'dealer_file_path' => $dealerPath,
            'distributor_file_path' => $distributorPath,
            'note' => $validated['note']
        ]);

        if ($amount > 0) {
            $member = $order->member;
            if ($member) {
                $balance = $member->dealerBalance;
                if (!$balance) {
                    $balance = \App\Models\DealerBalance::create([
                        'member_id' => $member->id,
                        'total_amount' => 0.00,
                        'paid_amount' => 0.00,
                        'due_amount' => 0.00,
                    ]);
                }

                $balance->paid_amount += $amount;
                $balance->due_amount = $balance->total_amount - $balance->paid_amount;
                $balance->save();

                $managerName = auth()->user() ? auth()->user()->name : 'System Admin';

                \App\Models\PassbookTransaction::create([
                    'member_id' => $member->id,
                    'managed_by' => $managerName,
                    'type' => 'Credit Note',
                    'amount' => $amount,
                    'ref' => $creditNoteNumber,
                    'status' => 'Confirmed',
                ]);
            }
        }

        // Send push notification to dealer
        try {
            FcmService::sendPushNotification(
                $order->member,
                'Credit Note Generated',
                "Credit Note {$creditNoteNumber} has been generated for order {$order->order_number}.",
                [
                    'type' => ($order->member->role === 'distributor') ? 'assigned_order' : 'order',
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'credit_note_number' => $creditNoteNumber
                ]
            );

            if ($order->distributor_id && $order->distributor_id !== $order->member_id) {
                $distributor = \App\Models\Member::find($order->distributor_id);
                if ($distributor) {
                    FcmService::sendPushNotification(
                        $distributor,
                        'Credit Note Generated',
                        "Credit Note {$creditNoteNumber} has been generated for assigned order {$order->order_number}.",
                        [
                            'type' => 'assigned_order',
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => $order->status,
                            'credit_note_number' => $creditNoteNumber
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send push notification: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Credit Note uploaded successfully!']);
    }

    public function cancelOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        if (in_array($order->status, ['Out for Delivery', 'Delivered', 'Returned', 'Cancelled', 'Invoiced'])) {
            return response()->json(['success' => false, 'message' => 'Order cannot be cancelled in its current state.']);
        }
        
        $order->update(['status' => 'Cancelled']);

        try {
            FcmService::sendPushNotification(
                $order->member,
                'Order Cancelled',
                "Your order {$order->order_number} has been cancelled.",
                [
                    'type' => ($order->member->role === 'distributor') ? 'assigned_order' : 'order',
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => 'Cancelled'
                ]
            );

            if ($order->distributor_id && $order->distributor_id !== $order->member_id) {
                $distributor = \App\Models\Member::find($order->distributor_id);
                if ($distributor) {
                    FcmService::sendPushNotification(
                        $distributor,
                        'Order Cancelled',
                        "Assigned order {$order->order_number} has been cancelled.",
                        [
                            'type' => 'assigned_order',
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => 'Cancelled'
                        ]
                    );
                }
            }
        } catch (\Exception $e) {}

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully!']);
    }

    public function updateRedeemStatus(Request $request, $id)
    {
        $redeem = RedeemRequest::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:Pending,Approved,Processed,Rejected',
            'credit_note' => 'nullable|string|max:255',
            'deduct_amount' => 'nullable|numeric|min:0',
            'dealer_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'distributor_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'salesman_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $redeem->status = $request->status;
        if ($request->has('credit_note')) {
            $redeem->Credit_note = $request->credit_note;
        }

        if ($request->hasFile('dealer_file')) {
            $redeem->dealer_file_path = $request->file('dealer_file')->store('redeem_requests/dealer', 'public');
        }

        if ($request->hasFile('distributor_file')) {
            $redeem->distributor_file_path = $request->file('distributor_file')->store('redeem_requests/distributor', 'public');
        }

        if ($request->hasFile('salesman_file')) {
            $redeem->salesman_file_path = $request->file('salesman_file')->store('redeem_requests/salesman', 'public');
        }

        $isNewDeduction = false;
        if ($request->filled('deduct_amount')) {
            if ($redeem->deduct_amount === null || $redeem->deduct_amount == 0) {
                $isNewDeduction = true;
            }
            $redeem->deduct_amount = $request->deduct_amount;
        }

        $redeem->save();

        if ($isNewDeduction && $request->deduct_amount > 0) {
            $amount = (float)$request->deduct_amount;
            $managerName = auth()->user() ? auth()->user()->name : 'System Admin';
            $ref = 'RDM-' . str_pad($redeem->id, 4, '0', STR_PAD_LEFT);

            if ($redeem->member && $redeem->member->role === 'dealer') {
                $dealer = $redeem->member;
                
                \Illuminate\Support\Facades\DB::transaction(function () use ($dealer, $amount, $managerName, $ref) {
                    // Deduct from Dealer
                    $balance = \App\Models\DealerBalance::firstOrCreate(
                        ['member_id' => $dealer->id],
                        ['total_amount' => 0, 'paid_amount' => 0, 'due_amount' => 0]
                    );
                    $balance->paid_amount += $amount;
                    $balance->due_amount -= $amount;
                    $balance->save();

                    \App\Models\PassbookTransaction::create([
                        'member_id' => $dealer->id,
                        'managed_by' => $managerName,
                        'type' => 'Redeem Request',
                        'amount' => $amount,
                        'ref' => $ref,
                        'status' => 'Completed',
                    ]);
                    
                    // Deduct from Distributor
                    if ($dealer->dist_id) {
                        $distributor = \App\Models\Member::where('role', 'distributor')
                            ->where('dist_id', $dealer->dist_id)->first();
                        
                        if ($distributor) {
                            $distBalance = \App\Models\DealerBalance::firstOrCreate(
                                ['member_id' => $distributor->id],
                                ['total_amount' => 0, 'paid_amount' => 0, 'due_amount' => 0]
                            );
                            $distBalance->paid_amount += $amount;
                            $distBalance->due_amount -= $amount;
                            $distBalance->save();

                            \App\Models\PassbookTransaction::create([
                                'member_id' => $distributor->id,
                                'managed_by' => $managerName,
                                'type' => 'Redeem Request',
                                'amount' => $amount,
                                'ref' => $ref . '-D',
                                'status' => 'Completed',
                            ]);
                        }
                    }
                });
            }
        }

        try {
            if ($redeem->member) {
                FcmService::sendPushNotification(
                    $redeem->member,
                    'Redeem Request ' . $redeem->status,
                    "Your redeem request #RDM-" . str_pad($redeem->id, 4, '0', STR_PAD_LEFT) . " status has been updated to " . $redeem->status . ".",
                    [
                        'type' => 'redeem',
                        'id' => $redeem->id,
                        'status' => $redeem->status
                    ]
                );
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Redeem request updated successfully!'
        ]);
    }

    public function destroyRequest($id)
    {
        $orderRequest = OrderRequest::findOrFail($id);
        $orderRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order request deleted successfully!'
        ]);
    }
}


