<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function login() { return view('auth.login'); }
    
    public function dashboard(Request $request) { 
        if ($request->has('role')) {
            session(['role' => $request->role]);
        }
        $role = session('role', 'Admin');
        return view('dashboard', compact('role'));
    }
    
    public function dealers() {
        $dealers = [
            ['id' => 1, 'name' => 'John Doe', 'shop' => 'JD Accessories', 'mobile' => '9876543210', 'status' => 'Active'],
            ['id' => 2, 'name' => 'Jane Smith', 'shop' => 'Smith Stores', 'mobile' => '9876543211', 'status' => 'Inactive'],
            ['id' => 3, 'name' => 'Bob Johnson', 'shop' => 'Bob\'s Shop', 'mobile' => '9876543212', 'status' => 'Pending'],
        ];
        return view('dealers', compact('dealers'));
    }

    public function salesmen() {
        $salesmen = [
            ['id' => 1, 'name' => 'Alice Smith', 'emp_id' => 'EMP001', 'ref_code' => 'ALICE123', 'status' => 'Active'],
            ['id' => 2, 'name' => 'Charlie Brown', 'emp_id' => 'EMP002', 'ref_code' => 'CHARLIE456', 'status' => 'Active'],
        ];
        return view('salesmen', compact('salesmen'));
    }

    public function distributors() {
        $distributors = [
            ['id' => 1, 'name' => 'Global Logistics', 'contact' => 'Michael Scott', 'area' => 'New York', 'status' => 'Active'],
        ];
        return view('distributors', compact('distributors'));
    }

    public function orderRequests() {
        $orders = [
            ['id' => 'REQ-2001', 'dealer' => 'John Doe', 'type' => 'Text', 'status' => 'Pending', 'date' => '2024-05-07 10:00 AM'],
            ['id' => 'REQ-2002', 'dealer' => 'Jane Smith', 'type' => 'Voice', 'status' => 'Pending', 'date' => '2024-05-07 10:15 AM'],
            ['id' => 'REQ-2003', 'dealer' => 'Bob Johnson', 'type' => 'Photo', 'status' => 'Processing', 'date' => '2024-05-07 10:30 AM'],
            ['id' => 'REQ-2004', 'dealer' => 'Sam Wilson', 'type' => 'Call', 'status' => 'Pending', 'date' => '2024-05-07 10:45 AM'],
        ];
        return view('orders.requests', compact('orders'));
    }

    public function ordersList() {
        $finalOrders = [
            ['id' => 'ORD-5580', 'dealer' => 'John Doe', 'amount' => '₹ 15,200', 'status' => 'Confirmed', 'date' => '2024-05-06'],
            ['id' => 'ORD-5581', 'dealer' => 'Jane Smith', 'amount' => '₹ 8,400', 'status' => 'Processing', 'date' => '2024-05-06'],
        ];
        return view('orders.index', compact('finalOrders'));
    }

    public function showOrder($id) {
        // Mock order details
        $order = [
            'id' => $id,
            'dealer' => 'John Doe',
            'date' => '2024-05-06',
            'status' => 'Confirmed',
            'delivery' => [
                'expected_date' => '2024-05-10',
                'remarks' => 'Bus No: AR-01-2234, Driver Contact: 9876543210. Deliver to main station.'
            ],
            'items' => [
                ['name' => 'Rear Bumper Guard', 'qty' => 2, 'price' => 3500],
                ['name' => 'Premium Floor Mats', 'qty' => 1, 'price' => 4200],
                ['name' => 'Door Visors (Set of 4)', 'qty' => 3, 'price' => 1500]
            ]
        ];
        return view('orders.show', compact('order'));
    }

    public function createOrder() {
        return view('orders.create');
    }

    public function delivery() {
        return view('delivery');
    }

    public function invoices() {
        return view('invoices');
    }

    public function rewards() {
        return view('rewards');
    }

    public function priceList() {
        return view('price-list');
    }

    public function passbook() {
        return view('passbook');
    }
}
