<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyreSize extends Model
{
    protected $table = 'tyre_sizes';
    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(TyreBrand::class, 'tyre_brand_id');
    }

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'tyre_size_id');
    }
}
