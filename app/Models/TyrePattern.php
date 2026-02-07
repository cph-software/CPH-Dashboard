<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyrePattern extends Model
{
    protected $table = 'master_import_pattern';
    protected $guarded = [];

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'tyre_pattern_id');
    }
}
