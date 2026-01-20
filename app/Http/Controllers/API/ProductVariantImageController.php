<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariantImage;
use App\Repositories\ProductVariantImageRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductVariantImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'image' => 'required|file|image|max:2048',
            'order' => 'nullable|integer|min:0',
        ], [
            'product_variant_id.required' => 'ID varian produk harus diisi.',
            'product_variant_id.exists' => 'Varian produk tidak ditemukan.',
            'image.required' => 'Gambar varian produk harus diunggah.',
            'image.file' => 'Gambar harus berupa file.',
            'image.image' => 'Gambar harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan tidak boleh kurang dari 0.',
        ]);

        try {
            $data = [
                ...$validatedData,
                'image' => $request->hasFile('image') ? $request->file('image') : null,
            ];

            $productVariantImage = ProductVariantImageRepository::createImage($data);

            return ResponseFormatter::success(
                $productVariantImage,
                'Gambar varian produk berhasil diunggah.',
                201
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'image' => 'nullable|file|image|max:2048',
            'order' => 'nullable|integer|min:0',
        ], [
            'image.file' => 'Gambar harus berupa file.',
            'image.image' => 'Gambar harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan tidak boleh kurang dari 0.',
        ]);

        $variantImage = ProductVariantImage::findOrFail($id);

        if (!$variantImage) {
            return ResponseFormatter::error(
                'Gambar varian produk tidak ditemukan.',
                404
            );
        }

        try {
            $data = [
                ...$validatedData,
                'image' => $request->hasFile('image') ? $request->file('image') : null,
            ];

            $variantImage = ProductVariantImageRepository::updateImage($variantImage, $data);

            return ResponseFormatter::success(
                $variantImage,
                'Gambar varian produk berhasil diperbarui.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $variantImage = ProductVariantImage::find($id);

        if (!$variantImage) {
            return ResponseFormatter::error(
                'Gambar varian produk tidak ditemukan.',
                404
            );
        }

        try {
            ProductVariantImageRepository::deleteImage($variantImage);

            return ResponseFormatter::success(
                null,
                'Gambar varian produk berhasil dihapus.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }
}
