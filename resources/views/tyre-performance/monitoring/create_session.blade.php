@extends('layouts.admin')

@section('title', 'Start New Monitoring Session')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/flatpickr/flatpickr.css') }}" />
   <style>
      /* Table container for horizontal scroll */
      .table-responsive {
         overflow-x: auto;
         -webkit-overflow-scrolling: touch;
         margin-bottom: 1rem;
         border: 1px solid #e5e7eb;
         border-radius: 8px;
         background: #fff;
      }

      #bulk-install-table {
         min-width: 1800px;
         /* Force overflow to trigger scroll */
         table-layout: fixed;
      }

      #bulk-install-table th,
      #bulk-install-table td {
         padding: 12px 10px !important;
         vertical-align: middle;
      }

      .rtd-input {
         height: 55px !important;
         font-size: 1.4rem !important;
         font-weight: 800 !important;
         text-align: center;
         padding: 5px !important;
         width: 100%;
         color: #1a202c;
         background-color: #fff !important;
         border: 2px solid #94a3b8 !important;
         border-radius: 6px;
      }

      .rtd-input:focus {
         border-color: #7367f0 !important;
         background-color: #f8fafc !important;
         box-shadow: 0 0 0 4px rgba(115, 103, 240, 0.15);
         outline: none;
      }

      .avg-rtd {
         font-size: 1.3rem !important;
         font-weight: 800;
         color: #7367f0;
         display: block;
      }

      .worn-pct {
         font-size: 1.1rem;
      }

      #bulk-install-table thead th {
         background-color: #233446 !important;
         color: #fff !important;
         text-transform: uppercase;
         letter-spacing: 0.5px;
         font-size: 0.8rem;
         font-weight: 600;
         text-align: center;
      }

      .select2-container--default .select2-selection--single {
         height: 45px !important;
         display: flex;
         align-items: center;
         font-weight: 600;
      }

      .tyre-info-box {
         background: #f1f5f9;
         padding: 8px 12px;
         border-radius: 6px;
         border-left: 4px solid #7367f0;
         white-space: normal;
      }

      /* Column Width Definitions */
      .col-pos {
         width: 70px;
      }

      .col-info {
         width: 320px;
      }

      .col-psi {
         width: 150px;
      }

      .col-date {
         width: 180px;
      }

      .col-rtd {
         width: 140px;
      }

      .col-avg {
         width: 120px;
      }

      .col-worn {
         width: 100px;
      }

      .col-cond {
         width: 240px;
      }

      .col-notes {
         width: 400px;
      }
   </style>
@endsection

@section('content')
   <div class="row">
      <div class="col-12">
         <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
               <h5 class="mb-0 fw-bold"><i class="ri ri-dashboard-line me-2"></i>Start New Monitoring & Bulk Installation
               </h5>
               <a href="{{ route('monitoring.vehicle.show', $vehicle->vehicle_id) }}" class="btn btn-label-secondary">
                  <i class="ri ri-arrow-left-line me-1"></i> Kembali
               </a>
            </div>
            <div class="card-body">
               <form action="{{ route('monitoring.sessions.store') }}" method="POST">
                  @csrf
                  <input type="hidden" name="vehicle_id" value="{{ $vehicle->vehicle_id }}">
                  <input type="hidden" name="master_vehicle_id" value="{{ $vehicle->master_vehicle_id }}">

                  <div class="row g-4 mb-5 border-bottom pb-4 bg-light p-3 rounded shadow-inner">
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal Pemasangan</label>
                        <input type="date" name="install_date" class="form-control form-control-lg" required
                           value="{{ date('Y-m-d') }}">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Odometer Saat Ini</label>
                        <input type="number" name="odometer_start" class="form-control form-control-lg" required
                           placeholder="KM" value="{{ old('odometer_start') }}">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Hour Meter Saat Ini</label>
                        <input type="number" name="hm_start" class="form-control form-control-lg" placeholder="HM"
                           value="{{ old('hm_start') }}">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Standar RTD Awal (Baru)</label>
                        <input type="number" name="original_rtd" class="form-control form-control-lg" step="0.1"
                           required placeholder="Contoh: 14.5">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Ukuran Ban Umum</label>
                        <select name="tyre_size" class="form-select select2" required>
                           <option value="">-- Pilih Ukuran --</option>
                           @foreach ($sizes as $s)
                              <option value="{{ $s->size }}">{{ $s->size }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>

                  <div class="d-flex justify-content-between align-items-end mb-3 mt-4">
                     <h6 class="fw-bold mb-0 text-primary"><i class="ri ri-list-check-2 me-1"></i> Data Ban Terpasang (Bulk
                        Snapshot)</h6>
                     <small class="text-muted"><i class="ri ri-information-line me-1"></i>Gunakan scroll horizontal untuk
                        melihat semua kolom</small>
                  </div>

                  <div class="table-responsive">
                     <table class="table table-bordered align-middle" id="bulk-install-table">
                        <thead>
                           <tr class="text-nowrap">
                              <th class="col-pos text-center">Pos</th>
                              <th class="col-info">Informasi Ban</th>
                              <th class="col-psi">
                                 Psi (Rec/Act)<br>
                                 <button type="button" class="btn btn-xxs btn-primary mt-1" id="btn-apply-all-psi"
                                    title="Copy Rec to Act for all rows">
                                    <i class="ri ri-arrow-down-line"></i> All Act
                                 </button>
                              </th>
                              <th class="col-date">
                                 Tgl Assembly<br>
                                 <button type="button" class="btn btn-xxs btn-info mt-1" id="btn-today-assembly"
                                    title="Set all to today">
                                    Today
                                 </button>
                              </th>
                              <th class="col-rtd">RTD 1</th>
                              <th class="col-rtd">RTD 2</th>
                              <th class="col-rtd">RTD 3</th>
                              <th class="col-rtd">RTD 4</th>
                              <th class="col-avg">Avg RTD</th>
                              <th class="col-worn">Worn %</th>
                              <th class="col-cond">Kondisi / Rekomendasi</th>
                              <th class="col-notes">Catatan (Notes)</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($masterPositions as $pos)
                              @php
                                 $tyre = $assignedTyres->get($pos->id) ?? null;
                                 $rowId = $tyre ? $tyre->serial_number : 'pos_' . $pos->id;
                              @endphp
                              <tr class="tyre-row" data-serial="{{ $tyre ? $tyre->serial_number : '' }}">
                                 <td class="text-center fw-bold bg-light" style="font-size: 1.2rem;">
                                    {{ $pos->position_code }}</td>
                                 <td>
                                    @if ($tyre)
                                       <div class="d-flex flex-column tyre-info-box">
                                          <span class="fw-bold text-primary"
                                             style="font-size: 1rem;">{{ $tyre->serial_number }}</span>
                                          <small
                                             class="text-dark fw-semibold">{{ $tyre->brand->brand_name ?? '-' }}</small>
                                          <small class="text-muted">{{ $tyre->size->size ?? '-' }}</small>
                                          <input type="hidden" name="checks[{{ $rowId }}][tyre_id]"
                                             value="{{ $tyre->id }}">
                                          <input type="hidden" name="checks[{{ $rowId }}][serial_number]"
                                             value="{{ $tyre->serial_number }}">
                                          <input type="hidden" name="checks[{{ $rowId }}][position_id]"
                                             value="{{ $pos->id }}">
                                       </div>
                                    @else
                                       <div class="d-flex flex-column gap-1">
                                          <select name="checks[{{ $rowId }}][tyre_id]"
                                             class="form-select select2 select2-tyre" data-row="{{ $rowId }}">
                                             <option value="">-- Pilih Ban Stok --</option>
                                             @foreach ($availableTyres as $at)
                                                <option value="{{ $at->id }}" data-sn="{{ $at->serial_number }}"
                                                   data-brand="{{ $at->brand->brand_name ?? '-' }}"
                                                   data-size="{{ $at->size->size ?? '-' }}"
                                                   data-pattern="{{ $at->pattern->name ?? '-' }}"
                                                   data-rtd="{{ $at->current_tread_depth ?? '' }}">
                                                   {{ $at->serial_number }}
                                                </option>
                                             @endforeach
                                          </select>
                                          <div class="tyre-detail-info-{{ $rowId }} mt-1 d-none">
                                             <div class="p-2 border rounded bg-white shadow-sm"
                                                style="font-size: 0.75rem;">
                                                <div class="text-primary fw-bold"><i
                                                      class="ri ri-settings-line me-1"></i>Detail Ban:</div>
                                                <div class="row g-0">
                                                   <div class="col-12"><span class="text-muted">Brand:</span> <span
                                                         class="tyre-brand fw-semibold"></span></div>
                                                   <div class="col-12"><span class="text-muted">Specs:</span> <span
                                                         class="tyre-specs fw-semibold"></span></div>
                                                </div>
                                             </div>
                                          </div>
                                          <input type="hidden" name="checks[{{ $rowId }}][serial_number]"
                                             class="row-sn-{{ $rowId }}">
                                          <input type="hidden" name="checks[{{ $rowId }}][position_id]"
                                             value="{{ $pos->id }}">
                                       </div>
                                    @endif
                                 </td>
                                 <td>
                                    <div class="input-group mb-1">
                                       <span class="input-group-text p-1" style="font-size: 0.75rem">Rec</span>
                                       <input type="number" name="checks[{{ $rowId }}][inf_press_recommended]"
                                          class="form-control" placeholder="0">
                                    </div>
                                    <div class="input-group">
                                       <span class="input-group-text p-1 text-primary fw-bold"
                                          style="font-size: 0.75rem">Act</span>
                                       <input type="number" name="checks[{{ $rowId }}][inf_press_actual]"
                                          class="form-control border-primary" placeholder="0">
                                    </div>
                                 </td>
                                 <td>
                                    <input type="date" name="checks[{{ $rowId }}][date_assembly]"
                                       class="form-control">
                                 </td>
                                 <td><input type="number" name="checks[{{ $rowId }}][rtd_1]"
                                       class="form-control rtd-input" data-idx="1" step="0.1"
                                       value="{{ $tyre->current_tread_depth ?? '' }}"></td>
                                 <td><input type="number" name="checks[{{ $rowId }}][rtd_2]"
                                       class="form-control rtd-input" data-idx="2" step="0.1"
                                       value="{{ $tyre->current_tread_depth ?? '' }}"></td>
                                 <td><input type="number" name="checks[{{ $rowId }}][rtd_3]"
                                       class="form-control rtd-input" data-idx="3" step="0.1"
                                       value="{{ $tyre->current_tread_depth ?? '' }}"></td>
                                 <td><input type="number" name="checks[{{ $rowId }}][rtd_4]"
                                       class="form-control rtd-input" data-idx="4" step="0.1"
                                       value="{{ $tyre->current_tread_depth ?? '' }}"></td>
                                 <td class="text-center bg-light">
                                    <span class="avg-rtd">0.00</span>
                                    <small class="text-muted" style="font-size: 0.7rem">mm</small>
                                 </td>
                                 <td class="text-center">
                                    <span class="worn-pct fw-bold">0%</span>
                                 </td>
                                 <td>
                                    <select name="checks[{{ $rowId }}][condition]"
                                       class="form-select mb-2 fw-bold">
                                       <option value="ok" class="text-success">OK</option>
                                       <option value="warning" class="text-warning">Warning</option>
                                       <option value="critical" class="text-danger">Critical</option>
                                    </select>
                                    <input type="text" name="checks[{{ $rowId }}][recommendation]"
                                       class="form-control" placeholder="Tulis saran/rekomendasi...">
                                 </td>
                                 <td>
                                    <textarea name="checks[{{ $rowId }}][notes]" class="form-control" rows="3"
                                       placeholder="Tambahkan catatan khusus ban ini..."></textarea>
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>

                  <div class="mt-4 text-end">
                     <button type="submit" class="btn btn-primary btn-lg">
                        <i class="ri ri-checkbox-circle-line me-1"></i> Confirm Installation & Start Period
                     </button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(function() {
         $('.select2').select2();

         const syncTyreSelections = () => {
            let selectedTyres = [];
            $('.select2-tyre').each(function() {
               let val = $(this).val();
               if (val) selectedTyres.push(val);
            });

            $('.select2-tyre').each(function() {
               let currentSelect = $(this);
               let currentVal = currentSelect.val();

               currentSelect.find('option').each(function() {
                  let optionVal = $(this).val();
                  if (!optionVal) return;

                  if (selectedTyres.includes(optionVal) && optionVal !== currentVal) {
                     $(this).prop('disabled', true);
                  } else {
                     $(this).prop('disabled', false);
                  }
               });
            });
         };

         $(document).on('change', '.select2-tyre', function() {
            const rowId = $(this).data('row');
            const selected = $(this).find(':selected');
            const sn = selected.data('sn');
            const brand = selected.data('brand');
            const size = selected.data('size');
            const pattern = selected.data('pattern');
            const rtd = selected.data('rtd');

            $('.row-sn-' + rowId).val(sn);

            const detailBox = $('.tyre-detail-info-' + rowId);
            const row = $(this).closest('tr');

            if ($(this).val()) {
               detailBox.find('.tyre-brand').text(brand);
               detailBox.find('.tyre-specs').text(size + ' / ' + pattern);
               detailBox.removeClass('d-none');
               row.addClass('table-info');

               // Auto-fill RTD if it exists
               if (rtd) {
                  row.find('.rtd-input').val(rtd).trigger('input');
               }
            } else {
               detailBox.addClass('d-none');
               row.removeClass('table-info');
            }

            // Sync other dropdowns to prevent duplicates
            syncTyreSelections();
         });

         // RTD Auto-fill: Typing in RTD 1 copies to 2,3,4 if they are empty
         $(document).on('input', 'input[name$="[rtd_1]"]', function() {
            const row = $(this).closest('tr');
            const val = $(this).val();

            row.find('.rtd-input').each(function() {
               const idx = $(this).data('idx');
               if (idx > 1) {
                  const currentVal = $(this).val();
                  if (!currentVal || currentVal == "0") {
                     $(this).val(val);
                  }
               }
            });
         });

         // PSI Apply All
         $(document).on('click', '#btn-apply-all-psi', function() {
            $('.tyre-row').each(function() {
               const rec = $(this).find('input[name$="[inf_press_recommended]"]').val();
               if (rec) {
                  $(this).find('input[name$="[inf_press_actual]"]').val(rec);
               }
            });
         });

         // Assembly Date Apply All
         $(document).on('click', '#btn-today-assembly', function() {
            const today = new Date().toISOString().split('T')[0];
            $('input[name$="[date_assembly]"]').val(today);
         });

         const calculateRow = (row) => {
            const originalRtd = parseFloat($('input[name="original_rtd"]').val()) || 0;
            let sum = 0;
            let count = 0;

            row.find('.rtd-input').each(function() {
               const val = parseFloat($(this).val());
               if (!isNaN(val)) {
                  sum += val;
                  count++;
               }
            });

            const avg = count > 0 ? sum / count : 0;
            row.find('.avg-rtd').text(avg.toFixed(2));

            if (originalRtd > 0) {
               const loss = originalRtd - avg;
               const worn = (loss / originalRtd) * 100;
               const wornSpan = row.find('.worn-pct');
               wornSpan.text(Math.round(worn) + '%');

               // Color coding
               wornSpan.removeClass('text-success text-warning text-danger');
               if (worn > 80) wornSpan.addClass('text-danger');
               else if (worn > 50) wornSpan.addClass('text-warning');
               else wornSpan.addClass('text-success');
            }
         };

         // Event listeners
         $(document).on('input', '.rtd-input', function() {
            calculateRow($(this).closest('tr'));
         });

         $(document).on('input', 'input[name="original_rtd"]', function() {
            $('.tyre-row').each(function() {
               calculateRow($(this));
            });
         });

         // Initial calculation
         $('.tyre-row').each(function() {
            calculateRow($(this));
         });
      });
   </script>
@endsection
