<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Travel',
            'Food',
            'Stay/Accommodation',
        ];

        foreach ($categories as $category) {
            \App\Models\ExpenseCategory::firstOrCreate(['name' => $category]);
        }
    }
}
