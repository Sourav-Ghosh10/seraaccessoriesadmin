<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Users (Admins)
        \App\Models\User::create([
            'name' => 'Sourav Ghosh',
            'email' => 'sourav@shera.com',
            'role' => 'Admin',
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        \App\Models\User::create([
            'name' => 'Amit Kumar',
            'email' => 'amit@shera.com',
            'role' => 'Operations',
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        // App Members (Salesmen first so they can be assigned to dealers)
        $salesman = \App\Models\Member::create([
            'name' => 'Alice Smith', 
            'mobile' => '9998887776',
            'email' => 'alice@example.com',
            'password' => bcrypt('password'),
            'role' => 'salesman',
            'emp_id' => 'EMP001', 
            'ref_code' => 'ALICE123', 
            'status' => 'Active'
        ]);

        $dealer1 = \App\Models\Member::create([
            'name' => 'John Doe', 
            'shop' => 'JD Accessories', 
            'mobile' => '9876543210', 
            'email' => 'john@example.com',
            'address' => '123, Main St',
            'role' => 'dealer',
            'status' => 'Active',
            'salesman_id' => $salesman->id,
            'password' => bcrypt('password')
        ]);

        \App\Models\Member::create([
            'name' => 'Jane Smith', 
            'shop' => 'Smith Stores', 
            'mobile' => '9876543211', 
            'email' => 'jane@example.com',
            'address' => '456, Side St',
            'role' => 'dealer',
            'status' => 'Inactive',
            'salesman_id' => $salesman->id,
            'password' => bcrypt('password')
        ]);
        
        \App\Models\Member::create([
            'name' => 'Global Logistics', 
            'email' => 'contact@global.com', 
            'mobile' => '+91 98765 43210', 
            'password' => bcrypt('password'),
            'role' => 'distributor',
            'status' => 'Active'
        ]);
        
        // Estimates
        \App\Models\Estimate::create(['member_id' => $dealer1->id, 'type' => 'Text', 'status' => 'Pending']);
        
        // Orders
        $order1 = \App\Models\Order::create(['member_id' => $dealer1->id, 'order_number' => 'ORD-5580', 'type' => 'Text', 'amount' => 15200, 'status' => 'Confirmed']);
        
        // Order Items
        \App\Models\OrderItem::create(['order_id' => $order1->id, 'name' => 'Rear Bumper Guard', 'qty' => 2, 'price' => 3500]);
        \App\Models\OrderItem::create(['order_id' => $order1->id, 'name' => 'Premium Floor Mats', 'qty' => 1, 'price' => 4200]);
    }
}
