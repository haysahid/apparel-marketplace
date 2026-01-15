<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductLink;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductRepository
{
    public static function getProducts(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $brandId = null,
        $colors = null,
        $categories = null,
        $sizes = null,
    ) {
        $products = Product::query();

        $products->with([
            'brand',
            'categories',
            'images',
            'links',
        ]);

        if ($storeId) {
            $products->where('store_id', $storeId);
        }

        if ($brandId) {
            $products->where('brand_id', $brandId);
        }

        if ($colors) {
            $products->whereHas('variants', function ($query) use ($colors) {
                $query->whereIn('color_id', $colors);
            });
        }

        if ($categories) {
            $products->whereHas('categories', function ($query) use ($categories) {
                $query->whereIn('category_id', $categories);
            });
        }

        if ($sizes) {
            $products->whereHas('variants', function ($query) use ($sizes) {
                $query->whereIn('size_id', $sizes);
            });
        }

        if ($search) {
            $products->where(function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('description', 'ilike', '%' . $search . '%')
                    ->orWhereHas('brand', function ($q) use ($search) {
                        $q->where('name', 'ilike', '%' . $search . '%');
                    })
                    ->orWhereHas('categories', function ($q) use ($search) {
                        $q->where('name', 'ilike', '%' . $search . '%');
                    });
            });
        }

        $products->orderBy($orderBy, $orderDirection);
        $products->get();

        return $products->paginate($limit);
    }

    public static function getProductDetail(string $id)
    {
        return Product::with([
            'brand',
            'categories',
            'images',
            'links.platform',
            'store',
            'variants.color',
            'variants.size',
            'variants.unit',
            'variants.images',
        ])->findOrFail($id);
    }

    public static function createProduct(array $data)
    {
        try {
            DB::beginTransaction();
            $product = Product::create([
                ...$data,
                'sku_prefix' => strtoupper(str_replace(' ', '', $data['sku_prefix'])),
                'slug' => str($data['name'])->slug(),
                'discount_type' => 'percentage',
                'store_id' => $data['store_id'],
            ]);

            if (isset($data['categories'])) {
                $product->categories()->attach($data['categories']);
            }

            if (isset($data['images'])) {
                foreach ($data['images'] as $key => $image) {
                    $imagePath = $image->store('product');
                    $product->images()->create([
                        'image' => $imagePath,
                        'order' => $key,
                    ]);
                }
            }

            if (isset($data['links'])) {
                foreach ($data['links'] as $link) {
                    $product->links()->create([
                        'platform_id' => $link['platform_id'] ?? null,
                        'url' => $link['url'],
                    ]);
                }
            }

            if (isset($data['variants'])) {
                foreach ($data['variants'] as $variant) {
                    $newVariant = ProductVariant::create([
                        'store_id' => $data['store_id'],
                        'product_id' => $product->id,
                        'sku' => strtoupper(str_replace(' ', '', $product->sku_prefix . '_' . $variant['motif'] . '_' . $variant['color_id'] . '_' . $variant['size_id'])),
                        'slug' => str($product->name . '-' . $variant['motif'] . '-' . $variant['color_id'] . '-' . $variant['size_id'])->slug(),
                        'motif' => $variant['motif'],
                        'color_id' => $variant['color_id'],
                        'size_id' => $variant['size_id'],
                        'material' => $variant['material'],
                        'purchase_price' => $variant['purchase_price'] ?? $variant['base_selling_price'],
                        'base_selling_price' => $variant['base_selling_price'],
                        'discount_type' => 'percentage',
                        'discount' => $variant['discount'] ?? 0,
                        'final_selling_price' => $variant['base_selling_price'] - ($variant['base_selling_price'] * ($variant['discount'] ?? 0) / 100),
                        'current_stock_level' => $variant['current_stock_level'],
                        'unit_id' => $variant['unit_id'],
                    ]);

                    if (isset($variant['images'])) {
                        foreach ($variant['images'] as $key => $image) {
                            $imagePath = $image->store('product');
                            $newVariant->images()->create([
                                'product_id' => $product->id,
                                'image' => $imagePath,
                                'order' => $key,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return $product;
        } catch (Exception $e) {
            Log::error('Failed to create product: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal membuat produk: ' . $e);
        }
    }

    public static function updateProduct(Product $product, array $data)
    {
        try {
            $oldProduct = Product::with([
                'categories',
                'links.platform',
                'variants.color',
                'variants.size',
                'variants.images',
            ])->find($product->id);

            DB::beginTransaction();

            $product->update([
                ...$data,
                'slug' => str($data['name'])->slug(),
            ]);

            if (isset($data['categories'])) {
                $product->categories()->sync($data['categories']);
            } else {
                $product->categories()->detach();
            }

            if (isset($data['links'])) {
                $product->links()->delete();

                foreach ($data['links'] as $link) {
                    $product->links()->create([
                        'platform_id' => isset($link['platform_id']) ? $link['platform_id'] : null,
                        'url' => $link['url'],
                    ]);
                }
            }

            if ($oldProduct->sku_prefix != $product->sku_prefix || $oldProduct->slug != $product->slug) {
                foreach ($product->variants as $variant) {
                    $variant->sku = strtoupper(str_replace(' ', '', $variant->product->sku_prefix . '_' . $variant->motif . '_' . $variant->color->name . '_' . $variant->size->name));

                    $variant->slug = str($variant->product->name . '-' . $variant->motif . '-' . $variant->color->name . '-' . $variant->size->name)->slug();

                    $variant->save();
                }
            }

            DB::commit();

            return $product;
        } catch (Exception $e) {
            Log::error('Failed to update product: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal memperbarui produk: ' . $e);
        }
    }

    public static function deleteProduct(Product $product)
    {
        try {
            DB::beginTransaction();

            // Delete product categories
            $product->categories()->detach();

            // Delete product images
            foreach ($product->images as $image) {
                if ($image->image) {
                    Storage::delete($image->image);
                }
                $image->delete();
            }

            // Delete product links
            $product->links()->delete();

            // Delete product variants
            foreach ($product->variants as $variant) {
                foreach ($variant->images as $image) {
                    if ($image->image) {
                        Storage::delete($image->image);
                    }
                    $image->delete();
                }
                $variant->delete();
            }

            // Delete product
            $product->delete();

            DB::commit();
        } catch (Exception $e) {
            Log::error('Failed to delete product: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal menghapus produk: ' . $e);
        }
    }
}
