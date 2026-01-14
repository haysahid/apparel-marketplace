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

    // Relationships
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
        return $this->belongsToMany(Role::class, 'user_store_role')->withTimestamps()->withPivot('store_id');
    }

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
