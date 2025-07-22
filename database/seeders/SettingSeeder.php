<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::insert([
            [
                'key' => 'site_name',
                'value' => 'Apparel Marketplace',
                'type' => 'string',
                'name' => 'Site Name',
                'description' => 'The name of the site displayed in the header and footer.',
            ],
            [
                'key' => 'site_description',
                'value' => 'Your one-stop shop for apparel and accessories.',
                'type' => 'string',
                'name' => 'Site Description',
                'description' => 'A brief description of the site.',
            ],
            [
                'key' => 'contact_email',
                'value' => 'support@apparelmarketplace.com',
                'type' => 'string',
                'name' => 'Contact Email',
                'description' => 'The email address for customer support inquiries.',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+1234567890',
                'type' => 'string',
                'name' => 'Contact Phone',
                'description' => 'The phone number for customer support inquiries.',
            ],
            [
                'key' => 'address',
                'value' => '123 Apparel St, Fashion City, FC 12345',
                'type' => 'string',
                'name' => 'Address',
                'description' => 'The physical address of the business.',
            ],
            [
                'key' => 'logo',
                'value' => 'logo-shopywear-white.png',
                'type' => 'string',
                'name' => 'Logo',
                'description' => 'The URL path to the site\'s logo image.',
            ],
            [
                'key' => 'social_links',
                'value' => json_encode([
                    'facebook' => 'https://facebook.com/apparelmarketplace',
                    'instagram' => 'https://instagram.com/apparelmarketplace',
                    'tiktok' => '',
                ]),
                'type' => 'json',
                'name' => 'Social Links',
                'description' => 'Links to the site\'s social media profiles.',
            ],
        ]);
    }
}
