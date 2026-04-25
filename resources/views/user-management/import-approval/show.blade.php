@extends('layouts.admin')

@section('title', 'Review Detail Import')

@php
    $readyItems = collect();
    $warningItems = collect();
    $attentionItems = collect();
    $headers = [];

    if ($batch->items->count() > 0) {
        $headers = array_diff(array_keys($batch->items->first()->data), ['_validation']);
        
        foreach ($batch->items as $item) {
            $validation = $item->data['_validation'] ?? null;
            $is_valid = $validation['is_valid'] ?? ($item->status === 'Success' || $item->status === 'Pending');
            $has_warnings = !empty($validation['warnings'] ?? []);
            if ($item->status === 'Failed') $is_valid = false;
            
            if (!$is_valid) {
                $attentionItems->push($item);
            } else if ($has_warnings) {
                $warningItems->push($item);
            } else {
                $readyItems->push($item);
            }
        }
    }
@endphp

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row align-items-center mb-4 g-3">
         <div class="col-md-8">
            <h4 class="fw-bold mb-1">
               <a href="{{ route('import-approval.index') }}" class="text-muted fw-light"><i
                     class="ri-arrow-left-line"></i></a>
               Detail Batch #{{ $batch->id }}
            </h4>
            <p class="text-muted mb-0 small">Review data mentah sebelum memproses ke database utama.</p>
         </div>
         <div class="col-md-4 text-md-end">
            @if (($batch->status === 'Pending' || $readyItems->count() > 0 || $warningItems->count() > 0) && auth()->user()->hasPermission('Import Approval', 'update'))
               <form action="{{ route('import-approval.approve', $batch->id) }}" method="POST" class="d-inline" id="approveForm">
                  @csrf
                  <button type="submit" class="btn btn-success me-2" id="btnApprove"
                     onclick="return confirm('Setujui dan proses data yang siap?')">
                     <i class="ri-check-line me-1"></i> Approve & Process
                  </button>
               </form>
               <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                  <i class="ri-close-line me-1"></i> Reject
               </button>
            @endif
         </div>
      </div>

      {{-- Batch Info Card --}}
      <div class="row mb-4">
         <div class="col-md-12">
            <div class="card">
               <div class="card-body">
                  <div class="row">
                     <div class="col-md-3 border-end">
                        <label class="small text-muted text-uppercase d-block mb-1">Modul</label>
                        <span class="fw-bold">{{ $batch->module }}</span>
                     </div>
                     <div class="col-md-3 border-end">
                        <label class="small text-muted text-uppercase d-block mb-1">Diupload Oleh</label>
                        <span class="fw-medium">{{ $batch->user->name ?? 'System' }}</span>
                        <div class="small text-primary fw-bold">
                           {{ $batch->user->tyreCompany->company_name ?? 'Global View' }}</div>
                     </div>
                     <div class="col-md-3 border-end">
                        <label class="small text-muted text-uppercase d-block mb-1">Total Baris</label>
                        <span class="fw-bold">{{ number_format($batch->total_rows) }} Item</span>
                     </div>
                     <div class="col-md-3">
                        <label class="small text-muted text-uppercase d-block mb-1">Status</label>
                        @php
                           $statusClass =
                               [
                                   'Pending' => 'bg-label-warning',
                                   'Approved' => 'bg-label-success',
                                   'Rejected' => 'bg-label-danger',
                                   'Processing' => 'bg-label-info',
                               ][$batch->status] ?? 'bg-label-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $batch->status }}</span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      {{-- Data Table Section --}}

      {{-- Summary Cards --}}
      <div class="row mb-4 g-3">
         <div class="col-md-4">
            <div class="card border-start border-success border-3 h-100">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center">
                     <div class="avatar avatar-sm me-3 bg-label-success rounded">
                        <i class="ri-check-double-line ri-lg"></i>
                     </div>
                     <div>
                        <h4 class="mb-0 fw-bold text-success">{{ $readyItems->count() }}</h4>
                        <small class="text-muted">Siap Disetujui</small>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-4">
            <div class="card border-start border-warning border-3 h-100">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center">
                     <div class="avatar avatar-sm me-3 bg-label-warning rounded">
                        <i class="ri-alert-line ri-lg"></i>
                     </div>
                     <div>
                        <h4 class="mb-0 fw-bold text-warning">{{ $warningItems->count() }}</h4>
                        <small class="text-muted">Siap + Ada Peringatan</small>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-4">
            <div class="card border-start border-danger border-3 h-100">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center">
                     <div class="avatar avatar-sm me-3 bg-label-danger rounded">
                        <i class="ri-close-circle-line ri-lg"></i>
                     </div>
                     <div>
                        <h4 class="mb-0 fw-bold text-danger">{{ $attentionItems->count() }}</h4>
                        <small class="text-muted">Perlu Perbaikan</small>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="card shadow-sm border-0">
         <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs nav-fill mb-0" role="tablist">
               <li class="nav-item">
                  <button type="button" class="nav-link active fw-bold text-success py-3" role="tab" data-bs-toggle="tab" data-bs-target="#navs-ready">
                     <i class="ri-check-double-line me-1"></i> Siap Disetujui
                     <span class="badge bg-success ms-1">{{ $readyItems->count() }}</span>
                  </button>
               </li>
               <li class="nav-item">
                  <button type="button" class="nav-link fw-bold text-warning py-3" role="tab" data-bs-toggle="tab" data-bs-target="#navs-warning">
                     <i class="ri-alert-line me-1"></i> Ada Peringatan
                     <span class="badge bg-warning ms-1">{{ $warningItems->count() }}</span>
                  </button>
               </li>
               <li class="nav-item">
                  <button type="button" class="nav-link fw-bold text-danger py-3" role="tab" data-bs-toggle="tab" data-bs-target="#navs-attention">
                     <i class="ri-error-warning-line me-1"></i> Perlu Perbaikan
                     <span class="badge bg-danger ms-1">{{ $attentionItems->count() }}</span>
                  </button>
               </li>
            </ul>
         </div>
         
         <div class="tab-content p-0">
            {{-- TAB 1: READY --}}
            <div class="tab-pane fade show active" id="navs-ready" role="tabpanel">
               <div class="table-responsive text-nowrap" style="max-height: 500px">
                  <table class="table table-hover table-striped mb-0">
                     <thead class="table-light sticky-top">
                        <tr>
                           <th width="50">#</th>
                           <th width="100">Status</th>
                           @foreach ($headers as $column)
                              <th>{{ ucwords(str_replace(['_', '-'], ' ', $column)) }}</th>
                           @endforeach
                        </tr>
                     </thead>
                     <tbody>
                        @forelse ($readyItems as $idx => $item)
                           <tr>
                              <td>{{ $idx + 1 }}</td>
                              <td><span class="badge bg-label-success small">Ready</span></td>
                              @foreach ($headers as $col)
                                 <td>{{ $item->data[$col] ?? '-' }}</td>
                              @endforeach
                           </tr>
                        @empty
                           <tr>
                              <td colspan="{{ count($headers) + 2 }}" class="text-center py-5 text-muted">Tidak ada data yang siap tanpa peringatan.</td>
                           </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>

            {{-- TAB 2: WARNING (siap tapi ada peringatan) --}}
            <div class="tab-pane fade" id="navs-warning" role="tabpanel">
               <div class="bg-label-warning p-2 small border-bottom text-center">
                  <i class="ri-information-line me-1"></i> Data ini tetap bisa disetujui, tetapi ada anomali yang perlu diperhatikan (odometer terbalik, posisi kosong, dll).
               </div>
               <div class="table-responsive text-nowrap" style="max-height: 500px">
                  <table class="table table-hover mb-0">
                     <thead class="table-light sticky-top">
                        <tr>
                           <th width="50">#</th>
                           <th width="100">Status</th>
                           <th width="250">Peringatan</th>
                           @foreach ($headers as $column)
                              <th>{{ ucwords(str_replace(['_', '-'], ' ', $column)) }}</th>
                           @endforeach
                        </tr>
                     </thead>
                     <tbody>
                        @forelse ($warningItems as $idx => $item)
                           @php
                              $itemWarnings = $item->data['_validation']['warnings'] ?? [];
                              $warningMsg = is_array($itemWarnings) ? implode(' | ', $itemWarnings) : '';
                           @endphp
                           <tr style="border-left: 3px solid #ffab00;">
                              <td>{{ $idx + 1 }}</td>
                              <td><span class="badge bg-label-warning small">Warning</span></td>
                              <td class="small text-warning fw-bold" style="white-space: normal; min-width: 250px;">
                                 <i class="ri-alert-line me-1"></i>{{ $warningMsg }}
                              </td>
                              @foreach ($headers as $col)
                                 <td>{{ $item->data[$col] ?? '-' }}</td>
                              @endforeach
                           </tr>
                        @empty
                           <tr>
                              <td colspan="{{ count($headers) + 3 }}" class="text-center py-5 text-muted">Tidak ada data dengan peringatan. Sempurna!</td>
                           </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>

            {{-- TAB 3: NEEDS ATTENTION (INLINE EDIT) --}}
            <div class="tab-pane fade" id="navs-attention" role="tabpanel">
               <div class="bg-label-danger p-2 small border-bottom text-center">
                  <i class="ri-error-warning-line me-1"></i> Data berikut memiliki <strong>error kritis</strong> dan <strong>tidak akan diproses</strong> saat Approve. Perbaiki data lalu klik <i class="ri-save-line text-primary"></i> untuk validasi ulang.
               </div>
               <div class="table-responsive text-nowrap" style="max-height: 500px; padding-bottom: 50px;">
                  <table class="table table-hover mb-0">
                     <thead class="table-light sticky-top">
                        <tr>
                           <th width="50">Aksi</th>
                           <th width="250">Detail Error</th>
                           @foreach ($headers as $column)
                              <th>{{ ucwords(str_replace(['_', '-'], ' ', $column)) }}</th>
                           @endforeach
                        </tr>
                     </thead>
                     <tbody>
                        @forelse ($attentionItems as $idx => $item)
                           @php
                              $errors = $item->data['_validation']['errors'] ?? [];
                              $errorMsg = is_array($errors) ? implode(' | ', $errors) : ($item->error_message ?? 'Kesalahan Data');
                           @endphp
                           <tr id="row-{{ $item->id }}" class="border-danger" style="border-left: 3px solid;">
                              <td>
                                 <button class="btn btn-sm btn-icon btn-primary btn-save-row" data-id="{{ $item->id }}" title="Simpan & Validasi Ulang">
                                    <i class="ri-save-line"></i>
                                 </button>
                              </td>
                              <td class="small text-danger fw-bold" style="white-space: normal; min-width: 250px;">
                                 @if(is_array($errors) && !empty($errors))
                                    @foreach($errors as $err)
                                       <div class="mb-1"><i class="ri-close-circle-fill me-1"></i>{{ $err }}</div>
                                    @endforeach
                                 @endif
                                 @if(!empty($item->error_message))
                                    <div class="mb-1 text-warning"><i class="ri-error-warning-fill me-1"></i>{{ $item->error_message }}</div>
                                 @elseif(empty($errors))
                                    <div><i class="ri-close-circle-fill me-1"></i>Kesalahan Data</div>
                                 @endif
                              </td>
                              
                              @foreach ($headers as $col)
                                 <td>
                                    <input type="text" class="form-control form-control-sm form-edit-input" 
                                           data-row="{{ $item->id }}" 
                                           data-key="{{ $col }}" 
                                           value="{{ $item->data[$col] ?? '' }}" 
                                           style="min-width: 120px; font-size: 0.85rem">
                                 </td>
                              @endforeach
                           </tr>
                        @empty
                           <tr>
                              <td colspan="{{ count($headers) + 2 }}" class="text-center py-5">
                                 <div class="text-success">
                                    <i class="ri-checkbox-circle-fill ri-2x mb-2 d-block"></i>
                                    <div class="fw-bold">Semua data valid! Tidak ada yang perlu diperbaiki.</div>
                                 </div>
                              </td>
                           </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>

      {{-- Reject Modal --}}
      <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog">
            <form action="{{ route('import-approval.reject', $batch->id) }}" method="POST" class="modal-content">
               @csrf
               <div class="modal-header">
                  <h5 class="modal-title">Tolak Request Import</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                  <div class="mb-3">
                     <label class="form-label">Alasan Penolakan</label>
                     <textarea name="notes" class="form-control" rows="3"
                        placeholder="Jelaskan alasan kenapa data ini ditolak (biar admin tahu apa yang harus diperbaiki)"></textarea>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-danger">Tolak & Hapus Batch</button>
               </div>
            </form>
         </div>
      </div>
   </div>
@endsection

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-save-row').forEach(btn => {
        btn.addEventListener('click', async function() {
            const rowId = this.dataset.id;
            const parentRow = document.getElementById('row-' + rowId);
            const inputs = parentRow.querySelectorAll('.form-edit-input');
            
            let data = {};
            inputs.forEach(input => {
                data[input.dataset.key] = input.value;
            });
            
            data['_token'] = '{{ csrf_token() }}';
            data['_method'] = 'PATCH';

            this.disabled = true;
            this.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';

            try {
                const response = await fetch(`{{ url('import-approval/item') }}/${rowId}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.is_valid) {
                    let msg = 'Data baris ini berhasil diperbaiki dan siap disetujui.';
                    if (result.warnings && result.warnings.length > 0) {
                        msg += '\n\nPeringatan: ' + result.warnings.join(', ');
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Valid!',
                        text: msg,
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false
                    });
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Masih ada kesalahan',
                        html: '<ul class="text-start mb-0">' + result.errors.map(e => '<li>' + e + '</li>').join('') + '</ul>',
                    });
                    this.disabled = false;
                    this.innerHTML = '<i class="ri-save-line"></i>';
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Gagal',
                    text: 'Terjadi kesalahan koneksi ke server.'
                });
                this.disabled = false;
                this.innerHTML = '<i class="ri-save-line"></i>';
            }
        });
    });
});
</script>
@endsection
