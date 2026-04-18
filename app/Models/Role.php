<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $guarded = [];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menu', 'role_id', 'menu_id')
            ->withPivot('permissions')
            ->withTimestamps();
    }

    public function aplikasi()
    {
        return $this->belongsToMany(Aplikasi::class, 'aplikasi_role', 'role_id', 'aplikasi_id')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function companies()
    {
        return $this->belongsToMany(TyreCompany::class, 'tyre_company_roles', 'role_id', 'tyre_company_id');
    }
}
