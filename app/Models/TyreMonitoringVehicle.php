<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TyreMonitoringVehicle extends Model
{
    use HasFactory;

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
        'status',
        'master_vehicle_id',
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
