@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Dealer Passbook</h3>
        <div class="form-group" style="margin-bottom: 0;">
            <select class="form-control" style="width: 250px;">
                <option>Select Dealer: JD Accessories</option>
            </select>
        </div>
    </div>

    <div class="grid">
        <div class="card glass">
            <p style="font-size: 12px; color: var(--text-muted);">Current Balance</p>
            <div style="font-size: 24px; font-weight: 700; color: var(--success);">₹ 24,500.00</div>
        </div>
        <div class="card glass">
            <p style="font-size: 12px; color: var(--text-muted);">Available Points</p>
            <div style="font-size: 24px; font-weight: 700; color: var(--primary);">1,250 pts</div>
        </div>
        <div class="card glass">
            <p style="font-size: 12px; color: var(--text-muted);">Total Orders</p>
            <div style="font-size: 24px; font-weight: 700; color: var(--secondary);">42</div>
        </div>
    </div>

    <div class="table-container" style="margin-top: 30px;">
        <h4 style="margin-bottom: 20px;">Transaction Ledger</h4>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Points</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2024-05-05</td>
                    <td><span class="badge badge-success">Credit</span></td>
                    <td>Payment - INV-2024-001</td>
                    <td>₹ 15,000</td>
                    <td>-</td>
                    <td>₹ 24,500</td>
                </tr>
                <tr>
                    <td>2024-05-04</td>
                    <td><span class="badge badge-danger">Debit</span></td>
                    <td>Order - ORD1001</td>
                    <td>-₹ 17,700</td>
                    <td>+500</td>
                    <td>₹ 9,500</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
