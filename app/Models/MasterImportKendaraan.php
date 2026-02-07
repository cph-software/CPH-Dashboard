<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterImportKendaraan extends Model
{
    protected $table = 'master_import_kendaraan';
    protected $guarded = [];

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'current_vehicle_id');
    }
}
