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
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'sku_prefix' => 'required|string|max:100',
            'description' => 'required|string',
            'discount' => 'nullable|numeric',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'images' => 'required|array',
            'images.*' => 'file|image|max:2048',
            'links' => 'nullable|array',
            'variants' => 'required|array',
            'variants.*.motif' => 'required|string|max:100',
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.size_id' => 'required|exists:sizes,id',
            'variants.*.material' => 'required|string|max:100',
            'variants.*.base_selling_price' => 'required|numeric',
            'variants.*.discount' => 'nullable|numeric',
            'variants.*.current_stock_level' => 'required|integer',
            'variants.*.unit_id' => 'required|exists:units,id',
            'variants.*.images' => 'required|array',
            'variants.*.images.*' => 'file|image|max:2048',
        ], [
            'brand_id.required' => 'Merek produk harus dipilih.',
            'brand_id.exists' => 'Merek yang dipilih tidak valid.',
            'name.required' => 'Nama produk harus diisi.',
            'sku_prefix.required' => 'Prefix SKU harus diisi.',
            'sku_prefix.string' => 'Prefix SKU harus berupa string.',
            'sku_prefix.max' => 'Prefix SKU tidak boleh lebih dari 100 karakter.',
            'description.required' => 'Deskripsi produk harus diisi.',
            'discount.numeric' => 'Diskon harus berupa angka.',
            'categories.array' => 'Kategori harus berupa array.',
            'categories.*.exists' => 'Kategori yang dipilih tidak valid.',
            'images.required' => 'Gambar produk harus diunggah.',
            'images.array' => 'Gambar harus berupa array.',
            'images.*.file' => 'Setiap gambar harus berupa file.',
            'images.*.mimes' => 'Gambar harus berupa file dengan format jpg, jpeg, png, atau webp.',
            'images.*.max' => 'Setiap gambar tidak boleh lebih dari 2MB.',
            'links.array' => 'Tautan harus berupa array.',
            'variants.required' => 'Varian produk harus diisi.',
            'variants.*.motif.required' => 'Motif varian harus diisi.',
            'variants.*.color_id.required' => 'Warna varian harus dipilih.',
            'variants.*.color_id.exists' => 'Warna yang dipilih tidak valid.',
            'variants.*.size_id.required' => 'Ukuran varian harus dipilih.',
            'variants.*.size_id.exists' => 'Ukuran yang dipilih tidak valid.',
            'variants.*.material.required' => 'Material varian harus diisi.',
            'variants.*.base_selling_price.required' => 'Harga jual dasar varian harus diisi.',
            'variants.*.base_selling_price.numeric' => 'Harga jual dasar varian harus berupa angka.',
            'variants.*.discount.numeric' => 'Diskon varian harus berupa angka.',
            'variants.*.current_stock_level.required' => 'Stok saat ini varian harus diisi.',
            'variants.*.current_stock_level.integer' => 'Stok saat ini varian harus berupa bilangan bulat.',
            'variants.*.unit_id.required' => 'Unit varian harus dipilih.',
            'variants.*.unit_id.exists' => 'Unit yang dipilih tidak valid.',
            'variants.*.images.required' => 'Gambar varian harus diunggah.',
            'variants.*.images.array' => 'Gambar varian harus berupa array.',
            'variants.*.images.*.file' => 'Setiap gambar varian harus berupa file.',
            'variants.*.images.*.mimes' => 'Gambar varian harus berupa file dengan format jpg, jpeg, png, atau webp.',
            'variants.*.images.*.max' => 'Setiap gambar varian tidak boleh lebih dari 2MB.',
        ]);

        try {
            $data = [
                ...$validated,
                'store_id' => $this->storeId,
            ];

            ProductRepository::createProduct(
                data: $data,
            );

            return redirect()->route('my-store.product.index')
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
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'sku_prefix' => 'required|string|max:100',
            'description' => 'required|string',
            'discount' => 'nullable|numeric',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'links' => 'nullable|array',
        ], [
            'brand_id.required' => 'Merek produk harus dipilih.',
            'brand_id.exists' => 'Merek yang dipilih tidak valid.',
            'name.required' => 'Nama produk harus diisi.',
            'sku_prefix.required' => 'Prefix SKU harus diisi.',
            'sku_prefix.string' => 'Prefix SKU harus berupa string.',
            'sku_prefix.max' => 'Prefix SKU tidak boleh lebih dari 100 karakter.',
            'description.required' => 'Deskripsi produk harus diisi.',
            'discount.numeric' => 'Diskon harus berupa angka.',
            'categories.array' => 'Kategori harus berupa array.',
            'categories.*.exists' => 'Kategori yang dipilih tidak valid.',
            'links.array' => 'Tautan harus berupa array.',
        ]);

        try {
            ProductRepository::updateProduct($product, $validated);

            return redirect()->route('my-store.product.index')
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
