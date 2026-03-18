<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class TyreMonitoringCheck extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'tyre_monitoring_check';
    protected $primaryKey = 'check_id';

    protected $fillable = [
        'session_id',
        'check_number',
        'check_date',
        'odometer_reading',
        'hm_reading',
        'operation_mileage',
        'operation_hm',
        'driver_name',
        'phone_number',
        'position',
        'position_id',
        'serial_number',
        'inf_press_recommended',
        'inf_press_actual',
        'date_assembly',
        'date_inspection',
        'rtd_1',
        'rtd_2',
        'rtd_3',
        'rtd_4',
        'worn_percentage',
        'km_per_mm',
        'projected_life_km',
        'condition',
        'recommendation',
        'notes',
        'is_sales_input',
        'approval_status',
        'approved_by',
        'approved_at',
        'reject_reason',
        'tyre_company_id',
    ];

    public function session()
    {
        return $this->belongsTo(TyreMonitoringSession::class, 'session_id', 'session_id');
    }

    public function positionDetail()
    {
        return $this->belongsTo(TyrePositionDetail::class, 'position_id');
    }

    public function tyre()
    {
        return $this->belongsTo(Tyre::class, 'serial_number', 'serial_number');
    }
}
