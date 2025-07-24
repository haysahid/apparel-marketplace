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
                'id' => 1,
                'store_id' => null,
                'name' => 'T-Shirts',
                'image' => 'category/t-shirt.png'
            ],
            [
                'id' => 2,
                'store_id' => null,
                'name' => 'Jeans',
                'image' => 'category/trouser.png'
            ],
            [
                'id' => 3,
                'store_id' => null,
                'name' => 'Jackets',
                'image' => 'category/jacket.png'
            ],
            [
                'id' => 4,
                'store_id' => null,
                'name' => 'Shoes',
                'image' => 'category/shoe.png'
            ],
            [
                'id' => 5,
                'store_id' => null,
                'name' => 'Accessories',
                'image' => 'category/ribbon.png'
            ],
        ]);
    }
}
