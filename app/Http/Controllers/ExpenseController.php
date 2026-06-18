<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Expense;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['salesman', 'category']);

        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $expenses = $query->orderBy('created_at', 'desc')->get();

        return view('expenses.index', compact('expenses'));
    }

    public function updateStatus(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
        ]);

        $expense->status = $request->status;
        $expense->save();

        return redirect()->route('expenses.index')->with('success', 'Expense status updated successfully.');
    }
}
