<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Repositories\VoucherRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyStoreVoucherController extends Controller
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:0',
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:0',
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
