<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TableSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $count = range(1, 10);
        $tables = collect($count)->map(fn($index) => [
            'number' => $index,
        ])->toArray();
        Table::insert($tables);
    }
}
