@extends('layouts.admin')

@section('title', 'Roles')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row align-items-center mb-4 g-3">
         <div class="col-md-5">
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-shield-keyhole-line me-2 text-primary"></i>Roles Management
            </h4>
            <p class="text-muted mb-0 small">Define access levels and granular permissions for each user role.</p>
         </div>
         <div class="col-md-7">
            <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-3">
               <div class="search-box">
                  <div class="input-group input-group-merge shadow-sm">
                     <span class="input-group-text border-0"><i class="ri-search-line"></i></span>
                     <input type="text" id="roleSearch" class="form-control border-0 px-2"
                        placeholder="Cari nama peran...">
                  </div>
               </div>
               <a href="{{ route('roles.create') }}" class="btn btn-primary shadow-sm">
                  <i class="icon-base ri ri-add-line me-1"></i> Create New Role
               </a>
            </div>
         </div>
      </div>

      @if (session('success'))
         <div class="alert alert-soft-success d-flex align-items-center alert-dismissible fade show" role="alert">
            <i class="icon-base ri ri-checkbox-circle-line me-2 fs-4"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      <!-- Role cards -->
      <div class="row g-4">
         @foreach ($roles as $role)
            <div class="col-xl-4 col-lg-6 col-md-6">
               <div class="card h-100 shadow-sm border-0 role-card hover-shadow-lg transition-all">
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                           <div class="avatar avatar-sm me-2">
                              <span class="avatar-initial rounded bg-label-primary">
                                 <i class="icon-base ri ri-user-settings-line"></i>
                              </span>
                           </div>
                           <span class="badge bg-label-primary rounded-pill">{{ $role->users_count }} users</span>
                        </div>
                        <div class="dropdown">
                           <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                              type="button" data-bs-toggle="dropdown">
                              <i class="icon-base ri ri-more-2-fill"></i>
                           </button>
                           <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                              <li><a class="dropdown-item" href="{{ route('roles.edit', $role->id) }}"><i
                                       class="icon-base ri ri-edit-line me-2"></i>Edit Role</a></li>
                              <li>
                                 <hr class="dropdown-divider">
                              </li>
                              <li><a class="dropdown-item text-danger delete-role" href="javascript:void(0);"
                                    data-id="{{ $role->id }}"><i
                                       class="icon-base ri ri-delete-bin-line me-2"></i>Delete Role</a>
                              </li>
                           </ul>
                        </div>
                     </div>
                     <div class="role-content">
                        <h5 class="mb-3 text-heading fw-bold">{{ $role->name }}</h5>
                        <div class="mb-4">
                           <label class="text-muted small fw-bold text-uppercase letter-spacing-1 d-block mb-2">Access
                              Summary</label>
                           <div class="d-flex flex-wrap gap-2">
                              @forelse ($role->menus->take(6) as $m)
                                 <div
                                    class="d-flex align-items-center bg-lighter px-2 py-1 rounded border border-light shadow-xs"
                                    title="{{ $m->name }}">
                                    <i
                                       class="icon-base ri {{ $m->icon ?: 'ri-checkbox-blank-circle-line' }} text-primary me-1 small"></i>
                                    <span class="small text-dark">{{ $m->name }}</span>
                                 </div>
                              @empty
                                 <span class="text-muted small">No permissions assigned</span>
                              @endforelse
                              @if ($role->menus->count() > 6)
                                 <div class="bg-label-secondary px-2 py-1 rounded small border-0">
                                    +{{ $role->menus->count() - 6 }} more
                                 </div>
                              @endif
                           </div>
                        </div>
                        <div class="d-grid mt-auto">
                           <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-label-primary waves-effect">
                              <i class="icon-base ri ri-settings-3-line me-1"></i> Manage Permissions
                           </a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         @endforeach

         <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100 shadow-sm border-0 border-dashed bg-transparent" style="min-height: 200px">
               <a href="{{ route('roles.create') }}"
                  class="card-body d-flex flex-column align-items-center justify-content-center text-center text-decoration-none">
                  <div class="avatar avatar-md mb-3">
                     <span class="avatar-initial rounded-circle bg-label-secondary">
                        <i class="icon-base ri ri-add-line ri-24px"></i>
                     </span>
                  </div>
                  <h5 class="mb-1 text-primary fw-bold">Add New Role</h5>
                  <p class="mb-0 text-muted small">Create a custom role for your system</p>
               </a>
            </div>
         </div>
      </div>
      <!--/ Role cards -->

      <form id="deleteForm" action="" method="POST" style="display: none;">
         @csrf
         @method('DELETE')
      </form>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $(document).on('click', '.delete-role', function() {
            const id = $(this).data('id');
            const name = $(this).closest('.card-body').find('.role-content h5').text();

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `Role "${name}" akan dihapus. User dengan role ini mungkin akan kehilangan akses!`,
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
                  form.action = '{{ url('cph_dashboard/roles') }}/' + id;
                  form.submit();
               }
            });
         });

         // Role Search Filtering
         $('#roleSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.role-card').parent().each(function() {
               const roleName = $(this).find('.role-content h5').text().toLowerCase();
               if (roleName.includes(value)) {
                  $(this).show();
               } else {
                  $(this).hide();
               }
            });
            // Always keep the 'Add New Role' card visible or hide if it doesn't match? 
            // Usually best to keep it if it matches 'add' or just always keep it.
            $('.border-dashed').parent().show();
         });

         @if (session('success'))
            Swal.fire({
               icon: 'success',
               title: 'Berhasil!',
               text: '{{ session('success') }}',
               timer: 2000,
               showConfirmButton: false
            });
         @endif
      });
   </script>
@endsection
