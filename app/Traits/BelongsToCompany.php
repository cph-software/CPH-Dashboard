<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        // 1. GLOBAL SCOPE: Filtering data
        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                
                // Pengecualian untuk Superadmin atau Internal Staff (CPH)
                // Role 1 = Administrator
                // Company ID 4 = Catur Putra Bahagia (CPH)
                $isInternal = ($user->role_id == 1) || ($user->tyre_company_id == 1);
                $table = $builder->getModel()->getTable();

                if ($isInternal) {
                    // Admin can see everything, unless a specific company filter is set in session
                    if (session()->has('active_company_id')) {
                        $builder->where($table . '.tyre_company_id', session('active_company_id'));
                    }
                } else if ($user->tyre_company_id) {
                    // Regular user: Sees only their company data (Strict Isolation)
                    $builder->where($table . '.tyre_company_id', $user->tyre_company_id);
                }
            }
        });

        // 2. AUTO-FILL: Filling company_id & created_by on creation
        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                
                // Isi company_id otomatis jika belum diisi manual
                if (!$model->tyre_company_id) {
                    $isInternal = ($user->role_id == 1) || ($user->tyre_company_id == 1);
                    if ($isInternal && session()->has('active_company_id')) {
                        $model->tyre_company_id = session('active_company_id');
                    } else {
                        $model->tyre_company_id = $user->tyre_company_id;
                    }
                }
                
                // Isi created_by otomatis
                if (SchemaHasColumn($model->getTable(), 'created_by') && !$model->created_by) {
                    $model->created_by = $user->id;
                }
            }
        });

        // 3. AUTO-UPDATE: Filling updated_by
        static::updating(function ($model) {
            if (Auth::check()) {
                if (SchemaHasColumn($model->getTable(), 'updated_by')) {
                    $model->updated_by = Auth::id();
                }
            }
        });
    }

    /**
     * Helper to bypass company filter if needed
     */
    public function scopeAllCompanies($query)
    {
        return $query->withoutGlobalScope('company');
    }
}

// Global helper function because Traits cannot access Schema inside static easily without it
if (!function_exists('SchemaHasColumn')) {
    function SchemaHasColumn($table, $column)
    {
        return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
    }
}
