<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TyrePositionConfiguration extends Model
{
    protected $table = 'tyre_position_configurations';
    protected $guarded = [];

    protected $casts = [
        'visual_config' => 'array',
    ];

    /**
     * Relationship to position details
     */
    public function details()
    {
        return $this->hasMany(TyrePositionDetail::class, 'configuration_id');
    }

    /**
     * Get active configurations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Generate position details based on configuration
     */
    public function generatePositions($axleConfig)
    {
        $positions = [];
        $order = 1;

        // Generate front axle positions
        if (isset($axleConfig['front']) && $axleConfig['front'] > 0) {
            for ($i = 1; $i <= $axleConfig['front']; $i++) {
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => 'F' . $i . 'L',
                    'position_name' => 'Front Axle ' . $i . ' - Left',
                    'axle_type' => 'Front',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => 'F' . $i . 'R',
                    'position_name' => 'Front Axle ' . $i . ' - Right',
                    'axle_type' => 'Front',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
            }
        }

        // Generate rear axle positions
        if (isset($axleConfig['rear']) && $axleConfig['rear'] > 0) {
            for ($i = 1; $i <= $axleConfig['rear']; $i++) {
                // Rear axles typically have dual tyres
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => 'R' . $i . 'LO',
                    'position_name' => 'Rear Axle ' . $i . ' - Left Outer',
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => 'R' . $i . 'LI',
                    'position_name' => 'Rear Axle ' . $i . ' - Left Inner',
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => 'R' . $i . 'RI',
                    'position_name' => 'Rear Axle ' . $i . ' - Right Inner',
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => 'R' . $i . 'RO',
                    'position_name' => 'Rear Axle ' . $i . ' - Right Outer',
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
            }
        }

        // Generate spare positions
        if (isset($axleConfig['spare']) && $axleConfig['spare'] > 0) {
            for ($i = 1; $i <= $axleConfig['spare']; $i++) {
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => 'SP' . $i,
                    'position_name' => 'Spare ' . $i,
                    'axle_type' => 'Spare',
                    'axle_number' => 0,
                    'side' => 'None',
                    'is_spare' => true,
                    'display_order' => $order++,
                ];
            }
        }

        return $positions;
    }
}
