@extends('layouts.admin')

@section('title', 'Add Periodic Check')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <style>
      .table-responsive {
         overflow-x: auto;
         -webkit-overflow-scrolling: touch;
         margin-bottom: 1rem;
         border: 1px solid #e5e7eb;
         border-radius: 8px;
         background: #fff;
      }

      #check-table {
         min-width: 1400px;
         table-layout: fixed;
      }

      #check-table th,
      #check-table td {
         padding: 12px 10px !important;
         vertical-align: middle;
      }

      .rtd-input {
         height: 45px !important;
         font-size: 1.1rem !important;
         font-weight: 700 !important;
         text-align: center;
         color: #1a202c;
         border: 2px solid #cbd5e1 !important;
         border-radius: 6px;
      }

      .rtd-input:focus {
         border-color: #7367f0 !important;
         outline: none;
      }

      #check-table thead th {
         background-color: #233446 !important;
         color: #fff !important;
         text-transform: uppercase;
         font-size: 0.75rem;
         text-align: center;
      }

      .tyre-info-box {
         background: #f8fafc;
         padding: 8px 12px;
         border-radius: 6px;
         border-left: 4px solid #00cfe8;
      }
   </style>
@endsection

@section('content')
   <div class="row">
      <div class="col-12">
         <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
               <h5 class="mb-0 fw-bold"><i class="ri ri-search-eye-line me-2"></i>Periodic Check #{{ $checkCount + 1 }}</h5>
               <a href="{{ route('monitoring.vehicle.show', $vehicle->vehicle_id) }}" class="btn btn-label-secondary">
                  <i class="ri ri-arrow-left-line me-1"></i> Kembali
               </a>
            </div>
            <div class="card-body">
               <form action="{{ route('monitoring.check.store') }}" method="POST">
                  @csrf
                  <input type="hidden" name="session_id" value="{{ $session->session_id }}">
                  <input type="hidden" name="check_number" value="{{ $checkCount + 1 }}">

                  <div class="row g-4 mb-5 border-bottom pb-4 bg-light p-3 rounded">
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal Pengecekan</label>
                        <input type="date" name="check_date" class="form-control form-control-lg" required
                           value="{{ date('Y-m-d') }}">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Odometer Saat Ini (KM)</label>
                        <input type="number" name="odometer" class="form-control form-control-lg" required
                           placeholder="KM">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Operation Mileage (KM)</label>
                        <input type="number" name="operation_mileage" class="form-control form-control-lg"
                           placeholder="KM">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Load Payload (Ton)</label>
                        <input type="text" name="load" class="form-control form-control-lg"
                           value="{{ $vehicle->load_capacity }}">
                     </div>
                  </div>

                  <div class="alert alert-info py-2 mb-3">
                     <i class="ri ri-information-line me-1"></i> Masukkan data RTD terbaru untuk semua ban yang terpasang
                     pada kendaraan ini.
                  </div>

                  <div class="table-responsive">
                     <table class="table table-bordered align-middle" id="check-table">
                        <thead>
                           <tr>
                              <th style="width: 80px;">Pos</th>
                              <th style="width: 280px;">Informasi Ban</th>
                              <th style="width: 150px;">Psi (Rec/Act)</th>
                              <th style="width: 100px;">RTD 1</th>
                              <th style="width: 100px;">RTD 2</th>
                              <th style="width: 100px;">RTD 3</th>
                              <th style="width: 100px;">RTD 4</th>
                              <th style="width: 180px;">Kondisi</th>
                              <th>Rekomendasi & Catatan</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($installedTyres as $idx => $tyre)
                              @php
                                 $posDetail = $masterPositions->firstWhere('id', $tyre->current_position_id);
                                 $serial = $tyre->serial_number;
                              @endphp
                              <tr>
                                 <td class="text-center fw-bold bg-light" style="font-size: 1.1rem;">
                                    {{ $posDetail->position_code ?? '-' }}
                                    <input type="hidden" name="checks[{{ $serial }}][position]"
                                       value="{{ $posDetail->position_code ?? '-' }}">
                                    <input type="hidden" name="checks[{{ $serial }}][position_id]"
                                       value="{{ $tyre->current_position_id }}">
                                 </td>
                                 <td>
                                    <div class="tyre-info-box">
                                       <div class="fw-bold text-primary">{{ $tyre->serial_number }}</div>
                                       <small class="text-muted d-block">{{ $tyre->brand->brand_name ?? '-' }} ·
                                          {{ $tyre->size->size ?? '-' }}</small>
                                       <input type="hidden" name="checks[{{ $serial }}][serial_number]"
                                          value="{{ $tyre->serial_number }}">
                                       <input type="hidden" name="checks[{{ $serial }}][tyre_id]"
                                          value="{{ $tyre->id }}">
                                    </div>
                                 </td>
                                 <td>
                                    <div class="input-group input-group-sm mb-1">
                                       <span class="input-group-text">Rec</span>
                                       <input type="number" name="checks[{{ $serial }}][psi_recommended]"
                                          class="form-control" value="{{ $session->retase }}">
                                    </div>
                                    <div class="input-group input-group-sm">
                                       <span class="input-group-text text-primary fw-bold">Act</span>
                                       <input type="number" name="checks[{{ $serial }}][psi_actual]"
                                          class="form-control border-primary">
                                    </div>
                                 </td>
                                 <td><input type="number" name="checks[{{ $serial }}][rtd_1]"
                                       class="form-control rtd-input" step="0.1"
                                       value="{{ $tyre->current_tread_depth }}"></td>
                                 <td><input type="number" name="checks[{{ $serial }}][rtd_2]"
                                       class="form-control rtd-input" step="0.1"
                                       value="{{ $tyre->current_tread_depth }}"></td>
                                 <td><input type="number" name="checks[{{ $serial }}][rtd_3]"
                                       class="form-control rtd-input" step="0.1"
                                       value="{{ $tyre->current_tread_depth }}"></td>
                                 <td><input type="number" name="checks[{{ $serial }}][rtd_4]"
                                       class="form-control rtd-input" step="0.1"
                                       value="{{ $tyre->current_tread_depth }}"></td>
                                 <td>
                                    <select name="checks[{{ $serial }}][condition]" class="form-select fw-bold">
                                       <option value="ok" class="text-success">OK</option>
                                       <option value="warning" class="text-warning">Warning</option>
                                       <option value="critical" class="text-danger">Critical</option>
                                    </select>
                                 </td>
                                 <td>
                                    <input type="text" name="checks[{{ $serial }}][recommendation]"
                                       class="form-control form-control-sm mb-1" placeholder="Rekomendasi...">
                                    <input type="text" name="checks[{{ $serial }}][notes]"
                                       class="form-control form-control-sm" placeholder="Catatan...">
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>

                  <div class="mt-4 text-end">
                     <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="ri ri-save-line me-1"></i> Submit Periodic Check
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
