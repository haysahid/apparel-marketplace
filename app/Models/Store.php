<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Store extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'address',
        'email',
        'phone',
        'logo',
        'banner',
        'rajaongkir_origin_id',
        'rajaongkir_origin_label',
        'province_name',
        'city_name',
        'district_name',
        'subdistrict_name',
        'zip_code',
    ];

    // Additional atributes
    public function getUserRolePairsAttribute()
    {
        $usersById = $this->users->keyBy('id');
        return $this->store_roles->map(function ($storeRole) use ($usersById) {
            $user = $usersById->get($storeRole->pivot->user_id, null);
            return [
                'user' => $user,
                'role' => $storeRole,
            ];
        });
    }

    // Relationships - Users and Roles
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_store_role')->withTimestamps()->withPivot(['role_id']);
    }

    public function store_roles()
    {
        return $this->belongsToMany(Role::class, 'user_store_role')->withTimestamps()->withPivot(['user_id'])->orderBy('id');
    }

    // Relationships - Memberships

    public function user_store_roles()
    {
        return $this->hasMany(UserStoreRole::class);
    }

    public function membership_types()
    {
        return $this->hasMany(MembershipType::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'store_memberships', 'store_id', 'user_id')
            ->withTimestamps();
    }

    public function store_memberships()
    {
        return $this->hasMany(StoreMembership::class);
    }

    // Relationships - Other
    public function advantages()
    {
        return $this->hasMany(StoreAdvantage::class);
    }

    public function certificates()
    {
        return $this->hasMany(StoreCertificate::class);
    }

    public function social_links()
    {
        return $this->hasMany(StoreSocialLink::class);
    }

    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
