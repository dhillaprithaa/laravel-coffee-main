<?php

namespace Database\Seeders;

use App\Enums\MenuCategory;
use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'name' => 'Es Kopi Susu',
                'category' => MenuCategory::MINUMAN,
                'price' => 18000,
                'stock' => 50,
            ],
            [
                'name' => 'Americano',
                'category' => MenuCategory::MINUMAN,
                'price' => 15000,
                'stock' => 50,
            ],
            [
                'name' => 'Roti Bakar',
                'category' => MenuCategory::MAKANAN,
                'price' => 12000,
                'stock' => 30,
            ],
        ];

        Menu::insert($menus);
    }
}
