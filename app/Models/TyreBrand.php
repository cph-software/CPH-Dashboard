<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserTracking;

class TyreBrand extends Model
{
    use UserTracking;
    protected $table = 'tyre_brands';
    protected $guarded = [];

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'tyre_brand_id');
    }

    public function sizes()
    {
        return $this->hasMany(TyreSize::class, 'tyre_brand_id');
    }

    public function patterns()
    {
        return $this->hasMany(TyrePattern::class, 'tyre_brand_id');
    }
}
