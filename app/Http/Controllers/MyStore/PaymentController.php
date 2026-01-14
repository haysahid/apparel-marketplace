<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Repositories\PaymentRepository;
use App\Repositories\TransactionTypeRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
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
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $typeId = $request->input('type_id');

        $payments = PaymentRepository::getPayments(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            typeId: $typeId,
        );

        return Inertia::render('MyStore/Payment', [
            'payments' => $payments,
            'transactionTypes' => TransactionTypeRepository::getTransactionTypeDropdown(
                orderBy: 'name',
                orderDirection: 'asc',
            ),
        ]);
    }
}
