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
     * Cached menu permissions — di-load sekali per request
     */
    protected $cachedMenuPermissions = null;

    /**
     * Check if user has specific permission for a menu.
     * 
     * @param string $menuName  Nama menu yang dicek
     * @param string|null $permission  Permission spesifik: 'view', 'create', 'update', 'delete', 'export', 'import'
     * @return bool
     */
    public function hasPermission($menuName, $permission = null)
    {
        if ($this->role_id == 1) {
            return true;
        }

        if (!$this->role)
            return false;

        // Load semua menu+permission sekali, simpan di memory
        if ($this->cachedMenuPermissions === null) {
            $this->cachedMenuPermissions = [];
            $menus = $this->role->menus()->get();
            foreach ($menus as $menu) {
                $this->cachedMenuPermissions[$menu->name] = json_decode($menu->pivot->permissions, true) ?? [];
            }
        }

        // Cek apakah menu ada
        if (!isset($this->cachedMenuPermissions[$menuName]))
            return false;

        // Jika tidak ada permission spesifik, cukup cek akses menu
        if (!$permission)
            return true;

        // Cek granular permission dari cache
        return in_array($permission, $this->cachedMenuPermissions[$menuName]);
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

    /**
     * Get all approvers for a specific company and module.
     * Excludes Super Admin (role_id = 1) unless they belong to that exact company.
     * 
     * @param int $companyId
     * @param string $menuName e.g. 'Import Approval' or 'Tyre Monitoring'
     * @param string|null $permission e.g. 'approve'
     * @return \Illuminate\Support\Collection
     */
    public static function getApprovers($companyId, $menuName, $permission = null)
    {
        // 1. Dapatkan semua user di perusahaan tersebut
        $usersInCompany = self::where('tyre_company_id', $companyId)->get();
        
        $approvers = collect();
        
        foreach ($usersInCompany as $u) {
            // Kita tidak otomatis memasukkan role_id = 1 (Super Admin) dari perusahaan lain.
            // Karena query di atas sudah difilter by tyre_company_id, maka kalau ada Super Admin 
            // yg terdaftar di company ini, dia akan otomatis masuk (karena hasPermission akan return true).
            
            if ($u->hasPermission($menuName, $permission)) {
                $approvers->push($u);
            }
        }
        
        return $approvers;
    }
}
