<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyreExaminationImage extends Model
{
    protected $primaryKey = 'image_id';
    protected $guarded = ['image_id'];

    public function examination()
    {
        return $this->belongsTo(TyreExamination::class, 'examination_id');
    }
}
