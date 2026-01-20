<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
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
            'product_id' => 'required|exists:products,id',
            'image' => 'required|file|image|max:2048',
            'order' => 'nullable|integer|min:0',
        ], [
            'product_id.required' => 'ID produk harus diisi.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'image.required' => 'Gambar produk harus diunggah.',
            'image.file' => 'Gambar harus berupa file.',
            'image.image' => 'Gambar harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan tidak boleh kurang dari 0.',
        ]);

        try {
            DB::beginTransaction();

            $lastImageOrder = ProductImage::where('product_id', $validatedData['product_id'])
                ->max('order') ?? -1;

            $productImage = ProductImage::create([
                'product_id' => $validatedData['product_id'],
                'image' => $request->file('image')->store('product'),
                'order' => $validatedData['order'] ?? $lastImageOrder + 1,
            ]);

            DB::commit();

            return ResponseFormatter::success(
                $productImage,
                'Gambar produk berhasil diunggah.',
                201,
            );
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Gagal mengunggah gambar produk: ' . $e->getMessage(),
                500,
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
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'order' => 'nullable|integer|min:0',
        ], [
            'image.file' => 'Gambar harus berupa file.',
            'image.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan tidak boleh kurang dari 0.',
        ]);

        $productImage = ProductImage::find($id);

        if (!$productImage) {
            return ResponseFormatter::error(
                'Gambar produk tidak ditemukan.',
                404,
            );
        }

        try {
            DB::beginTransaction();

            if (isset($validatedData['image'])) {
                // Delete the old image if it exists
                if ($productImage->image) {
                    // Check if the file used by another product
                    $otherImages = ProductImage::where('image', $productImage->image)
                        ->where('id', '!=', $productImage->id)
                        ->count();

                    if ($otherImages === 0) {
                        Storage::delete($productImage->image);
                    }
                }

                $productImage->image = $request->file('image')->store('product');
            }

            if (isset($validatedData['order'])) {
                $productImage->order = $validatedData['order'];
            }

            $productImage->save();

            DB::commit();

            return ResponseFormatter::success(
                $productImage,
                'Gambar produk berhasil diperbarui.',
                200,
            );
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Gagal memperbarui gambar produk: ' . $e->getMessage(),
                500,
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productImage = ProductImage::find($id);

        if (!$productImage) {
            return ResponseFormatter::error(
                null,
                'Gambar produk tidak ditemukan.',
                404,
            );
        }

        try {
            DB::beginTransaction();

            // Check if the file used by another product
            $otherImages = ProductImage::where('image', $productImage->image)
                ->where('id', '!=', $productImage->id)
                ->count();

            if ($otherImages === 0) {
                Storage::delete($productImage->image);
            }

            $productImage->delete();

            DB::commit();

            return ResponseFormatter::success(
                null,
                'Gambar produk berhasil dihapus.',
                200,
            );
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Gagal menghapus gambar produk: ' . $e->getMessage(),
                500,
            );
        }
    }
}
