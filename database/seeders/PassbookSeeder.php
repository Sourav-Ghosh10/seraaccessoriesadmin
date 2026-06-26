<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\DealerBalance;
use App\Models\PassbookTransaction;

class PassbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Salesmen exist
        $alice = Member::where('role', 'salesman')->where('name', 'Alice Smith')->first();
        if (!$alice) {
            $alice = Member::create([
                'name' => 'Alice Smith',
                'mobile' => '9998887776',
                'email' => 'alice@example.com',
                'password' => bcrypt('password'),
                'role' => 'salesman',
                'emp_id' => 'EMP001',
                'ref_code' => 'ALICE123',
                'status' => 'Active'
            ]);
        }

        $charlie = Member::where('role', 'salesman')->where('name', 'Charlie Brown')->first();
        if (!$charlie) {
            $charlie = Member::create([
                'name' => 'Charlie Brown',
                'mobile' => '9998887777',
                'email' => 'charlie@example.com',
                'password' => bcrypt('password'),
                'role' => 'salesman',
                'emp_id' => 'EMP002',
                'ref_code' => 'CHARLIE123',
                'status' => 'Active'
            ]);
        }

        // 2. Ensure Dealers exist and have balances seeded
        // JD Accessories
        $jdDealer = Member::where('role', 'dealer')->where('shop', 'JD Accessories')->first();
        if (!$jdDealer) {
            $jdDealer = Member::create([
                'name' => 'John Doe',
                'shop' => 'JD Accessories',
                'mobile' => '9876543210',
                'email' => 'john@example.com',
                'address' => '123, Main St',
                'role' => 'dealer',
                'status' => 'Active',
                'salesman_id' => $alice->id,
                'password' => bcrypt('password')
            ]);
        }

        // Smith Stores
        $smithDealer = Member::where('role', 'dealer')->where('shop', 'Smith Stores')->first();
        if (!$smithDealer) {
            $smithDealer = Member::create([
                'name' => 'Jane Smith',
                'shop' => 'Smith Stores',
                'mobile' => '9876543211',
                'email' => 'jane@example.com',
                'address' => '456, Side St',
                'role' => 'dealer',
                'status' => 'Active',
                'salesman_id' => $charlie->id,
                'password' => bcrypt('password')
            ]);
        }

        // Bob's Shop
        $bobDealer = Member::where('role', 'dealer')->where('shop', "Bob's Shop")->first();
        if (!$bobDealer) {
            $bobDealer = Member::create([
                'name' => 'Bob Johnson',
                'shop' => "Bob's Shop",
                'mobile' => '9876543212',
                'email' => 'bob@example.com',
                'address' => '789, North Ave',
                'role' => 'dealer',
                'status' => 'Active',
                'salesman_id' => $alice->id,
                'password' => bcrypt('password')
            ]);
        }

        // 3. Clear existing balances and transactions to seed fresh
        DealerBalance::truncate();
        PassbookTransaction::truncate();

        // 4. Seed Dealer Balances
        DealerBalance::create([
            'member_id' => $jdDealer->id,
            'total_amount' => 50000.00,
            'paid_amount' => 25500.00,
            'due_amount' => 24500.00,
        ]);

        DealerBalance::create([
            'member_id' => $smithDealer->id,
            'total_amount' => 35000.00,
            'paid_amount' => 35000.00,
            'due_amount' => 0.00,
        ]);

        DealerBalance::create([
            'member_id' => $bobDealer->id,
            'total_amount' => 75000.00,
            'paid_amount' => 15000.00,
            'due_amount' => 60000.00,
        ]);

        // 5. Seed Passbook Transactions
        // JD Accessories Transactions
        PassbookTransaction::create([
            'member_id' => $jdDealer->id,
            'managed_by' => 'Alice Smith',
            'type' => 'Order',
            'amount' => 50000.00,
            'ref' => 'ORD-5580',
            'status' => 'Confirmed',
            'created_at' => '2026-05-11 10:00:00'
        ]);

        PassbookTransaction::create([
            'member_id' => $jdDealer->id,
            'managed_by' => 'Alice Smith',
            'type' => 'Payment',
            'amount' => 25500.00,
            'ref' => 'TXN-9981',
            'status' => 'Completed',
            'created_at' => '2026-05-12 14:30:00'
        ]);

        // Smith Stores Transactions
        PassbookTransaction::create([
            'member_id' => $smithDealer->id,
            'managed_by' => 'Charlie Brown',
            'type' => 'Order',
            'amount' => 35000.00,
            'ref' => 'ORD-5565',
            'status' => 'Delivered',
            'created_at' => '2026-05-07 09:15:00'
        ]);

        PassbookTransaction::create([
            'member_id' => $smithDealer->id,
            'managed_by' => 'Charlie Brown',
            'type' => 'Payment',
            'amount' => 35000.00,
            'ref' => 'TXN-9975',
            'status' => 'Completed',
            'created_at' => '2026-05-10 11:00:00'
        ]);

        // Bob's Shop Transactions
        PassbookTransaction::create([
            'member_id' => $bobDealer->id,
            'managed_by' => 'System Admin',
            'type' => 'Order',
            'amount' => 75000.00,
            'ref' => 'ORD-5572',
            'status' => 'Confirmed',
            'created_at' => '2026-05-09 16:45:00'
        ]);

        PassbookTransaction::create([
            'member_id' => $bobDealer->id,
            'managed_by' => 'System Admin',
            'type' => 'Payment',
            'amount' => 15000.00,
            'ref' => 'TXN-9952',
            'status' => 'Completed',
            'created_at' => '2026-05-10 15:20:00'
        ]);

        // Seed Distributor Balances & Transactions
        $distributors = Member::whereRaw('LOWER(role) = ?', ['distributor'])->get();
        foreach ($distributors as $dist) {
            DealerBalance::create([
                'member_id' => $dist->id,
                'total_amount' => 85000.00,
                'paid_amount' => 50000.00,
                'due_amount' => 35000.00,
            ]);

            PassbookTransaction::create([
                'member_id' => $dist->id,
                'managed_by' => 'System Admin',
                'type' => 'Order',
                'amount' => 85000.00,
                'ref' => 'ORD-' . mt_rand(1000, 9999),
                'status' => 'Confirmed',
                'created_at' => now()->subDays(3)
            ]);

            PassbookTransaction::create([
                'member_id' => $dist->id,
                'managed_by' => 'System Admin',
                'type' => 'Payment',
                'amount' => 50000.00,
                'ref' => 'TXN-' . mt_rand(1000, 9999),
                'status' => 'Completed',
                'created_at' => now()->subDays(1)
            ]);
        }
    }
}
