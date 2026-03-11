<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class TyreMovement extends Model
{
    use BelongsToCompany;

    protected $table = 'tyre_movements';
    protected $guarded = [];

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
