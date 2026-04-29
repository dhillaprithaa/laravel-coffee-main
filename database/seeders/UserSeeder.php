<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
