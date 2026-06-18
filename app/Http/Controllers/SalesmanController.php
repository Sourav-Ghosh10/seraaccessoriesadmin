<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SalesmanController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|string|min:6',
            'ref_code' => 'required|alpha_num|size:6|unique:members,ref_code',
            'status' => 'required|string',
            'monthly_target' => 'required|numeric|min:0',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'salesman';
        Member::create($validated);

        return response()->json(['success' => true, 'message' => 'Salesman added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $salesman = Member::where('role', 'salesman')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|unique:members,email,' . $id,
            'ref_code' => 'required|alpha_num|size:6|unique:members,ref_code,' . $id,
            'status' => 'required|string',
            'monthly_target' => 'required|numeric|min:0',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $salesman->update($validated);

        return response()->json(['success' => true, 'message' => 'Salesman updated successfully!']);
    }

    public function performance($id)
    {
        $salesman = Member::where('role', 'salesman')->findOrFail($id);

        // 1. Dealers count assigned to this salesman (Active currently)
        $dealersCount = Member::where('role', 'dealer')
            ->where('salesman_id', $salesman->id)
            ->count();

        // 2. Orders placed by these dealers (Current Month)
        $ordersCount = Order::whereHas('member', function ($q) use ($salesman) {
            $q->where('salesman_id', $salesman->id);
        })
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();

        // 3. Revenue from invoices for orders placed by these dealers (Current Month)
        $totalRevenue = Invoice::whereHas('order.member', function ($q) use ($salesman) {
            $q->where('salesman_id', $salesman->id);
        })
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('amount');

        // 4. Monthly Target and Completion Percentage
        $monthlyTarget = $salesman->monthly_target ? (float) $salesman->monthly_target : (float) Setting::get('salesman_monthly_target', 100000);
        $targetCompletion = $monthlyTarget > 0 ? min(100, round(($totalRevenue / $monthlyTarget) * 100)) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $salesman->name,
                'dealers_count' => $dealersCount,
                'orders_count' => $ordersCount,
                'total_revenue' => (float) $totalRevenue,
                'target_completion' => (int) $targetCompletion,
                'monthly_target' => $monthlyTarget
            ]
        ]);
    }
    public function updatePoints(Request $request, $id)
    {
        $salesman = Member::where('role', 'salesman')->findOrFail($id);
        
        $request->validate([
            'points' => 'required|numeric'
        ]);

        $currentPoints = $salesman->points_balance;
        $newPoints = (int) $request->points;

        if ($currentPoints !== $newPoints) {
            $difference = $newPoints - $currentPoints;
            \App\Models\RewardTransaction::create([
                'member_id' => $salesman->id,
                'points' => $difference,
                'type' => 'Admin Adjustment'
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Points updated successfully!']);
    }
}
