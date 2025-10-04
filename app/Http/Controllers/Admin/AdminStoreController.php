<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Repositories\StoreRepository;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminStoreController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $stores = StoreRepository::getStores(
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('Admin/Store', [
            'stores' => $stores,
        ]);
    }

    public function show(Store $store)
    {
        $storeDetail = StoreRepository::getStoreDetail($store->id);
        return Inertia::render('Admin/Store/StoreDetail', $storeDetail);
    }

    public function create()
    {
        return Inertia::render('Admin/Store/AddStore');
    }

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
                'admin.store.show',
                ['store' => $store]
            )->with(
                'success',
                'Toko berhasil dibuat. Silakan lengkapi informasi toko Anda di halaman pengaturan toko.'
            );
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Store $store)
    {
        $store = Store::with(['advantages', 'social_links'])->find($store->id);
        return Inertia::render('Admin/Store/EditStore', [
            'store' => $store,
        ]);
    }

    public function update(Request $request, Store $store)
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

            $storeRepository = new StoreRepository($store);
            $storeRepository->updateStoreInfo(
                data: $data,
                advantages: $advantages,
                socialLinks: $socialLinks
            );

            return redirect()->route(
                'admin.store.show',
                ['store' => $store]
            )->with(
                [
                    'success' => 'Informasi toko berhasil diperbarui.',
                ]
            );
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('admin.store.index')->with('success', 'Toko berhasil dihapus.');
    }
}
