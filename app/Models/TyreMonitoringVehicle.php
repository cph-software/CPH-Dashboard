<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class TyreMonitoringVehicle extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'tyre_monitoring_vehicle';
    protected $primaryKey = 'vehicle_id';

    protected $fillable = [
        'fleet_name',
        'vehicle_number',
        'driver_name',
        'phone_number',
        'application',
        'load_capacity',
        'tire_positions',
        'is_trail',
        'status',
        'master_vehicle_id',
        'tyre_company_id',
    ];

    protected $casts = [
        'is_trail' => 'boolean'
    ];

    public function masterVehicle()
    {
        return $this->belongsTo(MasterImportKendaraan::class, 'master_vehicle_id');
    }

    public function sessions()
    {
        return $this->hasMany(TyreMonitoringSession::class, 'vehicle_id', 'vehicle_id');
    }
}
