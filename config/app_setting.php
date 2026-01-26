<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure your application settings for your application.
    |
    */

    'name' => env('APP_SETTING_NAME', 'Apparel Marketplace'),
    'description' => env('APP_SETTING_DESCRIPTION', 'Your one-stop shop for apparel and accessories.'),
    'contact_email' => env('APP_SETTING_CONTACT_EMAIL', 'support@apparelmarketplace.com'),
    'contact_phone' => env('APP_SETTING_CONTACT_PHONE', '+1234567890'),
    'address' => env('APP_SETTING_ADDRESS', '123 Apparel St, Fashion City, FC 12345'),

    /*
    |--------------------------------------------------------------------------
    | Logos
    |--------------------------------------------------------------------------
    | Here you may configure your application logos.
    |*/

    'logo' => env('APP_SETTING_LOGO', 'logo-shopywear.png'),
    'logo_white' => env('APP_SETTING_LOGO_WHITE', 'logo-shopywear-white.png'),
    'logo_black' => env('APP_SETTING_LOGO_BLACK', 'logo-shopywear-black.png'),

    /*
    |--------------------------------------------------------------------------
    | Social Media Links
    |--------------------------------------------------------------------------
    | Here you may configure your social media links for your application.
    |*/

    'social_links' => [
        'facebook' => env('APP_SETTING_SOCIAL_FACEBOOK', 'https://facebook.com/apparelmarketplace'),
        'instagram' => env('APP_SETTING_SOCIAL_INSTAGRAM', 'https://instagram.com/apparelmarketplace'),
        'tiktok' => env('APP_SETTING_SOCIAL_TIKTOK', 'https://tiktok.com/@apparelmarketplace'),
    ],
];
