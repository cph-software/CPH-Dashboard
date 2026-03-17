<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class MasterImportKendaraan extends Model
{
    use BelongsToCompany;

    protected $table = 'master_import_kendaraan';
    protected $guarded = [];

    public function tyres()
    {
        return $this->hasMany(Tyre::class, 'current_vehicle_id');
    }

    public function segment()
    {
        return $this->belongsTo(TyreSegment::class, 'operational_segment_id');
    }

    public function tyrePositionConfiguration()
    {
        return $this->belongsTo(TyrePositionConfiguration::class, 'tyre_position_configuration_id');
    }

    public function monitoringSessions()
    {
        return $this->hasMany(TyreMonitoringSession::class, 'master_vehicle_id');
    }

    public function setKodeKendaraanAttribute($value)
    {
        $this->attributes['kode_kendaraan'] = strtoupper($value);
    }

    public function setNoPolisiAttribute($value)
    {
        $this->attributes['no_polisi'] = strtoupper($value);
    }
}
