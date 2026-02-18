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

    public function details()
    {
        return $this->hasMany(TyreExaminationDetail::class, 'examination_id');
    }
}
