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
        
        $rules = [
            'status' => 'required|in:Pending,Approved,Rejected',
        ];

        if ($request->status == 'Approved') {
            $rules['admin_receipt'] = 'required|file|mimes:pdf,png,jpg,jpeg|max:20480';
            $rules['approved_amount'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        if ($request->status == 'Approved') {
            if ($request->hasFile('admin_receipt')) {
                $path = $request->file('admin_receipt')->store('expenses/admin_receipts', 'public');
                $expense->admin_receipt_path = $path;
                
                if (!$expense->receipt_photo_path) {
                    $expense->receipt_photo_path = $path;
                }
            }

            if ($request->has('approved_amount') && $request->approved_amount !== null && $request->approved_amount !== '') {
                $expense->approved_amount = $request->approved_amount;
            } else {
                $expense->approved_amount = $expense->amount;
            }
        }

        $expense->status = $request->status;
        $expense->save();

        return redirect()->route('expenses.index')->with('success', 'Expense status updated successfully.');
    }
}
