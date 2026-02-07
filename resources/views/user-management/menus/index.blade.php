@extends('layouts.admin')

@section('title', 'Menus')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="mb-0">Menus List</h4>
         <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuModal">Add New Menu</button>
      </div>

      <!-- Menus Table -->
      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-menus table border-top">
               <thead>
                  <tr>
                     <th>Name</th>
                     <th>Application</th>
                     <th>URL</th>
                     <th>Icon</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($menus as $menu)
                     <tr>
                        <td>{{ $menu->name }}</td>
                        <td>{{ $menu->aplikasi->name ?? '-' }}</td>
                        <td><code>{{ $menu->url }}</code></td>
                        <td><i class="ri {{ $menu->icon }}"></i></td>
                        <td>
                           <div class="d-inline-block text-nowrap">
                              <button class="btn btn-sm btn-icon edit-menu" data-id="{{ $menu->id }}"><i
                                    class="ri-edit-box-line"></i></button>
                              <button class="btn btn-sm btn-icon delete-menu" data-id="{{ $menu->id }}"><i
                                    class="ri-delete-bin-line"></i></button>
                           </div>
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>

      <!-- Add Menu Modal -->
      <div class="modal fade" id="addMenuModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">Add New Menu</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form action="{{ route('menus.store') }}" method="POST">
                  @csrf
                  <div class="modal-body">
                     <div class="mb-3">
                        <label class="form-label">Application</label>
                        <select name="aplikasi_id" class="form-select" required>
                           @foreach ($aplikasi as $app)
                              <option value="{{ $app->id }}">{{ $app->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Dashboard">
                     </div>
                     <div class="mb-3">
                        <label class="form-label">URL</label>
                        <input type="text" name="url" class="form-control" required placeholder="e.g. dashboard">
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Icon (Remix Icon Class)</label>
                        <input type="text" name="icon" class="form-control" placeholder="e.g. ri-home-line">
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-primary">Save Menu</button>
                  </div>
               </form>
            </div>
         </div>
      </div>

      <!-- Edit Menu Modal -->
      <div class="modal fade" id="editMenuModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">Edit Menu</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form id="editMenuForm" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-body">
                     <div class="mb-3">
                        <label class="form-label">Application</label>
                        <select name="aplikasi_id" id="edit_aplikasi_id" class="form-select" required>
                           @foreach ($aplikasi as $app)
                              <option value="{{ $app->id }}">{{ $app->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                     </div>
                     <div class="mb-3">
                        <label class="form-label">URL</label>
                        <input type="text" name="url" id="edit_url" class="form-control" required>
                     </div>
                     <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" id="edit_icon" class="form-control">
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-primary">Update Menu</button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('.datatables-menus').DataTable({
            order: [
               [1, 'asc'],
               [0, 'asc']
            ]
         });

         // Edit Menu
         $(document).on('click', '.edit-menu', function() {
            const id = $(this).data('id');
            const baseUrl = '{{ url('cph_dashboard/menus') }}';
            $.get(baseUrl + '/' + id + '/edit', function(menu) {
               $('#editMenuForm').attr('action', baseUrl + '/' + id);
               $('#edit_aplikasi_id').val(menu.aplikasi_id);
               $('#edit_name').val(menu.name);
               $('#edit_url').val(menu.url);
               $('#edit_icon').val(menu.icon);
               $('#editMenuModal').modal('show');
            });
         });

         // Delete Menu
         $(document).on('click', '.delete-menu', function() {
            const id = $(this).data('id');
            Swal.fire({
               title: 'Are you sure?',
               text: "You won't be able to revert this!",
               icon: 'warning',
               showCancelButton: true,
               confirmButtonColor: '#3085d6',
               cancelButtonColor: '#d33',
               confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
               if (result.isConfirmed) {
                  const form = document.createElement('form');
                  form.method = 'POST';
                  form.action = '{{ url('cph_dashboard/menus') }}/' + id;
                  form.innerHTML = `@csrf @method('DELETE')`;
                  document.body.appendChild(form);
                  form.submit();
               }
            });
         });
      });
   </script>
@endsection
