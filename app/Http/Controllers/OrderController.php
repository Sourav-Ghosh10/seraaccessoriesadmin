<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\RewardTransaction;
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

        if ($request->from_request_id) {
            OrderRequest::where('id', $request->from_request_id)->update(['status' => 'Processed']);
        }

        // Send push notification to dealer with deep link
        FcmService::sendPushNotification(
            $order->member,
            'New Order Confirmed',
            "Your order {$orderNumber} has been confirmed and is being processed.",
            [
                'type' => 'order',
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
                    'type' => 'order',
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status
                ]
            );

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
        ]);

        $order = Order::findOrFail($id);
        
        // Ensure time is in 24h format for database compatibility
        $time = date("H:i", strtotime($validated['expected_delivery_time']));
        $expectedAt = $validated['expected_delivery_date'] . ' ' . $time . ':00';

        Delivery::updateOrCreate(
            ['order_id' => $order->id],
            [
                'vehicle_no' => $validated['vehicle_no'],
                'vehicle_type' => $validated['vehicle_type'],
                'driver_phone' => $validated['driver_phone'],
                'expected_delivery_at' => $expectedAt,
                'remarks' => $validated['delivery_remarks'],
                'status' => 'Out for Delivery'
            ]
        );

        $order->update(['status' => 'Out for Delivery']);

        // Send push notification to dealer with deep link
        FcmService::sendPushNotification(
            $order->member,
            'Order Out for Delivery',
            "Your order {$order->order_number} is out for delivery! Vehicle: {$validated['vehicle_no']}.",
            [
                'type' => 'order',
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => 'Out for Delivery'
            ]
        );

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

            $order->update(['status' => 'Invoiced']);

            // Send push notification to dealer with deep link
            FcmService::sendPushNotification(
                $order->member,
                'Invoice Generated',
                "Invoice {$validated['invoice_number']} of ₹{$totalAmount} has been generated for order {$order->order_number}.",
                [
                    'type' => 'order',
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => 'Invoiced',
                    'invoice_number' => $validated['invoice_number']
                ]
            );

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
                'type' => 'estimate',
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
        ]);

        $order = Order::with(['member.salesman'])->findOrFail($validated['order_id']);
        $dealer = $order->member;
        $salesman = $dealer ? $dealer->salesman : null;

        $hasAddedPoints = false;

        // 1. Assign Dealer Points
        if (!empty($validated['dealer_points']) && $validated['dealer_points'] > 0) {
            RewardTransaction::create([
                'member_id' => $dealer->id,
                'order_id' => $order->id,
                'points' => $validated['dealer_points'],
                'type' => 'Order Points',
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
}
