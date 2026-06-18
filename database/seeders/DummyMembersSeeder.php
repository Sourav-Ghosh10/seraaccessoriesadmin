<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\City;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DummyMembersSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Ensure at least one city exists
        $city = City::first();
        if (!$city) {
            $city = City::create(['city' => 'Mumbai', 'status' => 1]);
        }
        $cityId = $city->id;

        $password = Hash::make('password123');

        $salesmen = [];
        $distributors = [];

        // Insert 20 Salesmen
        for ($i = 1; $i <= 20; $i++) {
            $salesmen[] = Member::create([
                'name' => 'Salesman ' . $faker->firstName,
                'email' => $faker->unique()->safeEmail,
                'mobile' => $faker->numerify('9#########'),
                'password' => $password,
                'role' => 'salesman',
                'status' => 'Active',
                'ref_code' => strtoupper($faker->bothify('SM####')),
                'monthly_target' => $faker->numberBetween(50000, 200000),
            ]);
        }

        // Insert 20 Distributors
        for ($i = 1; $i <= 20; $i++) {
            $distributors[] = Member::create([
                'name' => 'Distributor ' . $faker->company,
                'email' => $faker->unique()->safeEmail,
                'mobile' => $faker->numerify('8#########'),
                'password' => $password,
                'role' => 'distributor',
                'status' => 'Active',
                'dist_id' => strtoupper($faker->bothify('DS####')),
                'gst_no' => strtoupper($faker->bothify('22AAAAA####A1Z#')),
                'address' => $faker->address,
                'city_id' => $cityId,
            ]);
        }

        // Insert 20 Dealers
        for ($i = 1; $i <= 20; $i++) {
            $salesman = $faker->randomElement($salesmen);
            $distributor = $faker->randomElement($distributors);

            Member::create([
                'name' => 'Dealer ' . $faker->name,
                'email' => $faker->unique()->safeEmail,
                'mobile' => $faker->numerify('7#########'),
                'password' => $password,
                'role' => 'dealer',
                'status' => 'Active',
                'shop' => $faker->company . ' Store',
                'ref_code' => strtoupper($faker->bothify('DL####')),
                'gst_no' => strtoupper($faker->bothify('22BBBBB####B1Z#')),
                'address' => $faker->address,
                'city_id' => $cityId,
                'salesman_id' => $salesman->id,
                'dist_id' => $distributor->dist_id,
                'discount_percent' => $faker->randomElement([0, 5, 10, 15]),
            ]);
        }

        $this->command->info('20 Salesmen, 20 Distributors, and 20 Dealers created successfully.');
    }
}
