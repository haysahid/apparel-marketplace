<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::insert([
            [
                'store_id' => null,
                'name' => 'pcs',
                'description' => 'Satuan barang',
            ],
            [
                'store_id' => null,
                'name' => 'set',
                'description' => 'Satu set barang',
            ],
            [
                'store_id' => null,
                'name' => 'pasang',
                'description' => 'Sepasang barang',
            ],
        ]);
    }
}
