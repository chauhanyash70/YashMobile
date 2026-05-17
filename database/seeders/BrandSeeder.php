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
        $brands = [
            ['name' => 'Apple', 'type' => 'device'],
            ['name' => 'Samsung', 'type' => 'both'],
            ['name' => 'Xiaomi', 'type' => 'both'],
            ['name' => 'Oppo', 'type' => 'both'],
            ['name' => 'Vivo', 'type' => 'both'],
            ['name' => 'Realme', 'type' => 'both'],
            ['name' => 'OnePlus', 'type' => 'both'],
            ['name' => 'Motorola', 'type' => 'device'],
            ['name' => 'Nokia', 'type' => 'device'],
            ['name' => 'Google', 'type' => 'device'],
            ['name' => 'Huawei', 'type' => 'both'],
            ['name' => 'Honor', 'type' => 'device'],
            ['name' => 'Asus', 'type' => 'device'],
            ['name' => 'Sony', 'type' => 'device'],
            ['name' => 'LG', 'type' => 'device'],
            ['name' => 'Tecno', 'type' => 'device'],
            ['name' => 'Infinix', 'type' => 'device'],
            ['name' => 'Lava', 'type' => 'device'],
            ['name' => 'Micromax', 'type' => 'device'],
            ['name' => 'itel', 'type' => 'device'],
            // Accessory Brands
            ['name' => 'JBL', 'type' => 'accessory'],
            ['name' => 'boat', 'type' => 'accessory'],
            ['name' => 'Portronics', 'type' => 'accessory'],
            ['name' => 'Ambrane', 'type' => 'accessory'],
            ['name' => 'Syska', 'type' => 'accessory'],
            ['name' => 'Mi', 'type' => 'accessory'],
            ['name' => 'Anker', 'type' => 'accessory'],
            ['name' => 'Belkin', 'type' => 'accessory'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['name' => $brand['name']],
                [
                    'slug' => Str::slug($brand['name']),
                    'type' => $brand['type'],
                ]
            );
        }
    }
}
