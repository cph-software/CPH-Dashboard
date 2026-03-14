@php
   $installDate = \Carbon\Carbon::parse($session->install_date);
   $installDateFormatted = $installDate->format('d F Y');

   $removalDateObj = \Carbon\Carbon::parse($removal->removal_date);
   $removalDateFormatted = $removalDateObj->format('d F Y');
   $removalDateTable = $removalDateObj->format('d/m/Y');

   $odometerRemoval = $removal->odometer_reading ?? ($removal->odometer ?? 0);
   $opMileage = $removal->total_mileage ?? $odometerRemoval - $session->odometer_start;

   // Calculate total columns
   $totalCols = 3 + 2 + 2 + 2 + $rtdCount + 1 + 1;
@endphp
<table>
   {{-- Row 1: VEHICLE INFORMATION | TYRE MONITORING FORM | TYRE INFORMATION --}}
   <tr>
      <th colspan="5" style="font-weight: bold; font-size: 14pt; text-align: left;">VEHICLE INFORMATION</th>
      <th colspan="{{ $totalCols - 10 > 0 ? $totalCols - 10 : 2 }}"
         style="font-weight: bold; font-size: 16pt; text-align: center;">TYRE MONITORING FORM</th>
      <th colspan="5" style="font-weight: bold; font-size: 14pt; text-align: left;">TYRE INFORMATION</th>
   </tr>
   {{-- Row 2: Status Monitoring --}}
   <tr>
      <td colspan="5"></td>
      <td colspan="{{ $totalCols - 10 > 0 ? $totalCols - 10 : 2 }}"
         style="text-align: center; font-weight: bold; font-size: 11pt;">
         @foreach ($statusParts as $idx => $part)
            {{ $idx > 0 ? ' → ' : '' }}{{ $part == $currentStatus ? '[ ' . $part . ' ]' : $part }}
         @endforeach
      </td>
      <td colspan="5"></td>
   </tr>
   {{-- Row 3: Fleet Name / Tyre Size --}}
   <tr>
      <td style="font-weight: bold;">Fleet Name</td>
      <td colspan="4">{{ $session->vehicle->fleet_name }}</td>
      <td colspan="{{ $totalCols - 10 > 0 ? $totalCols - 10 : 2 }}"></td>
      <td style="font-weight: bold;">Tyre Size</td>
      <td colspan="4">{{ $session->tyre_size }}</td>
   </tr>
   {{-- Row 4: Install Date / Original Tread Depth --}}
   <tr>
      <td style="font-weight: bold;">Install Date</td>
      <td colspan="4">{{ $installDateFormatted }}</td>
      <td colspan="{{ $totalCols - 10 > 0 ? $totalCols - 10 : 2 }}"></td>
      <td style="font-weight: bold;">Original Tread Depth</td>
      <td colspan="4">{{ $session->original_rtd }} mm</td>
   </tr>
   {{-- Row 5: Vehicle Number / Odometer at Start --}}
   <tr>
      <td style="font-weight: bold;">Vehicle Number</td>
      <td colspan="4">{{ $session->vehicle->vehicle_number }}</td>
      <td colspan="{{ $totalCols - 10 > 0 ? $totalCols - 10 : 2 }}"></td>
      <td style="font-weight: bold;">Odometer at Start</td>
      <td colspan="4">{{ number_format($session->odometer_start) }}</td>
   </tr>
   {{-- Row 6: Driver Name / Odometer at Removal --}}
   <tr>
      <td style="font-weight: bold;">Driver Name</td>
      <td colspan="4">{{ $session->vehicle->driver_name }}</td>
      <td colspan="{{ $totalCols - 10 > 0 ? $totalCols - 10 : 2 }}"></td>
      <td style="font-weight: bold;">Odometer at Removal</td>
      <td colspan="4">{{ number_format($odometerRemoval) }}</td>
   </tr>
   {{-- Row 7: Phone Number / Operation Mileage --}}
   <tr>
      <td style="font-weight: bold;">Phone Number</td>
      <td colspan="4">{{ $session->vehicle->phone_number ?? '-' }}</td>
      <td colspan="{{ $totalCols - 10 > 0 ? $totalCols - 10 : 2 }}"></td>
      <td style="font-weight: bold;">Operation Mileage</td>
      <td colspan="4">{{ number_format($opMileage) }} KM</td>
   </tr>
   {{-- Row 8: Application / Removal Date --}}
   <tr>
      <td style="font-weight: bold;">Application</td>
      <td colspan="4">{{ $session->vehicle->application ?? '-' }}</td>
      <td colspan="{{ $totalCols - 10 > 0 ? $totalCols - 10 : 2 }}"></td>
      <td style="font-weight: bold;">Removal Date</td>
      <td colspan="4">{{ $removalDateFormatted }}</td>
   </tr>
   {{-- Row 9: Load --}}
   <tr>
      <td style="font-weight: bold;">Load</td>
      <td colspan="4">{{ $session->vehicle->load_capacity ?? '-' }} Ton</td>
      <td colspan="{{ $totalCols - 10 > 0 ? $totalCols - 10 : 2 }}"></td>
      <td></td>
      <td colspan="4"></td>
   </tr>
   {{-- Spacer --}}
   <tr></tr>
   {{-- Removal Details Table --}}
   <tr style="background-color: #8B0000; color: #ffffff;">
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Brand</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Pattern</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Pos</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Serial Number</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Removal Date</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Total Mileage (KM)</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Final RTD (mm)</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Removal Reason</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Condition After</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Target Status</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Notes</th>
   </tr>
   @php
      $inst = $installations->where('serial_number', $removal->serial_number)->first();
      $brandName = $inst->masterTyre->brand->brand_name ?? ($inst->brand ?? '-');
      $patternName = $inst->masterTyre->pattern->name ?? ($inst->pattern ?? '-');
      $posName = $inst->positionDetail->position_code ?? ($removal->position ?? '-');
   @endphp
   <tr>
      <td style="border: 1px solid #000;">{{ $brandName }}</td>
      <td style="border: 1px solid #000;">{{ $patternName }}</td>
      <td style="border: 1px solid #000; text-align: center;">{{ $posName }}</td>
      <td style="border: 1px solid #000;">{{ $removal->serial_number }}</td>
      <td style="border: 1px solid #000; text-align: center;">{{ $removalDateTable }}</td>
      <td style="border: 1px solid #000; text-align: center;">{{ number_format($removal->total_mileage) }}</td>
      <td style="border: 1px solid #000; text-align: center;">{{ $removal->final_rtd }}</td>
      <td style="border: 1px solid #000;">{{ $removal->removal_reason }}</td>
      <td style="border: 1px solid #000;">{{ $removal->tyre_condition_after }}</td>
      <td style="border: 1px solid #000; text-align: center;">{{ $removal->target_status ?? '-' }}</td>
      <td style="border: 1px solid #000;">{{ $removal->notes }}</td>
   </tr>
</table>
