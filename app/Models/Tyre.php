<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Tyre extends Model
{
    use BelongsToCompany;

    protected $table = 'tyres';
    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(TyreBrand::class, 'tyre_brand_id');
    }

    public function size()
    {
        return $this->belongsTo(TyreSize::class, 'tyre_size_id');
    }

    public function segment()
    {
        return $this->belongsTo(TyreSegment::class, 'tyre_segment_id');
    }

    public function pattern()
    {
        return $this->belongsTo(TyrePattern::class, 'tyre_pattern_id');
    }

    public function location()
    {
        return $this->belongsTo(TyreLocation::class, 'work_location_id');
    }

    public function currentVehicle()
    {
        return $this->belongsTo(MasterImportKendaraan::class, 'current_vehicle_id');
    }

    public function currentPosition()
    {
        return $this->belongsTo(TyrePosition::class, 'current_position_id');
    }

    public function latestInstallation()
    {
        return $this->hasOne(TyreMovement::class, 'tyre_id')
            ->where('movement_type', 'Installation')
            ->latest('movement_date')
            ->latest('id');
    }

    public function movements()
    {
        return $this->hasMany(TyreMovement::class, 'tyre_id');
    }
}
