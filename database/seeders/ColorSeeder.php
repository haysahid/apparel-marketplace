<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Color::insert([
            [
                'store_id' => null,
                'name' => 'Merah',
                'hex_code' => '#FF0000',
            ],
            [
                'store_id' => null,
                'name' => 'Biru',
                'hex_code' => '#0000FF',
            ],
            [
                'store_id' => null,
                'name' => 'Hijau',
                'hex_code' => '#00FF00',
            ],
            [
                'store_id' => null,
                'name' => 'Kuning',
                'hex_code' => '#FFFF00',
            ],
            [
                'store_id' => null,
                'name' => 'Hitam',
                'hex_code' => '#000000',
            ],
            [
                'store_id' => null,
                'name' => 'Putih',
                'hex_code' => '#FFFFFF',
            ],
            [
                'store_id' => null,
                'name' => 'Ungu',
                'hex_code' => '#800080',
            ],
            [
                'store_id' => null,
                'name' => 'Abu-abu',
                'hex_code' => '#808080',
            ],
            [
                'store_id' => null,
                'name' => 'Coklat',
                'hex_code' => '#A52A2A',
            ],
            [
                'store_id' => null,
                'name' => 'Oranye',
                'hex_code' => '#FFA500',
            ],
            [
                'store_id' => null,
                'name' => 'Pink',
                'hex_code' => '#FFC0CB',
            ],
            [
                'store_id' => null,
                'name' => 'Emas',
                'hex_code' => '#FFD700',
            ],
            [
                'store_id' => null,
                'name' => 'Olive',
                'hex_code' => '#808000',
            ],
        ]);
    }
}
