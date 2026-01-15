<?php

namespace App\Repositories;

use App\Models\MembershipType;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MembershipTypeRepository
{
    public static function getMembershipTypes(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $membershipTypes = MembershipType::query();

        if ($storeId) {
            $membershipTypes->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($search) {
            $membershipTypes->where(function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('description', 'ilike', '%' . $search . '%');
            });
        }

        $membershipTypes->orderBy($orderBy, $orderDirection);

        return $membershipTypes->paginate($limit);
    }

    public static function getMembershipTypeDropdown($storeId = null, $orderBy = 'name', $orderDirection = 'asc', $limit = null)
    {
        $membershipTypeDropdown = MembershipType::query();

        if ($storeId) {
            $membershipTypeDropdown->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $membershipTypeDropdown->orderBy($orderBy, $orderDirection)->limit($limit)->get();
    }

    public static function isMembershipTypeExists($name, $storeId = null, $excludeId = null)
    {
        return MembershipType::where('name', $name)
            ->where('store_id', $storeId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    public static function createMembershipType(array $data)
    {
        $isExists = self::isMembershipTypeExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null,
        );

        if ($isExists) {
            Log::error('Jenis keanggotaan dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Jenis keanggotaan dengan nama ini sudah ada.', 409);
        }

        try {
            return MembershipType::create($data);
        } catch (Exception $e) {
            Log::error('Failed to create membership type: ' . $e);
            throw new Exception('Gagal menyimpan jenis keanggotaan: ' . $e->getMessage());
        }
    }

    public static function updateMembershipType(
        MembershipType $membershipType,
        array $data
    ) {
        $isExists = self::isMembershipTypeExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null,
            excludeId: $membershipType->id,
        );

        if ($isExists) {
            Log::error('Jenis keanggotaan dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Jenis keanggotaan dengan nama ini sudah ada.', 409);
        }

        try {
            return $membershipType->update($data);
        } catch (Exception $e) {
            Log::error('Failed to update membership type: ' . $e);
            throw new Exception('Gagal memperbarui jenis keanggotaan: ' . $e);
        }
    }

    public static function deleteMembershipType(MembershipType $membershipType)
    {
        try {
            DB::beginTransaction();

            // Delete the brand logo if it exists
            if ($membershipType->logo) {
                Storage::disk('public')->delete($membershipType->logo);
            }

            $membershipType->delete();

            DB::commit();
        } catch (Exception $e) {
            Log::error('Failed to delete membership type: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal menghapus jenis keanggotaan: ' . $e);
        }
    }
}
