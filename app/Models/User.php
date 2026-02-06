<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'password',
        'role_id',
        'master_karyawan_id',
        'toko_id',
        'foto'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function karyawan()
    {
        return $this->belongsTo(KaryawanMasterKaryawan::class, 'master_karyawan_id', 'employee_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check if user has specific permission for a menu
     */
    public function hasPermission($menuName, $permission)
    {
        if (!$this->role)
            return false;

        $menu = $this->role->menus()->where('name', $menuName)->first();
        if (!$menu)
            return false;

        $permissions = json_decode($menu->pivot->permissions, true) ?? [];
        return in_array($permission, $permissions);
    }
}
