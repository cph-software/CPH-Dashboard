@extends('layouts.admin')

@section('title', 'Review Detail Import')

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
            @if ($batch->status === 'Pending' && auth()->user()->hasPermission('Import Approval', 'update'))
               <form action="{{ route('import-approval.approve', $batch->id) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-success me-2"
                     onclick="return confirm('Setujui dan proses data ini?')">
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

      {{-- Data Table --}}
      <div class="card shadow-sm border-0">
         <div class="card-header bg-label-secondary py-2">
            <h6 class="mb-0">Pratinjau Data (Imported Rows)</h6>
         </div>
         <div class="table-responsive text-nowrap" style="max-height: 500px">
            <table class="table table-hover table-striped border-top mb-0">
               <thead>
                  <tr>
                     <th width="50">#</th>
                     <th width="100">Status</th>
                     {{-- Find columns from the first item data keys --}}
                     @if ($batch->items->count() > 0)
                        @foreach (array_keys($batch->items->first()->data) as $column)
                           <th>{{ ucwords(str_replace(['_', '-'], ' ', $column)) }}</th>
                        @endforeach
                     @endif
                     <th>Notes/Error</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse ($batch->items as $idx => $item)
                     <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                           @php
                              $itemStatusClass =
                                  [
                                      'Pending' => 'bg-label-warning',
                                      'Success' => 'bg-label-success',
                                      'Failed' => 'bg-label-danger',
                                  ][$item->status] ?? 'bg-label-secondary';
                           @endphp
                           <span class="badge {{ $itemStatusClass }} small">{{ $item->status }}</span>
                        </td>
                        @foreach ($item->data as $val)
                           <td>{{ is_array($val) ? json_encode($val) : $val }}</td>
                        @endforeach
                        <td class="small text-danger">{{ $item->error_message ?? '-' }}</td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="10" class="text-center py-5">Baris data tidak ditemukan.</td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
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
