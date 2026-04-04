<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserTracking;

class TyreSegment extends Model
{
    use UserTracking;

    protected $table = 'tyre_segments';
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(TyreLocation::class, 'tyre_location_id');
    }

    // Relasi tyres() dihapus: kolom tyre_segment_id sudah diganti segment_name (string) di tabel tyres

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
