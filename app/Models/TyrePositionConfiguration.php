<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserTracking;

class TyrePositionConfiguration extends Model
{
    use UserTracking;
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
        $frontIsDual = ($this->config_type === 'Trailer'); // Trailers front axles are usually duals

        for ($i = 1; $i <= $frontCount; $i++) {
            if ($frontIsDual) {
                // Same 4-tyre pattern as Middle/Rear
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "LFO/" . ($seq + 1),
                    'position_name' => "Left Front ${i} Outer",
                    'axle_type' => 'Front',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "LFI/" . ($seq),
                    'position_name' => "Left Front ${i} Inner",
                    'axle_type' => 'Front',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "RFI/" . ($seq + 2),
                    'position_name' => "Right Front ${i} Inner",
                    'axle_type' => 'Front',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "RFO/" . ($seq + 3),
                    'position_name' => "Right Front ${i} Outer",
                    'axle_type' => 'Front',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $seq += 4;
            } else {
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
        }

        // Detect if middle/rear axles are single-tyre (2 per axle) or dual-tyre (4 per axle)
        // by calculating: total non-spare positions minus front positions = remaining for middle+rear
        $spareCount = $axleConfig['spare'] ?? 0;
        $middleCount = $axleConfig['middle'] ?? 0;
        $rearCount = $axleConfig['rear'] ?? 0;
        $totalNonSpare = $this->total_positions - $spareCount;
        $frontPositions = $frontCount * ($frontIsDual ? 4 : 2);
        $remainingPositions = $totalNonSpare - $frontPositions;
        $totalMiddleRearAxles = $middleCount + $rearCount;
        // If remaining positions / axles = 2, it's single-tyre per side; if 4, it's dual-tyre
        $isSingleTyreAxle = ($totalMiddleRearAxles > 0) && ($remainingPositions / $totalMiddleRearAxles) <= 2;

        // 2. Middle Axles
        for ($i = 1; $i <= $middleCount; $i++) {
            if ($isSingleTyreAxle) {
                // Single tyre per side: LM, RM
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "LM/" . ($seq),
                    'position_name' => "Left Middle {$i}",
                    'axle_type' => 'Middle',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "RM/" . ($seq + 1),
                    'position_name' => "Right Middle {$i}",
                    'axle_type' => 'Middle',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $seq += 2;
            } else {
                // Dual tyre per side: LMO, LMI, RMI, RMO
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "LMO/" . ($seq + 1),
                    'position_name' => "Left Middle {$i} Outer",
                    'axle_type' => 'Middle',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "LMI/" . ($seq),
                    'position_name' => "Left Middle {$i} Inner",
                    'axle_type' => 'Middle',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "RMI/" . ($seq + 2),
                    'position_name' => "Right Middle {$i} Inner",
                    'axle_type' => 'Middle',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "RMO/" . ($seq + 3),
                    'position_name' => "Right Middle {$i} Outer",
                    'axle_type' => 'Middle',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $seq += 4;
            }
        }

        // 3. Rear Axles
        for ($i = 1; $i <= $rearCount; $i++) {
            if ($isSingleTyreAxle) {
                // Single tyre per side: LR, RR
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "LR/" . ($seq),
                    'position_name' => "Left Rear {$i}",
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "RR/" . ($seq + 1),
                    'position_name' => "Right Rear {$i}",
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $seq += 2;
            } else {
                // Dual tyre per side: LRO, LRI, RRI, RRO
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "LRO/" . ($seq + 1),
                    'position_name' => "Left Rear {$i} Outer",
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "LRI/" . ($seq),
                    'position_name' => "Left Rear {$i} Inner",
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Left',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "RRI/" . ($seq + 2),
                    'position_name' => "Right Rear {$i} Inner",
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $positions[] = [
                    'configuration_id' => $this->id,
                    'position_code' => "RRO/" . ($seq + 3),
                    'position_name' => "Right Rear {$i} Outer",
                    'axle_type' => 'Rear',
                    'axle_number' => $i,
                    'side' => 'Right',
                    'is_spare' => false,
                    'display_order' => $order++,
                ];
                $seq += 4;
            }
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
