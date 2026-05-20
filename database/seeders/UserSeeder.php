<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\RoleType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'role' => RoleType::PIMPINAN,
            'username' => 'admin',
            'name' => 'Admin Pimpinan',
        ]);

        User::factory()->create([
            'role' => RoleType::STAFF,
            'username' => 'kasir',
            'name' => 'Staff Kasir',
        ]);
    }
}
