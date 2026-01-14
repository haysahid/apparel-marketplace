<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    protected $storeId;
    private $userRepository;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
        $this->userRepository = new UserRepository();
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $customers = $this->userRepository->getCustomers(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/Customer', [
            'customers' => $customers,
        ]);
    }

    public function show(User $customer)
    {
        $customerDetail = $this->userRepository->getCustomerDetail($customer->id, $this->storeId);
        return Inertia::render('MyStore/Customer/CustomerDetail', $customerDetail);
    }
}
