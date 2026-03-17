<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserTracking;
use App\Traits\BelongsToCompany;

class TyreLocation extends Model
{
    use UserTracking, BelongsToCompany;

    protected $table = 'tyre_locations';
    protected $guarded = [];

    public function segments()
    {
        return $this->hasMany(TyreSegment::class, 'tyre_location_id');
    }

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'work_location_id');
    }

    public function company()
    {
        return $this->belongsTo(TyreCompany::class, 'tyre_company_id');
    }

    public function setLocationNameAttribute($value)
    {
        $this->attributes['location_name'] = strtoupper($value);
    }
}
