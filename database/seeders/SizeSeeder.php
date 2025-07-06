<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Size::insert([
            ['store_id' => null, 'name' => 'XS'],
            ['store_id' => null, 'name' => 'S'],
            ['store_id' => null, 'name' => 'M'],
            ['store_id' => null, 'name' => 'L'],
            ['store_id' => null, 'name' => 'XL'],
            ['store_id' => null, 'name' => 'XXL'],
        ]);
    }
}
