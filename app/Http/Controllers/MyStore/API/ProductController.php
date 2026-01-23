<?php

namespace App\Http\Controllers\MyStore\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $storeId;

    public function __construct(Request $request)
    {
        $this->storeId = $request->header('X-Selected-Store-ID');
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $search = $request->input('search');

        $brandId = $request->input('brand_id');
        $colors = $request->input('colors');
        $categories = $request->input('categories');
        $sizes = $request->input('sizes');

        $products = ProductRepository::getProducts(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            brandId: $brandId,
            colors: $colors,
            categories: $categories,
            sizes: $sizes,
        );

        return ResponseFormatter::success($products, 'Produk berhasil diambil.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'sku_prefix' => 'required|string|max:100',
            'description' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'temporary_images' => 'nullable|array',
            'temporary_images.*' => 'integer|exists:temporary_media,id',
            'images' => 'nullable|array',
            'images.*' => 'integer|exists:media,id',
            'links' => 'nullable|array',
        ], [
            'brand_id.exists' => 'Merek yang dipilih tidak valid.',
            'name.required' => 'Nama produk harus diisi.',
            'sku_prefix.required' => 'Prefix SKU harus diisi.',
            'sku_prefix.string' => 'Prefix SKU harus berupa string.',
            'sku_prefix.max' => 'Prefix SKU tidak boleh lebih dari 100 karakter.',
            'discount.numeric' => 'Diskon harus berupa angka.',
            'categories.array' => 'Kategori harus berupa array.',
            'categories.*.exists' => 'Kategori yang dipilih tidak valid.',
            'temporary_images.array' => 'Gambar sementara harus berupa array.',
            'temporary_images.*.integer' => 'Setiap gambar sementara harus berupa ID yang valid.',
            'temporary_images.*.exists' => 'Gambar sementara yang dipilih tidak ditemukan.',
            'images.array' => 'Gambar harus berupa array.',
            'images.*.integer' => 'Setiap gambar harus berupa ID yang valid.',
            'images.*.exists' => 'Gambar yang dipilih tidak ditemukan.',
            'links.array' => 'Tautan harus berupa array.',
        ]);

        try {
            $data = [
                ...$validated,
                'store_id' => $this->storeId,
            ];

            $product = ProductRepository::createProduct(
                data: $data,
            );

            $updatedProduct = ProductRepository::getProductDetail(
                id: $product->id,
            );

            return ResponseFormatter::success(
                $updatedProduct,
                'Produk berhasil dibuat.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function show(string $id)
    {
        $product = ProductRepository::getProductDetail($id);

        if (!$product) {
            return ResponseFormatter::error(
                'Produk tidak ditemukan.',
                404
            );
        }

        return ResponseFormatter::success(
            $product,
            'Produk berhasil ditemukan.'
        );
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'sku_prefix' => 'required|string|max:100',
            'description' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'links' => 'nullable|array',
        ], [
            'brand_id.exists' => 'Merek yang dipilih tidak valid.',
            'name.required' => 'Nama produk harus diisi.',
            'sku_prefix.required' => 'Prefix SKU harus diisi.',
            'sku_prefix.string' => 'Prefix SKU harus berupa string.',
            'sku_prefix.max' => 'Prefix SKU tidak boleh lebih dari 100 karakter.',
            'discount.numeric' => 'Diskon harus berupa angka.',
            'categories.array' => 'Kategori harus berupa array.',
            'categories.*.exists' => 'Kategori yang dipilih tidak valid.',
            'links.array' => 'Tautan harus berupa array.',
        ]);

        try {
            $product = Product::findOrFail($id);

            $product = ProductRepository::updateProduct($product, $validated);
            $product = ProductRepository::getProductDetail(
                id: $product->id
            );

            return ResponseFormatter::success(
                $product,
                'Produk berhasil diperbarui.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function checkSkuPrefixAvailability(Request $request)
    {
        $skuPrefix = $request->input('sku_prefix');
        $productId = $request->input('product_id');

        $isAvailable = ProductRepository::isSkuPrefixAvailable(
            skuPrefix: $skuPrefix,
            storeId: $this->storeId,
            excludeProductId: $productId,
        );

        return ResponseFormatter::success(
            ['is_available' => $isAvailable],
            'Pemeriksaan ketersediaan SKU prefix berhasil.'
        );
    }
}
