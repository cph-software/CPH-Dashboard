<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyreExamination extends Model
{
    protected $table = 'tyre_examinations';
    protected $guarded = [];

    public function vehicle()
    {
        return $this->belongsTo(MasterImportKendaraan::class, 'vehicle_id');
    }

    public function location()
    {
        return $this->belongsTo(TyreLocation::class, 'location_id');
    }

    public function segment()
    {
        return $this->belongsTo(TyreSegment::class, 'operational_segment_id');
    }

    public function details()
    {
        return $this->hasMany(TyreExaminationDetail::class, 'examination_id');
    }
}
