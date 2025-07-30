<?php

namespace Database\Seeders;

use App\Jobs\DownloadProductImage;
use App\Jobs\DownloadProductVariantImage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BrandSeeder::class,
            CategorySeeder::class,
            ColorSeeder::class,
            SizeSeeder::class,
            UnitSeeder::class,
        ]);

        $products = require __DIR__ . '/data/products.php';
        shuffle($products);

        foreach ($products as $productData) {
            $brandId = null;

            Log::info('Processing product: ' . $productData['name']);

            // Ensure brand exists or create it
            if (isset($productData['brand'])) {
                Log::info('Creating brand: ' . $productData['brand']['name']);
                $brand = Brand::firstOrCreate(
                    [
                        'name' => $productData['brand']['name']
                    ]
                );
                $brandId = $brand->id;
            }

            // Create the product
            $product = Product::create([
                'store_id' => $productData['store_id'],
                'brand_id' => $brandId,
                'name' => $productData['name'],
                'slug' => $productData['slug'],
                'sku_prefix' => $productData['sku_prefix'],
                'description' => $productData['description'] ?? null,
                'discount_type' => $productData['discount_type'] ?? null,
                'discount' => $productData['discount'] ?? null,
            ]);

            // Attach categories
            if (isset($productData['categories'])) {
                foreach ($productData['categories'] as $category) {
                    if (isset($category['id'])) {
                        // If category ID is provided, attach it directly
                        $product->categories()->attach($category['id']);
                        continue;
                    }

                    // Check if category exists by name, otherwise create it
                    $categoryModel = Category::firstOrCreate(
                        ['name' => $category['name']],
                    );

                    if ($categoryModel) {
                        $product->categories()->attach($categoryModel->id);
                    }
                }
            }

            // Add product images
            if (isset($productData['images'])) {
                foreach ($productData['images'] as $key => $image) {
                    // If image is a URL, download it and store it
                    if (filter_var($image['image'], FILTER_VALIDATE_URL)) {
                        DownloadProductImage::dispatch($image['image'], $product->id, $key);
                    } else {
                        $product->images()->create(
                            [
                                'image' => $image['image'],
                                'order' => $key
                            ]
                        );
                    }
                }
            }

            // Add links
            if (isset($productData['links'])) {
                foreach ($productData['links'] as $link) {
                    $product->links()->create([
                        'platform_id' => $link['platform_id'],
                        'url' => $link['url'],
                    ]);
                }
            }

            // Create product variants
            if (isset($productData['variants'])) {
                foreach ($productData['variants'] as $variant) {
                    // Check color
                    $colorId = null;
                    if (isset($variant['color'])) {
                        if (isset($variant['color']['id'])) {
                            // If color ID is provided, use it directly
                            $colorId = $variant['color']['id'];
                        } else {
                            // Check if color exists by name or hex_color, otherwise create it
                            $existingColor = Color::where('name', $variant['color']['name'])
                                ->orWhere('hex_code', $variant['color']['hex_code'])
                                ->first();
                            if ($existingColor) {
                                $colorId = $existingColor->id;
                            } else {
                                // Create a new color if it doesn't exist
                                $newColor = Color::create([
                                    'name' => $variant['color']['name'],
                                    'hex_code' => $variant['color']['hex_code'],
                                ]);
                                $colorId = $newColor->id;
                            }
                        }
                    }

                    // Check size
                    $sizeId = null;
                    if (isset($variant['size'])) {
                        if (isset($variant['size']['id'])) {
                            // If size ID is provided, use it directly
                            $sizeId = $variant['size']['id'];
                        } else {
                            // Check if size exists by name, otherwise create it
                            $sizeModel = Size::firstOrCreate(
                                ['name' => $variant['size']['name']],
                            );
                            $sizeId = $sizeModel->id;
                        }
                    }

                    // Create the product variant
                    $productVariant = $product->variants()->create([
                        'store_id' => $product->store_id,
                        'sku' => $variant['sku'],
                        'barcode' => $variant['barcode'] ?? null,
                        'slug' => $variant['slug'] ?? null,
                        'motif' => $variant['motif'] ?? null,
                        'color_id' => $colorId,
                        'size_id' => $sizeId,
                        'material' => $variant['material'] ?? null,
                        'purchase_price' => $variant['purchase_price'] ?? 0,
                        'base_selling_price' => $variant['base_selling_price'] ?? 0,
                        'discount_type' => $variant['discount_type'] ?? null,
                        'discount' => $variant['discount'] ?? 0,
                        'final_selling_price' => $variant['final_selling_price'] ?? 0,
                        'current_stock_level' => $variant['current_stock_level'] ?? 0,
                        'last_stock_update' => $variant['last_stock_update'] ?? now(),
                        'unit_id' => Unit::where('name', $variant['unit'] ?? 'pcs')->first()->id ?? null,
                    ]);

                    // Add variant images
                    if (isset($variant['images'])) {
                        foreach ($variant['images'] as $key => $image) {
                            // If image is a URL, download it and store it
                            if (filter_var($image['image'], FILTER_VALIDATE_URL)) {
                                DownloadProductVariantImage::dispatch($image['image'], $productVariant->id, $product->id, $key);
                            } else {
                                $productVariant->images()->create(
                                    [
                                        'image' => $image['image'],
                                        'order' => $key
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
