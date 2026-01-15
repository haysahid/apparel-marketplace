<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            [
                'store_id' => null,
                'name' => 'T-Shirts',
                'image' => 'category/t-shirt.png',
            ],
            [
                'store_id' => null,
                'name' => 'Jeans',
                'image' => 'category/trouser.png'
            ],
            [
                'store_id' => null,
                'name' => 'Jackets',
                'image' => 'category/jacket.png'
            ],
            [
                'store_id' => null,
                'name' => 'Shoes',
                'image' => 'category/shoe.png'
            ],
            [
                'store_id' => null,
                'name' => 'Accessories',
                'image' => 'category/ribbon.png'
            ],
        ]);
    }
}
