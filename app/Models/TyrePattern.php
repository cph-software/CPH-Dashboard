<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserTracking;

class TyrePattern extends Model
{
    use UserTracking;
    protected $table = 'tyre_patterns';
    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(TyreBrand::class, 'tyre_brand_id');
    }

    public function sizes()
    {
        return $this->hasMany(TyreSize::class, 'tyre_pattern_id');
    }

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'tyre_pattern_id');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function companies()
    {
        return $this->belongsToMany(TyreCompany::class, 'tyre_company_patterns', 'tyre_pattern_id', 'tyre_company_id');
    }
}
