<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyStoreUnitController extends Controller
{
    private $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $units = UnitRepository::getUnits(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/Unit', [
            'units' => $units,
        ]);
    }

    public function create()
    {
        return Inertia::render('MyStore/Unit/AddUnit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_dialog' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.string' => 'Nama satuan harus berupa teks.',
            'name.max' => 'Nama satuan maksimal 50 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
        ]);

        try {
            UnitRepository::createUnit([
                'store_id' => $this->storeId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            if ($request->boolean('is_dialog')) {
                return redirect()->back()->with('success', 'Satuan berhasil ditambahkan.')->with('closeDialog', true);
            }

            return redirect()->route('my-store.unit')->with('success', 'Satuan berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal menambahkan satuan: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Unit $unit)
    {
        return Inertia::render('MyStore/Unit/EditUnit', [
            'unit' => $unit,
        ]);
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.string' => 'Nama satuan harus berupa teks.',
            'name.max' => 'Nama satuan maksimal 50 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
        ]);

        try {
            UnitRepository::updateUnit($unit->id, [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            return redirect()->route('my-store.unit')->with('success', 'Satuan berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui satuan: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Unit $unit)
    {
        try {
            UnitRepository::deleteUnit($unit);

            return redirect()->route('my-store.unit')->with('success', 'Satuan berhasil dihapus.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus satuan: ' . $e->getMessage()]);
        }
    }
}
