<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Repositories\ColorRepository;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ColorController extends Controller
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

        $colors = ColorRepository::getColors(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/Color/Index', [
            'colors' => $colors,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('MyStore/Color/AddColor');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_dialog' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama warna harus diisi.',
            'name.string' => 'Nama warna harus berupa string.',
            'name.max' => 'Nama warna tidak boleh lebih dari 255 karakter.',
            'hex_code.string' => 'Kode hex harus berupa string.',
            'hex_code.max' => 'Kode hex tidak boleh lebih dari 7 karakter.',
            'hex_code.regex' => 'Kode hex harus dalam format #RRGGBB atau #RGB.',
        ]);

        try {
            ColorRepository::createColor([
                'store_id' => $this->storeId,
                'name' => $validated['name'],
                'hex_code' => $validated['hex_code'],
            ]);

            if ($request->input('is_dialog')) {
                return redirect()->back()->with('success', 'Warna berhasil ditambahkan.');
            }

            return redirect()->route('my-store.color.index')->with('success', 'Warna berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Color $color)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Color $color)
    {
        return Inertia::render('MyStore/Color/EditColor', [
            'color' => $color,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Color $color)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ], [
            'name.required' => 'Nama warna harus diisi.',
            'name.string' => 'Nama warna harus berupa string.',
            'name.max' => 'Nama warna tidak boleh lebih dari 255 karakter.',
            'hex_code.string' => 'Kode hex harus berupa string.',
            'hex_code.max' => 'Kode hex tidak boleh lebih dari 7 karakter.',
            'hex_code.regex' => 'Kode hex harus dalam format #RRGGBB atau #RGB.',
        ]);

        try {
            ColorRepository::updateColor($color, [
                'store_id' => $this->storeId,
                'name' => $validated['name'],
                'hex_code' => $validated['hex_code'],
            ]);

            return redirect()->route('my-store.color.index')->with('success', 'Warna berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color)
    {
        try {
            ColorRepository::deleteColor($color);

            return redirect()->route('my-store.color.index')->with('success', 'Warna berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
