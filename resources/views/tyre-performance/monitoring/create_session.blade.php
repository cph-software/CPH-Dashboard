@extends('layouts.admin')

@section('title', 'Start New Monitoring Session')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endsection

@section('content')
   <div class="row">
      <div class="col-12">
         <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
               <h5 class="mb-0">Start New Monitoring & Bulk Installation</h5>
               <a href="{{ route('monitoring.vehicle.show', $vehicle->vehicle_id) }}" class="btn btn-label-secondary">
                  <i class="ri-arrow-left-line me-1"></i> Back
               </a>
            </div>
            <div class="card-body">
               <form action="{{ route('monitoring.sessions.store') }}" method="POST">
                  @csrf
                  <input type="hidden" name="vehicle_id" value="{{ $vehicle->vehicle_id }}">
                  <input type="hidden" name="master_vehicle_id" value="{{ $vehicle->master_vehicle_id }}">

                  <div class="row g-4 mb-5 border-bottom pb-4 bg-light p-3 rounded">
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Installation Date</label>
                        <input type="date" name="install_date" class="form-control" required
                           value="{{ date('Y-m-d') }}">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Odometer at Installation</label>
                        <input type="number" name="odometer_start" class="form-control" required placeholder="KM">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Default Original RTD</label>
                        <input type="number" name="original_rtd" class="form-control" step="0.1" required
                           placeholder="E.g. 14.5">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Common Tyre Size</label>
                        <select name="tyre_size" class="form-select select2" required>
                           <option value="">-- Select Size --</option>
                           @foreach ($sizes as $s)
                              <option value="{{ $s->size }}">{{ $s->size }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>

                  <h6 class="fw-bold mb-3 mt-4"><i class="ri-list-check me-1"></i> Bulk Installation (Current Vehicle
                     Snapshot)</h6>
                  <div class="table-responsive">
                     <table class="table table-bordered align-middle" id="bulk-install-table">
                        <thead class="table-dark">
                           <tr class="text-nowrap text-center">
                              <th width="40">Pos</th>
                              <th width="180">Tyre Information</th>
                              <th width="120">Psi (Rec/Act)</th>
                              <th width="120">Date Assembly</th>
                              <th width="90">RTD 1</th>
                              <th width="90">RTD 2</th>
                              <th width="90">RTD 3</th>
                              <th width="90">RTD 4</th>
                              <th width="100">Avg RTD</th>
                              <th width="80">Worn %</th>
                              <th width="180">Cond / Rec</th>
                              <th width="200">Notes</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($masterPositions as $pos)
                              @php
                                 $tyre = $assignedTyres->get($pos->id) ?? null;
                                 $rowId = $tyre ? $tyre->serial_number : 'pos_' . $pos->id;
                              @endphp
                              <tr class="tyre-row" data-serial="{{ $tyre ? $tyre->serial_number : '' }}">
                                 <td class="text-center fw-bold">{{ $pos->position_code }}</td>
                                 <td>
                                    @if ($tyre)
                                       <div class="d-flex flex-column">
                                          <span class="fw-bold text-primary">{{ $tyre->serial_number }}</span>
                                          <small class="text-muted">{{ $tyre->brand->brand_name ?? '-' }} /
                                             {{ $tyre->size->size ?? '-' }}</small>
                                          <input type="hidden" name="checks[{{ $rowId }}][tyre_id]"
                                             value="{{ $tyre->id }}">
                                          <input type="hidden" name="checks[{{ $rowId }}][serial_number]"
                                             value="{{ $tyre->serial_number }}">
                                          <input type="hidden" name="checks[{{ $rowId }}][position_id]"
                                             value="{{ $pos->id }}">
                                       </div>
                                    @else
                                       <span class="text-muted italic small">No tyre found</span>
                                       <input type="hidden" name="checks[{{ $rowId }}][position_id]"
                                          value="{{ $pos->id }}">
                                    @endif
                                 </td>
                                 <td>
                                    <div class="input-group input-group-sm mb-1">
                                       <input type="number" name="checks[{{ $rowId }}][inf_press_recommended]"
                                          class="form-control" placeholder="Rec">
                                    </div>
                                    <div class="input-group input-group-sm">
                                       <input type="number" name="checks[{{ $rowId }}][inf_press_actual]"
                                          class="form-control" placeholder="Act">
                                    </div>
                                 </td>
                                 <td>
                                    <input type="date" name="checks[{{ $rowId }}][date_assembly]"
                                       class="form-control form-control-sm">
                                 </td>
                                 <td>
                                    <input type="number" name="checks[{{ $rowId }}][rtd_1]"
                                       class="form-control rtd-input" data-idx="1" step="0.1"
                                       value="{{ $tyre->current_tread_depth ?? '' }}">
                                 </td>
                                 <td>
                                    <input type="number" name="checks[{{ $rowId }}][rtd_2]"
                                       class="form-control rtd-input" data-idx="2" step="0.1"
                                       value="{{ $tyre->current_tread_depth ?? '' }}">
                                 </td>
                                 <td>
                                    <input type="number" name="checks[{{ $rowId }}][rtd_3]"
                                       class="form-control rtd-input" data-idx="3" step="0.1"
                                       value="{{ $tyre->current_tread_depth ?? '' }}">
                                 </td>
                                 <td>
                                    <input type="number" name="checks[{{ $rowId }}][rtd_4]"
                                       class="form-control rtd-input" data-idx="4" step="0.1"
                                       value="{{ $tyre->current_tread_depth ?? '' }}">
                                 </td>
                                 <td class="text-center font-monospace fw-bold bg-light">
                                    <span class="avg-rtd text-primary">0.00</span>
                                 </td>
                                 <td class="text-center bg-light">
                                    <span class="worn-pct fw-bold">0%</span>
                                 </td>
                                 <td>
                                    <select name="checks[{{ $rowId }}][condition]" class="form-select mb-1">
                                       <option value="ok">OK</option>
                                       <option value="warning">Warning</option>
                                       <option value="critical">Critical</option>
                                    </select>
                                    <input type="text" name="checks[{{ $rowId }}][recommendation]"
                                       class="form-control" placeholder="Rec...">
                                 </td>
                                 <td>
                                    <textarea name="checks[{{ $rowId }}][notes]" class="form-control" rows="2" placeholder="Notes"></textarea>
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>

                  <div class="mt-4 text-end">
                     <button type="submit" class="btn btn-primary btn-lg">
                        <i class="ri-checkbox-circle-line me-1"></i> Confirm Installation & Start Period
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
