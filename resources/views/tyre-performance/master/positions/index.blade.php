@extends('layouts.admin')

@section('title', 'Master Konfigurasi Ban')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Konfigurasi Ban</h4>
         @if (hasPermission('Position Layouts', 'create'))
            <a href="{{ route('tyre-positions.create') }}" class="btn btn-primary">
               <i class="icon-base ri ri-add-line me-1"></i> Buat Konfigurasi Baru
            </a>
         @endif
      </div>

      <div class="row">
         @forelse($configurations as $config)
            <div class="col-md-6 col-lg-4 mb-4">
               <div class="card h-100">
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                           <h5 class="card-title mb-1">{{ $config->name }}</h5>
                           <p class="text-muted mb-0"><small>{{ $config->code }}</small></p>
                        </div>
                        <span class="badge bg-label-{{ $config->status == 'Active' ? 'success' : 'secondary' }}">
                           {{ $config->status }}
                        </span>
                     </div>

                     @if ($config->description)
                        <p class="card-text text-muted small mb-3">{{ $config->description }}</p>
                     @endif

                     @if ($config->config_type)
                        <div class="mb-3">
                           <span class="badge bg-label-info">{{ $config->config_type }}</span>
                        </div>
                     @endif

                     <div class="row g-2 mb-3">
                        <div class="col-6">
                           <div class="d-flex align-items-center">
                              <div
                                 class="avatar avatar-sm me-2 bg-label-primary rounded d-flex align-items-center justify-content-center">
                                 <i class="icon-base ri ri-steering-2-line"></i>
                              </div>
                              <div>
                                 <small class="text-muted d-block">Total Posisi</small>
                                 <strong>{{ $config->total_positions }}</strong>
                              </div>
                           </div>
                        </div>
                        <div class="col-6">
                           <div class="d-flex align-items-center">
                              <div
                                 class="avatar avatar-sm me-2 bg-label-warning rounded d-flex align-items-center justify-content-center">
                                 <i class="icon-base ri ri-tools-line"></i>
                              </div>
                              <div>
                                 <small class="text-muted d-block">Ban Cadangan</small>
                                 <strong>{{ $config->total_spare }}</strong>
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="d-flex gap-2">
                        <a href="{{ route('tyre-positions.show', $config->id) }}"
                           class="btn btn-sm btn-outline-primary flex-fill">
                           <i class="icon-base ri ri-eye-line me-1"></i> Lihat Detail
                        </a>
                        @if (hasPermission('Position Layouts', 'update'))
                           <a href="{{ route('tyre-positions.edit', $config->id) }}"
                              class="btn btn-sm btn-icon btn-outline-secondary" title="Edit">
                              <i class="icon-base ri ri-pencil-line"></i>
                           </a>
                        @endif
                        @if (hasPermission('Position Layouts', 'delete'))
                           <button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-config"
                              data-id="{{ $config->id }}" data-name="{{ $config->name }}" title="Hapus">
                              <i class="icon-base ri ri-delete-bin-line"></i>
                           </button>
                        @endif
                     </div>
                  </div>
               </div>
            </div>
         @empty
            <div class="col-12">
               <div class="card">
                  <div class="card-body text-center py-5">
                     <i class="ri-inbox-line" style="font-size: 3rem; color: #ddd;"></i>
                     <p class="text-muted mt-3">Belum ada konfigurasi posisi ban.</p>
                     @if (hasPermission('Position Layouts', 'create'))
                        <a href="{{ route('tyre-positions.create') }}" class="btn btn-primary">
                           <i class="ri-add-line me-1"></i> Buat Konfigurasi Pertama
                        </a>
                     @endif
                  </div>
               </div>
            </div>
         @endforelse
      </div>

      <div class="d-flex justify-content-center mt-4 overflow-auto">
         {{ $configurations->links() }}
      </div>
   </div>

   <form id="deleteForm" method="POST" style="display: none;">
      @csrf
      @method('DELETE')
   </form>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         // Show success/error toast
         @if (session('success'))
            Swal.fire({
               icon: 'success',
               title: 'Berhasil!',
               text: '{{ session('success') }}',
               toast: true,
               position: 'top-end',
               showConfirmButton: false,
               timer: 3000,
               timerProgressBar: true
            });
         @endif

         @if (session('error'))
            Swal.fire({
               icon: 'error',
               title: 'Error!',
               text: '{{ session('error') }}',
               toast: true,
               position: 'top-end',
               showConfirmButton: false,
               timer: 3000,
               timerProgressBar: true
            });
         @endif

         // Delete confirmation
         document.querySelectorAll('.delete-config').forEach(button => {
            button.addEventListener('click', function() {
               const configId = this.getAttribute('data-id');
               const configName = this.getAttribute('data-name');

               Swal.fire({
                  title: 'Hapus Konfigurasi?',
                  text: `Yakin ingin menghapus konfigurasi "${configName}"? Semua posisi ban dalam konfigurasi ini akan ikut terhapus.`,
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonText: 'Ya, Hapus!',
                  cancelButtonText: 'Batal',
                  customClass: {
                     confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                     cancelButton: 'btn btn-outline-secondary waves-effect'
                  },
                  buttonsStyling: false
               }).then((result) => {
                  if (result.isConfirmed) {
                     const form = document.getElementById('deleteForm');
                     form.action = `{{ url('master_position') }}/${configId}`;
                     form.submit();
                  }
               });
            });
         });
      });
   </script>
@endsection
