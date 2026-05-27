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
            'ref_code' => 'required|string|unique:members,ref_code',
            'status' => 'required|string',
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
            'ref_code' => 'required|string|unique:members,ref_code,' . $id,
            'status' => 'required|string',
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

        // 1. Dealers count assigned to this salesman
        $dealersCount = Member::where('role', 'dealer')
            ->where('salesman_id', $salesman->id)
            ->count();

        // 2. Total orders placed by these dealers
        $ordersCount = Order::whereHas('member', function ($q) use ($salesman) {
            $q->where('salesman_id', $salesman->id);
        })->count();

        // 3. Total revenue from invoices for orders placed by these dealers
        $totalRevenue = Invoice::whereHas('order.member', function ($q) use ($salesman) {
            $q->where('salesman_id', $salesman->id);
        })->sum('amount');

        // 4. Monthly Target and Completion Percentage
        $monthlyTarget = (float) Setting::get('salesman_monthly_target', 100000);
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
}
