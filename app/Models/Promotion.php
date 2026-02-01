<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'slug',
        'description',
        'image',
        'redirection_url',
        'start_date',
        'end_date',
    ];

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
