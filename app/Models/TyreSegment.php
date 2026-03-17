<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserTracking;
use App\Traits\BelongsToCompany;

class TyreSegment extends Model
{
    use UserTracking, BelongsToCompany;

    protected $table = 'tyre_segments';
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(TyreLocation::class, 'tyre_location_id');
    }

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'tyre_segment_id');
    }

    public function company()
    {
        return $this->belongsTo(TyreCompany::class, 'tyre_company_id');
    }

    public function setSegmentIdAttribute($value)
    {
        $this->attributes['segment_id'] = strtoupper($value);
    }

    public function setSegmentNameAttribute($value)
    {
        $this->attributes['segment_name'] = strtoupper($value);
    }
}
