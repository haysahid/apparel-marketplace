<?php

namespace App\Repositories;

use App\Models\TransactionType;

class TransactionTypeRepository
{
    public static function getTransactionTypes(
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $transactionTypes = TransactionType::query();

        if ($search) {
            $transactionTypes->where(
                function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('effect_on_stock', 'like', '%' . $search . '%');
                }
            );
        }

        $transactionTypes->orderBy($orderBy, $orderDirection);

        return $transactionTypes->paginate($limit);
    }

    public static function getTransactionTypeDropdown($orderBy = 'name', $orderDirection = 'asc')
    {
        $transactionTypeDropdown = TransactionType::query();
        return $transactionTypeDropdown->orderBy($orderBy, $orderDirection)->get();
    }
}
