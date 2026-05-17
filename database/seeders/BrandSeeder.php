<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();
        if (!$user) {
            return;
        }

        $brands = [
            ['name' => 'Apple', 'type' => 'both'],
            ['name' => 'Samsung', 'type' => 'both'],
            ['name' => 'Xiaomi', 'type' => 'both'],
            ['name' => 'Oppo', 'type' => 'both'],
            ['name' => 'Vivo', 'type' => 'both'],
            ['name' => 'Realme', 'type' => 'both'],
            ['name' => 'OnePlus', 'type' => 'both'],
            ['name' => 'Motorola', 'type' => 'device'],
            ['name' => 'Nokia', 'type' => 'device'],
            ['name' => 'Google', 'type' => 'device'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['name' => $brand['name'], 'user_id' => $user->id],
                [
                    'slug' => Str::slug($brand['name']),
                    'type' => $brand['type'],
                ]
            );
        }
    }
}
