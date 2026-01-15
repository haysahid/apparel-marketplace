<?php

namespace Database\Seeders;

use App\Models\MembershipType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MembershipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MembershipType::insert([
            [
                'store_id' => 1,
                'group' => 'Member',
                'name' => 'Member',
                'alias' => null,
                'slug' => 'member',
                'level' => 1,
                'item_discount_percentage' => 10,
                'shipping_discount_percentage' => 0,
                'min_purchase_amount' => 0,
                'hex_code_bg' => '#808080',
                'hex_code_text' => '#FFFFFF',
                'description' => 'Keanggotaan Member memberikan diskon 10% untuk setiap pembelian produk di toko kami.',
            ],
            [
                'store_id' => 1,
                'group' => 'Reseller',
                'name' => 'Reseller',
                'alias' => 'Gold 1',
                'slug' => 'reseller-gold-1',
                'level' => 2,
                'item_discount_percentage' => 15,
                'shipping_discount_percentage' => 0,
                'min_purchase_amount' => 0,
                'hex_code_bg' => '#C28C42',
                'hex_code_text' => '#FFFFFF',
                'description' => 'Keanggotaan Reseller memberikan diskon 15% untuk setiap pembelian produk di toko kami.',
            ],
            [
                'store_id' => 1,
                'group' => 'Reseller',
                'name' => 'Reseller',
                'alias' => 'Gold 2',
                'slug' => 'reseller-gold-2',
                'level' => 3,
                'item_discount_percentage' => 20,
                'shipping_discount_percentage' => 0,
                'min_purchase_amount' => 0,
                'hex_code_bg' => '#C28C42',
                'hex_code_text' => '#FFFFFF',
                'description' => 'Keanggotaan Reseller memberikan diskon 20% untuk setiap pembelian produk di toko kami.',
            ],
            [
                'store_id' => 1,
                'group' => 'Agen',
                'name' => 'Agen',
                'alias' => 'Platinum 1',
                'slug' => 'agen-platinum-1',
                'level' => 4,
                'item_discount_percentage' => 30,
                'shipping_discount_percentage' => 0,
                'min_purchase_amount' => 0,
                'hex_code_bg' => '#4d6fbd',
                'hex_code_text' => '#FFFFFF',
                'description' => 'Keanggotaan Agen memberikan diskon 30% untuk setiap pembelian produk di toko kami.',
            ],
            [
                'store_id' => 1,
                'group' => 'Agen',
                'name' => 'Agen',
                'alias' => 'Platinum 2',
                'slug' => 'agen-platinum-2',
                'level' => 5,
                'item_discount_percentage' => 35,
                'shipping_discount_percentage' => 0,
                'min_purchase_amount' => 0,
                'hex_code_bg' => '#4d6fbd',
                'hex_code_text' => '#FFFFFF',
                'description' => 'Keanggotaan Agen memberikan diskon 35% untuk setiap pembelian produk di toko kami.',
            ],
        ]);
    }
}
