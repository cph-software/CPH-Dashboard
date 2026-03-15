@php
   $installDate = \Carbon\Carbon::parse($session->install_date);
   $installDateFormatted = $installDate->format('d F Y');
   $installDateTable = $installDate->format('d/m/Y');

   // Calculate total columns for merging
   // Brand, Pattern, Pos, Serial, Size, Rcmd, Actl, Date Assembly, Date Inspection, mm1..mmN, Avg RTD, Notes
   $totalCols = 3 + 2 + 2 + 2 + $rtdCount + 1 + 1; // brand,pattern,pos + serial,size + rcmd,actl + dateAsm,dateInsp + rtdN + avgRtd + notes
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
            {{ $idx > 0 ? ' → ' : '' }}<u>{{ $part == $currentStatus ? '[ ' . $part . ' ]' : $part }}</u>
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
      <td colspan="4">{{ number_format($session->odometer_start ?? 0) }}</td>
   </tr>
   {{-- Row 6: Driver Name / Odometer at 1st Check --}}
   <tr>
      <td style="font-weight: bold;">Driver Name</td>
      <td colspan="4">{{ $session->vehicle->driver_name }}</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Odometer at 1st Check</td>
      <td colspan="4">{{ number_format($session->odometer_start ?? 0) }}</td>
   </tr>
   {{-- Row 7: Phone Number / Operation Mileage --}}
   <tr>
      <td style="font-weight: bold;">Phone Number</td>
      <td colspan="4">{{ $session->vehicle->phone_number ?? '-' }}</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Operation Mileage</td>
      <td colspan="4">0 KM</td>
   </tr>
   {{-- Row 8: Application / Retase (Psi) --}}
   <tr>
      <td style="font-weight: bold;">Application</td>
      <td colspan="4">{{ $session->vehicle->application ?? '-' }}</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td style="font-weight: bold;">Retase (Psi)</td>
      <td colspan="4">{{ $session->retase ?? '-' }}</td>
   </tr>
   {{-- Row 9: Load / empty --}}
   <tr>
      <td style="font-weight: bold;">Load</td>
      <td colspan="4">{{ $session->vehicle->load_capacity ?? '-' }} Ton</td>
      <td colspan="{{ $totalCols - 10 }}"></td>
      <td></td>
      <td colspan="4"></td>
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
      <th style="font-weight: bold; border: 1px solid #000; text-align: center;" rowspan="2">Notes</th>
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
   @foreach ($installations as $row => $inst)
      @php
         $brandName = $inst->masterTyre->brand->brand_name ?? ($inst->brand ?? '-');
         $patternName = $inst->masterTyre->pattern->name ?? ($inst->pattern ?? '-');
         $posName = $inst->positionDetail->position_code ?? ($inst->position ?? '-');
         $dateAsm = $inst->date_assembly ? \Carbon\Carbon::parse($inst->date_assembly)->format('d/m/Y') : '-';
         $dateInsp = $inst->date_inspection
             ? \Carbon\Carbon::parse($inst->date_inspection)->format('d/m/Y')
             : $installDateTable;
         // Excel row number (1-indexed, after header rows: title(2) + info(7) + spacer(1) + header(2) = 12)
         $excelRow = $row + 13;
         // RTD columns start at column index (Brand=A, Pattern=B, Pos=C, Serial=D, Size=E, Rcmd=F, Actl=G, DateAsm=H, DateInsp=I, mm1=J)
         $rtdStartCol = 'J';
      @endphp
      <tr>
         <td style="border: 1px solid #000;">{{ $brandName }}</td>
         <td style="border: 1px solid #000;">{{ $patternName }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $posName }}</td>
         <td style="border: 1px solid #000;">{{ $inst->serial_number }}</td>
         <td style="border: 1px solid #000;">{{ $inst->size }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $inst->inf_press_recommended }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $inst->inf_press_actual }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $dateAsm }}</td>
         <td style="border: 1px solid #000; text-align: center;">{{ $dateInsp }}</td>
         @for ($i = 1; $i <= $rtdCount; $i++)
            <td style="border: 1px solid #000; text-align: center;">{{ $inst->{'rtd_' . $i} }}</td>
         @endfor
         <td style="border: 1px solid #000; text-align: center; font-weight: bold;">
            {{ number_format($inst->avg_rtd, 2) }}</td>
         <td style="border: 1px solid #000;">{{ $inst->notes }}</td>
      </tr>
   @endforeach
</table>
