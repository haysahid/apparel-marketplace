<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Brand::insert([
            [
                'store_id' => 1,
                'name' => 'Yins',
                'description' => 'Deskripsi Yins',
                'logo' => 'brand/yins.png',
                'website' => url('catalog?brands=Yins'),
            ],
            [
                'store_id' => 1,
                'name' => 'Keke',
                'description' => 'Deskripsi Keke',
                'logo' => 'brand/keke.png',
                'website' => url('catalog?brands=Keke'),
            ],
            [
                'store_id' => 1,
                'name' => 'Nibras',
                'description' => 'Deskripsi Nibras',
                'logo' => 'brand/nibras.png',
                'website' => url('catalog?brands=Nibras'),
            ],
            [
                'store_id' => 1,
                'name' => 'Alnita',
                'description' => 'Deskripsi Alnita',
                'logo' => 'brand/alnita.png',
                'website' => url('catalog?brands=Alnita'),
            ],
            [
                'store_id' => 1,
                'name' => 'Ethica',
                'description' => 'Deskripsi Ethica',
                'logo' => 'brand/ethica.png',
                'website' => url('catalog?brands=Ethica'),
            ],
        ]);
    }
}
