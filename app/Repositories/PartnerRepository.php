<?php

namespace App\Repositories;

use App\Models\Partner;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PartnerRepository
{
    public static function getPartners(
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $storeId = null,
    ) {
        $partners = Partner::query();

        if ($storeId) {
            $partners->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($search) {
            $partners->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('contact_name', 'like', '%' . $search . '%')
                    ->orWhere('contact_email', 'like', '%' . $search . '%')
                    ->orWhere('contact_phone', 'like', '%' . $search . '%')
                    ->orWhere('website', 'like', '%' . $search . '%');
            });
        }

        $partners->orderBy($orderBy, $orderDirection);

        return $partners->paginate($limit);
    }

    public static function getPartnerDetail($partnerId): array
    {
        $partner = Partner::find($partnerId);
        $voucherStats = DB::table('vouchers')
            ->join('user_vouchers', 'vouchers.id', '=', 'user_vouchers.voucher_id')
            ->where('vouchers.partner_id', $partnerId)
            ->selectRaw('
                COUNT(*) as total_vouchers, 
                SUM(user_vouchers.usage_count * vouchers.amount) as total_balance
            ')
            ->first();

        $countVouchers = (int) $voucherStats->total_vouchers ?? 0;
        $currentBalance = (int) $voucherStats->total_balance ?? 0;
        // TODO: Implement withdrawal balance calculation
        $withdrawalBalance = 0;

        return [
            'partner' => $partner,
            'count_vouchers' => $countVouchers,
            'current_balance' => $currentBalance,
            'withdrawal_balance' => $withdrawalBalance,
        ];
    }

    public static function getPartnerDropdown(
        $orderBy = 'name',
        $orderDirection = 'asc',
        $storeId = null
    ) {
        $query = Partner::orderBy($orderBy, $orderDirection);

        if ($storeId) {
            $query->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $query->get();
    }

    public static function createPartner(array $data): Partner
    {
        try {
            DB::beginTransaction();

            // Handle logo upload if exists
            if (isset($data['logo'])) {
                $data['logo'] = Storage::put('partner', $data['logo']);
            }

            $data['slug'] = str($data['name'])->slug();

            $partner = Partner::create($data);

            DB::commit();
            return $partner;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat partner: ' . $e);
            throw new Exception('Gagal membuat partner: ' . $e->getMessage());
        }
    }

    public static function updatePartner(Partner $partner, array $data): Partner
    {
        try {
            DB::beginTransaction();

            // Handle logo upload if exists
            if (isset($data['logo'])) {
                // Delete old logo if exists
                if ($partner->logo) {
                    Storage::delete($partner->logo);
                }
                $data['logo'] = Storage::put('partner', $data['logo']);
            }

            if (isset($data['name']) && $data['name'] !== $partner->name) {
                $data['slug'] = str($data['name'])->slug();
            }

            $partner->update($data);

            DB::commit();
            return $partner;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui partner: ' . $e);
            throw new Exception('Gagal memperbarui partner: ' . $e->getMessage());
        }
    }

    public static function deletePartner(Partner $partner): void
    {
        try {
            DB::beginTransaction();

            // Delete the partner logo if exists
            if ($partner->logo) {
                Storage::delete($partner->logo);
            }

            $partner->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus partner: ' . $e);
            throw new Exception('Gagal menghapus partner: ' . $e->getMessage());
        }
    }
}
