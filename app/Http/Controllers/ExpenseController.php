<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Expense;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['salesman', 'category']);

        if ($request->tab != 'reimbursements') {
            if ($request->has('salesman_id') && $request->salesman_id != '') {
                $query->where('salesman_id', $request->salesman_id);
            }
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }
        }

        $expenses = $query->orderBy('created_at', 'desc')->get();
        $salesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get();
        $categories = \App\Models\ExpenseCategory::orderBy('name')->get();

        $reimbQuery = \App\Models\Reimbursement::with('salesman');
        $reimbSalesmanId = $request->reimb_salesman_id ?? ($request->tab == 'reimbursements' ? $request->salesman_id : null);

        if ($reimbSalesmanId != '') {
            $reimbQuery->where('salesman_id', $reimbSalesmanId);
        }
        $reimbursements = $reimbQuery->orderBy('created_at', 'desc')->get();

        return view('expenses.index', compact('expenses', 'salesmen', 'categories', 'reimbursements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'salesman_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0.01',
            'document' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:20480',
            'description' => 'nullable|string|max:1000',
        ]);

        $photoPath = null;
        if ($request->hasFile('document')) {
            $photoPath = $request->file('document')->store('reimbursements', 'public');
        }

        \App\Models\Reimbursement::create([
            'salesman_id' => $request->salesman_id,
            'amount' => $request->amount,
            'description' => $request->description ?: 'Reimbursement',
            'document_path' => $photoPath,
            'status' => 'Approved',
        ]);

        return redirect()->route('expenses.index', ['tab' => 'reimbursements'])->with('success', 'Reimbursement added successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        
        $rules = [
            'status' => 'required|in:Pending,Approved,Rejected',
            'admin_receipt' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:20480',
            'approved_amount' => 'nullable|numeric|min:0',
        ];

        $request->validate($rules);

        if ($request->status == 'Approved') {
            if ($request->hasFile('admin_receipt')) {
                $path = $request->file('admin_receipt')->store('expenses/admin_receipts', 'public');
                $expense->admin_receipt_path = $path;
                
                if (!$expense->receipt_photo_path) {
                    $expense->receipt_photo_path = $path;
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('expenses', 'approved_amount')) {
                if ($request->has('approved_amount') && $request->approved_amount !== null && $request->approved_amount !== '') {
                    $expense->approved_amount = $request->approved_amount;
                } else {
                    $expense->approved_amount = $expense->amount;
                }
            }
        }

        $expense->status = $request->status;
        $expense->save();

        return redirect()->route('expenses.index')->with('success', 'Expense status updated successfully.');
    }
}
