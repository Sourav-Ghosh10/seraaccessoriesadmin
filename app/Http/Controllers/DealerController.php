<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DealerController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ref_code'         => 'required|string|max:50|unique:members,ref_code',
            'name'             => 'required|string|max:255',
            'shop'             => 'required|string|max:255',
            'mobile'           => 'required|string|max:15',
            'email'            => 'required|email|unique:members,email',
            'city_id'          => 'required|exists:cities,id',
            'address'          => 'nullable|string',
            'gst_no'           => 'required|string|max:50',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'status'           => 'required|string',
            'salesman_id'      => 'required|exists:members,id',
            'dist_id'          => 'required|string|max:6',
            'password'         => 'required|string|min:6',
            'is_passbook_visible' => 'nullable|boolean',
        ]);

        $validated['is_passbook_visible'] = $request->has('is_passbook_visible') ? $request->boolean('is_passbook_visible') : true;

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'dealer';
        Member::create($validated);

        return response()->json(['success' => true, 'message' => 'Dealer registered successfully!']);
    }

    public function update(Request $request, $id)
    {
        $dealer = Member::where('role', 'dealer')->findOrFail($id);

        $validated = $request->validate([
            'ref_code'         => 'required|string|max:50|unique:members,ref_code,' . $id,
            'name'             => 'required|string|max:255',
            'shop'             => 'required|string|max:255',
            'mobile'           => 'required|string|max:15',
            'email'            => 'required|email|unique:members,email,' . $id,
            'city_id'          => 'required|exists:cities,id',
            'address'          => 'nullable|string',
            'gst_no'           => 'required|string|max:50',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'status'           => 'required|string',
            'salesman_id'      => 'required|exists:members,id',
            'dist_id'          => 'required|string|max:6',
            'is_passbook_visible' => 'nullable|boolean',
        ]);

        $validated['is_passbook_visible'] = $request->has('is_passbook_visible') ? $request->boolean('is_passbook_visible') : true;

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $dealer->update($validated);

        return response()->json(['success' => true, 'message' => 'Dealer updated successfully!']);
    }

    public function updatePoints(Request $request, $id)
    {
        $dealer = Member::where('role', 'dealer')->findOrFail($id);
        
        $request->validate([
            'points' => 'required|numeric'
        ]);

        $currentPoints = $dealer->points_balance;
        $newPoints = (int) $request->points;

        if ($currentPoints !== $newPoints) {
            $difference = $newPoints - $currentPoints;
            \App\Models\RewardTransaction::create([
                'member_id' => $dealer->id,
                'points' => $difference,
                'type' => 'Admin Adjustment'
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Points updated successfully!']);
    }

    public function togglePassbook(Request $request, $id)
    {
        $dealer = Member::where('role', 'dealer')->findOrFail($id);
        
        $request->validate([
            'is_passbook_visible' => 'required|boolean'
        ]);

        $dealer->update([
            'is_passbook_visible' => $request->boolean('is_passbook_visible')
        ]);

        return response()->json(['success' => true, 'message' => 'Passbook visibility updated successfully!']);
    }

    public function destroy($id)
    {
        $dealer = Member::where('role', 'dealer')->findOrFail($id);
        $dealer->delete();

        return response()->json(['success' => true, 'message' => 'Dealer deleted successfully!']);
    }
}
