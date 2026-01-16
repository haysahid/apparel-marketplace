<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'address',
        'phone',
        'avatar',
        'role_id',
        'disabled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Additional Attributes

    /**
     * Get store and role pairs for the user
     */
    public function getStoreRolePairs()
    {
        $storesById = $this->stores->keyBy('id');
        return $this->store_roles->map(function ($storeRole) use ($storesById) {
            $store = $storesById->get($storeRole->pivot->store_id, null);
            return [
                'store' => $store,
                'role' => $storeRole,
            ];
        });
    }

    /**
     * Check if user is admin (super-admin or admin)
     */
    public function isAdmin(): bool
    {
        return $this->role->slug === 'super-admin' || $this->role->slug === 'admin';
    }

    /**
     * Check if user has any store roles (super-admin, admin, store-owner)
     */
    public function hasStoreRoles(): bool
    {
        foreach ($this->store_roles as $storeRole) {
            if (in_array($storeRole->slug, ['super-admin', 'admin', 'store-owner'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has specific store role for a given store
     */
    public function hasStoreRole(int $storeId, array $roles): bool
    {
        foreach ($this->store_roles as $storeRole) {
            if ($storeRole->pivot->store_id === $storeId && in_array($storeRole->slug, $roles)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get store and membership pairs for the user
     */
    public function getStoreMembershipPairs()
    {
        $storesById = $this->member_of_stores->keyBy('id');
        return $this->store_memberships->map(function ($membership) use ($storesById) {
            $store = $storesById->get($membership->pivot->store_id, null);
            return [
                'store' => $store,
                'membership' => $membership,
            ];
        });
    }

    /**
     * Check if user has specific store membership for a given store
     */
    public function hasStoreMembership(
        int $storeId,
        array $memberships
    ): bool {
        foreach ($this->store_memberships as $membership) {
            if ($membership->pivot->store_id === $storeId && in_array($membership->slug, $memberships)) {
                return true;
            }
        }
        return false;
    }

    // Relationships - Roles and Stores
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'user_store_role')->withTimestamps()->withPivot(['store_id', 'role_id']);
    }

    public function store_roles()
    {
        return $this->belongsToMany(Role::class, 'user_store_role')
            ->withTimestamps()
            ->withPivot('store_id')
            ->whereNull('store.deleted_at')
            ->join('stores as store', 'user_store_role.store_id', '=', 'store.id')
        ;
    }

    public function user_store_roles()
    {
        return $this->hasMany(UserStoreRole::class);
    }

    // Relationships - Memberships
    public function member_of_stores()
    {
        return $this->belongsToMany(Store::class, 'store_membership', 'user_id', 'store_id')
            ->withTimestamps();
    }

    public function store_memberships()
    {
        return $this->belongsToMany(Membership::class, 'store_membership', 'user_id', 'membership_id')
            ->withTimestamps()
            ->withPivot('store_id')
            ->whereNull('store.deleted_at')
            ->join('stores as store', 'store_membership.store_id', '=', 'store.id')
        ;
    }

    public function user_store_memberships()
    {
        return $this->hasMany(StoreMembership::class);
    }


    // Relationships - Other
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'user_vouchers')->withTimestamps()->withPivot('usage_count');
    }

    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    public function user_points()
    {
        return $this->hasMany(UserPoint::class);
    }
}
