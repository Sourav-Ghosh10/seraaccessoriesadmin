<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderRequest;
use App\Models\Member;
use App\Models\Order;

class PageController extends Controller
{
    public function login() { return view('auth.login'); }
    
    public function dashboard(Request $request) { 
        if ($request->has('role')) {
            session(['role' => $request->role]);
        }
        $role = session('role', 'Admin');

        // Dynamic metrics
        $totalDealers = \App\Models\Member::where('role', 'dealer')->count();
        $totalSalesmen = \App\Models\Member::where('role', 'salesman')->count();
        $totalOrders = \App\Models\Order::count();
        $pendingOrders = \App\Models\Order::where('status', 'Pending')->count();

        $deliveredOrders = \App\Models\Order::where('status', 'Delivered')->count();
        $invoicePending = \App\Models\Order::where('status', '!=', 'Cancelled')->whereDoesntHave('invoice')->count();

        // Top Dealer
        $topDealerModel = \App\Models\Member::where('role', 'dealer')
            ->withSum(['orders' => function($q) {
                $q->where('status', '!=', 'Cancelled');
            }], 'amount')
            ->orderByDesc('orders_sum_amount')
            ->first();

        if ($topDealerModel) {
            $topDealerName = $topDealerModel->name;
        } else {
            $topDealerName = 'John Doe';
        }

        // Top Salesman
        $topSalesmanModel = \App\Models\Member::where('role', 'salesman')
            ->withSum('rewardTransactions', 'points')
            ->orderByDesc('reward_transactions_sum_points')
            ->first();

        if ($topSalesmanModel) {
            $topSalesmanName = $topSalesmanModel->name;
        } else {
            $topSalesmanName = 'Alice Smith';
        }

        // Last 6 months sales data
        $months = [];
        $salesData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');
            
            $sum = \App\Models\Order::where('status', '!=', 'Cancelled')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
                
            $salesData[] = (float) $sum;
        }

        return view('dashboard', compact(
            'role',
            'totalDealers',
            'totalSalesmen',
            'totalOrders',
            'pendingOrders',
            'deliveredOrders',
            'invoicePending',
            'topDealerName',
            'topSalesmanName',
            'months',
            'salesData'
        ));
    }
    
    public function dealers() {
        $dealers = \App\Models\Member::where('role', 'dealer')->get();
        return view('dealers', compact('dealers'));
    }

    public function salesmen() {
        $salesmen = \App\Models\Member::where('role', 'salesman')->get();
        return view('salesmen', compact('salesmen'));
    }

    public function distributors() {
        $distributors = \App\Models\Member::where('role', 'distributor')->get();
        return view('distributors', compact('distributors'));
    }

    public function complianceDashboard() {
        return view('compliance.dashboard');
    }

    public function siteCompliance() {
        $sites = [
            ['name' => 'Mall A', 'requirement' => 'Fire Equipment Training', 'status' => 'Compliant', 'expiry' => '2024-12-20', 'last_completed' => '2023-12-20', 'score' => 85],
            ['name' => 'Mall A', 'requirement' => 'Evacuation Drill', 'status' => 'Expired', 'expiry' => '2024-05-01', 'last_completed' => '2023-11-01', 'score' => 40],
            ['name' => 'Mall B', 'requirement' => 'Emergency Procedures', 'status' => 'Expiring Soon', 'expiry' => '2024-05-25', 'last_completed' => '2023-11-25', 'score' => 72],
        ];
        return view('compliance.sites', compact('sites'));
    }

    public function expiringCompliance() {
        $expiring = [
            ['site' => 'Mall B', 'training' => 'Emergency Procedures', 'expiry' => '2024-05-25', 'days_left' => 12],
            ['site' => 'Airport Terminal 1', 'training' => 'First Aid', 'expiry' => '2024-06-05', 'days_left' => 23],
        ];
        return view('compliance.expiring', compact('expiring'));
    }

    public function nonCompliantSites() {
        return view('compliance.non-compliant');
    }

    public function complianceReports() {
        return view('compliance.reports');
    }

    public function estimateRequests() {
        $estimates = \App\Models\Estimate::with('member')->orderBy('id', 'desc')->get();
        return view('estimates.requests', compact('estimates'));
    }

    public function orderRequests() {
        $orders = OrderRequest::with('member')->orderBy('id', 'desc')->get();
        $dealers = Member::where('role', 'dealer')->get();
        return view('orders.requests', compact('orders', 'dealers'));
    }

    public function ordersList() {
        $finalOrders = \App\Models\Order::with(['member', 'delivery'])
            ->where('status', '!=', 'Pending')
            ->where('order_number', 'like', 'ORD-%')
            ->orderBy('id', 'desc')
            ->get();
        return view('orders.index', compact('finalOrders'));
    }

    public function showOrder($id) {
        $order = \App\Models\Order::with(['member', 'items', 'distributor'])->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    public function createOrder() {
        $dealers = \App\Models\Member::where('role', 'dealer')->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->get();
        return view('orders.create', compact('dealers', 'distributors'));
    }

    public function delivery()
    {
        $orders = Order::with('delivery')
            ->whereIn('status', ['Confirmed', 'Out for Delivery', 'Delivered'])
            ->orderBy('id', 'desc')
            ->get();
        return view('delivery', compact('orders'));
    }

    public function invoices()
    {
        $invoices = \App\Models\Invoice::with('order')->orderBy('id', 'desc')->get();
        $orders = Order::where('status', '!=', 'Cancelled')
            ->whereDoesntHave('invoice')
            ->orderBy('id', 'desc')
            ->get();
        return view('invoices', compact('invoices', 'orders'));
    }

    public function rewards() {
        // Dealer Points distributed this month
        $dealerPointsSum = \App\Models\RewardTransaction::whereHas('member', function ($query) {
            $query->where('role', 'dealer');
        })->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('points');

        // Salesman Points distributed this month
        $salesmanPointsSum = \App\Models\RewardTransaction::whereHas('member', function ($query) {
            $query->where('role', 'salesman');
        })->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('points');

        // Points history
        $history = \App\Models\RewardTransaction::with(['member', 'order'])->orderBy('created_at', 'desc')->get();

        // Orders to populate drop down
        $orders = \App\Models\Order::with(['member.salesman'])
            ->where('status', '!=', 'Pending')
            ->where('order_number', 'like', 'ORD-%')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($ord) {
                return [
                    'id' => $ord->id,
                    'order_number' => $ord->order_number,
                    'dealer' => $ord->member->name . ($ord->member->shop ? ' (' . $ord->member->shop . ')' : ''),
                    'salesman' => $ord->member->salesman ? $ord->member->salesman->name : 'No Salesman Assigned'
                ];
            });

        return view('rewards', compact('dealerPointsSum', 'salesmanPointsSum', 'history', 'orders'));
    }

    public function priceList() {
        return view('price-list');
    }

    public function passbook() {
        $dealers = Member::where('role', 'dealer')->with('dealerBalance')->orderBy('name', 'asc')->get();
        return view('passbook', compact('dealers'));
    }

    public function allTransactions(Request $request) {
        $query = \App\Models\PassbookTransaction::with('member')->orderBy('created_at', 'desc');

        $salesmen = Member::where('role', 'salesman')->get();
        $admins = \App\Models\User::all();

        $transactions = $query->get()->map(function($txn) {
            return [
                'date' => $txn->created_at->format('Y-m-d'),
                'dealer' => $txn->member->shop ?? $txn->member->name,
                'user' => $txn->managed_by,
                'type' => $txn->type,
                'amount' => (float) $txn->amount,
                'ref' => $txn->ref,
                'status' => $txn->status
            ];
        });

        return view('transactions', compact('transactions', 'salesmen', 'admins'));
    }

    public function updateBalance(Request $request) {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'adjustment_type' => 'required|in:add,payment',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $dealer = Member::findOrFail($request->member_id);
        $amount = (float) $request->amount;
        $type = $request->adjustment_type;

        $txnInfo = \Illuminate\Support\Facades\DB::transaction(function() use ($dealer, $amount, $type) {
            $balance = $dealer->dealerBalance;
            if (!$balance) {
                $balance = new \App\Models\DealerBalance([
                    'member_id' => $dealer->id,
                    'total_amount' => 0.00,
                    'paid_amount' => 0.00,
                    'due_amount' => 0.00,
                ]);
            }

            if ($type === 'add') {
                $balance->total_amount += $amount;
                $txnType = 'Order';
                $txnStatus = 'Confirmed';
                $ref = 'ORD-' . mt_rand(1000, 9999);
            } else {
                $balance->paid_amount += $amount;
                $txnType = 'Payment';
                $txnStatus = 'Completed';
                $ref = 'TXN-' . mt_rand(1000, 9999);
            }

            $balance->due_amount = $balance->total_amount - $balance->paid_amount;
            $balance->save();

            // Ensure unique reference
            while (\App\Models\PassbookTransaction::where('ref', $ref)->exists()) {
                if ($type === 'add') {
                    $ref = 'ORD-' . mt_rand(1000, 9999);
                } else {
                    $ref = 'TXN-' . mt_rand(1000, 9999);
                }
            }

            $managerName = auth()->user() ? auth()->user()->name : 'System Admin';

            \App\Models\PassbookTransaction::create([
                'member_id' => $dealer->id,
                'managed_by' => $managerName,
                'type' => $txnType,
                'amount' => $amount,
                'ref' => $ref,
                'status' => $txnStatus,
            ]);

            return [
                'ref' => $ref,
                'due_amount' => $balance->due_amount,
            ];
        });

        // Trigger push notification to Dealer
        $title = $type === 'add' ? 'Account Ledger Updated' : 'Payment Received';
        $body = $type === 'add'
            ? "A new bill of ₹ " . number_format($amount, 2) . " (Ref: {$txnInfo['ref']}) has been added to your account. Current outstanding due: ₹ " . number_format($txnInfo['due_amount'], 2) . "."
            : "Your payment of ₹ " . number_format($amount, 2) . " (Ref: {$txnInfo['ref']}) has been successfully received. Remaining outstanding due: ₹ " . number_format($txnInfo['due_amount'], 2) . ".";

        try {
            \App\Services\FcmService::sendPushNotification(
                $dealer,
                $title,
                $body,
                [
                    'type' => 'passbook',
                    'deeplink' => 'my-passbook',
                    'deep_link' => 'my-passbook',
                    'ref' => $txnInfo['ref'],
                    'amount' => (string) $amount,
                    'due_amount' => (string) $txnInfo['due_amount'],
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send passbook update push notification: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Dealer balance updated successfully!'
        ]);
    }


    public function users() {
        $users = \App\Models\User::all();
        return view('users', compact('users'));
    }

    public function verifyPayments() {
        $submissions = \App\Models\PaymentSubmission::with('member')->orderBy('created_at', 'desc')->get();
        return view('payments.verify', compact('submissions'));
    }

    public function approvePayment(Request $request, $id) {
        $submission = \App\Models\PaymentSubmission::findOrFail($id);
        $submission->status = 'Approved';
        $submission->save();

        $dealer = $submission->member;
        $amount = (float) $submission->amount;

        // Update dealer balance
        $balance = $dealer->dealerBalance;
        if (!$balance) {
            $balance = new \App\Models\DealerBalance([
                'member_id' => $dealer->id,
                'total_amount' => 0.00,
                'paid_amount' => 0.00,
                'due_amount' => 0.00,
            ]);
        }
        $balance->paid_amount += $amount;
        $balance->due_amount = $balance->total_amount - $balance->paid_amount;
        $balance->save();

        $ref = 'TXN-' . mt_rand(1000, 9999);
        while (\App\Models\PassbookTransaction::where('ref', $ref)->exists()) {
            $ref = 'TXN-' . mt_rand(1000, 9999);
        }

        $managerName = auth()->user() ? auth()->user()->name : 'System Admin';

        \App\Models\PassbookTransaction::create([
            'member_id' => $dealer->id,
            'managed_by' => $managerName,
            'type' => 'Payment',
            'amount' => $amount,
            'ref' => $ref,
            'status' => 'Completed',
        ]);

        // Send push notification
        try {
            \App\Services\FcmService::sendPushNotification(
                $dealer,
                'Payment Verified & Approved',
                "Your payment upload of ₹ " . number_format($amount, 2) . " has been approved! Remaining due: ₹ " . number_format($balance->due_amount, 2) . ".",
                [
                    'type' => 'passbook',
                    'deeplink' => 'my-passbook',
                    'deep_link' => 'my-passbook',
                    'ref' => $ref,
                    'amount' => (string) $amount,
                    'due_amount' => (string) $balance->due_amount,
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send push notification: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Payment receipt approved successfully!');
    }

    public function rejectPayment(Request $request, $id) {
        $submission = \App\Models\PaymentSubmission::findOrFail($id);
        $submission->status = 'Rejected';
        $submission->remarks = $request->input('remarks');
        $submission->save();

        $dealer = $submission->member;
        $amount = (float) $submission->amount;

        // Send push notification
        try {
            \App\Services\FcmService::sendPushNotification(
                $dealer,
                'Payment Receipt Rejected',
                "Your payment of ₹ " . number_format($amount, 2) . " was rejected. Reason: " . ($submission->remarks ?: 'Invalid receipt image.'),
                [
                    'type' => 'passbook',
                    'deeplink' => 'my-passbook',
                    'deep_link' => 'my-passbook',
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send push notification: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Payment receipt rejected successfully!');
    }

    public function settings() {
        $whatsappNumber = \App\Models\Setting::get('whatsapp_number', '919876543210');
        return view('settings', compact('whatsappNumber'));
    }

    public function updateSettings(Request $request) {
        $request->validate([
            'whatsapp_number' => 'required|string',
        ]);

        \App\Models\Setting::set('whatsapp_number', $request->whatsapp_number);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
