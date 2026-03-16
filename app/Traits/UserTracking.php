<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait UserTracking
{
    protected static function bootUserTracking()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                if (Schema::hasColumn($model->getTable(), 'created_by') && !$model->created_by) {
                    $model->created_by = Auth::id();
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                    $model->updated_by = Auth::id();
                }
            }
        });
    }
}
