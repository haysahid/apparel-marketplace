<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Store;
use App\Models\UserStoreRole;
use App\Models\StoreSocialLink;
use App\Repositories\StoreRepository;
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
                'rajaongkir_origin_label' => 'nullable|string|max:255',
                'province_name' => 'nullable|string|max:255',
                'city_name' => 'nullable|string|max:255',
                'district_name' => 'nullable|string|max:255',
                'subdistrict_name' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:10',
                'advantages' => 'nullable|array',
                'social_links' => 'nullable|array',
            ],
            [
                'name.required' => 'Nama toko harus diisi.',
                'email.email' => 'Format email tidak valid.',
                'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
                'rajaongkir_origin_id.integer' => 'ID asal rajaongkir harus berupa angka.',
                'rajaongkir_origin_label.max' => 'Label asal rajaongkir tidak boleh lebih dari 255 karakter.',
                'province_name.max' => 'Nama provinsi tidak boleh lebih dari 255 karakter.',
                'city_name.max' => 'Nama kota tidak boleh lebih dari 255 karakter.',
                'district_name.max' => 'Nama kecamatan tidak boleh lebih dari 255 karakter.',
                'subdistrict_name.max' => 'Nama kelurahan tidak boleh lebih dari 255 karakter.',
                'zip_code.max' => 'Kode pos tidak boleh lebih dari 10 karakter.',
                'advantages.array' => 'Keunggulan harus berupa array.',
                'social_links.array' => 'Tautan sosial harus berupa array.',
            ]
        );

        try {
            $data = $request->only([
                'name',
                'description',
                'address',
                'email',
                'phone',
                'rajaongkir_origin_id',
                'rajaongkir_origin_label',
                'province_name',
                'city_name',
                'district_name',
                'subdistrict_name',
                'zip_code',
            ]);

            $advantages = $request->input('advantages');
            $socialLinks = $request->input('social_links');

            $store = StoreRepository::createStore(
                $data,
                $advantages,
                $socialLinks
            );

            return redirect()->route(
                'my-store.select-store',
                ['storeId' => $store->id]
            )->with(
                'success',
                'Toko berhasil dibuat. Silakan lengkapi informasi toko Anda di halaman pengaturan toko.'
            );
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
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
