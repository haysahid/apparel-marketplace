<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use App\Models\Size;
use App\Repositories\ProductVariantRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductVariantController extends Controller
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
        $validated = $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'product_id' => 'required|integer|exists:products,id',
            'motif' => 'required|string|max:255',
            'color_id' => 'required|integer|exists:colors,id',
            'size_id' => 'required|integer|exists:sizes,id',
            'material' => 'required|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'base_selling_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'current_stock_level' => 'required|integer',
            'unit_id' => 'required|exists:units,id',
            'images' => 'nullable|array',
            'images.*' => 'integer|exists:media,id',
        ], [
            'store_id.required' => 'ID toko harus diisi.',
            'store_id.exists' => 'Toko tidak ditemukan.',
            'product_id.required' => 'ID produk harus diisi.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'motif.required' => 'Motif harus diisi.',
            'color_id.required' => 'Warna harus dipilih.',
            'color_id.exists' => 'Warna yang dipilih tidak valid.',
            'size_id.required' => 'Ukuran harus dipilih.',
            'size_id.exists' => 'Ukuran yang dipilih tidak valid.',
            'material.required' => 'Bahan harus diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli tidak boleh kurang dari 0.',
            'base_selling_price.required' => 'Harga jual harus diisi.',
            'base_selling_price.numeric' => 'Harga jual harus berupa angka.',
            'base_selling_price.min' => 'Harga jual tidak boleh kurang dari 0.',
            'unit_id.required' => 'Unit harus dipilih.',
            'unit_id.exists' => 'Unit yang dipilih tidak valid.',
            'images.array' => 'Gambar harus berupa array.',
            'images.*.integer' => 'Setiap gambar harus berupa ID yang valid.',
            'images.*.exists' => 'Gambar yang dipilih tidak ditemukan.',
        ]);

        try {
            $variant = ProductVariantRepository::createVariant($validated);

            return ResponseFormatter::success(
                $variant,
                'Varian produk berhasil dibuat.',
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
        try {
            $variant = ProductVariantRepository::getVariantById($id);

            return ResponseFormatter::success(
                $variant,
                'Variant produk berhasil diambil.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'motif' => 'required|string|max:255',
            'color_id' => 'required|integer|exists:colors,id',
            'size_id' => 'required|integer|exists:sizes,id',
            'material' => 'required|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'base_selling_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'current_stock_level' => 'required|integer',
            'unit_id' => 'required|exists:units,id',
            'images' => 'nullable|array',
            'images.*' => 'integer|exists:media,id',
        ], [
            'motif.required' => 'Motif harus diisi.',
            'color_id.required' => 'Warna harus dipilih.',
            'color_id.exists' => 'Warna yang dipilih tidak valid.',
            'size_id.required' => 'Ukuran harus dipilih.',
            'size_id.exists' => 'Ukuran yang dipilih tidak valid.',
            'material.required' => 'Bahan harus diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli tidak boleh kurang dari 0.',
            'base_selling_price.required' => 'Harga jual harus diisi.',
            'base_selling_price.numeric' => 'Harga jual harus berupa angka.',
            'base_selling_price.min' => 'Harga jual tidak boleh kurang dari 0.',
            'discount.numeric' => 'Diskon harus berupa angka.',
            'discount.min' => 'Diskon tidak boleh kurang dari 0.',
            'current_stock_level.required' => 'Stok saat ini harus diisi.',
            'current_stock_level.integer' => 'Stok saat ini harus berupa angka.',
            'unit_id.required' => 'Unit harus dipilih.',
            'unit_id.exists' => 'Unit yang dipilih tidak valid.',
            'images.array' => 'Gambar harus berupa array.',
            'images.*.integer' => 'Setiap gambar harus berupa ID yang valid.',
            'images.*.exists' => 'Gambar yang dipilih tidak ditemukan.',
        ]);

        try {
            $variant = ProductVariantRepository::updateVariant($id, $validated);

            return ResponseFormatter::success(
                $variant,
                'Varian produk berhasil diperbarui.'
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
        try {
            ProductVariantRepository::deleteVariant($id);

            return ResponseFormatter::success(
                null,
                'Variant produk berhasil dihapus.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }
}
