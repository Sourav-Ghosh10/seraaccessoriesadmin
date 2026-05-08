@extends('layouts.app')

@section('content')
<div style="margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
    <a href="{{ route('orders.index') }}" class="btn glass" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 0;">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h3 style="margin: 0;">Order Details: {{ $order['id'] }}</h3>
</div>

<div class="card" style="padding: 40px; background: #0f172a; border: 1px solid var(--glass-border); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
    <!-- Header Info -->
    <div style="display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 30px;">
        <div>
            <p style="margin: 0; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Dealer Information</p>
            <h2 style="margin: 10px 0 5px 0; color: var(--primary);">{{ $order['dealer'] }}</h2>
            <p style="margin: 0; color: var(--text-muted);">Status: <span class="badge {{ $order['status'] == 'Confirmed' ? 'badge-success' : 'badge-warning' }}">{{ $order['status'] }}</span></p>
        </div>
        <div style="text-align: right;">
            <p style="margin: 0; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Order Date</p>
            <h3 style="margin: 10px 0 0 0;">{{ \Carbon\Carbon::parse($order['date'])->format('d M, Y') }}</h3>
            <p style="margin: 5px 0 0 0; color: var(--text-muted);">Invoice #INV-{{ rand(1000, 9999) }}</p>
        </div>
    </div>

    <!-- Delivery Status Section -->
    <div style="background: rgba(154, 90, 58, 0.05); border: 1px solid rgba(154, 90, 58, 0.2); border-radius: 15px; padding: 25px; margin-bottom: 40px; display: grid; grid-template-columns: 200px 1fr; gap: 30px; align-items: center;">
        <div>
            <p style="margin: 0; color: var(--primary); font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700;">Expected Delivery</p>
            <h3 style="margin: 5px 0 0 0; color: #fff;">{{ \Carbon\Carbon::parse($order['delivery']['expected_date'])->format('d M, Y') }}</h3>
        </div>
        <div style="border-left: 1px solid rgba(255,255,255,0.1); padding-left: 30px;">
            <p style="margin: 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700;">Delivery Remarks / Transport Details</p>
            <p style="margin: 5px 0 0 0; color: #cbd5e1; line-height: 1.6;">{{ $order['delivery']['remarks'] }}</p>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-container" style="background: rgba(0,0,0,0.2); border-radius: 15px; padding: 15px; margin-bottom: 40px;">
        <table style="width: 100%;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 13px;">PRODUCT DESCRIPTION</th>
                    <th style="padding: 15px; text-align: center; color: var(--text-muted); font-size: 13px;">QUANTITY</th>
                    <th style="padding: 15px; text-align: right; color: var(--text-muted); font-size: 13px;">UNIT PRICE</th>
                    <th style="padding: 15px; text-align: right; color: var(--text-muted); font-size: 13px;">TOTAL AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @foreach($order['items'] as $item)
                    @php 
                        $total = $item['qty'] * $item['price'];
                        $subtotal += $total;
                    @endphp
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 20px 15px;">
                            <div style="font-weight: 600;">{{ $item['name'] }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">SKU: SERA-{{ rand(100,999) }}</div>
                        </td>
                        <td style="padding: 20px 15px; text-align: center;">{{ $item['qty'] }}</td>
                        <td style="padding: 20px 15px; text-align: right;">₹ {{ number_format($item['price'], 2) }}</td>
                        <td style="padding: 20px 15px; text-align: right; font-weight: 700;">₹ {{ number_format($total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary -->
    <div style="display: flex; justify-content: flex-end;">
        <div style="width: 350px; background: rgba(255,255,255,0.02); padding: 25px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: var(--text-muted);">Subtotal</span>
                <span style="font-weight: 600;">₹ {{ number_format($subtotal, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: var(--text-muted);">GST (18%)</span>
                <span style="font-weight: 600;">₹ {{ number_format($subtotal * 0.18, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-top: 2px solid var(--primary); padding-top: 15px; margin-top: 15px;">
                <span style="font-weight: 800; font-size: 16px;">GRAND TOTAL</span>
                <span style="font-weight: 800; font-size: 22px; color: var(--primary);">₹ {{ number_format($subtotal * 1.18, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Footer Actions -->
    <div style="margin-top: 50px; display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px;">
        <button class="btn btn-primary" onclick="window.print()" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">
            <i class="fas fa-print"></i> Print Invoice
        </button>
        <button class="btn glass" style="padding: 12px 35px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.05); color: #fff;">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button>
    </div>
</div>
@endsection
