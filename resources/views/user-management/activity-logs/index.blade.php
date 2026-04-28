@extends('layouts.admin')

@section('title', 'Log Aktivitas Sistem')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/animate-css/animate.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
   <style>
      .table-fixed {
         table-layout: fixed;
      }

      .text-truncate-2 {
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
         overflow: hidden;
      }

      .pagination {
         margin-bottom: 0;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row align-items-center mb-4 g-3">
         <div class="col-md-6">
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-history-line me-2 text-primary"></i>{{ isset($pageTitle) ? $pageTitle : 'Log Aktivitas' }}</h4>
            <p class="text-muted mb-0 small">{{ isset($pageDescription) ? $pageDescription : 'Memantau seluruh jejak aktivitas pengguna di dalam sistem.' }}</p>
         </div>
         <div class="col-md-6 text-md-end">
            <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
               <i class="icon-base ri ri-filter-3-line me-1"></i> Advanced Filter
            </button>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'true']) }}" class="btn btn-success shadow-sm ms-2">
               <i class="icon-base ri ri-file-excel-2-line me-1"></i> Export CSV
            </a>
         </div>
      </div>

      <!-- Advanced Filter Collapse -->
      <div class="collapse {{ request()->except('page') ? 'show' : '' }} mb-4" id="filterCollapse">
         <div class="card shadow-sm border-0">
            <div class="card-body">
               <form action="{{ url()->current() }}" method="GET" class="row g-3 align-items-end">
                  <div class="col-md-4">
                     <label class="form-label text-muted small text-uppercase fw-bold">Pencarian Umum</label>
                     <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="icon-base ri ri-search-line"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari aktivitas, user..." value="{{ request('search') }}">
                     </div>
                  </div>
                  <div class="col-md-2">
                     <label class="form-label text-muted small text-uppercase fw-bold">Modul</label>
                     <select name="module" class="form-select select2">
                        <option value="">Semua Modul</option>
                        @foreach($modules as $mod)
                           <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-2">
                     <label class="form-label text-muted small text-uppercase fw-bold">Tipe Aksi</label>
                     <select name="action_type" class="form-select select2">
                        <option value="">Semua Aksi</option>
                        @foreach($actionTypes as $type)
                           <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-3">
                     <label class="form-label text-muted small text-uppercase fw-bold">Rentang Tanggal</label>
                     <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <span class="input-group-text">s/d</span>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                     </div>
                  </div>
                  <div class="col-md-1 d-flex">
                     <button type="submit" class="btn btn-primary w-100 me-2"><i class="icon-base ri ri-search-line"></i></button>
                     <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100"><i class="icon-base ri ri-refresh-line"></i></a>
                  </div>
               </form>
            </div>
         </div>
      </div>

      <!-- Activity Log Table -->
      <div class="card shadow-sm border-0">
         <div class="table-responsive">
            <table class="table table-hover border-top mb-0">
               <thead>
                  <tr>
                     <th width="60">#</th>
                     <th width="180">Waktu</th>
                     <th width="200">Pengguna</th>
                     <th>Aktivitas</th>
                     <th width="120">Tipe</th>
                     <th width="150">Modul</th>
                     <th width="70">Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @php
                     $start = ($logs->currentPage() - 1) * $logs->perPage() + 1;
                  @endphp
                  @forelse ($logs as $log)
                     <tr>
                        <td>{{ $start++ }}</td>
                        <td>
                           <div class="d-flex flex-column">
                              <span class="fw-bold">{{ $log->created_at->format('d M Y') }}</span>
                              <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                           </div>
                        </td>
                        <td>
                           @php
                              $user = $log->user;
                              $name = 'System';
                              if ($user) {
                                  if ($user->karyawan) {
                                      $name =
                                          $user->karyawan->full_name ??
                                          ($user->karyawan->nama ?? ($user->karyawan->employee_name ?? $user->name)) ?:
                                          'User #' . $user->id;
                                  } else {
                                      $name = $user->name ?: 'User #' . $user->id;
                                  }
                              }
                           @endphp
                           <div class="d-flex align-items-center">
                              <div class="avatar avatar-xs me-2">
                                 <span
                                    class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($name, 0, 1)) }}</span>
                              </div>
                              <span class="fw-medium text-truncate" style="max-width: 150px;">{{ $name }}</span>
                           </div>
                        </td>
                        <td>
                           @php
                              $isError =
                                  str_contains(strtolower($log->activity), 'human error') ||
                                  strtolower($log->action_type) === 'error';
                           @endphp
                           <span class="text-truncate-2 {{ $isError ? 'text-danger fw-bold' : '' }}"
                              title="{{ $log->activity }}">
                              @if ($isError)
                                 <i class="ri-error-warning-fill me-1"></i>
                              @endif
                              {{ $log->activity }}
                           </span>
                        </td>
                        <td>
                           @php
                              $badge = 'bg-label-secondary';
                              $type = strtolower($log->action_type ?? '');
                              if (str_contains($type, 'create')) {
                                  $badge = 'bg-label-success';
                              } elseif (str_contains($type, 'update')) {
                                  $badge = 'bg-label-warning';
                              } elseif (
                                  str_contains($type, 'delete') ||
                                  str_contains($type, 'error') ||
                                  strtolower($log->module ?? '') == 'human error'
                              ) {
                                  $badge = 'bg-label-danger';
                              } elseif (str_contains($type, 'login')) {
                                  $badge = 'bg-label-info';
                              }
                           @endphp
                           <span
                              class="badge {{ $badge }} text-capitalize">{{ $log->action_type ?: 'N/A' }}</span>
                        </td>
                        <td>{{ $log->module ?: '-' }}</td>
                        <td>
                           <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect view-detail"
                              data-id="{{ $log->id }}">
                              <i class="icon-base ri ri-eye-line"></i>
                           </button>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="7" class="text-center py-5">
                           <i class="icon-base ri ri-information-line ri-3x text-light mb-3"></i>
                           <p class="text-muted">Tidak ada data aktivitas ditemukan.</p>
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
         @if ($logs->hasPages())
            <div class="card-footer border-top bg-lighter py-3">
               <div class="d-flex justify-content-between align-items-center">
                  <small class="text-muted">Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari
                     {{ number_format($logs->total()) }} data</small>
                  {{ $logs->links() }}
               </div>
            </div>
         @endif
      </div>
   </div>

   <!-- Log Detail Modal -->
   <div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
         <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-label-primary py-3">
               <h5 class="modal-title"><i class="icon-base ri ri-information-line me-2"></i>Detail Aktivitas</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4">
               <div class="row g-3">
                  <div class="col-md-6">
                     <label class="form-label fw-bold text-muted small text-uppercase">Waktu Kejadian</label>
                     <p id="detailTime" class="fw-bold text-dark">-</p>
                  </div>
                  <div class="col-md-6">
                     <label class="form-label fw-bold text-muted small text-uppercase">User / Pelaksana</label>
                     <p id="detailUser" class="fw-bold text-dark">-</p>
                  </div>
                  <div class="col-12">
                     <label class="form-label fw-bold text-muted small text-uppercase">Aktivitas</label>
                     <p id="detailActivity" class="text-dark bg-lighter p-2 rounded">-</p>
                  </div>
                  <div class="col-md-4">
                     <label class="form-label fw-bold text-muted small text-uppercase">Modul</label>
                     <p id="detailModule" class="text-dark">-</p>
                  </div>
                  <div class="col-md-4">
                     <label class="form-label fw-bold text-muted small text-uppercase">Tipe Aksi</label>
                     <p id="detailType" class="text-dark">-</p>
                  </div>
                  <div class="col-md-4">
                     <label class="form-label fw-bold text-muted small text-uppercase">IP Address</label>
                     <p id="detailIP" class="text-dark">-</p>
                  </div>
                  <div class="col-12">
                     <div class="nav-align-top mb-4">
                        <ul class="nav nav-tabs nav-fill" role="tablist">
                           <li class="nav-item">
                              <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                 data-bs-target="#navs-data-after" aria-controls="navs-data-after"
                                 aria-selected="true">Data Sesudah</button>
                           </li>
                           <li class="nav-item">
                              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                 data-bs-target="#navs-data-before" aria-controls="navs-data-before"
                                 aria-selected="false">Data Sebelum</button>
                           </li>
                        </ul>
                        <div class="tab-content border-top-0 px-0 pb-0">
                           <div class="tab-pane fade show active" id="navs-data-after" role="tabpanel">
                              <div id="formattedDataAfter" class="p-0"></div>
                              <pre id="detailDataAfter" class="bg-dark text-light p-3 rounded mb-0 d-none"
                                 style="max-height: 300px; overflow-y: auto;">{}</pre>
                           </div>
                           <div class="tab-pane fade" id="navs-data-before" role="tabpanel">
                              <div id="formattedDataBefore" class="p-0"></div>
                              <pre id="detailDataBefore" class="bg-dark text-light p-3 rounded mb-0 d-none"
                                 style="max-height: 300px; overflow-y: auto;">{}</pre>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer border-top bg-lighter">
               <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         function renderFormattedData(data, containerId, rawId) {
            const container = $(`#${containerId}`);
            const raw = $(`#${rawId}`);
            container.empty();

            if (!data || Object.keys(data).length === 0) {
               container.append('<p class="text-muted italic p-3 text-center">Tidak ada detail data.</p>');
               raw.addClass('d-none');
               return;
            }

            let html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
            html += '<thead class="bg-light"><tr><th width="30%">Field</th><th>Value</th></tr></thead><tbody>';

            const excludeKeys = ['_token', '_method', 'created_at', 'updated_at', 'deleted_at', 'password'];
            const keyMapping = {
               // Common Technical to Human
               'odometer_reading': 'ODOMETER (KM)',
               'hour_meter_reading': 'HOUR METER (HM)',
               'odometer': 'ODOMETER (KM)',
               'hour_meter': 'HOUR METER (HM)',
               'psi_reading': 'TEKANAN PSI',
               'rtd_reading': 'KEDALAMAN RTD (MM)',
               'rtd_1': 'RTD Posisi #1',
               'rtd_2': 'RTD Posisi #2',
               'rtd_3': 'RTD Posisi #3',
               'rtd_4': 'RTD Posisi #4',
               'movement_type': 'TIPE TRANSAKSI',
               'movement_date': 'TANGGAL',
               'examination_date': 'TANGGAL PEMERIKSAAN',
               'install_condition': 'KONDISI PASANG',
               'target_status': 'STATUS AKHIR',
               'is_meter_reset': 'RESET METERAN?',
               'is_replacement': 'GANTI BAN?',
               'remarks': 'CATATAN',
               'notes': 'CATATAN TAMBAHAN',
               'tyreman_1': 'TYREMAN 1',
               'tyreman_2': 'TYREMAN 2',
               'warnings': 'DAFTAR PERINGATAN',
               'status': 'STATUS',
               'price': 'HARGA',
               'initial_tread_depth': 'OTD AWAL (MM)',
               'current_tread_depth': 'RTD SEKARANG (MM)',

               // Technical IDs (Mapping for old logs)
               'tyre_id': 'BAN (ID)',
               'vehicle_id': 'KENDARAAN (ID)',
               'position_id': 'POSISI (ID)',
               'work_location_id': 'LOKASI (ID)',
               'operational_segment_id': 'SEGMEN (ID)',
               'failure_code_id': 'KODE KERUSAKAN (ID)',
               'tyre_brand_id': 'BRAND (ID)',
               'tyre_size_id': 'UKURAN (ID)',
               'tyre_pattern_id': 'PATTERN (ID)',
               'tyre_segment_id': 'SEGMEN (ID)',
               'tyre_location_id': 'LOKASI (ID)',
               'tyre_position_configuration_id': 'KONFIGURASI (ID)',
               'created_by': 'DIBUAT OLEH (ID)',
               'updated_by': 'DIUBAH OLEH (ID)',
               'user_id': 'USER (ID)',
            };

            for (const [key, value] of Object.entries(data)) {
               if (excludeKeys.includes(key)) continue;

               let displayKey = keyMapping[key] || key.replace(/_/g, ' ').toUpperCase();
               let displayValue = value;

               if (Array.isArray(value)) {
                  displayValue = '<ul class="ps-3 mb-0 text-danger">' +
                     value.map(item => `<li class="mb-1"><i class="ri-error-warning-line me-1"></i>${item}</li>`)
                     .join('') +
                     '</ul>';
               } else if (typeof value === 'object' && value !== null) {
                  displayValue = '<pre class="small mb-0">' + JSON.stringify(value, null, 2) + '</pre>';
               } else if (value === null) {
                  displayValue = '<span class="text-muted">null</span>';
               }

               html += `<tr>
                   <td class="fw-bold bg-lighter bg-opacity-10 small">${displayKey}</td>
                   <td class="small">${displayValue}</td>
                </tr>`;
            }
            html += '</tbody></table></div>';
            container.append(html);
            raw.addClass('d-none'); // Keep raw hidden by default
         }

         // View Detail
         $(document).on('click', '.view-detail', function() {
            const id = $(this).data('id');
            const modal = $('#logDetailModal');
            const button = $(this);

            button.prop('disabled', true);

            $.get("{{ url('activity-logs') }}/" + id, function(data) {
               $('#detailTime').text(new Date(data.created_at).toLocaleString('id-ID'));

               let userName = 'System';
               if (data.user) {
                  userName = data.user.name || ('User #' + data.user.id);
                  if (data.user.karyawan) {
                     userName = data.user.karyawan.full_name || data.user.karyawan.nama || data.user
                        .karyawan.employee_name || userName;
                  }
               }
               $('#detailUser').text(userName);
               $('#detailActivity').text(data.activity);
               $('#detailModule').text(data.module || '-');
               $('#detailType').text(data.action_type || '-');
               $('#detailIP').text(data.ip_address || '-');

               let after = {};
               let before = {};
               try {
                  after = typeof data.data_after === 'string' ? JSON.parse(data.data_after) : data
                     .data_after;
                  before = typeof data.data_before === 'string' ? JSON.parse(data.data_before) : data
                     .data_before;
               } catch (e) {
                  console.error("JSON Parse error", e);
               }

               renderFormattedData(after, 'formattedDataAfter', 'detailDataAfter');
               renderFormattedData(before, 'formattedDataBefore', 'detailDataBefore');

               $('#detailDataAfter').text(JSON.stringify(after || {}, null, 2));
               $('#detailDataBefore').text(JSON.stringify(before || {}, null, 2));

               modal.modal('show');
               button.prop('disabled', false);
            }).fail(function() {
               Swal.fire('Error', 'Gagal memuat detail aktivitas', 'error');
               button.prop('disabled', false);
            });
         });
      });
   </script>
@endsection
