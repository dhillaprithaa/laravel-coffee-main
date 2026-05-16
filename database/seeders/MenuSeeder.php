<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Enums\MenuCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
