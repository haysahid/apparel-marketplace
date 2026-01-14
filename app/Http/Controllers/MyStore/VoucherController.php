<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Repositories\VoucherRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoucherController extends Controller
{
    protected $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $limit = request()->get('limit', 10);
        $search = request()->get('search');
        $orderBy = request()->get('order_by', 'created_at');
        $orderDirection = request()->get('order_direction', 'desc');

        $vouchers = VoucherRepository::getVouchers(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            activeOnly: false,
        );

        return Inertia::render('MyStore/Voucher', [
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('MyStore/Voucher/AddVoucher');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:vouchers,code',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'redeem_start_date' => 'required|date',
            'redeem_end_date' => 'nullable|date|after_or_equal:redeem_start_date',
            'usage_duration_days' => 'nullable|integer|min:0',
            'usage_start_date' => 'nullable|date',
            'usage_end_date' => 'nullable|date|after_or_equal:usage_start_date',
            'usage_limit' => 'nullable|integer|min:0',
            'required_points' => 'nullable|integer|min:0',
            'usage_url' => 'nullable|url',
            'is_public' => 'nullable|boolean',
            'is_internal' => 'nullable|boolean',
            'partner_id' => 'nullable|exists:partners,id',
        ], [
            'name.required' => 'Nama voucher wajib diisi.',
            'name.string' => 'Nama voucher harus berupa teks.',
            'name.max' => 'Nama voucher maksimal 255 karakter.',
            'code.required' => 'Kode voucher wajib diisi.',
            'code.string' => 'Kode voucher harus berupa teks.',
            'code.max' => 'Kode voucher maksimal 50 karakter.',
            'code.unique' => 'Kode voucher sudah digunakan.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            'type.required' => 'Tipe voucher wajib diisi.',
            'type.in' => 'Tipe voucher tidak valid.',
            'amount.required' => 'Jumlah voucher wajib diisi.',
            'amount.numeric' => 'Jumlah voucher harus berupa angka.',
            'amount.min' => 'Jumlah voucher minimal 0.',
            'min_amount.numeric' => 'Jumlah minimum harus berupa angka.',
            'min_amount.min' => 'Jumlah minimum minimal 0.',
            'max_amount.numeric' => 'Jumlah maksimum harus berupa angka.',
            'max_amount.min' => 'Jumlah maksimum minimal 0.',
            'redeem_start_date.required' => 'Tanggal mulai penukaran wajib diisi.',
            'redeem_start_date.date' => 'Tanggal mulai penukaran tidak valid.',
            'redeem_end_date.date' => 'Tanggal akhir penukaran tidak valid.',
            'redeem_end_date.after_or_equal' => 'Tanggal akhir penukaran harus setelah atau sama dengan tanggal mulai penukaran.',
            'usage_duration_days.integer' => 'Durasi penggunaan harus berupa angka bulat.',
            'usage_duration_days.min' => 'Durasi penggunaan minimal 0 hari.',
            'usage_start_date.date' => 'Tanggal mulai penggunaan tidak valid.',
            'usage_end_date.date' => 'Tanggal akhir penggunaan tidak valid.',
            'usage_end_date.after_or_equal' => 'Tanggal akhir penggunaan harus setelah atau sama dengan tanggal mulai penggunaan.',
            'usage_limit.integer' => 'Batas penggunaan harus berupa angka bulat.',
            'usage_limit.min' => 'Batas penggunaan minimal 0.',
            'required_points.integer' => 'Poin yang dibutuhkan harus berupa angka bulat.',
            'required_points.min' => 'Poin yang dibutuhkan minimal 0.',
            'usage_url.url' => 'URL penggunaan tidak valid.',
            'is_public.boolean' => 'Nilai publik tidak valid.',
            'is_internal.boolean' => 'Nilai internal tidak valid.',
            'partner_id.exists' => 'Mitra tidak ditemukan.',
        ]);

        $validated['store_id'] = $this->storeId;

        VoucherRepository::createVoucher($validated);

        return redirect()->route('my-store.voucher');
    }

    /**
     * Display the specified resource.
     */
    public function show(Voucher $voucher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Voucher $voucher)
    {
        return Inertia::render('MyStore/Voucher/EditVoucher', [
            'voucher' => $voucher,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'redeem_start_date' => 'required|date',
            'redeem_end_date' => 'required|date|after_or_equal:redeem_start_date',
            'usage_duration_days' => 'nullable|integer|min:0',
            'usage_start_date' => 'nullable|date',
            'usage_end_date' => 'nullable|date|after_or_equal:usage_start_date',
            'usage_limit' => 'nullable|integer|min:0',
            'required_points' => 'nullable|integer|min:0',
            'usage_url' => 'nullable|url',
            'is_public' => 'nullable|boolean',
            'is_internal' => 'nullable|boolean',
            'partner_id' => 'nullable|exists:partners,id',
        ], [
            'name.required' => 'Nama voucher wajib diisi.',
            'name.string' => 'Nama voucher harus berupa teks.',
            'name.max' => 'Nama voucher maksimal 255 karakter.',
            'code.required' => 'Kode voucher wajib diisi.',
            'code.string' => 'Kode voucher harus berupa teks.',
            'code.max' => 'Kode voucher maksimal 50 karakter.',
            'code.unique' => 'Kode voucher sudah digunakan.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            'type.required' => 'Tipe voucher wajib diisi.',
            'type.in' => 'Tipe voucher tidak valid.',
            'amount.required' => 'Jumlah voucher wajib diisi.',
            'amount.numeric' => 'Jumlah voucher harus berupa angka.',
            'amount.min' => 'Jumlah voucher minimal 0.',
            'min_amount.numeric' => 'Jumlah minimum harus berupa angka.',
            'min_amount.min' => 'Jumlah minimum minimal 0.',
            'max_amount.numeric' => 'Jumlah maksimum harus berupa angka.',
            'max_amount.min' => 'Jumlah maksimum minimal 0.',
            'redeem_start_date.required' => 'Tanggal mulai penukaran wajib diisi.',
            'redeem_start_date.date' => 'Tanggal mulai penukaran tidak valid.',
            'redeem_end_date.required' => 'Tanggal akhir penukaran wajib diisi.',
            'redeem_end_date.date' => 'Tanggal akhir penukaran tidak valid.',
            'redeem_end_date.after_or_equal' => 'Tanggal akhir penukaran harus setelah atau sama dengan tanggal mulai penukaran.',
            'usage_duration_days.integer' => 'Durasi penggunaan harus berupa angka bulat.',
            'usage_duration_days.min' => 'Durasi penggunaan minimal 0 hari.',
            'usage_start_date.date' => 'Tanggal mulai penggunaan tidak valid.',
            'usage_end_date.date' => 'Tanggal akhir penggunaan tidak valid.',
            'usage_end_date.after_or_equal' => 'Tanggal akhir penggunaan harus setelah atau sama dengan tanggal mulai penggunaan.',
            'usage_limit.integer' => 'Batas penggunaan harus berupa angka bulat.',
            'usage_limit.min' => 'Batas penggunaan minimal 0.',
            'required_points.integer' => 'Poin yang dibutuhkan harus berupa angka bulat.',
            'required_points.min' => 'Poin yang dibutuhkan minimal 0.',
            'usage_url.url' => 'URL penggunaan tidak valid.',
            'is_public.boolean' => 'Nilai publik tidak valid.',
            'is_internal.boolean' => 'Nilai internal tidak valid.',
            'partner_id.exists' => 'Mitra tidak ditemukan.',
        ]);

        VoucherRepository::updateVoucher($voucher, $validated);

        return redirect()->route('my-store.voucher');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        VoucherRepository::deleteVoucher($voucher);

        return redirect()->route('my-store.voucher');
    }
}
