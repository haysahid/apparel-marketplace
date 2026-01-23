<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Platform;
use App\Models\Product;
use App\Models\Size;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ColorRepository;
use App\Repositories\PlatformRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SizeRepository;
use App\Repositories\UnitRepository;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    protected $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id') ?? null;
    }

    /**
     * Display a listing of the resource.
     */
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

        $brands = BrandRepository::getBrandDropdown(
            storeId: $this->storeId
        );

        return Inertia::render('MyStore/Product/Index', [
            'products' => $products,
            'brands' => $brands,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = BrandRepository::getBrandDropdown(
            storeId: $this->storeId
        );
        $categories = CategoryRepository::getCategoryDropdown(
            storeId: $this->storeId
        );
        $sizes = SizeRepository::getSizeDropdown(
            storeId: $this->storeId
        );
        $colors = ColorRepository::getColorDropdown(
            storeId: $this->storeId
        );
        $units = UnitRepository::getUnitDropdown(
            storeId: $this->storeId
        );
        $platforms = PlatformRepository::getPlatformDropdown(
            storeId: $this->storeId
        );

        return Inertia::render('MyStore/Product/AddProduct', [
            'brands' => $brands,
            'categories' => $categories,
            'sizes' => $sizes,
            'colors' => $colors,
            'units' => $units,
            'platforms' => $platforms,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
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

            return redirect()->route('my-store.product.edit', [
                'id' => $product->id,
                'tab' => 1,
            ])
                ->with('success', 'Produk berhasil dibuat.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load([
            'brand',
            'categories',
            'images',
            'links.platform',
            'variants.color',
            'variants.size',
            'variants.images',
            'variants.unit',
        ]);

        $brands = BrandRepository::getBrandDropdown(
            storeId: $this->storeId
        );
        $categories = CategoryRepository::getCategoryDropdown(
            storeId: $this->storeId
        );
        $sizes = SizeRepository::getSizeDropdown(
            storeId: $this->storeId
        );
        $colors = ColorRepository::getColorDropdown(
            storeId: $this->storeId
        );
        $units = UnitRepository::getUnitDropdown(
            storeId: $this->storeId
        );
        $platforms = PlatformRepository::getPlatformDropdown(
            storeId: $this->storeId
        );

        return Inertia::render('MyStore/Product/EditProduct', [
            'product' => $product,
            'brands' => $brands,
            'categories' => $categories,
            'sizes' => $sizes,
            'colors' => $colors,
            'units' => $units,
            'platforms' => $platforms,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
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
            ProductRepository::updateProduct($product, $validated);

            return redirect()->back()
                ->with('success', 'Produk berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            ProductRepository::deleteProduct($product);

            return redirect()->route('my-store.product.index')
                ->with('success', 'Produk berhasil dihapus.');
        } catch (Exception $e) {

            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
