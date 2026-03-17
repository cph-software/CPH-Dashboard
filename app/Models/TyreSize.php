<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserTracking;

class TyreSize extends Model
{
    use UserTracking;
    protected $table = 'tyre_sizes';
    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(TyreBrand::class, 'tyre_brand_id');
    }

    public function pattern()
    {
        return $this->belongsTo(TyrePattern::class, 'tyre_pattern_id');
    }

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'tyre_size_id');
    }

    public function setSizeAttribute($value)
    {
        $this->attributes['size'] = strtoupper($value);
    }

    public function companies()
    {
        return $this->belongsToMany(TyreCompany::class, 'tyre_company_sizes', 'tyre_size_id', 'tyre_company_id');
    }
}
