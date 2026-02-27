<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyreCompany extends Model
{
    protected $table = 'tyre_companies';
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class, 'tyre_company_id');
    }

    public function aliases()
    {
        return $this->hasMany(TyreFailureAlias::class, 'tyre_company_id');
    }
}
