<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DistributorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:6',
            'status' => 'required|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'distributor';
        $validated['mobile'] = $validated['phone']; // Map phone to mobile column
        Member::create($validated);

        return response()->json(['success' => true, 'message' => 'Distributor registered successfully!']);
    }

    public function update(Request $request, $id)
    {
        $distributor = Member::where('role', 'distributor')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,' . $id,
            'phone' => 'required|string|max:15',
            'status' => 'required|string',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        
        $validated['mobile'] = $validated['phone']; // Map phone to mobile column

        $distributor->update($validated);

        return response()->json(['success' => true, 'message' => 'Distributor updated successfully!']);
    }
}
