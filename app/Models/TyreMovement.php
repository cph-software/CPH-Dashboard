<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyreMovement extends Model
{
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
}
