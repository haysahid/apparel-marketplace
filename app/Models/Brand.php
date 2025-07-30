<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'logo',
        'website',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
