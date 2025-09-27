<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'store_users')->withPivot('role');
    }

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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_store_role');
    }

    public function partners()
    {
        return $this->hasMany(Partner::class);
    }
}
