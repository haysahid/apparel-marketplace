<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\Size;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function home()
    {
        $brands = Brand::take(5)->get();
        $categories = Category::whereNotNull('image')->orderBy('name', 'asc')->get();
        $popularProducts = Product::with(['brand', 'categories', 'images'])->take(8)->get();

        return Inertia::render('Home', [
            'brands' => $brands,
            'categories' => $categories,
            'popularProducts' => $popularProducts,
        ]);
    }

    public function catalog(Request $request)
    {
        $limit = $request->input('limit', 10);
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $search = $request->input('search');

        $brands = $request->input('brands');
        $colors = $request->input('colors');
        $categories = $request->input('categories');
        $sizes = $request->input('sizes');

        $products = Product::query();
        $products->with(['brand', 'categories', 'images']);

        if ($brands) {
            if (is_string($brands)) {
                $brands = explode(',', $brands);
                $brands = Brand::whereIn('name', $brands)->pluck('id')->toArray();
            }
            $products->whereIn('brand_id', $brands);
        }

        if ($colors) {
            if (is_string($colors)) {
                $colors = explode(',', $colors);
                $colors = Color::whereIn('name', $colors)->pluck('id')->toArray();
            }
            $products->whereHas('variants', function ($query) use ($colors) {
                $query->whereIn('color_id', $colors);
            });
        }

        if ($categories) {
            if (is_string($categories)) {
                $categories = explode(',', $categories);
                Log::info('Categories input:', ['categories' => $categories]);
                $categories = Category::whereIn('name', $categories)->pluck('id')->toArray();
            }
            Log::info('Categories input:', ['categories' => $categories]);
            $products->whereHas('categories', function ($query) use ($categories) {
                $query->whereIn('category_id', $categories);
            });
        }

        if ($sizes) {
            if (is_string($sizes)) {
                $sizes = explode(',', $sizes);
                $sizes = Size::whereIn('name', $sizes)->pluck('id')->toArray();
            }
            $products->whereHas('variants', function ($query) use ($sizes) {
                $query->whereIn('size_id', $sizes);
            });
        }

        if ($search) {
            $search = trim($search);
            $products->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
                $query->orWhereHas('brand', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
                $query->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
                $query->orWhereHas('variants', function ($q) use ($search) {
                    $q->where('motif', 'like', '%' . $search . '%');
                    $q->orWhereHas('color', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
                    $q->orWhereHas('size', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
                });
            });
        }

        $products->orderBy($orderBy, $orderDirection);

        $products->get();

        return Inertia::render('Catalog', [
            'products' => $products->paginate($limit),
            'filters' => [
                'brands' => Brand::get(),
                'colors' => Color::get(),
                'categories' => Category::orderBy('name', 'asc')->get(),
                'sizes' => Size::get(),
            ],
        ]);
    }

    public function productDetail($slug)
    {
        $product = Product::with(
            [
                'brand',
                'categories',
                'images',
                'links.platform',
                'store',
                'variants' => function ($query) {
                    $query->with(['color', 'size', 'images']);
                }
            ]
        )->where('slug', $slug)->firstOrFail();

        $accumulatedStock = $product->variants->sum('current_stock_level');
        $minOrder = $product->variants->min('min_order');
        $variants = $product->variants;
        $motifs = $variants->pluck('motif')->unique()->filter()->sort()->values()->all();
        $colors = $variants->pluck('color')->unique('id')->values()->all();
        $sizes = $variants->pluck('size')->filter()->unique('id')->sortBy('id')->values()->all();

        $relatedProducts = Product::where('id', '!=', $product->id)
            ->with(['brand', 'categories', 'images'])
            ->take(4)
            ->get();

        return Inertia::render('ProductDetail', [
            'product' => $product,
            'accumulatedStock' => $accumulatedStock,
            'minOrder' => $minOrder,
            'motifs' => $motifs,
            'colors' => $colors,
            'sizes' => $sizes,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function myCart()
    {
        return Inertia::render('MyCart');
    }
}
