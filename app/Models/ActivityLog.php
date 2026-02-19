<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'project',
        'activity',
        'action_type',
        'module',
        'data_before',
        'data_after',
        'ip_address',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor: decode data_before dari JSON
     */
    public function getDataBeforeAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    /**
     * Accessor: decode data_after dari JSON
     */
    public function getDataAfterAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    /**
     * Scope: filter by action type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('action_type', $type);
    }

    /**
     * Scope: filter by module
     */
    public function scopeOfModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope: filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
