<?php

namespace App\Repositories;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use App\Models\Size;
use App\Models\Unit;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductVariantRepository
{
    public static function generateSku(string $skuPrefix, array $metadata): string
    {
        $skuSeparator = config('product.sku_separator');

        $metadata = array_filter($metadata, function ($item) {
            return !is_null($item) && $item !== '';
        });

        $sku = $skuPrefix;
        if (!empty($metadata)) {
            $sku .= $skuSeparator . implode($skuSeparator, array_map(function ($item) {
                return strtoupper(str_replace(' ', '', $item));
            }, $metadata));
        }
        return $sku;
    }

    public static function generateSlug(string $productName, array $metadata): string
    {
        $metadata = array_filter($metadata, function ($item) {
            return !is_null($item) && $item !== '';
        });

        $slug = str($productName);
        if (!empty($metadata)) {
            $slug .= '-' . implode('-', $metadata);
        }
        return Str::slug($slug);
    }

    public static function createVariant(array $data)
    {
        try {
            DB::beginTransaction();

            $product = Product::find($data['product_id']);
            $color = Color::find($data['color_id']);
            $size = Size::find($data['size_id']);
            $unit = Unit::find($data['unit_id']);

            $variant = ProductVariant::create([
                'store_id' => $data['store_id'],
                'product_id' => $data['product_id'],
                'sku' => self::generateSku(
                    $product->sku_prefix,
                    [
                        $data['motif'] ?? null,
                        $color->name,
                        $size->name,
                    ]
                ),
                'slug' => self::generateSlug(
                    $product->name,
                    [
                        $data['motif'] ?? null,
                        $color->name,
                        $size->name,
                    ]
                ),
                'barcode' => $data['barcode'] ?? null,
                'motif' => $data['motif'] ?? null,
                'color_id' => $color->id,
                'size_id' => $size->id,
                'material' => $data['material'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? $data['base_selling_price'],
                'base_selling_price' => $data['base_selling_price'],
                'discount_type' => 'percentage',
                'discount' => $data['discount'] ?? 0,
                'final_selling_price' => $data['base_selling_price'] - ($data['base_selling_price'] * ($data['discount'] ?? 0) / 100),
                'current_stock_level' => $data['current_stock_level'],
                'unit_id' => $unit->id,
            ]);

            if (isset($data['images'])) {
                foreach ($data['images'] as $key => $mediaId) {
                    ProductVariantImage::create([
                        'product_variant_id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'media_id' => $mediaId,
                        'order' => $key,
                    ]);
                }
            }

            DB::commit();

            $variant = ProductVariant::with(['color', 'size', 'images'])
                ->find($variant->id);

            return $variant;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal membuat varian produk: ' . $e);
        }
    }

    public static function getVariantById(string $id)
    {
        return ProductVariant::with(['color', 'size', 'unit', 'images'])->findOrFail($id);
    }

    public static function getVariantsByProductId(string $productId)
    {
        return ProductVariant::with(['color', 'size', 'unit', 'images'])
            ->where('product_id', $productId)
            ->get();
    }

    public static function updateVariant(string $id, array $data)
    {
        try {
            DB::beginTransaction();

            $color = Color::find($data['color_id']);
            $size = Size::find($data['size_id']);
            $unit = Unit::find($data['unit_id']);

            $variant = ProductVariant::with(['product'])->findOrFail($id);
            $variant->sku = self::generateSku(
                $variant->product->sku_prefix,
                [
                    $data['motif'] ?? null,
                    $color->name,
                    $size->name,
                ]
            );
            $variant->slug = self::generateSlug(
                $variant->product->name,
                [
                    $data['motif'] ?? null,
                    $color->name,
                    $size->name,
                ]
            );
            $variant->barcode = $data['barcode'] ?? null;
            $variant->motif = $data['motif'] ?? null;
            $variant->color_id = $data['color_id'];
            $variant->size_id = $data['size_id'];
            $variant->material = $data['material'] ?? null;

            if ($data['purchase_price'] !== null) {
                $variant->purchase_price = $data['purchase_price'] ?: $data['base_selling_price'];
            }

            $variant->base_selling_price = $data['base_selling_price'];
            $variant->discount = $data['discount'] ?? 0;
            $variant->final_selling_price = $variant->base_selling_price - ($variant->base_selling_price * ($variant->discount / 100));
            $variant->current_stock_level = $data['current_stock_level'];
            $variant->unit_id = $unit->id;

            $variant->save();

            if (isset($data['images'])) {
                foreach ($data['images'] as $key => $mediaId) {
                    if (!ProductVariantImage::where('product_variant_id', $variant->id)->where('media_id', $mediaId)->exists()) {
                        ProductVariantImage::create([
                            'product_variant_id' => $variant->id,
                            'product_id' => $variant->product_id,
                            'media_id' => $mediaId,
                            'order' => $key,
                        ]);
                        continue;
                    }
                }

                // Remove images that are not in the new list
                ProductVariantImage::where('product_variant_id', $variant->id)
                    ->whereNotIn('media_id', $data['images'])
                    ->each(function ($image) {
                        $image->delete();
                    });
            }

            DB::commit();

            return $variant;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal memperbarui varian produk: ' . $e);
        }
    }

    public static function deleteVariant(string $id)
    {
        try {
            DB::beginTransaction();

            $variant = ProductVariant::findOrFail($id);
            $variant->delete();

            // Delete associated images
            ProductVariantImage::where('product_variant_id', $id)->each(function ($image) {
                $image->delete();
            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal menghapus varian produk: ' . $e);
        }
    }
}
