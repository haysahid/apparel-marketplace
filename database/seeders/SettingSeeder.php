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
                'value' => config('app_setting.name'),
                'type' => 'string',
                'name' => 'Site Name',
                'description' => 'The name of the site displayed in the header and footer.',
            ],
            [
                'key' => 'site_description',
                'value' => config('app_setting.description'),
                'type' => 'string',
                'name' => 'Site Description',
                'description' => 'A brief description of the site.',
            ],
            [
                'key' => 'contact_email',
                'value' => config('app_setting.contact_email'),
                'type' => 'string',
                'name' => 'Contact Email',
                'description' => 'The email address for customer support inquiries.',
            ],
            [
                'key' => 'contact_phone',
                'value' => config('app_setting.contact_phone'),
                'type' => 'string',
                'name' => 'Contact Phone',
                'description' => 'The phone number for customer support inquiries.',
            ],
            [
                'key' => 'address',
                'value' => config('app_setting.address'),
                'type' => 'string',
                'name' => 'Address',
                'description' => 'The physical address of the business.',
            ],
            [
                'key' => 'logo',
                'value' => config('app_setting.logo'),
                'type' => 'string',
                'name' => 'Logo',
                'description' => 'The URL path to the site\'s logo image.',
            ],
            [
                'key' => 'logo_white',
                'value' => config('app_setting.logo_white'),
                'type' => 'string',
                'name' => 'Logo White',
                'description' => 'The URL path to the site\'s logo image.',
            ],
            [
                'key' => 'logo_black',
                'value' => config('app_setting.logo_black'),
                'type' => 'string',
                'name' => 'Logo Black',
                'description' => 'The URL path to the site\'s logo image.',
            ],
            [
                'key' => 'social_links',
                'value' => json_encode([
                    'facebook' => config('app_setting.social_links.facebook'),
                    'instagram' => config('app_setting.social_links.instagram'),
                    'tiktok' => config('app_setting.social_links.tiktok'),
                ]),
                'type' => 'json',
                'name' => 'Social Links',
                'description' => 'Links to the site\'s social media profiles.',
            ],
        ]);
    }
}
