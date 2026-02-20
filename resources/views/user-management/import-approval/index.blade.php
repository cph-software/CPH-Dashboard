@extends('layouts.admin')

@section('title', 'Persetujuan Import Data')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row align-items-center mb-4 g-3">
         <div class="col-md-6">
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-checkbox-multiple-line me-2 text-primary"></i>Persetujuan
               Import</h4>
            <p class="text-muted mb-0 small">Menyetujui atau menolak permintaan import data massal dari Admin.</p>
         </div>
      </div>

      @if (session('success'))
         <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      @if (session('warning'))
         <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      <div class="card shadow-sm border-0">
         <div class="table-responsive text-nowrap">
            <table class="table table-hover border-top mb-0">
               <thead>
                  <tr>
                     <th width="60">#</th>
                     <th>Tanggal Request</th>
                     <th>User</th>
                     <th>Modul</th>
                     <th>File</th>
                     <th>Total Baris</th>
                     <th>Status</th>
                     <th width="100">Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @php
                     $start = ($batches->currentPage() - 1) * $batches->perPage() + 1;
                  @endphp
                  @forelse ($batches as $batch)
                     <tr>
                        <td>{{ $start++ }}</td>
                        <td>
                           <div class="d-flex flex-column">
                              <span class="fw-bold">{{ $batch->created_at->format('d M Y') }}</span>
                              <small class="text-muted">{{ $batch->created_at->format('H:i') }}</small>
                           </div>
                        </td>
                        <td>
                           <span class="fw-medium">{{ $batch->user->name ?? 'Unknown' }}</span>
                        </td>
                        <td>
                           <span class="badge bg-label-info">{{ $batch->module }}</span>
                        </td>
                        <td>
                           <span class="small">{{ $batch->filename }}</span>
                        </td>
                        <td>
                           <span class="fw-bold">{{ number_format($batch->total_rows) }}</span>
                        </td>
                        <td>
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
                        </td>
                        <td>
                           <div class="dropdown">
                              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                 <i class="icon-base ri ri-more-2-line"></i>
                              </button>
                              <div class="dropdown-menu">
                                 <a class="dropdown-item" href="{{ route('import-approval.show', $batch->id) }}">
                                    <i class="ri-eye-line me-1"></i> Detail & Review
                                 </a>
                                 @if ($batch->status === 'Pending')
                                    <form action="{{ route('import-approval.approve', $batch->id) }}" method="POST"
                                       class="d-inline">
                                       @csrf
                                       <button type="submit" class="dropdown-item text-success"
                                          onclick="return confirm('Setujui import data ini? Data akan segera dimasukkan ke database.')">
                                          <i class="ri-check-line me-1"></i> Approve
                                       </button>
                                    </form>
                                    <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                                       data-bs-target="#rejectModal{{ $batch->id }}">
                                       <i class="ri-close-line me-1"></i> Reject
                                    </button>
                                 @endif
                              </div>
                           </div>

                           {{-- Reject Modal --}}
                           @if ($batch->status === 'Pending')
                              <div class="modal fade" id="rejectModal{{ $batch->id }}" tabindex="-1"
                                 aria-hidden="true">
                                 <div class="modal-dialog">
                                    <form action="{{ route('import-approval.reject', $batch->id) }}" method="POST"
                                       class="modal-content">
                                       @csrf
                                       <div class="modal-header">
                                          <h5 class="modal-title">Tolak Request Import</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal"
                                             aria-label="Close"></button>
                                       </div>
                                       <div class="modal-body">
                                          <div class="mb-3">
                                             <label class="form-label">Alasan Penolakan</label>
                                             <textarea name="notes" class="form-control" rows="3"
                                                placeholder="Contoh: Format data salah atau data tidak valid" required></textarea>
                                          </div>
                                       </div>
                                       <div class="modal-footer">
                                          <button type="button" class="btn btn-outline-secondary"
                                             data-bs-dismiss="modal">Batal</button>
                                          <button type="submit" class="btn btn-danger">Tolak Data</button>
                                       </div>
                                    </form>
                                 </div>
                              </div>
                           @endif
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="8" class="text-center py-5 text-muted"> Belum ada data permintaan import. </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
         @if ($batches->hasPages())
            <div class="card-footer clearfix">
               {{ $batches->links() }}
            </div>
         @endif
      </div>
   </div>
@endsection
