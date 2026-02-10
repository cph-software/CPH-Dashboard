<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyreFailureCode extends Model
{
    protected $table = 'tyre_failure_codes';
    protected $guarded = [];
    public function movements()
    {
        return $this->hasMany(TyreMovement::class, 'failure_code_id');
    }
}
