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
        'name',
        'password',
        'role_id',
        'master_karyawan_id',
        'toko_id',
        'tyre_company_id',
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

    public function tyreCompany()
    {
        return $this->belongsTo(TyreCompany::class, 'tyre_company_id');
    }

    /**
     * Check if user has specific permission for a menu.
     * 
     * @param string $menuName  Nama menu yang dicek
     * @param string|null $permission  Permission spesifik: 'view', 'create', 'update', 'delete', 'export', 'import'
     * @return bool
     */
    public function hasPermission($menuName, $permission = null)
    {
        if (!$this->role)
            return false;

        $menu = $this->role->menus()->where('name', $menuName)->first();

        if (!$menu)
            return false;

        // Jika tidak ada permission spesifik, cukup cek akses menu
        if (!$permission)
            return true;

        // Cek granular permission dari pivot
        $permissions = json_decode($menu->pivot->permissions, true) ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Get display name: prioritas karyawan relation (full_name), lalu name column
     */
    public function getDisplayNameAttribute()
    {
        if ($this->karyawan) {
            return $this->karyawan->full_name ?? $this->karyawan->nama ?? $this->karyawan->employee_name ?? ($this->name ?: 'User #' . $this->id);
        }

        return $this->name ?: 'User #' . $this->id;
    }
}
