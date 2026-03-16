<!DOCTYPE html>
<html>

<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>Monitoring Report - {{ $vehicle->vehicle_number }}</title>
   <style>
      @page {
         margin: 0;
         size: a4 landscape;
      }

      body {
         font-family: 'Helvetica', 'Arial', sans-serif;
         margin: 0;
         padding: 0;
         color: #333;
      }

      .slide {
         page-break-after: always;
         width: 100%;
         height: 100%;
         position: relative;
         padding: 40px;
         box-sizing: border-box;
      }

      .slide:last-child {
         page-break-after: avoid;
      }

      /* Typography */
      h1 {
         font-size: 48px;
         margin: 0;
         font-weight: bold;
      }

      h2 {
         font-size: 32px;
         margin: 10px 0;
      }

      h3 {
         font-size: 24px;
         margin: 5px 0;
         border-bottom: 2px solid #333;
         padding-bottom: 5px;
      }

      .text-center {
         text-align: center;
      }

      .bold {
         font-weight: bold;
      }

      /* Layout Grid */
      .row {
         width: 100%;
         clear: both;
      }

      .col-6 {
         width: 50%;
         float: left;
      }

      .col-4 {
         width: 33.33%;
         float: left;
      }

      .col-12 {
         width: 100%;
      }

      /* Tables */
      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
      }

      table,
      th,
      td {
         border: 1px solid #ddd;
      }

      th,
      td {
         padding: 10px;
         text-align: left;
      }

      th {
         background-color: #f2f2f2;
         font-weight: bold;
      }

      /* Utilities */
      .mt-50 {
         margin-top: 50px;
      }

      .mt-100 {
         margin-top: 100px;
      }

      .img-container {
         text-align: center;
         margin-top: 10px;
      }

      .img-label {
         font-size: 14px;
         margin-top: 5px;
         font-weight: bold;
      }

      .photo-box {
         width: 90%;
         height: 250px;
         border: 1px dashed #ccc;
         display: inline-block;
         margin: 5px;
         overflow: hidden;
         background-color: #fafafa;
      }

      .img-placeholder {
         line-height: 250px;
         color: #aaa;
      }

      img {
         max-width: 100%;
         max-height: 100%;
         object-fit: contain;
      }

      .company-info .label {
         width: 40%;
         display: inline-block;
         font-weight: bold;
      }

      .company-info .value {
         width: 55%;
         display: inline-block;
      }

      .company-info div {
         margin-bottom: 8px;
         font-size: 14px;
         border-bottom: 1px solid #eee;
         padding-bottom: 2px;
      }
   </style>
</head>

<body>

   <!-- SLIDE 1: COVER -->
   <div class="slide">
      <div style="text-align: center; margin-top: 150px;">
         <h1 style="text-transform: uppercase;">MONITORING REPORT</h1>
         <div style="margin-top: 40px;">
            @php
               $firstTyre = $session->installations->first();
            @endphp
            <h2 class="bold">{{ $firstTyre->brand ?? 'BRAND' }}</h2>
            <h3>CPK / TESTING / MONITORING</h3>
            <h3 style="border-bottom: none;">{{ $firstTyre->size ?? 'SIZE' }} / {{ $firstTyre->pattern ?? 'PATTERN' }}
            </h3>

            <div style="margin-top: 80px;">
               <h2 class="bold">{{ $vehicle->fleet_name }}</h2>
               <h1 class="bold">({{ $vehicle->vehicle_number }})</h1>
            </div>
         </div>
      </div>
   </div>

   <!-- SLIDE 2: COMPANY PROFILE -->
   <div class="slide">
      <h2 class="bold">Company Profile</h2>
      <div class="row">
         <div class="col-6 company-info">
            <div><span class="label">Company Name</span>: <span
                  class="value">{{ $masterVehicle->company->name ?? 'PT ABC' }}</span></div>
            <div><span class="label">Address</span>: <span
                  class="value">{{ $masterVehicle->company->address ?? '-' }}</span></div>
            <div><span class="label">PIC - Contact</span>: <span
                  class="value">{{ $vehicle->phone_number ?? '-' }}</span></div>
            <div><span class="label">PIC - Driver</span>: <span
                  class="value">{{ $checks->first()->driver_name ?? $vehicle->driver_name }}</span></div>
            <div><span class="label">Truck Vehicle Number</span>: <span
                  class="value">{{ $vehicle->vehicle_number }}</span></div>
            <div><span class="label">Total Truck</span>: <span class="value">-</span></div>
            <div><span class="label">Vehicle Tonnage</span>: <span class="value">{{ $vehicle->load_capacity ?? '-' }}
                  Ton</span></div>
            <div><span class="label">Load Average</span>: <span class="value">{{ $session->retase ?? '-' }}
                  Ton</span></div>
            <div><span class="label">Tyre Pressure</span>: <span
                  class="value">{{ $checks->first()->inf_press_recommended ?? '-' }} PSI</span></div>
            <div><span class="label">Tyre Used (Merk Size Pattern)</span>: <span
                  class="value">{{ $firstTyre->brand ?? '' }} {{ $firstTyre->size ?? '' }}
                  {{ $firstTyre->pattern ?? '' }}</span></div>
            <div><span class="label">Road Condition</span>: <span
                  class="value">{{ $vehicle->application ?? '-' }}</span></div>
            <div><span class="label">Rute</span>: <span class="value">{{ $vehicle->application ?? '-' }}</span></div>
            <div><span class="label">Target Lifetime (KM)</span>: <span
                  class="value">{{ $checks->max('projected_life_km') ? number_format($checks->max('projected_life_km'), 0) : '-' }}
                  KM</span></div>
         </div>
         <div class="col-6">
            <div class="img-container">
               <div class="photo-box" style="height: 400px; width: 100%;">
                  @if (isset($generalImages['map']))
                     <img src="{{ public_path('storage/' . $generalImages['map']->image_path) }}">
                  @else
                     <div class="img-placeholder">FOTO MAP</div>
                  @endif
               </div>
               <div class="img-label">Screenshoot Map</div>
            </div>
         </div>
      </div>
   </div>

   <!-- SLIDE 3: FLEET PHOTO -->
   <div class="slide">
      <h2 class="bold">Fleet Photo</h2>
      <div class="row" style="margin-top: 30px;">
         <div class="col-6">
            <div class="img-container">
               <div class="photo-box" style="height: 400px; width: 95%;">
                  @if (isset($generalImages['fleet']))
                     <img src="{{ public_path('storage/' . $generalImages['fleet']->image_path) }}">
                  @else
                     <div class="img-placeholder">FOTO FLEET</div>
                  @endif
               </div>
            </div>
         </div>
         <div class="col-6">
            <div class="img-container">
               <div class="photo-box" style="height: 400px; width: 95%;">
                  @if (isset($generalImages['vehicle']))
                     <img src="{{ public_path('storage/' . $generalImages['vehicle']->image_path) }}">
                  @else
                     <div class="img-placeholder">FOTO KENDARAAN</div>
                  @endif
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- SLIDE 4: TESTED TYRE -->
   <div class="slide">
      <h2 class="bold">Tested Tyre</h2>
      <table>
         <thead>
            <tr>
               <th>Specification</th>
               <th style="width: 300px;">Remark</th>
               <th style="width: 400px;">Photo</th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <td>Tyre Brand</td>
               <td>{{ $firstTyre->brand ?? '-' }}</td>
               <td rowspan="7" style="vertical-align: middle; text-align: center;">
                  <div class="photo-box" style="height: 350px; width: 350px; border: none;">
                     @if (isset($images[$firstTyre->serial_number]) &&
                             $images[$firstTyre->serial_number]->where('image_type', 'tyre_serial')->first())
                        <img
                           src="{{ public_path('storage/' . $images[$firstTyre->serial_number]->where('image_type', 'tyre_serial')->first()->image_path) }}">
                     @else
                        <div class="img-placeholder">FOTO BAN</div>
                     @endif
                  </div>
               </td>
            </tr>
            <tr>
               <td>Size</td>
               <td>{{ $firstTyre->size ?? '-' }}</td>
            </tr>
            <tr>
               <td>Tipe</td>
               <td>{{ $firstTyre->pattern ?? '-' }}</td>
            </tr>
            <tr>
               <td>Tyre PR</td>
               <td>-</td>
            </tr>
            <tr>
               <td>OTD</td>
               <td>{{ number_format($firstTyre->original_rtd ?? 0, 1) }} mm</td>
            </tr>
            <tr>
               <td>Target KM/HM</td>
               <td>{{ number_format($checks->max('projected_life_km'), 0) }} KM</td>
            </tr>
            <tr>
               <td>Target CPK</td>
               <td>-</td>
            </tr>
         </tbody>
      </table>
   </div>

   <!-- INSTALLATION PROCESS PER TYRE -->
   @foreach ($checks as $check)
      <div class="slide">
         <h2 class="bold">Installation Process - Pos: {{ $check->position }} ({{ $check->serial_number }})</h2>
         <div class="row">
            @php
               $tyrePhotos = $images->get($check->serial_number, collect())->take(6);
            @endphp
            @foreach ($tyrePhotos as $img)
               <div class="col-4">
                  <div class="img-container">
                     <div class="photo-box">
                        <img src="{{ public_path('storage/' . $img->image_path) }}">
                     </div>
                     <div class="img-label">{{ ucwords(str_replace(['tyre_', '_'], ' ', $img->image_type)) }}</div>
                  </div>
               </div>
               @if ($loop->iteration % 3 == 0)
         </div>
         <div class="row">
   @endif
   @endforeach

   @for ($i = $tyrePhotos->count(); $i < 6; $i++)
      <div class="col-4">
         <div class="img-container">
            <div class="photo-box">
               <div class="img-placeholder">TIDAK ADA FOTO</div>
            </div>
         </div>
      </div>
      @if (($i + 1) % 3 == 0)
         </div>
         <div class="row">
      @endif
   @endfor
   </div>
   </div>
   @endforeach

   <!-- LAST SLIDE: TESTED UNIT ODOMETER -->
   <div class="slide">
      <h2 class="bold">Tested Unit Odo Meter</h2>
      <div style="text-align: center; margin-top: 50px;">
         <div class="photo-box" style="height: 450px; width: 700px;">
            @if (isset($generalImages['odometer_km']))
               <img src="{{ public_path('storage/' . $generalImages['odometer_km']->image_path) }}">
            @else
               <div class="img-placeholder">FOTO ODOMETER</div>
            @endif
         </div>
         <div style="margin-top: 20px;">
            <h1 class="bold">Actual KM: {{ number_format($checks->first()->odometer_reading ?? 0, 0) }} KM</h1>
         </div>
      </div>
   </div>

</body>

</html>
