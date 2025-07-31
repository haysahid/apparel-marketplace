<?php

namespace App\Repositories;

use App\Models\Voucher;

class VoucherRepository
{
    public static function getVouchers(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $activeOnly = true,
    ) {
        $vouchers = Voucher::query();

        $vouchers->with(['store']);

        if ($storeId) {
            $vouchers->where('store_id', $storeId);
        } else {
            $vouchers->whereNull('store_id');
        }

        if ($activeOnly) {
            $vouchers->whereNull('disabled_at')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now());
        }

        if ($search) {
            $vouchers->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

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
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

        return $vouchers;
    }

    public static function getVoucherByCode($code, $storeId = null)
    {
        $voucher = Voucher::where('code', $code);

        if ($storeId) {
            $voucher->where('store_id', $storeId);
        } else {
            $voucher->whereNull('store_id');
        }

        $voucher->whereNull('disabled_at')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());

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

    public static function calculateVoucherAmount($voucher, $amount)
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

    public static function applyVoucherToInvoice($invoice, $voucher)
    {
        if (self::isVoucherValid($voucher, $invoice->amount)) {
            $voucherAmount = self::calculateVoucherAmount($voucher, $invoice->amount);
            $invoice->voucher_id = $voucher->id;
            $invoice->voucher_amount = $voucherAmount;
            $invoice->amount -= $voucherAmount;
            $invoice->save();
        }
    }

    public static function removeVoucherFromInvoice($invoice)
    {
        if ($invoice->voucher_id) {
            $invoice->amount += $invoice->voucher_amount;
            $invoice->voucher_id = null;
            $invoice->voucher_amount = 0;
            $invoice->save();
        }
    }

    public static function getVoucherById($id, $storeId = null)
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

    public static function getAllVouchers($storeId = null)
    {
        $vouchers = Voucher::query();

        if ($storeId) {
            $vouchers->where('store_id', $storeId);
        } else {
            $vouchers->whereNull('store_id');
        }

        return $vouchers->get();
    }

    public static function createVoucher(array $data)
    {
        return Voucher::create($data);
    }

    public static function updateVoucher(Voucher $voucher, array $data)
    {
        $voucher->update($data);
        return $voucher;
    }

    public static function deleteVoucher(Voucher $voucher)
    {
        return $voucher->delete();
    }
}
