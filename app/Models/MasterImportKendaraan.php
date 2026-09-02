<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToCompany;

class MasterImportKendaraan extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'master_import_kendaraan';
    protected $guarded = [];

    protected $casts = [
        'permanent_deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($vehicle) {
            // Automatically create or update corresponding TyreMonitoringVehicle
            \App\Models\TyreMonitoringVehicle::updateOrCreate(
                ['master_vehicle_id' => $vehicle->id],
                [
                    'fleet_name' => $vehicle->kode_kendaraan,
                    'vehicle_number' => $vehicle->no_polisi ?: '-',
                    'driver_name' => 'Driver', // fallback default
                    'tire_positions' => $vehicle->total_tyre_position ?: 6,
                    'is_trail' => stripos($vehicle->model_kendaraan ?? '', 'trailer') !== false || stripos($vehicle->model_kendaraan ?? '', 'gandengan') !== false,
                    'status' => $vehicle->tyre_unit_status === 'Active' ? 'active' : 'inactive',
                    'tyre_company_id' => $vehicle->tyre_company_id ?? 1,
                    'measurement_unit' => $vehicle->measurement_unit ?? 'KM'
                ]
            );
        });

        static::deleted(function ($vehicle) {
            \App\Models\TyreMonitoringVehicle::where('master_vehicle_id', $vehicle->id)->delete();
        });
    }

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'current_vehicle_id')->withoutGlobalScope('company');
    }

    public function installedTyres()
    {
        return $this->hasMany(Tyre::class, 'current_vehicle_id')->withoutGlobalScope('company')->where('status', 'Installed');
    }

    public function getTyreCapacityLabelAttribute()
    {
        $installed = $this->tyres_count ?? $this->tyres()->count();
        $total = $this->total_tyre_position ?? 0;
        
        if ($total == 0) return "[0/0]";
        if ($installed >= $total) return "[Full]";
        return "[$installed/$total]";
    }

    public function segment()
    {
        return $this->belongsTo(TyreSegment::class, 'operational_segment_id');
    }

    public function company()
    {
        return $this->belongsTo(TyreCompany::class, 'tyre_company_id');
    }

    public function tyrePositionConfiguration()
    {
        return $this->belongsTo(TyrePositionConfiguration::class, 'tyre_position_configuration_id');
    }

    public function monitoringSessions()
    {
        return $this->hasMany(TyreMonitoringSession::class, 'master_vehicle_id')->withoutGlobalScope('company');
    }

    public function setKodeKendaraanAttribute($value)
    {
        $this->attributes['kode_kendaraan'] = strtoupper($value);
    }

    public function setNoPolisiAttribute($value)
    {
        $this->attributes['no_polisi'] = strtoupper($value);
    }
}
