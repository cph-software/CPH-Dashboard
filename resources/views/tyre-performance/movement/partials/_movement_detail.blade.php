@php
   $m = $movement;
   $tyre = $m->tyre;
   $vehicle = $m->vehicle;
   $position = $m->position;

   $typeBadge = match ($m->movement_type) {
       'Installation' => 'success',
       'Removal' => 'danger',
       'Rotation' => 'warning',
       default => 'secondary',
   };
   $typeLabel = match ($m->movement_type) {
       'Installation' => 'Pemasangan',
       'Removal' => 'Pelepasan',
       'Rotation' => 'Rotasi',
       default => $m->movement_type,
   };
@endphp

<div class="detail-movement-wrap">
   {{-- HEADER --}}
   <div class="d-flex align-items-center mb-3 gap-2">
      <span class="badge bg-{{ $typeBadge }} fs-6">{{ $typeLabel }}</span>
      <span class="text-muted small">ID #{{ $m->id }} &bull;
         {{ \Carbon\Carbon::parse($m->movement_date)->format('d M Y') }}</span>
   </div>

   {{-- INFO GRID --}}
   <div class="row g-2 mb-4">
      <div class="col-md-6">
         <table class="table table-sm table-borderless table-hover align-middle">
            <tr>
               <td class="fw-bold text-muted" style="width:40%">SN Ban</td>
               <td>{{ $tyre->serial_number ?? '-' }}</td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">Brand</td>
               <td>{{ $tyre->brand->brand_name ?? '-' }}</td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">Ukuran</td>
               <td>{{ $tyre->size->size ?? '-' }}</td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">Pattern</td>
               <td>{{ $tyre->pattern->name ?? '-' }}</td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">Unit</td>
               <td>{{ $vehicle->kode_kendaraan ?? '-' }}</td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">Posisi</td>
               <td>{{ $position ? $position->position_code . ' — ' . $position->position_name : '-' }}</td>
            </tr>
         </table>
      </div>
      <div class="col-md-6">
         <table class="table table-sm table-borderless table-hover align-middle">
            <tr>
               <td class="fw-bold text-muted" style="width:40%">Odometer</td>
               <td>{{ $m->odometer_reading ? number_format($m->odometer_reading, 0) . ' km' : '-' }}</td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">Hour Meter</td>
               <td>{{ $m->hour_meter_reading ? number_format($m->hour_meter_reading, 0) . ' Hm' : '-' }}</td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">PSI</td>
               <td>{{ $m->psi_reading ?? '-' }}</td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">RTD (Avg)</td>
               <td>{{ $m->rtd_reading ? $m->rtd_reading . ' mm' : '-' }}</td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">RTD 1-4</td>
               <td>
                  @php $rtdParts = array_filter([$m->rtd_1, $m->rtd_2, $m->rtd_3, $m->rtd_4], fn($v) => $v !== null); @endphp
                  {{ count($rtdParts) > 0 ? implode(' / ', $rtdParts) . ' mm' : '-' }}
               </td>
            </tr>
            <tr>
               <td class="fw-bold text-muted">Waktu</td>
               <td>{{ $m->start_time ?? '-' }} – {{ $m->end_time ?? '-' }}</td>
            </tr>
         </table>
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
