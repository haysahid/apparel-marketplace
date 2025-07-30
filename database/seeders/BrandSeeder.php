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
                'name' => 'Yins',
                'description' => 'Deskripsi Yins',
                'logo' => 'brand/yins.png',
                'website' => url('catalog?brands=Yins'),
            ],
            [
                'name' => 'Keke',
                'description' => 'Deskripsi Keke',
                'logo' => 'brand/keke.png',
                'website' => url('catalog?brands=Keke'),
            ],
            [
                'name' => 'Nibras',
                'description' => 'Deskripsi Nibras',
                'logo' => 'brand/nibras.png',
                'website' => url('catalog?brands=Nibras'),
            ],
            [
                'name' => 'Alnita',
                'description' => 'Deskripsi Alnita',
                'logo' => 'brand/alnita.png',
                'website' => url('catalog?brands=Alnita'),
            ],
            [
                'name' => 'Ethica',
                'description' => 'Deskripsi Ethica',
                'logo' => 'brand/ethica.png',
                'website' => url('catalog?brands=Ethica'),
            ],
        ]);
    }
}
