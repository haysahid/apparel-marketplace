<?php
return [
    'store_id' => 1,
    'brand' => [
        'name' => 'Kulit Garut',
        'description' => 'Brand produk kulit asli berkualitas dari Garut',
        'website' => 'catalog?brands=KulitGarut',
    ],
    'name' => 'Tas Kulit Premium',
    'slug' => 'tas-kulit-premium',
    'sku_prefix' => 'PRD006_TAS_KULIT_PREMIUM',
    'description' => 'Tas Kulit Premium adalah tas berkualitas tinggi yang dirancang untuk memberikan kesan elegan dan profesional. Terbuat dari kulit asli, tas ini tahan lama dan cocok untuk berbagai kesempatan, baik formal maupun kasual.',
    'material' => 'Kulit Asli',
    'discount_type' => 'percentage',
    'discount' => 20,
    'categories' => [
        ['name' => 'Accessories'],
        ['name' => 'Aksesoris Muslim'],
        ['name' => 'Tas Muslim'],
    ],
    'images' => [
        ['image' => 'https://down-id.img.susercontent.com/file/sg-11134201-22110-7jwz46ds77jvcf.webp'], // Gambar utama
    ],
    'links' => [
        // Tambahkan URL platform jika ada
    ],
    'variants' => [
        // Varian Cream - Medium
        [
            'sku' => 'PRD006_TAS_KLT_PREM_CRM_MDM',
            'barcode' => 'KGTPCRMMDM',
            'slug' => 'tas-kulit-premium-cream-medium',
            'motif' => 'Polos',
            'color' => ['name' => 'Cream', 'hex_code' => '#FFFDD0'],
            'size' => ['name' => 'Medium'],
            'material' => 'Kulit Asli',
            'purchase_price' => 210000, // Contoh harga beli (asumsi 60% dari selling price)
            'base_selling_price' => 350000,
            'discount_type' => 'percentage',
            'discount' => 20,
            'final_selling_price' => 280000, // 350000 * (1 - 0.20)
            'current_stock_level' => 5, // Alokasi stok
            'last_stock_update' => '2025-06-30T10:59:07+07:00',
            'unit' => 'pcs',
            'images' => [
                ['image' => 'https://down-id.img.susercontent.com/file/sg-11134201-22110-7jwz46ds77jvcf.webp'],
            ],
        ],
        // Varian Cream - Large
        [
            'sku' => 'PRD006_TAS_KLT_PREM_CRM_LRG',
            'barcode' => 'KGTPCRMLRG',
            'slug' => 'tas-kulit-premium-cream-large',
            'motif' => 'Polos',
            'color' => ['name' => 'Cream', 'hex_code' => '#FFFDD0'],
            'size' => ['name' => 'Large'],
            'material' => 'Kulit Asli',
            'purchase_price' => 210000, // Contoh harga beli
            'base_selling_price' => 350000,
            'discount_type' => 'percentage',
            'discount' => 20,
            'final_selling_price' => 280000,
            'current_stock_level' => 5, // Alokasi stok
            'last_stock_update' => '2025-06-30T10:59:07+07:00',
            'unit' => 'pcs',
            'images' => [
                ['image' => 'https://down-id.img.susercontent.com/file/sg-11134201-22110-7jwz46ds77jvcf.webp'],
            ],
        ],
    ],
];
