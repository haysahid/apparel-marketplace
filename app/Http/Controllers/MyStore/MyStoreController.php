<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\StoreRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MyStoreController extends Controller
{
    protected $storeId;
    protected $storeRepository;

    public function __construct()
    {
        $this->storeId = session('selected_store_id', null);

        if (!$this->storeId) {
            return redirect()->route('store.create')
                ->with('error', 'Anda belum memiliki toko. Silakan buat toko terlebih dahulu.');
        }

        $store = Store::find($this->storeId);
        $this->storeRepository = new StoreRepository($store);
    }

    public function selectStore($storeId)
    {
        session(['selected_store_id' => $storeId]);
        Cookie::queue(Cookie::forever(
            name: 'selected_store_id',
            value: $storeId,
            secure: false,
            httpOnly: false,
        ));
        return redirect()->route('my-store.dashboard')
            ->with('success', 'Toko berhasil dipilih.');
    }

    public function index()
    {
        return redirect()->route('my-store.dashboard');
    }

    public function dashboard()
    {
        $productCount = Product::where('store_id', $this->storeId)->count();
        $userCount = User::count();
        return Inertia::render('MyStore/Dashboard', [
            'productCount' => $productCount,
            'userCount' => $userCount,
        ]);
    }

    public function show()
    {
        $storeDetail = StoreRepository::getStoreDetail(
            storeId: $this->storeId,
        );

        $roles = RoleRepository::getRoleDropdown();

        return Inertia::render('MyStore/Store/StoreDetail', [
            ...$storeDetail,
            'roles' => $roles,
        ]);
    }

    public function storeInfo()
    {
        return Inertia::render('MyStore/StoreInfo', [
            'store' => $this->storeRepository->getStoreInfo(),
        ]);
    }

    public function updateStore(Request $request)
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
                'logo' => 'nullable|image|max:2048',
                'banner' => 'nullable|image|max:2048',
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
                'logo.image' => 'Logo harus berupa gambar.',
                'logo.max' => 'Logo tidak boleh lebih dari 2MB.',
                'banner.image' => 'Banner harus berupa gambar.',
                'banner.max' => 'Banner tidak boleh lebih dari 2MB.',
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

            $this->storeRepository->updateStoreInfo(
                data: $data,
                advantages: $advantages,
                socialLinks: $socialLinks,
                logo: $request->hasFile('logo') ? $request->file('logo') : null,
                banner: $request->hasFile('banner') ? $request->file('banner') : null,
            );

            return redirect()->back()
                ->with('success', 'Informasi toko berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
