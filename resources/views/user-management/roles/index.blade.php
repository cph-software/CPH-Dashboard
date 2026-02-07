@extends('layouts.admin')

@section('title', 'Roles')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <div>
            <h4 class="mb-1">Roles List</h4>
            <p class="mb-0 text-muted">Manage roles and their associated menu access permissions.</p>
         </div>
         <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> Add New Role
         </a>
      </div>

      @if (session('success'))
         <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      <!-- Role cards -->
      <div class="row g-4">
         @foreach ($roles as $role)
            <div class="col-xl-4 col-lg-6 col-md-6">
               <div class="card h-100 shadow-sm border-0">
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="badge bg-label-primary rounded-pill">Total {{ $role->users_count }} users</span>
                        <div class="dropdown">
                           <button class="btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
                              type="button" data-bs-toggle="dropdown">
                              <i class="ri-more-2-fill"></i>
                           </button>
                           <ul class="dropdown-menu dropdown-menu-end">
                              <li><a class="dropdown-item text-danger delete-role" href="javascript:void(0);"
                                    data-id="{{ $role->id }}">Delete Role</a></li>
                           </ul>
                        </div>
                     </div>
                     <div class="role-content">
                        <h5 class="mb-2 text-primary fw-bold">{{ $role->name }}</h5>
                        <div class="mb-4">
                           <small class="text-muted d-block mb-2">Access Summary:</small>
                           <div class="d-flex flex-wrap gap-1">
                              @php $count = 0; @endphp
                              @foreach ($role->menus->take(5) as $m)
                                 <span class="badge bg-lighter text-dark border small">{{ $m->name }}</span>
                                 @php $count++; @endphp
                              @endforeach
                              @if ($role->menus->count() > 5)
                                 <span class="badge bg-lighter text-muted border small">+{{ $role->menus->count() - 5 }}
                                    More</span>
                              @endif
                           </div>
                        </div>
                        <div class="d-grid mt-3">
                           <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-outline-primary">
                              <i class="ri-edit-2-line me-1"></i> Edit Permissions
                           </a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         @endforeach

         <div class="col-xl-4 col-lg-6 col-md-6">
            <a href="{{ route('roles.create') }}" class="text-decoration-none">
               <div
                  class="card h-100 shadow-sm border-0 border-dashed bg-transparent d-flex align-items-center justify-content-center py-5">
                  <div class="text-center p-4">
                     <div class="avatar avatar-lg mb-3 mx-auto">
                        <span class="avatar-initial rounded bg-label-primary">
                           <i class="ri-add-circle-line ri-32px"></i>
                        </span>
                     </div>
                     <h5 class="mb-1 text-primary">Add New Role</h5>
                     <p class="mb-0 text-muted">Create a new role and set its permissions</p>
                  </div>
               </div>
            </a>
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
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
   <script>
      $(document).on('click', '.delete-role', function() {
         const id = $(this).data('id');
         const url = '{{ url('cph_dashboard/roles') }}/' + id;

         Swal.fire({
            title: 'Are you sure?',
            text: "User with this role might lose access!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
         }).then((result) => {
            if (result.isConfirmed) {
               $('#deleteForm').attr('action', url).submit();
            }
         });
      });
   </script>
@endsection
