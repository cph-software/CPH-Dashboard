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

    protected $casts = [
        'data_before' => 'array',
        'data_after' => 'array',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
