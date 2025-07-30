<?php

namespace App\Repositories;

use App\Models\ProductVariantImage;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductVariantImageRepository
{
    public static function createImage(array $data)
    {
        try {
            DB::beginTransaction();

            $variant = ProductVariantImage::where('product_variant_id', $data['product_variant_id'])->first();

            $lastImageOrder = ProductVariantImage::where('product_variant_id', $data['product_variant_id'])
                ->max('order') ?? -1;

            $productVariantImage = ProductVariantImage::create([
                'product_variant_id' => $data['product_variant_id'],
                'product_id' => $variant->product_id,
                'image' => $data['image']->store('product'),
                'order' => $data['order'] ?? $lastImageOrder + 1,
            ]);

            DB::commit();

            return $productVariantImage;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal membuat gambar varian produk: ' . $e);
        }
    }

    public static function updateImage(ProductVariantImage $variantImage, array $data)
    {
        try {
            DB::beginTransaction();

            if (isset($data['image'])) {
                // Delete the old image file if it exists
                if ($variantImage->image) {
                    // Check if the file used by another product
                    $otherImages = ProductVariantImage::where('image', $variantImage->image)
                        ->where('id', '!=', $variantImage->id)
                        ->count();

                    if ($otherImages === 0) {
                        Storage::delete($variantImage->image);
                    }
                }

                $variantImage->image = $data['image']->store('product');
            }

            if (isset($data['order'])) {
                $variantImage->order = $data['order'];
            }

            $variantImage->save();

            DB::commit();

            return $variantImage;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal memperbarui gambar varian produk: ' . $e);
        }
    }

    public static function deleteImage(ProductVariantImage $variantImage)
    {
        try {
            DB::beginTransaction();

            // Check if the file used by another product
            $otherImages = ProductVariantImage::where('image', $variantImage->image)
                ->where('id', '!=', $variantImage->id)
                ->count();

            if ($otherImages === 0) {
                Storage::delete($variantImage->image);
            }

            $variantImage->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal menghapus gambar varian produk: ' . $e);
        }
    }
}
