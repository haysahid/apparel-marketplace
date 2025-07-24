<?php
return [
    'store_id' => 1,
    'brand' => [
        'name' => 'Nibras',
        'description' => 'Brand busana muslim dan aksesoris Nibras',
        'website' => 'catalog?brands=Nibras',
    ],
    'name' => 'Aksesoris Muslim Stylish',
    'slug' => 'aksesoris-muslim-stylish',
    'sku_prefix' => 'PRD004_AKSESORIS_STYLISH',
    'description' => 'Aksesoris Muslim Stylish adalah pelengkap sempurna untuk penampilan Anda. Dengan desain yang unik dan bahan berkualitas, aksesoris ini menambah sentuhan elegan pada busana Muslim Anda. Cocok untuk berbagai acara, baik formal maupun santai.',
    'material' => 'Logam',
    'discount_type' => 'percentage',
    'discount' => 5,
    'categories' => [
        ['name' => 'Accessories'],
        ['name' => 'Aksesoris Muslim'],
    ],
    'images' => [
        ['image' => 'https://down-id.img.susercontent.com/file/1ab9bf690704c94586d2a1069a2b03ac.webp'],
    ],
    'links' => [
        // Tambahkan URL platform jika ada
    ],
    'variants' => [
        // Varian Kuning - One Size
        [
            'sku' => 'PRD004_AKSESORIS_STYLISH_KNG_OS',
            'barcode' => 'NBSAKSSTLKNGOS',
            'slug' => 'aksesoris-muslim-stylish-kuning-one-size',
            'motif' => 'Polos', // Asumsi motif polos untuk aksesoris jika tidak ada detail lebih lanjut
            'color' => ['name' => 'Kuning', 'hex_code' => '#FFFF00'],
            'size' => ['name' => 'One Size'],
            'material' => 'Logam',
            'purchase_price' => 45000, // Contoh harga beli
            'base_selling_price' => 75000,
            'discount_type' => 'percentage',
            'discount' => 5,
            'final_selling_price' => 71250, // 75000 * (1 - 0.05)
            'current_stock_level' => 50, // Semua stok dialokasikan ke satu varian
            'last_stock_update' => '2025-06-30T10:49:55+07:00',
            'unit' => 'pcs',
            'images' => [
                ['image' => 'https://down-id.img.susercontent.com/file/1ab9bf690704c94586d2a1069a2b03ac.webp'],
            ],
        ],
    ],
];
