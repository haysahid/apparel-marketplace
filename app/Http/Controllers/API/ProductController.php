<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(string $id)
    {
        $product = Product::with([
            'brand',
            'categories',
            'images',
            'links.platform',
            'variants.color',
            'variants.size',
            'variants.images',
        ])->find($id);

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
