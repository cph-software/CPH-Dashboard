<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyreCompany extends Model
{
    protected $table = 'tyre_companies';
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class, 'tyre_company_id');
    }

    public function aliases()
    {
        return $this->hasMany(TyreFailureAlias::class, 'tyre_company_id');
    }

    public function brands()
    {
        return $this->belongsToMany(TyreBrand::class, 'tyre_company_brands', 'tyre_company_id', 'tyre_brand_id');
    }

    public function patterns()
    {
        return $this->belongsToMany(TyrePattern::class, 'tyre_company_patterns', 'tyre_company_id', 'tyre_pattern_id');
    }

    public function sizes()
    {
        return $this->belongsToMany(TyreSize::class, 'tyre_company_sizes', 'tyre_company_id', 'tyre_size_id');
    }
}
