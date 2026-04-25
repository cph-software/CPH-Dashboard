<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <title>Monitoring Report - {{ $vehicle->vehicle_number }}</title>
   <style>
      body {
         font-family: 'Helvetica', 'Arial', sans-serif;
         font-size: 11px;
         color: #333;
         margin: 0;
         padding: 0;
      }

      @page {
         margin: 25px 30px;
         size: a4 landscape;
      }

      .header {
         text-align: center;
         margin-bottom: 20px;
         border-bottom: 2px solid #c0392b;
         padding-bottom: 10px;
      }

      .header h2 {
         margin: 0;
         color: #000;
         font-size: 18px;
         text-transform: uppercase;
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
         margin-bottom: 20px;
      }

      .details-table th {
         background-color: #2c3e50;
         color: #fff;
         padding: 6px 4px;
         text-align: center;
         font-size: 9px;
         border: 1px solid #000;
      }

      .details-table td {
         padding: 6px 4px;
         border: 1px solid #ccc;
         font-size: 10px;
         text-align: center;
         vertical-align: middle;
      }

      .details-table td.text-left {
         text-align: left;
      }

      .bg-light {
         background-color: #f9f9f9;
      }

      .footer {
         position: fixed;
         bottom: -15px;
         width: 100%;
         font-size: 8px;
         color: #999;
         text-align: right;
         border-top: 1px solid #ddd;
         padding-top: 5px;
      }

      .photo-box {
         background: #fff;
         border: 1px solid #ddd;
         text-align: center;
         height: 120px;
         overflow: hidden;
         padding: 3px;
      }

      .photo-box img {
         max-height: 110px;
         max-width: 100%;
         object-fit: contain;
      }

      .photo-label {
         background: #ecf0f1;
         border: 1px solid #ddd;
         border-top: none;
         text-align: center;
         font-size: 9px;
         font-weight: bold;
         padding: 4px;
         color: #c0392b;
      }

      .img-placeholder {
         color: #999;
         line-height: 110px;
         font-size: 10px;
         font-weight: bold;
      }

      .row {
         width: 100%;
         clear: both;
         margin-bottom: 5px;
      }

      .col-3 {
         width: 24.25%;
         float: left;
         margin-right: 1%;
      }

      .col-3:last-child {
         margin-right: 0;
      }

      .clearfix::after {
         content: "";
         clear: both;
         display: table;
      }

      .section-title {
         background: #c0392b;
         color: #fff;
         padding: 5px 10px;
         font-weight: bold;
         margin: 10px 0 10px 0;
         font-size: 12px;
         text-transform: uppercase;
      }
      
      .page-break {
         page-break-before: always;
      }
   </style>
</head>

<body>
   @php 
      $firstTyre = $session->installations->first();
      $firstCheck = $checks->first();
      $measurementMode = $masterVehicle->operation_measurement ?? 'HM';
   @endphp

   <div class="header">
      <h2>TYRE MONITORING REPORT (CHECK #{{ $checkNumber }})</h2>
      <div style="font-size: 10px; margin-top: 5px;">CPH Dashboard - Tyre Performance Module</div>
   </div>

   <table class="info-table">
      <tr>
         <td width="25%">
            <span class="label">DATE</span>
            <span class="value">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</span>
            <br><br>
            <span class="label">COMPANY</span>
            <span class="value">{{ $masterVehicle->company->name ?? 'PT ABC' }}</span>
         </td>
         <td width="25%">
            <span class="label">UNIT / NO. POLISI</span>
            <span class="value" style="color: #c0392b; font-size: 12px;">{{ $vehicle->vehicle_number }} / {{ $vehicle->no_polisi ?? '-' }}</span>
            <br><br>
            <span class="label">VEHICLE TYPE</span>
            <span class="value">{{ $vehicle->jenis_kendaraan ?? '-' }}</span>
         </td>
         <td width="25%">
            <span class="label">ODOMETER (KM)</span>
            <span class="value">{{ number_format($firstCheck->odometer_reading ?? $session->odometer_start, 0) }}</span>
            <br><br>
            <span class="label">HOUR METER (HM)</span>
            <span class="value">{{ number_format($firstCheck->hm_reading ?? $session->hm_start, 0) }}</span>
         </td>
         <td width="25%">
            <span class="label">INSPECTOR</span>
            <span class="value">{{ $firstCheck->driver_name ?? $vehicle->driver_name ?? '-' }}</span>
            <br><br>
            <span class="label">LOAD AVG / RETASE</span>
            <span class="value">{{ $session->retase ?? '-' }} Ton</span>
         </td>
      </tr>
   </table>

   <div class="section-title">A. TYRE INSPECTION DETAILS</div>
   <table class="details-table">
      <thead>
         <tr>
            <th width="30">POS</th>
            <th>SERIAL NUMBER</th>
            <th>BRAND / SIZE</th>
            <th width="45">PSI (R/A)</th>
            <th width="80">RTD (1-4)</th>
            <th width="30">AVG</th>
            <th width="35">WORN%</th>
            <th width="40">{{ $measurementMode === 'HM' ? 'HM/mm' : 'KM/mm' }}</th>
            <th width="45">{{ $measurementMode === 'HM' ? 'SISA HM' : 'SISA KM' }}</th>
            <th width="50">CONDITION</th>
            <th>REMARKS</th>
            <th width="35">FOTO</th>
         </tr>
      </thead>
      <tbody>
         @foreach ($checks as $c)
            @php 
               $avgVal = ($c->rtd_1 + $c->rtd_2 + $c->rtd_3 + ($c->rtd_4 ?? 0)) / ($c->rtd_4 ? 4 : 3);
            @endphp
            <tr>
               <td class="bg-light"><strong>{{ $c->position }}</strong></td>
               <td><strong>{{ $c->serial_number }}</strong></td>
               <td>
                  {{ $c->tyre->brand->brand_name ?? '-' }}<br>
                  <span style="font-size: 8px; color:#666;">{{ $c->tyre->size->size ?? '-' }}</span>
               </td>
               <td>{{ $c->inf_press_recommended ?? '-' }} / {{ $c->inf_press_actual ?? '-' }}</td>
               <td style="font-size: 9px;">
                  {{ $c->rtd_1 }}/{{ $c->rtd_2 }}/{{ $c->rtd_3 }}{{ $c->rtd_4 ? '/' . $c->rtd_4 : '' }}
               </td>
               <td><strong>{{ number_format($avgVal, 1) }}</strong></td>
               <td>{{ number_format($c->worn_percentage, 0) }}%</td>
               <td>{{ $c->km_per_mm > 0 ? number_format($c->km_per_mm, 0) : 'N/A' }}</td>
               <td style="color: #c0392b; font-weight: bold;">
                  {{ $c->projected_life_km > 0 ? number_format($c->projected_life_km, 0) : 'N/A' }}
               </td>
               <td style="text-transform: uppercase;">
                  <span style="color: {{ $c->condition == 'ok' ? '#27ae60' : ($c->condition == 'warning' ? '#f39c12' : '#c0392b') }}; font-weight: bold;">
                     {{ $c->condition }}
                  </span>
               </td>
               <td class="text-left" style="font-size: 9px;">
                  {{ $c->recommendation ?? '-' }}<br>
                  <span style="color: #666;">{{ $c->notes ?? '' }}</span>
               </td>
               <td>
                  @if (isset($images[$c->serial_number]) && count($images[$c->serial_number]) > 0)
                     @php $photo = $images[$c->serial_number]->first(); @endphp
                     <img src="{{ public_path('storage/' . $photo->image_path) }}" style="width: 25px; height: 25px; object-fit: cover; border: 1px solid #ccc;">
                  @else
                     -
                  @endif
               </td>
            </tr>
         @endforeach
      </tbody>
   </table>

   <div class="page-break"></div>
   <div class="header">
      <h2>GENERAL EVIDENCE & PHOTOS</h2>
      <div style="font-size: 10px; margin-top: 5px;">CPH Dashboard - Tyre Performance Module</div>
   </div>

   <div class="row clearfix" style="margin-top: 20px;">
      <div class="col-3">
          <div class="photo-box">
             @if (isset($generalImages['vehicle']))
                <img src="{{ public_path('storage/' . $generalImages['vehicle']->image_path) }}">
             @else
                <div class="img-placeholder">NO PHOTO</div>
             @endif
          </div>
          <div class="photo-label">Vehicle Identity</div>
      </div>
      <div class="col-3">
          <div class="photo-box">
             @if (isset($generalImages['odometer_km']))
                <img src="{{ public_path('storage/' . $generalImages['odometer_km']->image_path) }}">
             @elseif (isset($generalImages['hm']))
                <img src="{{ public_path('storage/' . $generalImages['hm']->image_path) }}">
             @else
                <div class="img-placeholder">NO PHOTO</div>
             @endif
          </div>
          <div class="photo-label">Odometer (KM/HM)</div>
      </div>
      <div class="col-3">
          <div class="photo-box">
             @if (isset($generalImages['fleet']))
                <img src="{{ public_path('storage/' . $generalImages['fleet']->image_path) }}">
             @else
                <div class="img-placeholder">NO PHOTO</div>
             @endif
          </div>
          <div class="photo-label">Fleet / Unit Status</div>
      </div>
      <div class="col-3">
          <div class="photo-box">
             @if (isset($generalImages['map']))
                <img src="{{ public_path('storage/' . $generalImages['map']->image_path) }}">
             @else
                <div class="img-placeholder">NO PHOTO</div>
             @endif
          </div>
          <div class="photo-label">Map / Location Map</div>
      </div>
   </div>

   <div class="footer">
      Generated on: {{ date('d/m/Y H:i:s') }} | CPH Dashboard Monitoring System
   </div>
</body>

</html>
