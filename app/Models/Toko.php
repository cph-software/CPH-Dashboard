<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    protected $table = 'master_import_toko';
    protected $guarded = [];

    // Jika id_toko adalah primary key (string/varchar)
    // protected $primaryKey = 'id_toko';
    // public $incrementing = false;
    // protected $keyType = 'string';
}
