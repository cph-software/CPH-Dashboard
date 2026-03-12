<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TyreMonitoringInstallation extends Model
{
    use HasFactory;

    protected $table = 'tyre_monitoring_installation';
    protected $primaryKey = 'install_id';

    protected $fillable = [
        'session_id',
        'position',
        'serial_number',
        'brand',
        'pattern',
        'size',
        'inf_press_recommended',
        'inf_press_actual',
        'install_date',
        'rtd_1',
        'rtd_2',
        'rtd_3',
        'avg_rtd',
        'odometer',
        'notes',
    ];

    public function session()
    {
        return $this->belongsTo(TyreMonitoringSession::class, 'session_id', 'session_id');
    }

    public function tyre()
    {
        return $this->belongsTo(Tyre::class, 'serial_number', 'serial_number');
    }
}
