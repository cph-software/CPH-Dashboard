@forelse ($configuration->details as $pos)
   @php
      $tyre = $tyres[$pos->id] ?? null;
      $index = $loop->index;
   @endphp
   <tr class="tyre-row {{ !$tyre ? 'empty-pos' : '' }}">
      <!-- POS & TYRE INFO -->
      <td class="pos-column text-center bg-light fw-bold align-middle">
         <span class="d-none d-md-inline">{{ $pos->position_code }}</span>
         <div class="d-md-none p-2 bg-primary text-white rounded-circle shadow-sm mx-auto"
            style="width: 40px; height: 40px; line-height: 25px;">
            {{ $pos->position_code }}
         </div>
      </td>

      <td class="info-column align-middle">
         @if ($tyre)
            <div class="tyre-info-box">
               <input type="hidden" name="details[{{ $index }}][position_id]" value="{{ $pos->id }}">
               <input type="hidden" name="details[{{ $index }}][tyre_id]" value="{{ $tyre->id }}">
               <div class="fw-bold text-primary mb-1 serial-number">{{ $tyre->serial_number }}</div>
               <div class="small text-muted text-uppercase detail-text">
                  {{ $tyre->brand->brand_name ?? '-' }} | {{ $tyre->pattern->name ?? '-' }} <br>
                  <span class="badge bg-label-secondary mt-1">{{ $tyre->size->size ?? '-' }}</span>
               </div>
            </div>
         @else
            <div class="py-2 text-center text-md-start">
               <span class="badge bg-label-danger">POSISI KOSONG</span>
               <p class="small text-muted mb-0 mt-1 d-none d-md-block">Tidak ada ban terpasang</p>
            </div>
         @endif
      </td>

      <!-- MEASUREMENTS GRID -->
      <td class="measure-column p-0">
         <div class="row g-0 h-100">
            <!-- PSI Section -->
            <div class="col-12 col-md-2 p-2 border-end-md measurement-group psi-group">
               <label class="d-md-none small fw-bold text-muted d-block mb-1">PSI</label>
               <div class="input-group input-group-sm">
                  <span class="input-group-text d-md-none"><i class="ri-dashboard-3-line"></i></span>
                  <input type="number" step="0.1" name="details[{{ $index }}][psi]"
                     class="form-control text-center fw-bold" placeholder="PSI"
                     @if (!$tyre) disabled @endif>
               </div>
            </div>

            <!-- RTD Sections Grid -->
            <div class="col-12 col-md-7 p-2 measurement-group rtd-group">
               <label class="d-md-none small fw-bold text-muted d-block mb-1">RTD Measurement (1-4)</label>
               <div class="row g-1">
                  <div class="col-3">
                     <input type="number" step="0.1" name="details[{{ $index }}][rtd_1]"
                        class="form-control form-control-sm text-center rtd-input" placeholder="R1"
                        @if (!$tyre) disabled @endif>
                  </div>
                  <div class="col-3">
                     <input type="number" step="0.1" name="details[{{ $index }}][rtd_2]"
                        class="form-control form-control-sm text-center rtd-input" placeholder="R2"
                        @if (!$tyre) disabled @endif>
                  </div>
                  <div class="col-3">
                     <input type="number" step="0.1" name="details[{{ $index }}][rtd_3]"
                        class="form-control form-control-sm text-center rtd-input" placeholder="R3"
                        @if (!$tyre) disabled @endif>
                  </div>
                  <div class="col-3">
                     <input type="number" step="0.1" name="details[{{ $index }}][rtd_4]"
                        class="form-control form-control-sm text-center rtd-input" placeholder="R4"
                        @if (!$tyre) disabled @endif>
                  </div>
               </div>
            </div>

            <!-- Remarks Section -->
            <div class="col-12 col-md-3 p-2 bg-light-remarks">
               <label class="d-md-none small fw-bold text-muted d-block mb-1">Remarks & Photo</label>
               <div class="input-group input-group-sm">
                  <input type="text" name="details[{{ $index }}][remarks]"
                     class="form-control form-control-sm" placeholder="Catatan..."
                     @if (!$tyre) disabled @endif>
                  <label class="input-group-text cursor-pointer" for="photo_{{ $index }}" title="Upload Foto"
                     @if (!$tyre) disabled @endif>
                     <i class="ri-camera-line"></i>
                  </label>
                  <input type="file" name="details[{{ $index }}][photo]" id="photo_{{ $index }}"
                     class="d-none photo-input" accept="image/*" @if (!$tyre) disabled @endif>
               </div>
               <div class="small text-muted mt-1 d-none file-name-label" id="label_{{ $index }}"></div>
            </div>
         </div>
      </td>
   </tr>
@empty
   <tr>
      <td colspan="3" class="text-center py-5 text-warning">
         <i class="ri-error-warning-line ri-2x mb-2"></i>
         <p>Konfigurasi axle untuk unit ini tidak ditemukan.</p>
      </td>
   </tr>
@endforelse
