<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductLink;
use App\Models\ProductVariant;
use App\Models\TemporaryMedia;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductRepository
{
    private static function temporaryMediaGenerateNewFileName(
        TemporaryMedia $tempMedia,
        Product $product
    ) {
        // Determine new file name based on product's slug or name
        if (isset($product->slug)) {
            // Use slug if available
            $baseName = $product->slug;
        } elseif (isset($product->name)) {
            // Use name as slug
            $baseName = Str::slug($product->name);
        } else {
            // Use original
            $baseName = pathinfo($tempMedia->file_name, PATHINFO_FILENAME);
        }

        // Ensure unique file name
        $baseName .= '-' . uniqid();
        $extension = pathinfo($tempMedia->file_name, PATHINFO_EXTENSION);

        return $baseName . '.' . $extension;
    }

    private static function mediaGenerateNewFileName(
        Media $media,
        Product $product
    ) {
        // Determine new file name based on model's slug or name
        if (isset($product->slug)) {
            // Use slug if available
            $baseName = $product->slug;
        } elseif (isset($product->name)) {
            // Use name as slug
            $baseName = Str::slug($product->name);
        } else {
            // Use original
            $baseName = pathinfo($media->file_name, PATHINFO_FILENAME);
        }

        // Ensure unique file name
        $baseName .= '-' . uniqid();

        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        return $baseName . '.' . $extension;
    }

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

            if (isset($data['temporary_images'])) {
                foreach ($data['temporary_images'] as $tempMediaId) {
                    $tempMedia = TemporaryMedia::find($tempMediaId);
                    if ($tempMedia) {
                        $newFileName = self::temporaryMediaGenerateNewFileName(
                            $tempMedia,
                            $product
                        );
                        $newMedia = $tempMedia->copyToMedia($product, 'product');
                        $newMedia->file_name = $newFileName;
                        $newMedia->save();

                        // Delete temporary media record and files
                        $tempMedia->delete();
                    }
                }
            }

            if (isset($data['images'])) {
                foreach ($data['images'] as $key => $mediaId) {
                    $media = Media::find($mediaId);
                    if ($media) {
                        $newFileName = self::mediaGenerateNewFileName($media, $product);
                        $newMedia = $media->copy($product, 'product');
                        $newMedia->file_name = $newFileName;
                        $newMedia->order_column = $key;
                        $newMedia->save();
                    }
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

            if (isset($data['images'])) {
                // Reorder images based on the provided array
                foreach ($data['images'] as $index => $mediaId) {
                    $media = Media::find($mediaId);
                    if ($media) {
                        $media->order_column = $index;
                        $media->save();
                    }
                }
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

    public static function isSkuPrefixAvailable(
        string $skuPrefix,
        $storeId,
        ?string $excludeProductId = null,
    ): bool {
        $query = Product::where('sku_prefix', strtoupper(str_replace(' ', '', $skuPrefix)))
            ->where('store_id', $storeId);

        if ($excludeProductId) {
            $query->where('id', '!=', $excludeProductId);
        }

        return !$query->exists();
    }
}
