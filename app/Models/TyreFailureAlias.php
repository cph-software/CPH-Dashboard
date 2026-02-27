<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyreFailureAlias extends Model
{
    protected $guarded = [];

    public function failureCode()
    {
        return $this->belongsTo(TyreFailureCode::class, 'tyre_failure_code_id');
    }

    public function company()
    {
        return $this->belongsTo(TyreCompany::class, 'tyre_company_id');
    }
}
