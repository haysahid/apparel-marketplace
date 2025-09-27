<?php

namespace App\Repositories;

use App\Models\UserVoucher;

class UserVoucherRepository
{
    public static function getUserVouchers(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $userId = null,
        $activeOnly = true,
    ) {
        $query = UserVoucher::query();

        $query->with(['voucher']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($storeId) {
            $query->whereHas('voucher', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });
        }

        if ($activeOnly) {
            $query->whereHas('voucher', function ($q) {
                $q->whereNull('disabled_at')
                    ->where(function ($q2) {
                        $q2->where('redeem_start_date', '<=', now())
                            ->orWhereNull('redeem_start_date');
                    })
                    ->where(function ($q2) {
                        $q2->where('redeem_end_date', '>=', now())
                            ->orWhereNull('redeem_end_date');
                    });
            });
        }

        if ($search) {
            $query->whereHas('voucher', function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            });
        }

        return $query->orderBy($orderBy, $orderDirection)->paginate($limit);
    }
}
