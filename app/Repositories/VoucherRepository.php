<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Voucher;
use DateTime;
use Illuminate\Support\Facades\DB;

class VoucherRepository
{
    public static function getVouchers(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $userId = null,
        $activeOnly = true,
    ) {
        $vouchers = Voucher::query();

        $vouchers->with(['store']);

        if ($storeId) {
            $vouchers->where('store_id', $storeId);
        } else {
            $vouchers->whereNull('store_id');
        }

        if ($userId) {
            $vouchers->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        if ($activeOnly) {
            $vouchers->whereNull('disabled_at')
                ->where('redeem_start_date', '<=', now())
                ->where('redeem_end_date', '>=', now());
        }

        if ($search) {
            $vouchers->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $vouchers->select([
            'vouchers.*',
            DB::raw('EXISTS(SELECT 1 FROM user_vouchers WHERE user_vouchers.voucher_id = vouchers.id AND user_vouchers.user_id = ' . intval($userId) . ') as is_redeemed'),
        ]);

        return $vouchers->orderBy($orderBy, $orderDirection)->paginate($limit);
    }

    public static function getActiveVouchers($storeId)
    {
        $vouchers = Voucher::query();

        if ($storeId) {
            $vouchers->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        $vouchers->whereNull('disabled_at')
            ->where('redeem_start_date', '<=', now())
            ->where('redeem_end_date', '>=', now())
            ->get();

        return $vouchers;
    }

    public static function getVoucherByCode($code, $storeId = null): ?Voucher
    {
        $voucher = Voucher::where('code', $code);

        if ($storeId) {
            $voucher->where('store_id', $storeId);
        } else {
            $voucher->whereNull('store_id');
        }

        $voucher->whereNull('disabled_at')
            ->where('redeem_start_date', '<=', now())
            ->where('redeem_end_date', '>=', now())
            ->whereNull('required_points')
            ->where('is_internal', true);

        return $voucher->first();
    }

    public static function isVoucherValid($voucher, $amount)
    {
        if (!$voucher) {
            return false;
        }

        if ($voucher->disabled_at || $voucher->start_date > now() || $voucher->end_date < now()) {
            return false;
        }

        if ($voucher->min_amount && $amount < $voucher->min_amount) {
            return false;
        }

        if ($voucher->max_amount && $amount > $voucher->max_amount) {
            return false;
        }

        return true;
    }

    public static function calculateVoucherAmount($voucher, $amount): int
    {
        if (!$voucher) {
            return 0;
        }

        if ($voucher->type === 'fixed') {
            return min($voucher->amount, $amount);
        } elseif ($voucher->type === 'percentage') {
            return (int) (($voucher->amount / 100) * $amount);
        }

        return 0;
    }

    public static function applyVoucherToInvoice($invoice, $voucher): void
    {
        if (self::isVoucherValid($voucher, $invoice->amount)) {
            $voucherAmount = self::calculateVoucherAmount($voucher, $invoice->amount);
            $invoice->voucher_id = $voucher->id;
            $invoice->voucher_amount = $voucherAmount;
            $invoice->amount -= $voucherAmount;
            $invoice->save();
        }
    }

    public static function removeVoucherFromInvoice($invoice): void
    {
        if ($invoice->voucher_id) {
            $invoice->amount += $invoice->voucher_amount;
            $invoice->voucher_id = null;
            $invoice->voucher_amount = 0;
            $invoice->save();
        }
    }

    public static function getVoucherById($id, $storeId = null): ?Voucher
    {
        $query = Voucher::where('id', $id);

        if ($storeId) {
            $query->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $query->first();
    }

    public static function getAllVouchers(
        $storeId = null,
        $userId = null,
        $isPublic = null,
    ) {
        $vouchers = Voucher::query()->where('is_internal', true);

        if ($storeId) {
            $vouchers->where('store_id', $storeId);
        } else {
            $vouchers->whereNull('store_id');
        }

        if ($isPublic === true) {
            $vouchers->where(function ($q) use ($userId) {
                $q->where('is_public', true)
                    ->orWhereHas('users', function ($q2) use ($userId) {
                        $q2->where('user_id', $userId);
                    });
            });
        }

        $vouchers->select([
            'vouchers.*',
            DB::raw('EXISTS(SELECT 1 FROM user_vouchers WHERE user_vouchers.voucher_id = vouchers.id AND user_vouchers.user_id = ' . intval($userId) . ') as is_redeemed'),
        ]);

        return $vouchers->get();
    }

    public static function createVoucher(array $data)
    {
        return Voucher::create($data);
    }

    public static function updateVoucher(Voucher $voucher, array $data): Voucher
    {
        $voucher->update($data);
        return $voucher;
    }

    public static function deleteVoucher(Voucher $voucher): bool
    {
        return $voucher->delete();
    }

    public static function getUserVouchers($userId, $storeId = null)
    {
        $query = Voucher::with(['users' => function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }])
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });

        if ($storeId) {
            $query->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $query->get();
    }

    public static function getVoucherExpiryDate(Voucher $voucher): ?DateTime
    {
        if ($voucher->usage_duration_days) {
            return now()->addDays($voucher->usage_duration_days);
        } else if ($voucher->usage_end_date) {
            return new DateTime($voucher->usage_end_date);
        }

        return null;
    }

    public static function redeemVoucher(Voucher $voucher, User $user, bool $use = false)
    {
        if (!$voucher || !$user) {
            return false;
        }

        // Check if user already redeemed the voucher
        $existingUserVoucher = $voucher->user_vouchers()
            ->where('user_id', $user->id)
            ->first();
        if ($existingUserVoucher) {
            if ($voucher->usage_limit && $existingUserVoucher->usage_count >= $voucher->usage_limit) {
                // User has reached the usage limit
                return false;
            }

            // Increment usage count
            $existingUserVoucher->increment('usage_count');

            if ($use) {
                $existingUserVoucher->last_used_at = now();
            }

            $existingUserVoucher->updated_at = now();
            $existingUserVoucher->save();
        } else {
            // Redeem the voucher
            $voucher->user_vouchers()->create([
                'user_id' => $user->id,
                'unique_code' => strtoupper($voucher->code . '-' . $user->id . '-' . date('YmdHis')),
                'redeemed_at' => now(),
                'last_used_at' => $use ? now() : null,
                'usage_count' => 1,
                'expired_at' => self::getVoucherExpiryDate($voucher),
            ]);
        }

        return true;
    }

    public static function isVoucherRedeemedByUser(Voucher $voucher, User $user): bool
    {
        if (!$voucher || !$user) {
            return false;
        }

        $existingUserVoucher = $voucher->user_vouchers()
            ->where('user_id', $user->id)
            ->first();

        return $existingUserVoucher !== null;
    }
}
