@extends('layouts.admin')

@section('title', 'Users')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="mb-0">Users List</h4>
         <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add New User</button>
      </div>

      <!-- Users Table -->
      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
               <thead>
                  <tr>
                     <th>User</th>
                     <th>Role</th>
                     <th>Karyawan ID</th>
                     <th>Toko ID</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($users as $user)
                     <tr>
                        <td>
                           <div class="d-flex justify-content-start align-items-center user-name">
                              <div class="avatar-wrapper">
                                 <div class="avatar avatar-sm me-3">
                                    <img src="{{ $user->foto ?: asset('template/full-version/assets/img/avatars/1.png') }}"
                                       alt="Avatar" class="rounded-circle">
                                 </div>
                              </div>
                              <div class="d-flex flex-column">
                                 <span class="fw-medium">{{ $user->karyawan->name ?? 'User ' . $user->id }}</span>
                                 <small class="text-muted">{{ $user->id }}</small>
                              </div>
                           </div>
                        </td>
                        <td>
                           <span class="text-truncate d-flex align-items-center">
                              <span class="badge badge-center rounded-pill bg-label-primary w-px-30 h-px-30 me-2">
                                 <i class="ri-user-line ri-20px"></i>
                              </span>
                              {{ $user->role->name ?? 'No Role' }}
                           </span>
                        </td>
                        <td>{{ $user->master_karyawan_id ?: '-' }}</td>
                        <td>{{ $user->toko_id ?: '-' }}</td>
                        <td>
                           <div class="d-inline-block text-nowrap">
                              <button class="btn btn-sm btn-icon edit-user" data-id="{{ $user->id }}"><i
                                    class="ri-edit-box-line"></i></button>
                              <button class="btn btn-sm btn-icon delete-user" data-id="{{ $user->id }}"><i
                                    class="ri-delete-bin-line"></i></button>
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
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">Add New User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form action="{{ route('users.store') }}" method="POST">
                  @csrf
                  <div class="modal-body">
                     <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select" required>
                           <option value="">Select Role</option>
                           @foreach ($roles as $role)
                              <option value="{{ $role->id }}">{{ $role->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Karyawan ID (Optional)</label>
                        <input type="text" name="master_karyawan_id" class="form-control">
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Toko ID (Optional)</label>
                        <input type="text" name="toko_id" class="form-control">
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-primary">Save User</button>
                  </div>
               </form>
            </div>
         </div>
      </div>

      <!-- Edit User Modal -->
      <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">Edit User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form id="editUserForm" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-body">
                     <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" id="edit_role_id" class="form-select" required>
                           <option value="">Select Role</option>
                           @foreach ($roles as $role)
                              <option value="{{ $role->id }}">{{ $role->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Karyawan ID</label>
                        <input type="text" name="master_karyawan_id" id="edit_master_karyawan_id"
                           class="form-control">
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Toko ID</label>
                        <input type="text" name="toko_id" id="edit_toko_id" class="form-control">
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-primary">Update User</button>
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
         $('.datatables-users').DataTable();

         // Edit User
         $(document).on('click', '.edit-user', function() {
            const id = $(this).data('id');
            $.get('{{ url('cph_dashboard/users') }}/' + id + '/edit', function(user) {
               $('#editUserForm').attr('action', '{{ url('cph_dashboard/users') }}/' + id);
               $('#edit_role_id').val(user.role_id);
               $('#edit_master_karyawan_id').val(user.master_karyawan_id);
               $('#edit_toko_id').val(user.toko_id);
               $('#editUserModal').modal('show');
            });
         });

         $(document).on('click', '.delete-user', function() {
            const id = $(this).data('id');
            const name = $(this).closest('tr').find('.user-name .fw-medium').text();

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `User "${name}" akan dihapus permanen!`,
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
                  form.action = '{{ url('cph_dashboard/users') }}/' + id;
                  form.submit();
               }
            });
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
