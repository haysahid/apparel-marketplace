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
            ->selectRaw(join(', ', [
                'COUNT(invoices.id) as count_orders',
                "COUNT(CASE WHEN invoices.status IN ('pending', 'paid', 'processing') THEN 1 END) as count_active_orders",
                "COUNT(CASE WHEN invoices.status = 'completed' THEN 1 END) as count_completed_orders",
                "COUNT(CASE WHEN invoices.status = 'cancelled' THEN 1 END) as count_cancelled_orders",
                'SUM(invoices.amount) as total_spent',
            ]))
            ->first();

        $countOrders = (int) $invoiceStats->count_orders ?? 0;
        $countActiveOrders = (int) $invoiceStats->count_active_orders ?? 0;
        $countCompletedOrders = (int) $invoiceStats->count_completed_orders ?? 0;
        $countCancelledOrders = (int) $invoiceStats->count_cancelled_orders ?? 0;
        $totalSpent = (int) $invoiceStats->total_spent ?? 0;

        return [
            'customer' => $customer,
            'count_orders' => $countOrders,
            'count_active_orders' => $countActiveOrders,
            'count_completed_orders' => $countCompletedOrders,
            'count_cancelled_orders' => $countCancelledOrders,
            'total_spent' => $totalSpent,
        ];
    }
}
