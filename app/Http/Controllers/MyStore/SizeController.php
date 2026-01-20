<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Size;
use App\Repositories\SizeRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SizeController extends Controller
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
        $orderBy = $request->input('order_by', 'id');
        $orderDirection = $request->input('order_direction', 'desc');

        $sizes = SizeRepository::getSizes(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return inertia('MyStore/Size/Index', [
            'sizes' => $sizes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('MyStore/Size/AddSize');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_dialog' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama ukuran harus diisi.',
            'name.string' => 'Nama ukuran harus berupa teks.',
            'name.max' => 'Nama ukuran tidak boleh lebih dari 255 karakter.',
        ]);

        try {
            SizeRepository::createSize([
                'store_id' => $this->storeId,
                'name' => $validated['name'],
            ]);

            if ($request->input('is_dialog')) {
                return redirect()->back()->with('success', 'Ukuran berhasil ditambahkan.');
            }

            return redirect()->route('my-store.size.index')->with('success', 'Ukuran berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menambahkan ukuran: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Size $size)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Size $size)
    {
        return Inertia::render('MyStore/Size/EditSize', [
            'size' => $size,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Size $size)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama ukuran harus diisi.',
            'name.string' => 'Nama ukuran harus berupa teks.',
            'name.max' => 'Nama ukuran tidak boleh lebih dari 255 karakter.',
        ]);

        try {
            SizeRepository::updateSize($size, [
                'store_id' => $this->storeId,
                'name' => $validated['name'],
            ]);

            return redirect()->route('my-store.size.index')->with('success', 'Ukuran berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui ukuran: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Size $size)
    {
        try {
            SizeRepository::deleteSize($size);

            return redirect()->route('my-store.size.index')->with('success', 'Ukuran berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus ukuran: ' . $e->getMessage()]);
        }
    }
}
