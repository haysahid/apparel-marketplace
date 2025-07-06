<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Platform::insert([
            ['store_id' => null, 'name' => 'Shopee', 'icon' => 'platform/shopee.png'],
            ['store_id' => null, 'name' => 'Lazada', 'icon' => 'platform/lazada.png'],
            ['store_id' => null, 'name' => 'Tokopedia', 'icon' => 'platform/tokopedia.png'],
            ['store_id' => null, 'name' => 'Blibli', 'icon' => 'platform/blibli.png'],
            ['store_id' => null, 'name' => 'JD.ID', 'icon' => 'platform/jd_id.png'],
            ['store_id' => null, 'name' => 'Tiktok Shop', 'icon' => 'platform/tiktok_shop.png'],
            ['store_id' => null, 'name' => 'Facebook Marketplace', 'icon' => 'platform/facebook_marketplace.png'],
        ]);
    }
}
