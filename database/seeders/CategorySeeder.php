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
                'image' => 'category/t-shirts.jpg'
            ],
            [
                'id' => 2,
                'store_id' => null,
                'name' => 'Jeans',
                'image' => 'category/jeans.jpg'
            ],
            [
                'id' => 3,
                'store_id' => null,
                'name' => 'Jackets',
                'image' => 'category/jackets.jpg'
            ],
            [
                'id' => 4,
                'store_id' => null,
                'name' => 'Shoes',
                'image' => 'category/shoes.jpg'
            ],
            [
                'id' => 5,
                'store_id' => null,
                'name' => 'Accessories',
                'image' => 'category/accessories.jpg'
            ],
        ]);
    }
}
