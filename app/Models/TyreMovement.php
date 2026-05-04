<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class TyreMovement extends Model
{
    use BelongsToCompany;

    protected $table = 'tyre_movements';
    protected $fillable = [
        'tyre_company_id', 'tyre_id', 'vehicle_id', 'position_id',
        'operational_segment_id', 'work_location_id', 'work_location',
        'start_time', 'end_time', 'tyreman_1', 'tyreman_2',
        'psi_reading', 'rtd_1', 'rtd_2', 'rtd_3', 'rtd_4', 'rtd_reading',
        'new_bolts_used', 'new_bolts_quantity', 'rim_size',
        'failure_code_id', 'movement_type', 'install_condition',
        'is_replacement', 'target_status', 'movement_date',
        'odometer_reading', 'running_km', 'hour_meter_reading', 'running_hm',
        'notes', 'photo', 'photo_target', 'remarks',
        'created_by', 'updated_by',
        'import_batch_id',
    ];

    protected $casts = [
        'is_replacement' => 'boolean',
        'new_bolts_used' => 'boolean',
    ];

    public function tyre()
    {
        return $this->belongsTo(Tyre::class, 'tyre_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(MasterImportKendaraan::class, 'vehicle_id');
    }

    public function position()
    {
        return $this->belongsTo(TyrePositionDetail::class, 'position_id');
    }

    public function failureCode()
    {
        return $this->belongsTo(TyreFailureCode::class, 'failure_code_id');
    }
}
