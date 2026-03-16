<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class TyreExaminationDetail extends Model
{
    use BelongsToCompany;
    protected $table = 'tyre_examination_details';
    protected $guarded = [];

    public function examination()
    {
        return $this->belongsTo(TyreExamination::class, 'examination_id');
    }

    public function position()
    {
        return $this->belongsTo(TyrePositionDetail::class, 'position_id');
    }

    public function tyre()
    {
        return $this->belongsTo(Tyre::class, 'tyre_id');
    }
}
