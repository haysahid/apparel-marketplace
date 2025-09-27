<?php

namespace App\Repositories;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public static function createGuestUser(array $data)
    {
        try {
            $existingGuest = User::where('email', $data['email'])
                ->where('role_id', 8)
                ->first();

            if ($existingGuest) {
                // Update existing guest user data
                $existingGuest->update($data);
                return $existingGuest;
            }

            $user = User::create([
                ...$data,
                'role_id' => 8, // guest role
            ]);
            return $user;
        } catch (Exception $e) {
            Log::error('Gagal membuat user tamu: ' . $e);
            throw $e;
        }
    }

    public static function getCustomers(
        $storeId,
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        // Exclude superadmin and admin roles
        $query = User::with([
            'role',
            'store_roles' => function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            },
        ])->whereNotIn('role_id', [1, 2]);

        // Filter users who have transactions or roles in the specified store
        $query->where(function ($q) use ($storeId) {
            $q->whereHas('transactions', function ($q1) use ($storeId) {
                $q1->whereHas('invoices', function ($q2) use ($storeId) {
                    $q2->where('store_id', $storeId);
                });
            })->orWhereHas('store_roles', function ($q3) use ($storeId) {
                $q3->where('store_id', $storeId);
            });
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        return $query->orderBy($orderBy, $orderDirection)
            ->paginate($limit);
    }

    public static function getCustomerDetail($customerId)
    {
        $customer = User::with(['role', 'stores'])->find($customerId);
        $invoiceStats = DB::table('invoices')
            ->join('transactions', 'invoices.transaction_id', '=', 'transactions.id')
            ->where('transactions.user_id', $customerId)
            ->selectRaw('COUNT(invoices.id) as count_orders, SUM(invoices.amount) as total_spent')
            ->first();

        $countOrders = (int) $invoiceStats->count_orders ?? 0;
        $totalSpent = (int) $invoiceStats->total_spent ?? 0;

        return [
            'customer' => $customer,
            'count_orders' => $countOrders,
            'total_spent' => $totalSpent,
        ];
    }
}
