<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Store;
use App\Models\StoreRole;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('CreateStore');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'address' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'rajaongkir_origin_id' => 'nullable|integer',
                'rajaongkir_origin_name' => 'nullable|string',
                'zip_code' => 'nullable|string|max:10',
                'advantages' => 'array',
                'social_links' => 'array',
            ],
            [
                'name.required' => 'Nama toko harus diisi.',
                'email.email' => 'Format email tidak valid.',
                'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
                'rajaongkir_origin_id.integer' => 'ID asal rajaongkir harus berupa angka.',
                'zip_code.max' => 'Kode pos tidak boleh lebih dari 10 karakter.',
                'advantages.array' => 'Keunggulan harus berupa array.',
                'social_links.array' => 'Tautan sosial harus berupa array.',
            ]
        );

        try {
            DB::beginTransaction();

            // Create store
            $store = Store::create($request->only([
                'name',
                'description',
                'address',
                'email',
                'phone',
                'rajaongkir_origin_id',
                'rajaongkir_origin_name',
                'zip_code',
            ]));

            // Create user store relationship
            StoreRole::create([
                'store_id' => $store->id,
                'user_id' => Auth::id(),
                'role_id' => Role::where('slug', 'store-owner')->first()->id,
            ]);

            // Create advantages
            if ($request->has('advantages')) {
                foreach ($request->input('advantages') as $advantage) {
                    $store->advantages()->create([
                        'store_id' => $store->id,
                        'name' => $advantage['name'],
                        'description' => $advantage['description'] ?? null,
                    ]);
                }
            }

            // Create social links
            if ($request->has('social_links')) {
                foreach ($request->input('social_links') as $link) {
                    $store->social_links()->create([
                        'store_id' => $store->id,
                        'url' => $link['url'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('home', $store)->with('success', 'Toko berhasil dibuat. Silakan lengkapi informasi toko Anda di halaman pengaturan toko.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Store $store)
    {
        $store = Store::with([
            'advantages',
            'social_links',
        ])->first();

        return Inertia::render('Admin/StoreInfo', [
            'store' => $store,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'address' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'advantages' => 'array',
                'social_links' => 'array',
            ],
            [
                'name.required' => 'Nama toko harus diisi.',
                'email.email' => 'Format email tidak valid.',
                'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
            ]
        );

        try {
            DB::beginTransaction();

            $store = Store::firstOrFail();

            $store->update($request->only([
                'name',
                'description',
                'address',
                'email',
                'phone',
            ]));

            // Update advantages
            if ($request->has('advantages')) {
                $currentAdvantages = $store->advantages()->pluck('id')->toArray();
                foreach ($request->input('advantages') as $advantage) {
                    if (isset($advantage['id']) && in_array($advantage['id'], $currentAdvantages)) {
                        // Update existing advantage
                        $store->advantages()->where('id', $advantage['id'])->update([
                            'name' => $advantage['name'],
                            'description' => $advantage['description'] ?? null,
                        ]);
                    } else {
                        // Create new advantage
                        $store->advantages()->create([
                            'store_id' => $store->id,
                            'name' => $advantage['name'],
                            'description' => $advantage['description'] ?? null,
                        ]);
                    }
                }
            }

            // Update social links
            if ($request->has('social_links')) {
                $currentLinks = $store->social_links()->pluck('id')->toArray();
                foreach ($request->input('social_links') as $link) {
                    if (isset($link['id']) && in_array($link['id'], $currentLinks)) {
                        // Update existing social link
                        $store->social_links()->where('id', $link['id'])->update([
                            'url' => $link['url'],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.store.edit')
                ->with('success', 'Informasi toko berhasil diperbarui.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui informasi toko: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store)
    {
        //
    }
}
