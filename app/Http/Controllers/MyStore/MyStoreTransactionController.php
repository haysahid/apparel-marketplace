<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Repositories\TransactionRepository;
use App\Models\Brand;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyStoreTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $brandId = $request->input('brand_id');
        $search = $request->input('search');

        $transactions = Transaction::query()->with([
            'user',
            'payment_method',
            'shipping_method',
            'items.variant.product.brand',
        ]);

        if ($brandId) {
            $transactions->whereHas('items.variant.product.brand', function ($query) use ($brandId) {
                $query->where('id', $brandId);
            });
        }

        if ($search) {
            $transactions->where(function ($query) use ($search) {
                $query->whereHas('invoices', function ($q) use ($search) {
                    $q->where('code', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('items.variant', function ($q) use ($search) {
                        $q->where('motif', 'like', '%' . $search . '%')
                            ->orWhereHas('color', function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('size', function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%');
                            })
                            ->where('material', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('items.variant.product', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhereHas('brand', function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('categories', function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $transactions->orderBy($orderBy, $orderDirection);

        return Inertia::render('MyStore/Transaction', [
            'transactions' => $transactions->paginate($limit)->withQueryString(),
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
