<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyreLocation extends Model
{
    protected $table = 'tyre_locations';
    protected $guarded = [];

    public function segments()
    {
        return $this->hasMany(TyreSegment::class, 'tyre_location_id');
    }
}
