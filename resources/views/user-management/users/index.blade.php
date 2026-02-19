@extends('layouts.admin')

@section('title', 'Users Management')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
   <style>
      .user-card:hover {
         transform: translateY(-3px);
         transition: all 0.3s ease;
         box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
      }

      .avatar-initial.bg-label-primary {
         color: #7367f0 !important;
         background-color: #e7e7ff !important;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row align-items-center mb-4 g-3">
         <div class="col-md-6">
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-user-follow-line me-2 text-primary"></i>Users Management
            </h4>
            <p class="text-muted mb-0 small">Create and manage system access for employees and partners.</p>
         </div>
         <div class="col-md-6 text-md-end">
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
               <i class="icon-base ri ri-user-add-line me-1"></i> Add New User
            </button>
         </div>
      </div>

      <!-- Users Stats -->
      <div class="row g-4 mb-4">
         <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 user-card">
               <div class="card-body">
                  <div class="d-flex align-items-center">
                     <div class="avatar avatar-md me-3">
                        <span class="avatar-initial rounded-3 bg-label-primary"><i
                              class="icon-base ri ri-group-line ri-24px"></i></span>
                     </div>
                     <div>
                        <h4 class="mb-0 fw-bold">{{ $users->count() }}</h4>
                        <small class="text-muted">Total Users</small>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 user-card">
               <div class="card-body">
                  <div class="d-flex align-items-center">
                     <div class="avatar avatar-md me-3">
                        <span class="avatar-initial rounded-3 bg-label-success"><i
                              class="icon-base ri ri-shield-user-line ri-24px"></i></span>
                     </div>
                     <div>
                        <h4 class="mb-0 fw-bold">{{ $users->where('role.name', 'Super Admin')->count() }}</h4>
                        <small class="text-muted">Admins</small>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Users Table -->
      <div class="card shadow-sm border-0">
         <div class="card-datatable table-responsive">
            <table class="datatables-users table align-middle">
               <thead class="bg-lighter">
                  <tr>
                     <th class="ps-4">User Details</th>
                     <th>Role Access</th>
                     <th>Employee Code</th>
                     <th>Branch / Store</th>
                     <th class="text-center">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($users as $user)
                     <tr>
                        <td class="ps-4">
                           <div class="d-flex justify-content-start align-items-center">
                              <div class="avatar-wrapper">
                                 <div class="avatar avatar-md me-3">
                                    @if ($user->foto)
                                       <img src="{{ url($user->foto) }}" alt="Avatar" class="rounded-circle shadow-sm">
                                    @else
                                       <span class="avatar-initial rounded-circle bg-label-primary shadow-sm">
                                          {{ strtoupper(substr($user->karyawan->name ?? ($user->name ?? 'U'), 0, 1)) }}
                                       </span>
                                    @endif
                                 </div>
                              </div>
                              <div class="d-flex flex-column">
                                 <span
                                    class="fw-bold text-heading">{{ $user->karyawan->name ?? ($user->name ?? 'N/A') }}</span>
                                 <small class="text-muted">{{ $user->master_karyawan_id ?: 'External User' }}</small>
                              </div>
                           </div>
                        </td>
                        <td>
                           @php
                              $roleBadge = 'bg-label-primary';
                              if ($user->role && $user->role->name == 'Super Admin') {
                                  $roleBadge = 'bg-label-danger';
                              } elseif ($user->role && str_contains(strtolower($user->role->name), 'manager')) {
                                  $roleBadge = 'bg-label-success';
                              }
                           @endphp
                           <span class="badge {{ $roleBadge }} rounded-pill px-3 py-1">
                              <i
                                 class="icon-base ri ri-user-settings-line me-1 small"></i>{{ $user->role->name ?? 'No Role' }}
                           </span>
                        </td>
                        <td><code class="text-primary fw-bold">{{ $user->master_karyawan_id ?: '-' }}</code></td>
                        <td><span class="text-muted fw-medium">{{ $user->toko_id ?: 'Main Office' }}</span></td>
                        <td class="text-center pe-4">
                           <div class="d-flex justify-content-center gap-2">
                              <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect edit-user"
                                 data-id="{{ $user->id }}" title="Edit User">
                                 <i class="icon-base ri ri-edit-line"></i>
                              </button>
                              <button class="btn btn-sm btn-icon btn-text-danger rounded-pill waves-effect delete-user"
                                 data-id="{{ $user->id }}" title="Delete User">
                                 <i class="icon-base ri ri-delete-bin-line"></i>
                              </button>
                           </div>
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>

      <!-- Add User Modal -->
      <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
               <div class="modal-header bg-primary py-3">
                  <h5 class="modal-title text-white"><i class="icon-base ri ri-user-add-line me-2"></i>Create New Account
                  </h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                     aria-label="Close"></button>
               </div>
               <form action="{{ route('users.store') }}" method="POST">
                  @csrf
                  <div class="modal-body pt-4">
                     <div class="mb-4">
                        <label class="form-label fw-bold">Select System Role <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select select2-modal" required
                           data-placeholder="Choose Access Level">
                           <option value=""></option>
                           @foreach ($roles as $role)
                              <option value="{{ $role->id }}">{{ $role->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="mb-4">
                        <label class="form-label fw-bold">Employee ID / Code</label>
                        <input type="text" name="master_karyawan_id" class="form-control" placeholder="E.g. EMP001">
                        <div class="form-text small">Used for internal staff identification.</div>
                     </div>
                     <div class="mb-4">
                        <label class="form-label fw-bold">Access Password <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                           <input type="password" name="password" class="form-control"
                              placeholder="Minimum 6 characters" required>
                           <span class="input-group-text cursor-pointer"><i
                                 class="icon-base ri ri-eye-off-line"></i></span>
                        </div>
                     </div>
                     <div class="mb-0">
                        <label class="form-label fw-bold">Store / Branch ID (Optional)</label>
                        <input type="text" name="toko_id" class="form-control" placeholder="E.g. STORE-01">
                     </div>
                  </div>
                  <div class="modal-footer border-top-0 pt-0 pb-4 justify-content-center">
                     <button type="button" class="btn btn-outline-secondary px-4 me-2"
                        data-bs-dismiss="modal">Cancel</button>
                     <button type="submit" class="btn btn-primary px-4 shadow-sm">Save & Active User</button>
                  </div>
               </form>
            </div>
         </div>
      </div>

      <!-- Edit User Modal -->
      <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
               <div class="modal-header bg-label-primary py-3">
                  <h5 class="modal-title"><i class="icon-base ri ri-edit-box-line me-2 text-primary"></i>Update User
                     Account</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form id="editUserForm" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-body pt-4">
                     <div class="mb-4">
                        <label class="form-label fw-bold text-primary">Account Role</label>
                        <select name="role_id" id="edit_role_id" class="form-select select2-modal" required>
                           @foreach ($roles as $role)
                              <option value="{{ $role->id }}">{{ $role->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="mb-4">
                        <label class="form-label fw-bold">Employee ID</label>
                        <input type="text" name="master_karyawan_id" id="edit_master_karyawan_id"
                           class="form-control" readonly>
                        <div class="form-text small text-warning"><i class="icon-base ri ri-error-warning-line"></i>
                           Identification
                           code is usually unique and immutable.</div>
                     </div>
                     <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Reset Password</label>
                        <input type="password" name="password" class="form-control"
                           placeholder="Leave empty to keep current password">
                     </div>
                     <div class="mb-0">
                        <label class="form-label fw-bold">Store / Branch Access</label>
                        <input type="text" name="toko_id" id="edit_toko_id" class="form-control">
                     </div>
                  </div>
                  <div class="modal-footer border-top-0 pt-0 pb-4 justify-content-center">
                     <button type="button" class="btn btn-label-secondary px-4 me-2"
                        data-bs-dismiss="modal">Discard</button>
                     <button type="submit" class="btn btn-primary px-4 shadow-sm">Update Information</button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>

   <form id="deleteForm" method="POST" style="display: none;">
      @csrf
      @method('DELETE')
   </form>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('.datatables-users').DataTable({
            order: [
               [0, 'asc']
            ],
            dom: '<"card-header d-flex flex-wrap pb-0 pt-0"<"col-md-6"f><"col-md-6 text-end"l>>t<"card-footer d-flex flex-wrap"<"col-md-6"i><"col-md-6 text-end"p>>',
            language: {
               search: '',
               searchPlaceholder: 'Search Users...'
            },
            initComplete: function() {
               $('.dataTables_filter input').addClass('form-control shadow-xs').removeClass(
                  'form-control-sm');
            }
         });

         if ($.fn.select2) {
            $('.select2-modal').select2({
               dropdownParent: $('.modal')
            });
         }

         // Edit User
         $(document).on('click', '.edit-user', function() {
            const id = $(this).data('id');
            const button = $(this);
            button.prop('disabled', true);

            $.get('{{ url('cph_dashboard/users') }}/' + id + '/edit', function(user) {
               $('#editUserForm').attr('action', '{{ url('cph_dashboard/users') }}/' + id);
               $('#edit_role_id').val(user.role_id).trigger('change');
               $('#edit_master_karyawan_id').val(user.master_karyawan_id);
               $('#edit_toko_id').val(user.toko_id);
               $('#editUserModal').modal('show');
               button.prop('disabled', false);
            });
         });

         $(document).on('click', '.delete-user', function() {
            const id = $(this).data('id');
            const name = $(this).closest('tr').find('.text-heading').text();

            Swal.fire({
               title: 'Remove access for ' + name + '?',
               text: "This user will no longer be able to log in to the system!",
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Yes, Delete!',
               cancelButtonText: 'Cancel',
               customClass: {
                  confirmButton: 'btn btn-danger me-3 waves-effect waves-light',
                  cancelButton: 'btn btn-outline-secondary waves-effect'
               },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  const form = document.getElementById('deleteForm');
                  form.action = '{{ url('cph_dashboard/users') }}/' + id;
                  form.submit();
               }
            });
         });
      });
   </script>
@endsection
