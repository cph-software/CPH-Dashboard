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
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-history-line me-2 text-primary"></i>Log Aktivitas</h4>
            <p class="text-muted mb-0 small">Memantau seluruh jejak aktivitas pengguna di dalam sistem.</p>
         </div>
         <div class="col-md-6">
            <form action="{{ url()->current() }}" method="GET">
               <div class="input-group input-group-merge shadow-sm">
                  <span class="input-group-text border-0"><i class="icon-base ri ri-search-line"></i></span>
                  <input type="text" name="search" class="form-control border-0"
                     placeholder="Cari aktivitas, modul, atau user..." value="{{ request('search') }}">
                  <button type="submit" class="btn btn-primary">Cari</button>
                  @if (request('search'))
                     <a href="{{ url()->current() }}" class="btn btn-outline-secondary"><i
                           class="icon-base ri ri-refresh-line"></i></a>
                  @endif
               </div>
            </form>
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
                              $name = $user
                                  ? $user->karyawan->nama ?? ($user->karyawan->employee_name ?? $user->name)
                                  : 'System';
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
                           <span class="text-truncate-2" title="{{ $log->activity }}">
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
                              } elseif (str_contains($type, 'delete')) {
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
                              <pre id="detailDataAfter" class="bg-dark text-light p-3 rounded mb-0" style="max-height: 300px; overflow-y: auto;">{}</pre>
                           </div>
                           <div class="tab-pane fade" id="navs-data-before" role="tabpanel">
                              <pre id="detailDataBefore" class="bg-dark text-light p-3 rounded mb-0" style="max-height: 300px; overflow-y: auto;">{}</pre>
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
         // View Detail
         $(document).on('click', '.view-detail', function() {
            const id = $(this).data('id');
            const modal = $('#logDetailModal');
            const button = $(this);

            button.prop('disabled', true);

            $.get("{{ url('master_data_tyre/activity-logs') }}/" + id, function(data) {
               $('#detailTime').text(new Date(data.created_at).toLocaleString('id-ID'));

               let userName = 'System';
               if (data.user) {
                  userName = data.user.name;
                  if (data.user.karyawan) {
                     userName = data.user.karyawan.nama || data.user.karyawan.employee_name || userName;
                  }
               }
               $('#detailUser').text(userName);
               $('#detailActivity').text(data.activity);
               $('#detailModule').text(data.module || '-');
               $('#detailType').text(data.action_type || '-');
               $('#detailIP').text(data.ip_address || '-');

               try {
                  const after = typeof data.data_after === 'string' ? JSON.parse(data.data_after) : data
                     .data_after;
                  const before = typeof data.data_before === 'string' ? JSON.parse(data.data_before) :
                     data.data_before;
                  $('#detailDataAfter').text(JSON.stringify(after || {}, null, 2));
                  $('#detailDataBefore').text(JSON.stringify(before || {}, null, 2));
               } catch (e) {
                  $('#detailDataAfter').text(data.data_after || '{}');
                  $('#detailDataBefore').text(data.data_before || '{}');
               }

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
