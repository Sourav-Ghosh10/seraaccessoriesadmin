<?php

namespace App\Http\Controllers;

use App\Models\Member;
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
}
