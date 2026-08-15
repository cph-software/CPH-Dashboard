@php
   $m = $movement;
   $tyre = $m->tyre;
   $vehicle = $m->vehicle;
   $position = $m->position;

   // Badges color mapping
   $badges = [
       'Installation' => 'success',
       'Removal' => 'danger',
       'Rotation' => 'warning',
   ];
   $typeBadge =
       isset($m->movement_type) && isset($badges[$m->movement_type]) ? $badges[$m->movement_type] : 'secondary';

   // Label mapping
   $labels = [
       'Installation' => 'Pemasangan',
       'Removal' => 'Pelepasan',
       'Rotation' => 'Rotasi',
   ];
   $typeLabel =
       isset($m->movement_type) && isset($labels[$m->movement_type])
           ? $labels[$m->movement_type]
           : $m->movement_type ?? '-';
@endphp

<div class="detail-movement-wrap">
   {{-- HEADER --}}
   <div class="d-flex align-items-center mb-3 gap-2">
      <span class="badge bg-{{ $typeBadge }} fs-6">{{ $typeLabel }}</span>
      <span class="text-muted small">ID #{{ $m->id }} &bull;
         {{ $m->movement_date ? \Carbon\Carbon::parse($m->movement_date)->format('d M Y') : '-' }}</span>
   </div>

   {{-- INFO GRID --}}
   <div class="row g-3 mb-4">
      <div class="col-md-6">
         <div class="card bg-light border-0 shadow-none p-2 mb-2">
            <h6 class="fw-bold mb-2 text-primary small text-uppercase"><i class="ri-disc-line me-1"></i> Data Ban & Stok</h6>
            <table class="table table-sm table-borderless table-hover align-middle mb-0">
               <tr>
                  <td class="fw-bold text-muted" style="width:40%">SN Ban</td>
                  <td>
                     <strong>{{ $tyre->serial_number ?? '-' }}</strong>
                     @if (!empty($tyre->custom_serial_number))
                        <span class="badge bg-label-info font-monospace ms-1">#{{ $tyre->custom_serial_number }}</span>
                     @endif
                  </td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">Pemilik Stok (Induk/Anak)</td>
                  <td>
                     @if ($tyre && $tyre->company)
                        <span class="badge bg-label-primary"><i class="ri-store-2-line me-1"></i>{{ $tyre->company->company_name }}</span>
                     @else
                        <span class="text-muted">-</span>
                     @endif
                  </td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">Brand / Size</td>
                  <td>{{ $tyre->brand->brand_name ?? '-' }} · {{ $tyre->size->size ?? '-' }}</td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">Pattern</td>
                  <td>{{ $tyre->pattern->name ?? '-' }}</td>
               </tr>
            </table>
         </div>

         <div class="card bg-light border-0 shadow-none p-2">
            <h6 class="fw-bold mb-2 text-primary small text-uppercase"><i class="ri-truck-line me-1"></i> Data Unit & Posisi</h6>
            <table class="table table-sm table-borderless table-hover align-middle mb-0">
               <tr>
                  <td class="fw-bold text-muted" style="width:40%">Unit Kendaraan</td>
                  <td>
                     <strong>{{ $vehicle->kode_kendaraan ?? '-' }}</strong>
                     @if (!empty($vehicle->no_polisi))
                        <span class="text-muted small">({{ $vehicle->no_polisi }})</span>
                     @endif
                  </td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">Perusahaan Unit</td>
                  <td>
                     @if ($vehicle && $vehicle->company)
                        <span class="badge bg-label-secondary"><i class="ri-building-line me-1"></i>{{ $vehicle->company->company_name }}</span>
                     @else
                        <span class="text-muted">-</span>
                     @endif
                  </td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">Posisi Terpasang</td>
                  <td>
                     <span class="badge bg-label-dark">{{ $position ? $position->position_code . ' — ' . $position->position_name : '-' }}</span>
                  </td>
               </tr>
            </table>
         </div>
      </div>

      <div class="col-md-6">
         <div class="card bg-light border-0 shadow-none p-2 mb-2">
            <h6 class="fw-bold mb-2 text-primary small text-uppercase"><i class="ri-map-pin-2-line me-1"></i> Lokasi & Pelaksana</h6>
            <table class="table table-sm table-borderless table-hover align-middle mb-0">
               <tr>
                  <td class="fw-bold text-muted" style="width:40%">Lokasi Kerja</td>
                  <td>{{ $m->workLocation->location_name ?? $m->work_location ?? '-' }}</td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">Segmen</td>
                  <td>{{ $m->segment->segment_name ?? '-' }}</td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">Petugas Tyreman</td>
                  <td>{{ $m->tyreman_1 ?? '-' }} {{ $m->tyreman_2 ? ' / ' . $m->tyreman_2 : '' }}</td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">Waktu Pengerjaan</td>
                  <td>{{ $m->start_time ?? '-' }} – {{ $m->end_time ?? '-' }}</td>
               </tr>
            </table>
         </div>

         <div class="card bg-light border-0 shadow-none p-2">
            <h6 class="fw-bold mb-2 text-primary small text-uppercase"><i class="ri-speed-up-line me-1"></i> Metrik & Kondisi</h6>
            <table class="table table-sm table-borderless table-hover align-middle mb-0">
               <tr>
                  <td class="fw-bold text-muted" style="width:40%">Odo / HM</td>
                  <td>
                     {{ $m->odometer_reading ? number_format($m->odometer_reading, 0) . ' km' : '-' }} 
                     {{ $m->hour_meter_reading ? ' / ' . number_format($m->hour_meter_reading, 0) . ' Hm' : '' }}
                  </td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">Tekanan PSI</td>
                  <td>{{ $m->psi_reading ? $m->psi_reading . ' PSI' : '-' }}</td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">RTD (Rata-rata)</td>
                  <td><strong>{{ $m->rtd_reading ? $m->rtd_reading . ' mm' : '-' }}</strong></td>
               </tr>
               <tr>
                  <td class="fw-bold text-muted">RTD 1-4</td>
                  <td>
                     @php
                        $rtdParts = array_filter([$m->rtd_1, $m->rtd_2, $m->rtd_3, $m->rtd_4], function ($v) {
                            return $v !== null;
                        });
                     @endphp
                     {{ count($rtdParts) > 0 ? implode(' / ', $rtdParts) . ' mm' : '-' }}
                  </td>
               </tr>
            </table>
         </div>
      </div>
   </div>

   @if ($m->notes || $m->remarks || $m->failureCode)
      <div class="alert alert-light border mb-3 py-2">
         @if ($m->failureCode)
            <div><strong>Failure Code:</strong>
               {{ $m->failureCode->display_name ?? $m->failureCode->failure_code . ' - ' . $m->failureCode->failure_name }}
            </div>
         @endif
         @if ($m->remarks)
            <div><strong>Remarks:</strong> {{ $m->remarks }}</div>
         @endif
         @if ($m->notes)
            <div><strong>Catatan:</strong> {{ $m->notes }}</div>
         @endif
      </div>
   @endif

   {{-- PHOTO GALLERY --}}
   @php
      $photos = array_filter([
          'Foto Operasi' => $m->photo,
          'Foto Ban B (Swap/Target)' => $m->photo_target,
      ]);
   @endphp

   @if (count($photos) > 0)
      <hr class="my-3">
      <h6 class="fw-bold mb-3"><i class="ri-image-2-line me-1 text-primary"></i> Foto Dokumentasi</h6>
      <div class="row g-3">
         @foreach ($photos as $label => $path)
            <div class="col-md-6">
               <p class="small fw-bold text-muted mb-1">{{ $label }}</p>
               <a href="{{ asset('storage/' . $path) }}" target="_blank">
                  <img src="{{ asset('storage/' . $path) }}" class="img-fluid rounded shadow-sm border"
                     style="max-height: 220px; width: 100%; object-fit: cover;">
               </a>
            </div>
         @endforeach
      </div>
   @else
      <div class="text-center text-muted py-3">
         <i class="ri-camera-off-line" style="font-size: 2rem;"></i>
         <p class="small mt-1 mb-0">Tidak ada foto yang diunggah untuk transaksi ini.</p>
      </div>
   @endif
</div>
