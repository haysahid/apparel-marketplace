<?php

namespace App\Repositories;

use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserVoucher;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public static function register(array $data)
    {
        try {
            $data['password'] = Hash::make($data['password']);
            $data['role_id'] = 3; // Default role for regular users

            // Create the user
            $user = User::create($data);

            // Add user points if rule exists
            $startingPoint = 0;
            $onRegisterPointRule = PointRule::where('type', 'on_signup')->first();

            if ($onRegisterPointRule) {
                // Get the points from the point rule
                $startingPoint = $onRegisterPointRule->points_earned;

                // Create initial point transaction
                PointTransaction::create([
                    'user_id' => $user->id,
                    'points' => $startingPoint,
                    'type' => 'earn',
                    'description' => 'Poin awal pendaftaran',
                    'points_amount' => $startingPoint,
                    'balance_before' => 0,
                    'balance_after' => $startingPoint,
                    'reference_type' => 'point_rule',
                    'point_rule_id' => $onRegisterPointRule->id,
                ]);
            }

            // Create user points record
            UserPoint::create([
                'user_id' => $user->id,
                'current_balance' => $startingPoint,
                'lifetime_points' => $startingPoint,
            ]);

            // Automatically log in the user after registration
            Auth::login($user);

            return $user;
        } catch (Exception $e) {
            Log::error('Gagal membuat user: ' . $e);
            throw $e;
        }
    }

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

    public static function getUsers(
        $storeId = null,
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        $query = User::with([
            'role',
            'stores',
            'store_roles',
        ]);

        if ($storeId) {
            $query->whereHas('store_roles', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->orderBy($orderBy, $orderDirection)
            ->paginate($limit);

        // Attach store-role pairs for each user
        $users->getCollection()->transform(function ($user) {
            $user->store_role_pairs = $user->getStoreRolePairsAttribute();
            $user->makeHidden([
                'stores',
                'store_roles'
            ]);
            return $user;
        });

        return $users;
    }

    public static function getUserDetail($userId)
    {
        $user =  User::with([
            'role',
            'stores',
            'store_roles',
            'user_points'
        ])->find($userId);

        $user->store_role_pairs = $user->getStoreRolePairsAttribute();
        $user->makeHidden([
            'stores',
            'store_roles'
        ]);

        $invoiceStats = DB::table('invoices')
            ->join('transactions', 'invoices.transaction_id', '=', 'transactions.id')
            ->where('transactions.user_id', $userId)
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
            'user' => $user,
            'count_orders' => $countOrders,
            'count_active_orders' => $countActiveOrders,
            'count_completed_orders' => $countCompletedOrders,
            'count_cancelled_orders' => $countCancelledOrders,
            'total_spent' => $totalSpent,
        ];
    }

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

    public static function getUserPointTransactions(
        $userId,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $type = null,
    ) {
        $query = PointTransaction::with([
            'transaction',
            'voucher',
            'admin',
            'point_rule',
        ])->where('user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                    ->orWhere('type', 'like', "%$search%");
            });
        }

        return $query->orderBy($orderBy, $orderDirection)
            ->paginate($limit);
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

    public static function getCustomerDetail($userId, $storeId = null)
    {
        $customer = User::with([
            'role',
            'store_roles',
            'user_points'
        ])->find($userId);

        $invoiceStats = DB::table('invoices')
            ->join('transactions', 'invoices.transaction_id', '=', 'transactions.id')
            ->where('transactions.user_id', $userId)
            ->when($storeId, function ($q) use ($storeId) {
                $q->where('invoices.store_id', $storeId);
            })
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
