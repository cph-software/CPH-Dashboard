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

    public function vehicles()
    {
        return $this->hasMany(MasterImportKendaraan::class, 'tyre_position_configuration_id');
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
     * New Standard:
     * - Sequential from front to back, left to right symmetrical
     * - Front: LF/1, RF/2
     * - Dual (Middle/Rear): Inner (Lower Number), Outer (Higher Number)
     * - Format: Code/Number
     */
    public function generatePositions($axleConfig)
    {
        $positions = [];
        $seq = 1;      // Sequential number for display in code (e.g., LF/1)
        $order = 1;    // Display order for layout sorting (left to right)

        // 1. Front Axles
        $frontCount = $axleConfig['front'] ?? 0;
        for ($i = 1; $i <= $frontCount; $i++) {
            $prefix = $frontCount > 1 ? "$i" : "";

            // Left (1)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "LF/" . ($seq),
                'position_name' => "Left Front $i",
                'axle_type' => 'Front',
                'axle_number' => $i,
                'side' => 'Left',
                'is_spare' => false,
                'display_order' => $order++,
            ];

            // Right (2)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "RF/" . ($seq + 1),
                'position_name' => "Right Front $i",
                'axle_type' => 'Front',
                'axle_number' => $i,
                'side' => 'Right',
                'is_spare' => false,
                'display_order' => $order++,
            ];
            $seq += 2;
        }

        // 2. Middle Axles
        $middleCount = $axleConfig['middle'] ?? 0;
        for ($i = 1; $i <= $middleCount; $i++) {
            $prefix = $middleCount > 1 ? $i : "";

            // User Pattern: LMO/4 LMI/3 - RMI/5 RMO/6
            // Order in visual (Left to Right): LO, LI, RI, RO
            // Order in numbering (Inner first): LI=seq, LO=seq+1, RI=seq+2, RO=seq+3

            // Left side group
            // LMO (LO)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "LMO/" . ($seq + 1),
                'position_name' => "Left Middle ${i} Outer",
                'axle_type' => 'Middle',
                'axle_number' => $i,
                'side' => 'Left',
                'is_spare' => false,
                'display_order' => $order++,
            ];
            // LMI (LI)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "LMI/" . ($seq),
                'position_name' => "Left Middle ${i} Inner",
                'axle_type' => 'Middle',
                'axle_number' => $i,
                'side' => 'Left',
                'is_spare' => false,
                'display_order' => $order++,
            ];

            // Right side group
            // RMI (RI)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "RMI/" . ($seq + 2),
                'position_name' => "Right Middle ${i} Inner",
                'axle_type' => 'Middle',
                'axle_number' => $i,
                'side' => 'Right',
                'is_spare' => false,
                'display_order' => $order++,
            ];
            // RMO (RO)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "RMO/" . ($seq + 3),
                'position_name' => "Right Middle ${i} Outer",
                'axle_type' => 'Middle',
                'axle_number' => $i,
                'side' => 'Right',
                'is_spare' => false,
                'display_order' => $order++,
            ];
            $seq += 4;
        }

        // 3. Rear Axles
        $rearCount = $axleConfig['rear'] ?? 0;
        for ($i = 1; $i <= $rearCount; $i++) {
            $prefix = $rearCount > 1 ? $i : "";

            // Same pattern for Rear
            // Left side group
            // LRO (LO)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "LRO/" . ($seq + 1),
                'position_name' => "Left Rear ${i} Outer",
                'axle_type' => 'Rear',
                'axle_number' => $i,
                'side' => 'Left',
                'is_spare' => false,
                'display_order' => $order++,
            ];
            // LRI (LI)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "LRI/" . ($seq),
                'position_name' => "Left Rear ${i} Inner",
                'axle_type' => 'Rear',
                'axle_number' => $i,
                'side' => 'Left',
                'is_spare' => false,
                'display_order' => $order++,
            ];

            // Right side group
            // RRI (RI)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "RRI/" . ($seq + 2),
                'position_name' => "Right Rear ${i} Inner",
                'axle_type' => 'Rear',
                'axle_number' => $i,
                'side' => 'Right',
                'is_spare' => false,
                'display_order' => $order++,
            ];
            // RRO (RO)
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "RRO/" . ($seq + 3),
                'position_name' => "Right Rear ${i} Outer",
                'axle_type' => 'Rear',
                'axle_number' => $i,
                'side' => 'Right',
                'is_spare' => false,
                'display_order' => $order++,
            ];
            $seq += 4;
        }

        // 4. Spare Tyres
        $spareCount = $axleConfig['spare'] ?? 0;
        for ($i = 1; $i <= $spareCount; $i++) {
            $positions[] = [
                'configuration_id' => $this->id,
                'position_code' => "SP/" . ($seq),
                'position_name' => "Spare $i",
                'axle_type' => 'Spare',
                'axle_number' => 0,
                'side' => 'None',
                'is_spare' => true,
                'display_order' => $order++,
            ];
            $seq++;
        }

        return $positions;
    }
}
