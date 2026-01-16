<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Repositories\MembershipRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MembershipController extends Controller
{
    protected $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'level');
        $orderDirection = $request->input('order_direction', 'asc');

        $memberships = MembershipRepository::getMemberships(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/Membership/Index', [
            'memberships' => $memberships,
        ]);
    }

    public function create()
    {
        return Inertia::render('MyStore/Membership/AddMembership');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255',
            'level' => 'required|integer',
            'description' => 'nullable|string|max:1000',
            'item_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'shipping_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'hex_code_bg' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'hex_code_text' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ], [
            'name.required' => 'Nama jenis keanggotaan wajib diisi.',
            'name.max' => 'Nama jenis keanggotaan maksimal 255 karakter.',
            'group.required' => 'Grup jenis keanggotaan wajib diisi.',
            'group.max' => 'Grup jenis keanggotaan maksimal 255 karakter.',
            'level.required' => 'Level jenis keanggotaan wajib diisi.',
            'level.integer' => 'Level jenis keanggotaan harus berupa angka bulat.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'item_discount_percentage.numeric' => 'Persentase diskon item harus berupa angka.',
            'item_discount_percentage.min' => 'Persentase diskon item minimal 0%.',
            'item_discount_percentage.max' => 'Persentase diskon item maksimal 100%.',
            'shipping_discount_percentage.numeric' => 'Persentase diskon pengiriman harus berupa angka.',
            'shipping_discount_percentage.min' => 'Persentase diskon pengiriman minimal 0%.',
            'shipping_discount_percentage.max' => 'Persentase diskon pengiriman maksimal 100%.',
            'min_purchase_amount.numeric' => 'Jumlah pembelian minimum harus berupa angka.',
            'min_purchase_amount.min' => 'Jumlah pembelian minimum minimal 0.',
            'hex_code_bg.regex' => 'Kode warna latar belakang tidak valid.',
            'hex_code_text.regex' => 'Kode warna teks tidak valid.',
        ]);

        try {
            $validated['store_id'] = $this->storeId;
            $textToSlug = $validated['name'];
            if (isset($validated['alias']) && !empty($validated['alias'])) {
                $textToSlug = $textToSlug . ' ' . $validated['alias'];
            }
            $validated['slug'] = Str::slug($textToSlug);

            MembershipRepository::createMembership($validated);

            Log::info('Membership type created', ['store_id' => $this->storeId, 'membership' => $validated]);

            return redirect()->route('my-store.membership.index')
                ->with('success', 'Jenis keanggotaan berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Membership $membership)
    {
        return Inertia::render('MyStore/Membership/EditMembership', [
            'membership' => $membership,
        ]);
    }

    public function update(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'group' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255',
            'level' => 'required|integer',
            'description' => 'nullable|string|max:1000',
            'item_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'shipping_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'hex_code_bg' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'hex_code_text' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ], [
            'name.required' => 'Nama jenis keanggotaan wajib diisi.',
            'name.max' => 'Nama jenis keanggotaan maksimal 255 karakter.',
            'group.required' => 'Grup jenis keanggotaan wajib diisi.',
            'group.max' => 'Grup jenis keanggotaan maksimal 255 karakter.',
            'level.required' => 'Level jenis keanggotaan wajib diisi.',
            'level.integer' => 'Level jenis keanggotaan harus berupa angka bulat.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'item_discount_percentage.numeric' => 'Persentase diskon item harus berupa angka.',
            'item_discount_percentage.min' => 'Persentase diskon item minimal 0%.',
            'item_discount_percentage.max' => 'Persentase diskon item maksimal 100%.',
            'shipping_discount_percentage.numeric' => 'Persentase diskon pengiriman harus berupa angka.',
            'shipping_discount_percentage.min' => 'Persentase diskon pengiriman minimal 0%.',
            'shipping_discount_percentage.max' => 'Persentase diskon pengiriman maksimal 100%.',
            'min_purchase_amount.numeric' => 'Jumlah pembelian minimum harus berupa angka.',
            'min_purchase_amount.min' => 'Jumlah pembelian minimum minimal 0.',
            'hex_code_bg.regex' => 'Kode warna latar belakang tidak valid.',
            'hex_code_text.regex' => 'Kode warna teks tidak valid.',
        ]);

        try {
            $textToSlug = $validated['name'];
            if (isset($validated['alias']) && !empty($validated['alias'])) {
                $textToSlug = $textToSlug . ' ' . $validated['alias'];
            }
            $validated['slug'] = Str::slug($textToSlug);

            MembershipRepository::updateMembership($membership, $validated);

            return redirect()->route('my-store.membership.index')
                ->with('success', 'Jenis keanggotaan berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Membership $membership)
    {
        try {
            MembershipRepository::deleteMembership($membership);

            return redirect()->route('my-store.membership.index')
                ->with('success', 'Jenis keanggotaan berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
