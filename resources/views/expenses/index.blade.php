@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Salesman Expenses</h3>
    </div>
    
    @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #fff; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
        <form method="GET" action="{{ route('expenses.index') }}" style="display: flex; gap: 10px;">
            <select name="status" class="form-control" style="width: 180px; background: #1e293b; border-color: rgba(255,255,255,0.1); color: #fff;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Salesman</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Receipt</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->created_at->format('d M Y, h:i A') }}</td>
                    <td>{{ $expense->salesman->name ?? '—' }}</td>
                    <td>{{ $expense->category->name ?? '—' }}</td>
                    <td>{{ $expense->description ?? '—' }}</td>
                    <td style="font-weight: bold; color: var(--primary);">₹{{ number_format($expense->amount, 2) }}</td>
                    <td>
                        @if($expense->receipt_photo_path)
                            <a href="{{ asset('uploads/' . $expense->receipt_photo_path) }}" target="_blank" style="color: var(--secondary); text-decoration: none;">
                                <i class="fas fa-image"></i> View Receipt
                            </a>
                        @else
                            <span style="color: var(--text-muted);">No Receipt</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $expense->status == 'Approved' ? 'badge-success' : ($expense->status == 'Pending' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $expense->status }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            @if($expense->status == 'Pending')
                                <form method="POST" action="{{ route('expenses.status.update', $expense->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Approved">
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px; background: #22c55e; border-color: #22c55e;">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('expenses.status.update', $expense->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Rejected">
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px; background: #ef4444; border-color: #ef4444;">Reject</button>
                                </form>
                            @else
                                <span style="color: var(--text-muted); font-size: 12px;">Processed</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 20px;">No expenses found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
