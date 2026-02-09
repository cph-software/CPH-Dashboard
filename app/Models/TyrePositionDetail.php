<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyrePositionDetail extends Model
{
    protected $table = 'tyre_position_details';
    protected $guarded = [];

    protected $fillable = [
        'configuration_id',
        'tyre_id',
        'position_code',
        'position_name',
        'axle_type',
        'axle_number',
        'side',
        'is_spare',
        'display_order',
        'x_coordinate',
        'y_coordinate',
    ];


    /**
     * Relationship to configuration
     */
    public function configuration()
    {
        return $this->belongsTo(TyrePositionConfiguration::class, 'configuration_id');
    }

    /**
     * Relationship to assigned tyre
     */
    public function tyre()
    {
        return $this->belongsTo(Tyre::class, 'tyre_id');
    }

    /**
     * Get positions by axle type
     */
    public function scopeByAxleType($query, $axleType)
    {
        return $query->where('axle_type', $axleType);
    }

    /**
     * Get spare positions
     */
    public function scopeSpare($query)
    {
        return $query->where('is_spare', true);
    }

    /**
     * Get non-spare positions
     */
    public function scopeNonSpare($query)
    {
        return $query->where('is_spare', false);
    }
}
