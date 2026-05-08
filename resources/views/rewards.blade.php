@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Reward Point Management</h3>
    </div>

    <div class="grid">
        <div class="card">
            <h4>Dealer Points</h4>
            <div style="font-size: 24px; font-weight: 700; color: var(--primary); margin-top: 10px;">45,200 pts</div>
            <div style="font-size: 12px; color: var(--text-muted);">Total distributed this month</div>
        </div>
        <div class="card">
            <h4>Salesman Points</h4>
            <div style="font-size: 24px; font-weight: 700; color: var(--secondary); margin-top: 10px;">12,800 pts</div>
            <div style="font-size: 12px; color: var(--text-muted);">Total distributed this month</div>
        </div>
    </div>

    <div class="table-container" style="margin-top: 30px;">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entity</th>
                    <th>Order #</th>
                    <th>Points Earned</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2024-05-01</td>
                    <td>JD Accessories</td>
                    <td>ORD1001</td>
                    <td>+500</td>
                    <td><span class="badge badge-success">Order Points</span></td>
                </tr>
                <tr>
                    <td>2024-05-02</td>
                    <td>Alice Smith (Sales)</td>
                    <td>ORD1001</td>
                    <td>+100</td>
                    <td><span class="badge badge-success">Referral</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
