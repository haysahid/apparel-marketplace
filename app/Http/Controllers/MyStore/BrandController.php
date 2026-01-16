<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Repositories\BrandRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BrandController extends Controller
{
    protected $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $brands = BrandRepository::getBrands(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/Brand/Index', [
            'brands' => $brands,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('MyStore/Brand/AddBrand');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'is_dialog' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama brand harus diisi.',
            'logo.mimes' => 'Logo harus berupa file dengan format jpg, jpeg, png, atau webp.',
            'logo.max' => 'Ukuran logo tidak boleh lebih dari 2MB.',
        ]);

        try {
            $data = [
                'store_id' => session('selected_store_id'),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'logo' => $request->file('logo') ? $request->file('logo') : null,
                'website' => url('catalog?brands=' . $validated['name']),
            ];

            BrandRepository::createBrand($data);

            if ($request->input('is_dialog')) {
                return redirect()->back()->with('success', 'Brand berhasil dibuat.');
            }

            return redirect()->route('my-store.brand.index')->with('success', 'Brand berhasil dibuat.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        return Inertia::render('MyStore/Brand/EditBrand', [
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {
            $data = [
                ...$validated,
                'website' => url('catalog?brands=' . $validated['name']),
            ];

            BrandRepository::updateBrand($brand, $data);

            return redirect()->route('my-store.brand.index')->with('success', 'Brand berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        try {
            BrandRepository::deleteBrand($brand);

            return redirect()->route('my-store.brand.index')->with('success', 'Brand berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
