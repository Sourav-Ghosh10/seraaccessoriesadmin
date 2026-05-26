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
            'name' => 'required|string|max:255',
            'shop' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|unique:members,email',
            'address' => 'nullable|string',
            'status' => 'required|string',
            'salesman_id' => 'nullable|exists:members,id',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'dealer';
        Member::create($validated);

        return response()->json(['success' => true, 'message' => 'Dealer registered successfully!']);
    }

    public function update(Request $request, $id)
    {
        $dealer = Member::where('role', 'dealer')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shop' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|unique:members,email,' . $id,
            'address' => 'nullable|string',
            'status' => 'required|string',
            'salesman_id' => 'nullable|exists:members,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $dealer->update($validated);

        return response()->json(['success' => true, 'message' => 'Dealer updated successfully!']);
    }
}
