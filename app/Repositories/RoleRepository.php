<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository
{
    public static function getRoles(
        $storeId = null,
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        $query = Role::query();

        if ($storeId) {
            $query->whereHas('users', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('slug', 'ilike', '%' . $search . '%');
            });
        }

        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($limit);
    }

    public static function getRoleDropdown($hideRoles = [])
    {
        $query = Role::query();

        if (!empty($hideRoles)) {
            $query->whereNotIn('slug', $hideRoles);
        }

        return $query->orderBy('id', 'asc')->get();
    }
}
