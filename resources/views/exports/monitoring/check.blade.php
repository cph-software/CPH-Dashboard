@php
   $installDate = \Carbon\Carbon::parse($session->install_date);
   $installDateFormatted = $installDate->format('d F Y');

   $checkDateObj = \Carbon\Carbon::parse($checks[0]->check_date ?? $session->install_date);
   $checkDateFormatted = $checkDateObj->format('d F Y');
   $checkDateTable = $checkDateObj->format('d/m/Y');

   // Ordinal suffix
   if (!function_exists('ordinalCheck')) {
       function ordinalCheck($n)
       {
           $s = ['th', 'st', 'nd', 'rd'];
           $v = $n % 100;
           return $n . ($s[($v - 20) % 10] ?? ($s[$v] ?? $s[0]));
       }
   }
   $ordCheck = ordinalCheck($checkNumber);

   // Operation mileage from first check record
   $opMileage = $checks[0]->operation_mileage ?? 0;
   $odometerCheck = $checks[0]->odometer_reading ?? ($checks[0]->odometer ?? 0);

   // Calculate total columns
   // Brand, Pattern, Pos, Serial, Size, Rcmd, Actl, DateAsm, DateInsp, mm1..mmN, AvgRTD, OpMileage, Worn%, KM/mm, ProjLife, Day, Moon, Condition, Recommendation
   $totalCols = 3 + 2 + 2 + 2 + $rtdCount + 1 + 1 + 1 + 1 + 1 + 1 + 1 + 1 + 1; // = 14 + rtdCount + 4 extra analysis cols
@endphp
<table>
   {{-- Row 1: VEHICLE INFORMATION | TYRE MONITORING FORM | TYRE INFORMATION --}}
   <tr>
      <th colspan="5" style="font-weight: bold; font-size: 14pt; text-align: left;">VEHICLE INFORMATION</th>
      <th colspan="{{ $totalCols - 10 }}" style="font-weight: bold; font-size: 16pt; text-align: center;">TYRE MONITORING
         FORM</th>
      <th colspan="5" style="font-weight: bold; font-size: 14pt; text-align: left;">TYRE INFORMATION</th>
   </tr>
   {{-- Row 2: Status Monitoring --}}
   <tr>
      <td colspan="5"></td>
      <td colspan="{{ $totalCols - 10 }}" style="text-align: center; font-weight: bold; font-size: 11pt;">
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
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Tyre Size</td>
      <td colspan="4">{{ $session->tyre_size }}</td>
   </tr>
   {{-- Row 4: Install Date / Original Tread Depth --}}
   <tr>
      <td style="font-weight: bold;">Install Date</td>
      <td colspan="4">{{ $installDateFormatted }}</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Original Tread Depth</td>
      <td colspan="4">{{ $session->original_rtd }} mm</td>
   </tr>
   {{-- Row 5: Vehicle Number / Odometer at Start --}}
   <tr>
      <td style="font-weight: bold;">Vehicle Number</td>
      <td colspan="4">{{ $session->vehicle->vehicle_number }}</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Odometer at Start</td>
      <td colspan="4">{{ number_format($session->odometer_start) }}</td>
   </tr>
   {{-- Row 6: Driver Name / Odometer at Nth Check --}}
   <tr>
      <td style="font-weight: bold;">Driver Name</td>
      <td colspan="4">{{ $checks[0]->driver_name ?? $session->vehicle->driver_name }}</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Odometer at {{ $ordCheck }} Check</td>
      <td colspan="4">{{ number_format($odometerCheck) }}</td>
   </tr>
   {{-- Row 7: Phone Number / Operation Mileage --}}
   <tr>
      <td style="font-weight: bold;">Phone Number</td>
      <td colspan="4">{{ $checks[0]->phone_number ?? ($session->vehicle->phone_number ?? '-') }}</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Operation Mileage</td>
      <td colspan="4">{{ number_format($opMileage) }} KM</td>
   </tr>
   {{-- Row 8: Application / Retase (Psi) --}}
   <tr>
      <td style="font-weight: bold;">Application</td>
      <td colspan="4">{{ $session->vehicle->application ?? '-' }}</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Retase (Psi)</td>
      <td colspan="4">{{ $checks[0]->inf_press_recommended ?? ($session->retase ?? '-') }}</td>
   </tr>
   {{-- Row 9: Load / Check Date --}}
   <tr>
      <td style="font-weight: bold;">Load</td>
      <td colspan="4">{{ $session->vehicle->load_capacity ?? '-' }} Ton</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Check Date</td>
      <td colspan="4">{{ $checkDateFormatted }}</td>
   </tr>
   {{-- Spacer --}}
   <tr></tr>
   {{-- Table Header Row 1 (Merged headers) --}}
   <tr style="background-color: #333333; color: #ffffff;">
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Brand</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Pattern</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Pos</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" colspan="2">Name of Tyre</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" colspan="2">Inf Press (Psi)</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Date Assembly</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Date Inspection</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" colspan="{{ $rtdCount }}">Tread
         Depth</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Avg RTD</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Op. Mileage (KM)</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Worn%</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">KM/mm</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Proj.Life 3mm (KM)</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Day</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Month</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Condition</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Recommendation</th>
   </tr>
   {{-- Table Header Row 2 (Sub-columns) --}}
   <tr style="background-color: #555555; color: #ffffff;">
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Serial</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Size</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Rcmd</th>
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Actl</th>
      @for ($i = 1; $i <= $rtdCount; $i++)
         <th style="font-weight: bold; border: 1px solid #000; text-align: center;">mm{{ $i }}</th>
      @endfor
   </tr>
   {{-- Data Rows --}}
   @foreach ($checks as $row => $check)
      @php
         $posName = $check->position ?? '-';
         $inst = $session->installations->where('serial_number', $check->serial_number)->first();
         $sizeName = $inst->size ?? $session->tyre_size;
         $dateAsm = $check->date_assembly ? \Carbon\Carbon::parse($check->date_assembly)->format('d/m/Y') : '-';
         $dateInsp = $check->check_date ? \Carbon\Carbon::parse($check->check_date)->format('d/m/Y') : $checkDateTable;

         // Excel row (title2 + info7 + spacer1 + header2 + 1-indexed = 13 + row)
         $excelRow = $row + 13;
         // Column mapping: J=mm1 (index 9), K=mm2, L=mm3, M=mm4
         // The Avg RTD column comes after the last RTD column
         $rtdColLetters = ['J', 'K', 'L', 'M'];
         $avgRtdColIdx = 9 + $rtdCount; // 0-indexed
         $avgRtdCol = chr(65 + $avgRtdColIdx); // 'J' + rtdCount
         if ($avgRtdColIdx > 25) {
             $avgRtdCol = 'A' . chr(65 + $avgRtdColIdx - 26);
         }

         // Build AVERAGE formula for RTD cells
         $firstRtdCol = 'J';
         $lastRtdCol = chr(ord('J') + $rtdCount - 1);
         $avgFormula = "=AVERAGE({$firstRtdCol}{$excelRow}:{$lastRtdCol}{$excelRow})";
      @endphp
      <tr>
         <td style="border: 1px solid #000;">{{ $check->brand_name ?? '-' }}</td>
         <td style="border: 1px solid #000;">{{ $check->pattern_name ?? '-' }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $posName }}</td>
         <td style="border: 1px solid #000;">{{ $check->serial_number }}</td>
         <td style="border: 1px solid #000;">{{ $sizeName }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $check->inf_press_recommended }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $check->inf_press_actual }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $dateAsm }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $dateInsp }}</td>
         @for ($i = 1; $i <= $rtdCount; $i++)
            <td style="border: 1px solid #000; text-align: center;">{{ $check->{'rtd_' . $i} }}</td>
         @endfor
         <td style="border: 1px solid #000; text-align: center; font-weight: bold;">
            {{ number_format($check->calculated['avg_rtd'], 2) }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ number_format($check->operation_mileage) }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $check->calculated['worn_pct'] }}%</td>
         <td style="border: 1px solid #000; text-align: center;">
            {{ number_format($check->calculated['km_per_mm'], 1) }}</td>
         <td style="border: 1px solid #000; text-align: center; font-weight: bold;">
            {{ number_format($check->calculated['proj_life_km']) }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $check->calculated['proj_life_day'] }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $check->calculated['proj_life_month'] }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ strtoupper($check->condition) }}</td>
         <td style="border: 1px solid #000;">{{ $check->recommendation }}</td>
      </tr>
   @endforeach
</table>
