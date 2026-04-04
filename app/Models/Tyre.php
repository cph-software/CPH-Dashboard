<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Tyre extends Model
{
    use BelongsToCompany;

    protected $table = 'tyres';
    protected $fillable = [
        'tyre_company_id', 'serial_number', 'custom_serial_number',
        'tyre_pattern_id', 'ply_rating', 'original_tread_depth',
        'is_in_warehouse', 'current_location_id', 'segment_name',
        'status', 'retread_count', 'tyre_brand_id', 'tyre_size_id', 'price',
        'initial_tread_depth', 'current_tread_depth',
        'total_lifetime_km', 'total_lifetime_hm', 'current_km', 'current_hm',
        'last_inspection_date', 'current_vehicle_id', 'current_position_id',
        'created_by', 'updated_by', 'last_hm_reading',
    ];

    protected $casts = [
        'is_in_warehouse' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(TyreBrand::class, 'tyre_brand_id');
    }

    public function size()
    {
        return $this->belongsTo(TyreSize::class, 'tyre_size_id');
    }

    public function pattern()
    {
        return $this->belongsTo(TyrePattern::class, 'tyre_pattern_id');
    }

    public function location()
    {
        return $this->belongsTo(TyreLocation::class, 'current_location_id');
    }

    public function company()
    {
        return $this->belongsTo(TyreCompany::class, 'tyre_company_id');
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

    public function monitoringInstallations()
    {
        return $this->hasMany(TyreMonitoringInstallation::class, 'tyre_id');
    }

    public function monitoringChecks()
    {
        return $this->hasMany(TyreMonitoringCheck::class, 'serial_number', 'serial_number');
    }

    public function monitoringRemovals()
    {
        return $this->hasMany(TyreMonitoringRemoval::class, 'serial_number', 'serial_number');
    }

    public function setSerialNumberAttribute($value)
    {
        $this->attributes['serial_number'] = strtoupper($value);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($tyre) {
            $tyre->syncCompanyCount();
        });

        static::deleted(function ($tyre) {
            $tyre->syncCompanyCount();
        });
    }

    public function syncCompanyCount()
    {
        if ($this->tyre_company_id) {
            $company = TyreCompany::find($this->tyre_company_id);
            if ($company) {
                // Synchronize total_tyre_capacity column with real count from tyres table
                $count = Tyre::where('tyre_company_id', $this->tyre_company_id)->count();
                $company->update(['total_tyre_capacity' => $count]);
            }
        }
    }
}

