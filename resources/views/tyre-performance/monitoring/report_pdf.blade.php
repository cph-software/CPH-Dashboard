<!DOCTYPE html>
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
   <title>Monitoring Report - {{ $vehicle->vehicle_number }}</title>
   <style>
      @page { margin: 25px 30px; size: a4 landscape; }
      body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
      
      .header, .footer { width: 100%; border-bottom: 2px solid #e74c3c; padding-bottom: 5px; margin-bottom: 10px; }
      .footer { border-bottom: none; border-top: 1px solid #ddd; padding-top: 5px; margin-top: 10px; text-align: center; font-size: 9px; color: #777; position: fixed; bottom: -15px; }
      
      .title { font-size: 18px; font-weight: bold; color: #2c3e50; text-transform: uppercase; float: right; margin-top: 10px; }

      .section-title { background: #34495e; color: #fff; padding: 5px 10px; font-weight: bold; margin: 10px 0 5px 0; font-size: 13px; text-transform: uppercase; }
      .row { width: 100%; clear: both; margin-bottom: 5px; }
      .col-6 { width: 49%; float: left; }
      .col-6:last-child { float: right; }
      .col-3 { width: 24.25%; float: left; margin-right: 1%; }

      /* Tables */
      table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
      th, td { border: 1px solid #bdc3c7; padding: 4px 6px; vertical-align: top; }
      th { background-color: #ecf0f1; font-weight: bold; font-size: 10px; }
      td { font-size: 10px; }
      
      .company-info table td.label { font-weight: bold; width: 35%; background: #f9f9f9; }
      
      /* Images */
      .photo-box { background: #fff; border: 1px solid #ddd; text-align: center; height: 140px; overflow: hidden; padding: 3px; }
      .photo-box img { max-height: 130px; max-width: 100%; object-fit: contain; }
      .photo-label { background: #ecf0f1; border: 1px solid #ddd; border-top: none; text-align: center; font-size: 9px; font-weight: bold; padding: 3px; }
      .img-placeholder { color: #999; line-height: 140px; font-size: 10px; font-weight: bold; }

      .page-break { page-break-after: always; }
      .avoid-break { page-break-inside: avoid; }
      
      .text-center { text-align: center; }
      .bold { font-weight: bold; }
      .mb-10 { margin-bottom: 10px; }
      .mt-10 { margin-top: 10px; }
      .clearfix::after { content: ""; clear: both; display: table; }
   </style>
</head>
<body>

@php $firstTyre = $session->installations->first(); @endphp

<!-- HEADER & PAGE 1 -->
<div class="header clearfix">
   <div style="float: left; width: 50%;">
      <h2 style="margin: 0; color: #e74c3c;">CPH TYRE PERFORMANCE</h2>
      <div style="font-size: 10px; color: #7f8c8d;">Trusted Monitoring Dashboard</div>
   </div>
   <div class="title">MONITORING REPORT</div>
</div>

<div class="section-title">A. EXECUTIVE SUMMARY & COMPANY PROFILE</div>
<div class="row">
   <div class="col-6">
      <table class="company-info">
         <tbody>
            <tr><td class="label">Company Name</td><td>{{ $masterVehicle->company->name ?? 'PT ABC' }}</td></tr>
            <tr><td class="label">Address</td><td>{{ $masterVehicle->company->address ?? '-' }}</td></tr>
            <tr><td class="label">PIC / Contact</td><td>{{ $checks->first()->driver_name ?? $vehicle->driver_name }} ({{ $vehicle->phone_number ?? '-' }})</td></tr>
            <tr><td class="label">Vehicle Number</td><td class="bold" style="font-size: 12px; color: #e74c3c;">{{ $vehicle->vehicle_number }}</td></tr>
            <tr><td class="label">Vehicle Tonnage</td><td>{{ $vehicle->load_capacity ?? '-' }} Ton</td></tr>
            <tr><td class="label">Load Average / Retase</td><td>{{ $session->retase ?? '-' }} Ton</td></tr>
            <tr><td class="label">Route / Condition</td><td>{{ $vehicle->application ?? '-' }}</td></tr>
         </tbody>
      </table>
   </div>
   <div class="col-6">
       <table class="company-info">
          <tbody>
             <tr><td class="label">Tyre Brand used</td><td>{{ $firstTyre->brand ?? '-' }}</td></tr>
             <tr><td class="label">Size / Pattern</td><td>{{ $firstTyre->size ?? '-' }} / {{ $firstTyre->pattern ?? '-' }}</td></tr>
             <tr><td class="label">Recommended PSI</td><td>{{ $checks->first()->inf_press_recommended ?? '-' }} PSI</td></tr>
             <tr><td class="label">Original Tread (OTD)</td><td>{{ number_format($firstTyre->original_rtd ?? 0, 1) }} mm</td></tr>
             <tr><td class="label">Target Lifetime</td><td>{{ $checks->max('projected_life_km') ? number_format($checks->max('projected_life_km'), 0) : '-' }} KM</td></tr>
             <tr><td class="label">Total Inspect Date</td><td>{{ $date }}</td></tr>
             <tr><td class="label">Actual Reading ODO</td><td class="bold">{{ number_format($checks->first()->odometer_reading ?? 0, 0) }} KM</td></tr>
          </tbody>
       </table>
   </div>
</div>

<div class="section-title mt-10">B. UNIT PHOTOS & EVIDENCE</div>
<div class="row">
   <div class="col-3">
       <div class="photo-box">
          @if (isset($generalImages['map']))
             <img src="{{ public_path('storage/' . $generalImages['map']->image_path) }}">
          @else
             <div class="img-placeholder">NO SCREENSHOT</div>
          @endif
       </div>
       <div class="photo-label">Route Map Screenshot</div>
   </div>
   <div class="col-3">
       <div class="photo-box">
          @if (isset($generalImages['fleet']))
             <img src="{{ public_path('storage/' . $generalImages['fleet']->image_path) }}">
          @else
             <div class="img-placeholder">NO PHOTO</div>
          @endif
       </div>
       <div class="photo-label">Fleet Photo</div>
   </div>
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
   <div class="col-3" style="margin-right: 0;">
       <div class="photo-box">
          @if (isset($generalImages['odometer_km']))
             <img src="{{ public_path('storage/' . $generalImages['odometer_km']->image_path) }}">
          @else
             <div class="img-placeholder">NO PHOTO</div>
          @endif
       </div>
       <div class="photo-label" style="color: #e74c3c">Actual ODO: {{ number_format($checks->first()->odometer_reading ?? 0, 0) }} KM</div>
   </div>
</div>

<!-- PAGE BREAK FOR TYRES GALLERY -->
<div class="page-break"></div>

<div class="header clearfix">
   <div style="float: left; width: 50%;">
      <h2 style="margin: 0; color: #e74c3c;">CPH TYRE PERFORMANCE</h2>
      <div style="font-size: 10px; color: #7f8c8d;">Trusted Monitoring Dashboard</div>
   </div>
   <div class="title">TYRE INSPECTION GALLERY</div>
</div>

@php $counter = 0; @endphp
@foreach ($checks as $index => $check)
   @if ($counter > 0 && $counter % 2 == 0)
      <div class="page-break"></div>
      <div class="header clearfix">
         <div style="float: left; width: 50%;">
            <h2 style="margin: 0; color: #e74c3c;">CPH TYRE PERFORMANCE</h2>
            <div style="font-size: 10px; color: #7f8c8d;">Trusted Monitoring Dashboard</div>
         </div>
         <div class="title">TYRE INSPECTION GALLERY</div>
      </div>
   @endif

   <div class="avoid-break mb-10" style="padding: 10px; border: 1px solid #ccc; background: #fafafa; border-radius: 4px;">
      <div class="section-title" style="margin-top:0; background: #2c3e50;">Position: {{ $check->position }} | Serial: {{ $check->serial_number }}</div>
      
      <!-- Stats mini table -->
      <table>
         <tr>
             <th>Brand</th><td>{{ $check->tyre->brand->brand_name ?? '-' }}</td>
             <th>Size</th><td>{{ $check->tyre->size->size ?? '-' }}</td>
             <th>Avg RTD</th><td class="bold">{{ number_format(($check->rtd_1+$check->rtd_2+$check->rtd_3+$check->rtd_4)/4, 1) }} mm</td>
             <th>Pressure</th><td class="bold">{{ $check->inf_press_actual }} PSI</td>
             <th>Condition</th><td class="bold" style="text-transform:uppercase;">{{ $check->condition }}</td>
         </tr>
      </table>

      <!-- Photos block -->
      <div class="row" style="margin-top: 8px;">
         @php
            $tyrePhotos = $images->get($check->serial_number, collect())->take(6);
            $cols = 0;
         @endphp
         
         @foreach ($tyrePhotos as $img)
            <div style="width: 15.6%; float: left; margin-right: 1.1%; <?php if($cols==5) echo 'margin-right:0;'; ?>">
               <div class="photo-box" style="height: 110px; background: #fff;">
                  <img src="{{ public_path('storage/' . $img->image_path) }}" style="max-height: 104px;">
               </div>
               <div class="photo-label" style="font-size:8px; border-bottom: 2px solid #34495e;">{{ ucwords(str_replace(['tyre_', '_'], ' ', $img->image_type)) }}</div>
            </div>
            @php $cols++; @endphp
         @endforeach

         @for ($i = $cols; $i < 6; $i++)
            <div style="width: 15.6%; float: left; margin-right: 1.1%; <?php if($i==5) echo 'margin-right:0;'; ?>">
               <div class="photo-box" style="height: 110px; background: #eee;">
                  <div class="img-placeholder" style="line-height:110px; color:#bbb;">NO PHOTO</div>
               </div>
               <div class="photo-label" style="font-size:8px; border-bottom: 2px solid #bdc3c7; color:#bbb;">NOT RECORDED</div>
            </div>
         @endfor
      </div>
   </div>

   @php $counter++; @endphp
@endforeach

<div class="footer">
   CPH Dashboard Monitoring System &copy; {{ date('Y') }} - Printed on {{ date('d M Y H:i:s') }}
</div>

</body>
</html>
