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
                $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
                $table = $builder->getModel()->getTable();

                if ($activeCompanyId !== null) {
                    if (is_array($activeCompanyId)) {
                        if ($table === 'tyres') {
                            $builder->where(function ($q) use ($table, $activeCompanyId) {
                                $q->whereIn($table . '.tyre_company_id', $activeCompanyId)
                                  ->orWhereIn($table . '.current_vehicle_id', function ($vq) use ($activeCompanyId) {
                                      $vq->select('id')->from('master_import_kendaraan')->whereIn('tyre_company_id', $activeCompanyId);
                                  });
                            });
                        } elseif ($table === 'tyre_movements') {
                            $builder->where(function ($q) use ($table, $activeCompanyId) {
                                $q->whereIn($table . '.tyre_company_id', $activeCompanyId)
                                  ->orWhereIn($table . '.vehicle_id', function ($vq) use ($activeCompanyId) {
                                      $vq->select('id')->from('master_import_kendaraan')->whereIn('tyre_company_id', $activeCompanyId);
                                  });
                            });
                        } else {
                            $builder->whereIn($table . '.tyre_company_id', $activeCompanyId);
                        }
                    } else {
                        if ($table === 'tyres') {
                            $builder->where(function ($q) use ($table, $activeCompanyId) {
                                $q->where($table . '.tyre_company_id', $activeCompanyId)
                                  ->orWhereIn($table . '.current_vehicle_id', function ($vq) use ($activeCompanyId) {
                                      $vq->select('id')->from('master_import_kendaraan')->where('tyre_company_id', $activeCompanyId);
                                  });
                            });
                        } elseif ($table === 'tyre_movements') {
                            $builder->where(function ($q) use ($table, $activeCompanyId) {
                                $q->where($table . '.tyre_company_id', $activeCompanyId)
                                  ->orWhereIn($table . '.vehicle_id', function ($vq) use ($activeCompanyId) {
                                      $vq->select('id')->from('master_import_kendaraan')->where('tyre_company_id', $activeCompanyId);
                                  });
                            });
                        } else {
                            $builder->where($table . '.tyre_company_id', $activeCompanyId);
                        }
                    }
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
                    $isWorkshopAdmin = \App\Helpers\SessionCompanyHelper::isWorkshopAdmin();

                    if ($isInternal && session()->has('active_company_id')) {
                        $sessionVal = session('active_company_id');
                        $model->tyre_company_id = is_array($sessionVal) ? $user->tyre_company_id : $sessionVal;
                    } elseif ($isWorkshopAdmin && session()->has('active_company_id')) {
                        $sessionCompany = session('active_company_id');
                        // Jika mode Global Klien (array), data masuk ke perusahaan induk
                        if (is_array($sessionCompany)) {
                            $model->tyre_company_id = $user->tyre_company_id;
                        } else {
                            $model->tyre_company_id = $sessionCompany;
                        }
                    } else {
                        $model->tyre_company_id = $user->tyre_company_id;
                    }
                }
                
                // Isi created_by otomatis (cached check)
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

// Optimized: Cache hasil Schema::hasColumn per tabel+kolom agar tidak query INFORMATION_SCHEMA berulang
if (!function_exists('SchemaHasColumn')) {
    function SchemaHasColumn($table, $column)
    {
        static $cache = [];
        $key = $table . '.' . $column;

        if (!isset($cache[$key])) {
            $cache[$key] = \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }
}

