<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    use HasFactory;

    protected $table = 'role_menu';
    protected $guarded = [];

    /**
     * Get the role that owns the role menu.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the menu that owns the role menu.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Get permissions as array
     */
    public function getPermissionsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * Set permissions as JSON
     */
    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = is_array($value) ? json_encode($value) : $value;
    }
}
