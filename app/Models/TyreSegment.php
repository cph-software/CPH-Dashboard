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

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'tyre_segment_id');
    }
}
