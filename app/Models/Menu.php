<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $guarded = [];

    public function aplikasi()
    {
        return $this->belongsTo(Aplikasi::class, 'aplikasi_id');
    }



    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menu', 'menu_id', 'role_id')
            ->withPivot('permissions')
            ->withTimestamps();
    }
}
