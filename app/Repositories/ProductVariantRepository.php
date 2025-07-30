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

class ProductVariantRepository
{
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
                'sku' => $product->sku_prefix . '_' . strtoupper(str_replace(' ', '', $data['motif'] . '_' . $color->name . '_' . $size->name)),
                'slug' => str($product->name . '-' . $data['motif'] . '-' . $color->name . '-' . $size->name)->slug(),
                'motif' => $data['motif'],
                'color_id' => $color->id,
                'size_id' => $size->id,
                'material' => $data['material'],
                'purchase_price' => $data['purchase_price'] ?? $data['base_selling_price'],
                'base_selling_price' => $data['base_selling_price'],
                'discount_type' => 'percentage',
                'discount' => $data['discount'] ?? 0,
                'final_selling_price' => $data['base_selling_price'] - ($data['base_selling_price'] * ($data['discount'] ?? 0) / 100),
                'current_stock_level' => $data['current_stock_level'],
                'unit_id' => $unit->id,
            ]);

            if ($data['images']) {
                foreach ($data['images'] as $key => $image) {
                    ProductVariantImage::create([
                        'product_variant_id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'image' => $image->store('product'),
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

    public static function updateVariant(string $id, array $data)
    {
        try {
            DB::beginTransaction();

            $color = Color::find($data['color_id']);
            $size = Size::find($data['size_id']);
            $unit = Unit::find($data['unit_id']);

            $variant = ProductVariant::with(['product'])->findOrFail($id);
            $variant->sku = strtoupper(str_replace(' ', '', $variant->product->sku_prefix . '_' . $data['motif'] . '_' . $color->name . '_' . $size->name));
            $variant->slug = str($variant->product->name . '-' . $data['motif'] . '-' . $color->name . '-' . $size->name)->slug();
            $variant->motif = $data['motif'];
            $variant->color_id = $data['color_id'];
            $variant->size_id = $data['size_id'];
            $variant->material = $data['material'];

            if ($data['purchase_price'] !== null) {
                $variant->purchase_price = $data['purchase_price'];
            }

            $variant->base_selling_price = $data['base_selling_price'];
            $variant->discount = $data['discount'] ?? 0;
            $variant->final_selling_price = $variant->base_selling_price - ($variant->base_selling_price * ($variant->discount / 100));
            $variant->current_stock_level = $data['current_stock_level'];
            $variant->unit_id = $unit->id;

            $variant->save();

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
                // Check if the file used by another product
                $otherImages = ProductVariantImage::where('image', $image->image)
                    ->where('id', '!=', $image->id)
                    ->count();

                if ($otherImages === 0) {
                    // Delete the image file
                    Storage::delete($image->image);
                }

                $image->delete();
            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal menghapus varian produk: ' . $e);
        }
    }
}
