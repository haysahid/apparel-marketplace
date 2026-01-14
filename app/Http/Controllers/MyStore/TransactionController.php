<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Repositories\TransactionRepository;
use App\Models\Brand;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Repositories\TransactionTypeRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    private $storeId;

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
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $typeId = $request->input('type_id');
        $brandId = $request->input('brand_id');
        $search = $request->input('search');

        $transactions = TransactionRepository::getTransactions(
            storeId: session('selected_store_id'),
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            typeId: $typeId,
            brandId: $brandId,
        );

        return Inertia::render('MyStore/Transaction', [
            'transactions' => $transactions,
            'transactionTypes' => TransactionTypeRepository::getTransactionTypeDropdown(
                orderBy: 'name',
                orderDirection: 'asc',
            ),
            'brands' => Brand::orderBy('name', 'asc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $transactionDetail = TransactionRepository::getTransactionDetail($transaction->code);

        return Inertia::render('MyStore/Transaction/TransactionDetail', $transactionDetail);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
