<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\BrandRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id') ?? null;
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
}
