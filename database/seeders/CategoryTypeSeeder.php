<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryType;

class CategoryTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Flights',
            'Hotels',
            'Car Rentals',
            'Electronics',
            'Food & Beverage',
            'Furniture',
        ];

        foreach ($types as $type) {
            CategoryType::firstOrCreate(['name' => $type]);
        }
    }
}
