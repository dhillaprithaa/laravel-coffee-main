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
                'description' => 'Es kopi susu dengan campuran gula aren dan susu segar.',
            ],
            [
                'name' => 'Americano',
                'category' => MenuCategory::MINUMAN,
                'price' => 15000,
                'stock' => 50,
                'description' => 'Espresso klasik dengan tambahan air panas.',
            ],
            [
                'name' => 'Roti Bakar',
                'category' => MenuCategory::MAKANAN,
                'price' => 12000,
                'stock' => 30,
                'description' => 'Roti tawar panggang dengan olesan mentega dan gula.',
            ],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
