<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TyreMonitoringSession extends Model
{
    use HasFactory;

    protected $table = 'tyre_monitoring_session';
    protected $primaryKey = 'session_id';

    protected $fillable = [
        'vehicle_id',
        'install_date',
        'tyre_size',
        'original_rtd',
        'odometer_start',
        'pattern',
        'retase',
        'status',
    ];

    public function vehicle()
    {
        return $this->belongsTo(TyreMonitoringVehicle::class, 'vehicle_id', 'vehicle_id');
    }

    public function installations()
    {
        return $this->hasMany(TyreMonitoringInstallation::class, 'session_id', 'session_id');
    }

    public function checks()
    {
        return $this->hasMany(TyreMonitoringCheck::class, 'session_id', 'session_id');
    }

    public function removal()
    {
        return $this->hasOne(TyreMonitoringRemoval::class, 'session_id', 'session_id');
    }
}
