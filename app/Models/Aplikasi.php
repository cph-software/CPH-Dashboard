<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aplikasi extends Model
{
    protected $table = 'aplikasi';
    protected $guarded = [];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'aplikasi_role', 'aplikasi_id', 'role_id');
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'aplikasi_id');
    }
}
