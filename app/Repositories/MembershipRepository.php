<?php

namespace App\Repositories;

use App\Models\Membership;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MembershipRepository
{
    public static function getMemberships(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $memberships = Membership::query();

        if ($storeId) {
            $memberships->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($search) {
            $memberships->where(function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('description', 'ilike', '%' . $search . '%');
            });
        }

        $memberships->orderBy($orderBy, $orderDirection);

        return $memberships->paginate($limit);
    }

    public static function getMembershipDropdown($storeId = null, $orderBy = 'name', $orderDirection = 'asc', $limit = null)
    {
        $membershipDropdown = Membership::query();

        if ($storeId) {
            $membershipDropdown->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $membershipDropdown->orderBy($orderBy, $orderDirection)->limit($limit)->get();
    }

    public static function isMembershipExists($name, $storeId = null, $excludeId = null)
    {
        return Membership::where('name', $name)
            ->where('store_id', $storeId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    public static function createMembership(array $data)
    {
        $isExists = self::isMembershipExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null,
        );

        if ($isExists) {
            Log::error('Jenis keanggotaan dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Jenis keanggotaan dengan nama ini sudah ada.', 409);
        }

        try {
            return Membership::create($data);
        } catch (Exception $e) {
            Log::error('Failed to create membership type: ' . $e);
            throw new Exception('Gagal menyimpan jenis keanggotaan: ' . $e->getMessage());
        }
    }

    public static function updateMembership(
        Membership $membership,
        array $data
    ) {
        $isExists = self::isMembershipExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null,
            excludeId: $membership->id,
        );

        if ($isExists) {
            Log::error('Jenis keanggotaan dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Jenis keanggotaan dengan nama ini sudah ada.', 409);
        }

        try {
            return $membership->update($data);
        } catch (Exception $e) {
            Log::error('Failed to update membership type: ' . $e);
            throw new Exception('Gagal memperbarui jenis keanggotaan: ' . $e);
        }
    }

    public static function deleteMembership(Membership $membership)
    {
        try {
            DB::beginTransaction();

            // Delete the brand logo if it exists
            if ($membership->logo) {
                Storage::disk('public')->delete($membership->logo);
            }

            $membership->delete();

            DB::commit();
        } catch (Exception $e) {
            Log::error('Failed to delete membership type: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal menghapus jenis keanggotaan: ' . $e);
        }
    }
}
