<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <title>Examination Form - {{ $exam->vehicle->kode_kendaraan }}</title>
   <style>
      body {
         font-family: 'Helvetica', 'Arial', sans-serif;
         font-size: 11px;
         color: #333;
         margin: 0;
         padding: 0;
      }

      .header {
         text-align: center;
         margin-bottom: 20px;
         border-bottom: 2px solid #ffd700;
         padding-bottom: 10px;
      }

      .header h2 {
         margin: 0;
         color: #000;
         font-size: 18px;
      }

      .info-table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 20px;
      }

      .info-table td {
         padding: 5px;
         vertical-align: top;
         border: 1px solid #eee;
      }

      .label {
         color: #666;
         font-size: 9px;
         text-transform: uppercase;
         display: block;
         margin-bottom: 2px;
      }

      .value {
         font-weight: bold;
         font-size: 11px;
      }

      .details-table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 30px;
      }

      .details-table th {
         background-color: #ffd700;
         color: #000;
         padding: 8px 4px;
         text-align: left;
         font-size: 9px;
         border: 1px solid #000;
      }

      .details-table td {
         padding: 6px 4px;
         border: 1px solid #ccc;
         font-size: 10px;
      }

      .text-center {
         text-align: center;
      }

      .bg-light {
         background-color: #f9f9f9;
      }

      .signature-section {
         width: 100%;
         margin-top: 30px;
      }

      .signature-box {
         width: 19%;
         display: inline-block;
         text-align: center;
         vertical-align: top;
      }

      .signature-line {
         margin-top: 50px;
         border-top: 1px solid #000;
         width: 80%;
         margin-left: auto;
         margin-right: auto;
      }

      .footer {
         position: fixed;
         bottom: 0;
         width: 100%;
         font-size: 8px;
         color: #999;
         text-align: right;
      }

      .notes-box {
         margin-top: 20px;
         padding: 10px;
         border-left: 3px solid #ffd700;
         background-color: #fffdf0;
      }
   </style>
</head>

<body>
   <div class="header">
      <h2>EXAMINATION FORM</h2>
      <div style="font-size: 10px; margin-top: 5px;">CPH Dashboard - Tyre Performance Module</div>
   </div>

   <table class="info-table">
      <tr>
         <td width="25%">
            <span class="label">DATE</span>
            <span class="value">{{ \Carbon\Carbon::parse($exam->examination_date)->format('d F Y') }}</span>
            <br><br>
            <span class="label">LOCATION / SEGMENT</span>
            <span class="value">{{ $exam->location->location_name ?? '-' }} /
               {{ $exam->segment->segment_name ?? '-' }}</span>
         </td>
         <td width="25%">
            <span class="label">UNIT / NO. POLISI</span>
            <span class="value">{{ $exam->vehicle->kode_kendaraan }} / {{ $exam->vehicle->no_polisi }}</span>
            <br><br>
            <span class="label">VEHICLE TYPE</span>
            <span class="value">{{ $exam->vehicle->jenis_kendaraan ?? '-' }}</span>
         </td>
         <td width="25%">
            <span class="label">ODOMETER (KM)</span>
            <span class="value">{{ number_format($exam->odometer, 0) }}</span>
            <br><br>
            <span class="label">HOUR METER (HM)</span>
            <span class="value">{{ number_format($exam->hour_meter, 0) }}</span>
         </td>
         <td width="25%">
            <span class="label">INSPECTION TIME</span>
            <span class="value">{{ substr($exam->start_time, 0, 5) }} - {{ substr($exam->end_time, 0, 5) }}</span>
            <br><br>
            <span class="label">INSPECTOR (TYRE MAN)</span>
            <span class="value">{{ $exam->tyre_man ?: '-' }}</span>
         </td>
      </tr>
   </table>

   <table class="details-table">
      <thead>
         <tr>
            <th width="30" class="text-center">POS</th>
            <th>BRAND</th>
            <th>PATTERN</th>
            <th>SIZE / PR</th>
            <th>SERIAL NUMBER</th>
            <th width="35" class="text-center">PSI</th>
            <th width="35" class="text-center">RTD 1</th>
            <th width="35" class="text-center">RTD 2</th>
            <th width="35" class="text-center">RTD 3</th>
            <th width="35" class="text-center">RTD 4</th>
            <th>REMARKS</th>
            <th width="40" class="text-center">FOTO</th>
         </tr>
      </thead>
      <tbody>
         @foreach ($exam->details as $detail)
            @php $tyre = $detail->tyre; @endphp
            <tr>
               <td class="text-center bg-light"><strong>{{ $detail->position->position_code }}</strong></td>
               <td>{{ $tyre->brand->brand_name ?? '-' }}</td>
               <td>{{ $tyre->pattern->name ?? '-' }}</td>
               <td>{{ $tyre->size->size ?? '-' }}
                  {{ $tyre->size->ply_rating ? '/ ' . $tyre->size->ply_rating . ' PR' : '' }}
               </td>
               <td><strong>{{ $tyre->serial_number ?? '-' }}</strong></td>
               <td class="text-center">{{ $detail->psi_reading ?: '-' }}</td>
               <td class="text-center">{{ $detail->rtd_1 ?: '-' }}</td>
               <td class="text-center">{{ $detail->rtd_2 ?: '-' }}</td>
               <td class="text-center">{{ $detail->rtd_3 ?: '-' }}</td>
               <td class="text-center">{{ $detail->rtd_4 ?: '-' }}</td>
               <td>{{ $detail->remarks ?: '-' }}</td>
               <td class="text-center">
                  @if (isset($images[$tyre->serial_number]) && count($images[$tyre->serial_number]) > 0)
                     @php $photo = $images[$tyre->serial_number]->first(); @endphp
                     <img src="{{ public_path('storage/' . $photo->image_path) }}"
                        style="width: 30px; height: 30px; object-fit: cover;">
                  @else
                     -
                  @endif
               </td>
            </tr>
         @endforeach
      </tbody>
   </table>

   @if ($exam->notes)
      <div class="notes-box">
         <span class="label">ADDITIONAL NOTES:</span>
         <div>{{ $exam->notes }}</div>
      </div>
   @endif

   @if ($exam->photo_unit_front)
      <div style="margin-top: 20px;">
         <span class="label">UNIT PHOTO:</span>
         <div style="text-align: center; border: 1px solid #ccc; padding: 10px; margin-top: 10px;">
            <img src="{{ public_path('storage/' . $exam->photo_unit_front) }}"
               style="width: 100%; max-height: 300px; object-fit: contain;">
         </div>
      </div>
   @endif



   <div class="footer">
      Generated on: {{ date('d/m/Y H:i') }} | CPH Dashboard System
   </div>
</body>

</html>
