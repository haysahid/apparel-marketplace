<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Repositories\VoucherRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserVoucherController extends Controller
{
    private $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    public function dropdown(Request $request)
    {
        $user_id = $request->input('user_id');
        $vouchers = VoucherRepository::getUserVouchers(
            userId: $user_id,
            storeId: $this->storeId,
        );

        return Inertia::render('MyStore/Voucher/Dropdown', [
            'vouchers' => $vouchers,
        ]);
    }
}
