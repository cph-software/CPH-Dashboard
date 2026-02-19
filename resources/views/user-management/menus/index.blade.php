@extends('layouts.admin')

@section('title', 'System Menus')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row align-items-center mb-4 g-3">
         <div class="col-md-6">
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-menu-search-line me-2 text-primary"></i>System Menus</h4>
            <p class="text-muted mb-0 small">Configure navigation links and icons for different modules.</p>
         </div>
         <div class="col-md-6 text-md-end">
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addMenuModal">
               <i class="icon-base ri ri-add-line me-1"></i> Add New Menu
            </button>
         </div>
      </div>

      <!-- Menus Table -->
      <div class="card shadow-sm border-0">
         <div class="card-datatable table-responsive">
            <table class="datatables-menus table align-middle">
               <thead class="bg-lighter">
                  <tr>
                     <th class="ps-4">Menu Name</th>
                     <th>Application</th>
                     <th>Route / URL</th>
                     <th class="text-center">Icon</th>
                     <th class="text-center">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($menus as $menu)
                     <tr>
                        <td class="ps-4">
                           <div class="d-flex align-items-center">
                              <div class="avatar avatar-sm me-2">
                                 <span class="avatar-initial rounded bg-label-secondary small">
                                    <i class="icon-base ri {{ $menu->icon ?: 'ri-circle-fill' }}"></i>
                                 </span>
                              </div>
                              <span class="fw-bold text-heading">{{ $menu->name }}</span>
                           </div>
                        </td>
                        <td>
                           <span class="badge bg-label-info rounded-pill px-3">
                              {{ $menu->aplikasi->name ?? 'Standalone' }}
                           </span>
                        </td>
                        <td><code class="text-primary">{{ $menu->url }}</code></td>
                        <td class="text-center">
                           <div class="d-inline-flex p-2 bg-lighter rounded border">
                              <i class="icon-base ri {{ $menu->icon ?: 'ri-circle-fill' }} ri-lg"></i>
                           </div>
                        </td>
                        <td class="text-center pe-4">
                           <div class="d-flex justify-content-center gap-2">
                              <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill edit-menu"
                                 data-id="{{ $menu->id }}" title="Edit Menu">
                                 <i class="icon-base ri ri-edit-line"></i>
                              </button>
                              <button class="btn btn-sm btn-icon btn-text-danger rounded-pill delete-menu"
                                 data-id="{{ $menu->id }}" title="Delete Menu">
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

      <!-- Add Menu Modal -->
      <div class="modal fade" id="addMenuModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
               <div class="modal-header bg-primary py-3">
                  <h5 class="modal-title text-white"><i class="icon-base ri ri-add-circle-line me-2"></i>Create New Menu
                     Item</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                     aria-label="Close"></button>
               </div>
               <form action="{{ route('menus.store') }}" method="POST">
                  @csrf
                  <div class="modal-body pt-4">
                     <div class="mb-4">
                        <label class="form-label fw-bold">Parent Application <span class="text-danger">*</span></label>
                        <select name="aplikasi_id" class="form-select select2-modal" required
                           data-placeholder="Select Application">
                           <option value=""></option>
                           @foreach ($aplikasi as $app)
                              <option value="{{ $app->id }}">{{ $app->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="mb-4">
                        <label class="form-label fw-bold">Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                           placeholder="e.g. Master Data Tyre">
                     </div>
                     <div class="mb-4">
                        <label class="form-label fw-bold">Target URL / Route <span class="text-danger">*</span></label>
                        <input type="text" name="url" class="form-control" required
                           placeholder="e.g. tyre/master-data">
                        <div class="form-text small">Internal path without base URL.</div>
                     </div>
                     <div class="mb-0">
                        <label class="form-label fw-bold">Menu Icon Class</label>
                        <div class="input-group input-group-merge">
                           <span class="input-group-text"><i class="icon-base ri ri-remixicon-line"></i></span>
                           <input type="text" name="icon" class="form-control" placeholder="ri-circle-line"
                              value="ri-circle-line">
                        </div>
                        <div class="form-text small">Use <a href="https://remixicon.com/" target="_blank">Remix Icons</a>
                           classes.</div>
                     </div>
                  </div>
                  <div class="modal-footer border-top-0 pt-0 pb-4 justify-content-center">
                     <button type="button" class="btn btn-outline-secondary px-4 me-2"
                        data-bs-dismiss="modal">Cancel</button>
                     <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Menu Item</button>
                  </div>
               </form>
            </div>
         </div>
      </div>

      <!-- Edit Menu Modal -->
      <div class="modal fade" id="editMenuModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
               <div class="modal-header bg-label-primary py-3">
                  <h5 class="modal-title"><i class="icon-base ri ri-edit-box-line me-2 text-primary"></i>Update Menu Item
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form id="editMenuForm" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-body pt-4">
                     <div class="mb-4">
                        <label class="form-label fw-bold">Application</label>
                        <select name="aplikasi_id" id="edit_aplikasi_id" class="form-select select2-modal" required>
                           @foreach ($aplikasi as $app)
                              <option value="{{ $app->id }}">{{ $app->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Display Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                     </div>
                     <div class="mb-4">
                        <label class="form-label fw-bold">Target URL</label>
                        <input type="text" name="url" id="edit_url" class="form-control" required>
                     </div>
                     <div class="mb-0">
                        <label class="form-label fw-bold text-primary">Preview Icon</label>
                        <div class="input-group input-group-merge">
                           <span class="input-group-text" id="iconPreview"><i
                                 class="icon-base ri ri-circle-line"></i></span>
                           <input type="text" name="icon" id="edit_icon" class="form-control">
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer border-top-0 pt-0 pb-4 justify-content-center">
                     <button type="button" class="btn btn-label-secondary px-4 me-2"
                        data-bs-dismiss="modal">Discard</button>
                     <button type="submit" class="btn btn-primary px-4 shadow-sm">Update Menu</button>
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
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('.datatables-menus').DataTable({
            order: [
               [1, 'asc'],
               [0, 'asc']
            ],
            dom: '<"card-header d-flex flex-wrap pb-0 pt-0"<"col-md-6"f><"col-md-6 text-end"l>>t<"card-footer d-flex flex-wrap"<"col-md-6"i><"col-md-6 text-end"p>>',
            language: {
               search: '',
               searchPlaceholder: 'Search Menus...'
            }
         });

         if ($.fn.select2) {
            $('.select2-modal').select2({
               dropdownParent: $('.modal')
            });
         }

         $('#edit_icon').on('input', function() {
            $('#iconPreview i').attr('class', $(this).val() || 'ri-circle-line');
         });

         // Edit Menu
         $(document).on('click', '.edit-menu', function() {
            const id = $(this).data('id');
            const baseUrl = '{{ url('cph_dashboard/menus') }}';
            const btn = $(this);
            btn.prop('disabled', true);

            $.get(baseUrl + '/' + id + '/edit', function(menu) {
               $('#editMenuForm').attr('action', baseUrl + '/' + id);
               $('#edit_aplikasi_id').val(menu.aplikasi_id).trigger('change');
               $('#edit_name').val(menu.name);
               $('#edit_url').val(menu.url);
               $('#edit_icon').val(menu.icon);
               $('#iconPreview i').attr('class', menu.icon || 'ri-circle-line');
               $('#editMenuModal').modal('show');
               btn.prop('disabled', false);
            });
         });

         $(document).on('click', '.delete-menu', function() {
            const id = $(this).data('id');
            const name = $(this).closest('tr').find('.fw-bold').text();

            Swal.fire({
               title: 'Delete "' + name + '"?',
               text: "Warning! Removing a menu might affect user navigation access.",
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Yes, Delete',
               cancelButtonText: 'Cancel',
               customClass: {
                  confirmButton: 'btn btn-danger me-3 waves-effect waves-light',
                  cancelButton: 'btn btn-outline-secondary waves-effect'
               },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  const form = document.getElementById('deleteForm');
                  form.action = '{{ url('cph_dashboard/menus') }}/' + id;
                  form.submit();
               }
            });
         });
      });
   </script>
@endsection
