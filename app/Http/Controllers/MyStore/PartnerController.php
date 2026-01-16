<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Repositories\PartnerRepository;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnerController extends Controller
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

        $partners = PartnerRepository::getPartners(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/Partner/Index', [
            'partners' => $partners,
        ]);
    }

    public function show(Partner $partner)
    {
        $partnerDetail = PartnerRepository::getPartnerDetail($partner->id);
        return Inertia::render('MyStore/Partner/PartnerDetail', $partnerDetail);
    }

    public function create()
    {
        return Inertia::render('MyStore/Partner/AddPartner');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif,svg,webp',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
        ], [
            'name.required' => 'Nama partner wajib diisi.',
            'name.string' => 'Nama partner harus berupa teks.',
            'name.max' => 'Nama partner maksimal 255 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            'logo.image' => 'Logo harus berupa file gambar.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'logo.mimes' => 'Format logo harus berupa jpeg, png, jpg, gif, svg, atau webp.',
            'contact_name.string' => 'Nama kontak harus berupa teks.',
            'contact_name.max' => 'Nama kontak maksimal 255 karakter.',
            'contact_email.email' => 'Email tidak valid.',
            'contact_email.max' => 'Email maksimal 255 karakter.',
            'contact_phone.string' => 'Nomor telepon harus berupa teks.',
            'contact_phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'website.url' => 'Website tidak valid.',
            'website.max' => 'Website maksimal 255 karakter.',
        ]);

        try {
            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo');
            }

            $validated['store_id'] = $this->storeId;

            PartnerRepository::createPartner($validated);

            return redirect()->route('my-store.partner.index')->with('success', 'Mitra berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal menambahkan mitra: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Partner $partner)
    {
        return Inertia::render('MyStore/Partner/EditPartner', [
            'partner' => $partner,
        ]);
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif,svg,webp',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
        ], [
            'name.required' => 'Nama partner wajib diisi.',
            'name.string' => 'Nama partner harus berupa teks.',
            'name.max' => 'Nama partner maksimal 255 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            'logo.image' => 'Logo harus berupa file gambar.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'logo.mimes' => 'Format logo harus berupa jpeg, png, jpg, gif, svg, atau webp.',
            'contact_name.string' => 'Nama kontak harus berupa teks.',
            'contact_name.max' => 'Nama kontak maksimal 255 karakter.',
            'contact_email.email' => 'Email tidak valid.',
            'contact_email.max' => 'Email maksimal 255 karakter.',
            'contact_phone.string' => 'Nomor telepon harus berupa teks.',
            'contact_phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'website.url' => 'Website tidak valid.',
            'website.max' => 'Website maksimal 255 karakter.',
        ]);

        try {
            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo');
            }

            $validated['store_id'] = $this->storeId;

            PartnerRepository::updatePartner($partner, $validated);

            return redirect()->route('my-store.partner.index')->with('success', 'Mitra berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui mitra: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Partner $partner)
    {
        try {
            PartnerRepository::deletePartner($partner);

            return redirect()->route('my-store.partner.index')->with('success', 'Mitra berhasil dihapus.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus mitra: ' . $e->getMessage()]);
        }
    }
}
