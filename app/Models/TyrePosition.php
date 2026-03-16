<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserTracking;

class TyrePosition extends Model
{
    use UserTracking;
    protected $table = 'tyre_positions';
    protected $guarded = [];
}
