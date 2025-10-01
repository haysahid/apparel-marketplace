<?php

namespace App\Repositories;

use App\Models\PointRule;

class PointRuleRepository
{
    public static function getPointRules(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $pointRules = PointRule::query();

        if ($storeId) {
            $pointRules->where('store_id', $storeId);
        }

        if ($search) {
            $pointRules->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        $pointRules->orderBy($orderBy, $orderDirection);

        return $pointRules->paginate($limit);
    }

    public static function isPointRuleExists($name, $storeId = null, $excludeId = null)
    {
        return PointRule::where('name', $name)
            ->where('store_id', $storeId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    public static function createPointRule(array $data)
    {
        return PointRule::create($data);
    }

    public static function updatePointRule(PointRule $pointRule, array $data)
    {
        $pointRule->update($data);
        return $pointRule;
    }

    public static function deletePointRule(PointRule $pointRule)
    {
        return $pointRule->delete();
    }
}
