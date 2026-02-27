@extends('layouts.admin')

@section('title', 'Master Tyre Failure Codes')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Failure Codes</h4>
         <div class="d-flex gap-2">
            <a href="{{ route('master_data.export', ['type' => 'failure_codes', 'format' => 'excel']) }}"
               class="btn btn-outline-primary">
               <i class="ri-file-excel-2-line me-1"></i> Export Excel
            </a>
            @if (hasPermission('Failure Codes', 'create'))
               <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                  data-bs-target="#importModal">
                  <i class="ri-upload-2-line me-1"></i> Import
               </button>
               <a href="{{ route('tyre-failure-codes.create') }}" class="btn btn-primary">
                  <i class="icon-base ri ri-add-line me-1"></i> Add Failure Code
               </a>
            @endif
         </div>
      </div>

      <div class="card mb-4">
         <div class="card-body">
            <div class="row align-items-center">
               <div class="col-md-4">
                  <label class="form-label fw-bold">View as Company (Preview Aliases)</label>
                  <select id="companyFilter" class="form-select select2">
                     <option value="">Default (Master Names)</option>
                     @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                     @endforeach
                  </select>
               </div>
               <div class="col-md-8 text-muted small">
                  <i class="ri-information-line me-1"></i> Pilih instansi untuk melihat bagaimana nama kode kerusakan
                  muncul pada dashboard mereka.
               </div>
            </div>
         </div>
      </div>

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-failures table border-top table-hover">
               <thead>
                  <tr>
                     <th>Code</th>
                     <th>Name</th>
                     <th>Company Aliases</th>
                     <th>Image</th>
                     <th>Category</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @foreach ($failureCodes as $fc)
                     <tr>
                        <td><strong>{{ $fc->failure_code }}</strong></td>
                        <td class="failure-name-cell" data-master-name="{{ $fc->failure_name }}"
                           data-aliases="{{ json_encode($fc->aliases->pluck('alias_name', 'tyre_company_id')) }}">
                           <span class="main-name">{{ $fc->failure_name }}</span>
                           @if ($fc->display_name)
                              <div class="small text-muted">Original: {{ $fc->display_name }}</div>
                           @endif
                        </td>
                        <td>
                           @if ($fc->aliases->count() > 0)
                              <div class="d-flex flex-wrap gap-1">
                                 @foreach ($fc->aliases->take(2) as $alias)
                                    <span class="badge bg-label-info border" title="{{ $alias->company->company_name }}">
                                       {{ $alias->alias_name }}
                                    </span>
                                 @endforeach
                                 @if ($fc->aliases->count() > 2)
                                    <span class="badge bg-label-secondary">+{{ $fc->aliases->count() - 2 }} More</span>
                                 @endif
                              </div>
                           @else
                              <small class="text-muted">No Aliases</small>
                           @endif
                        </td>
                        <td>
                           @if ($fc->image_1)
                              <a href="javascript:void(0);"
                                 onclick="showImagePreview('{{ asset('storage/' . $fc->image_1) }}')">
                                 <img src="{{ asset('storage/' . $fc->image_1) }}" alt="Img 1" class="rounded"
                                    width="80" height="80" style="object-fit: cover;">
                              </a>
                           @endif
                           @if ($fc->image_2)
                              <a href="javascript:void(0);"
                                 onclick="showImagePreview('{{ asset('storage/' . $fc->image_2) }}')">
                                 <img src="{{ asset('storage/' . $fc->image_2) }}" alt="Img 2" class="rounded ms-1"
                                    width="80" height="80" style="object-fit: cover;">
                              </a>
                           @endif
                           @if (!$fc->image_1 && !$fc->image_2)
                              <span class="text-muted">-</span>
                           @endif
                        </td>
                        <td>
                           <span
                              class="badge bg-label-{{ $fc->default_category == 'Scrap' ? 'danger' : ($fc->default_category == 'Repair' ? 'warning' : 'primary') }}">
                              {{ $fc->default_category }}
                           </span>
                        </td>
                        <td>
                           <div class="d-flex align-items-center">
                              <a href="{{ route('tyre-failure-codes.show', $fc->id) }}"
                                 class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1"
                                 title="View Detail (Guidebook)">
                                 <i class="icon-base ri ri-eye-line"></i>
                              </a>
                              @if (hasPermission('Failure Codes', 'update'))
                                 <button type="button"
                                    class="btn btn-sm btn-icon btn-text-info rounded-pill waves-effect waves-light me-1 manage-alias"
                                    data-id="{{ $fc->id }}" data-code="{{ $fc->failure_code }}"
                                    data-name="{{ $fc->failure_name }}" title="Manage Company Aliases">
                                    <i class="icon-base ri ri-price-tag-3-line"></i>
                                 </button>
                                 <a href="{{ route('tyre-failure-codes.edit', $fc->id) }}"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1"
                                    title="Edit">
                                    <i class="icon-base ri ri-pencil-line"></i>
                                 </a>
                              @endif
                              @if (hasPermission('Failure Codes', 'delete'))
                                 <button type="button"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-failure"
                                    data-id="{{ $fc->id }}" data-code="{{ $fc->failure_code }}" title="Delete">
                                    <i class="icon-base ri ri-delete-bin-line"></i>
                                 </button>
                              @endif
                           </div>
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- Quick Manage Alias Modal -->
   <div class="modal fade" id="manageAliasModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header border-bottom">
               <h5 class="modal-title">Set Company Alias</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-failure-aliases.store') }}" method="POST">
               @csrf
               <input type="hidden" name="tyre_failure_code_id" id="alias_fc_id">
               <div class="modal-body py-4">
                  <div class="mb-3">
                     <label class="form-label text-muted small">Failure Code</label>
                     <div class="fw-bold" id="alias_fc_display"></div>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">Select Company</label>
                     <select name="tyre_company_id" class="form-select select2-modal" required
                        data-placeholder="Choose Company...">
                        <option value=""></option>
                        @foreach ($companies as $company)
                           <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-0">
                     <label class="form-label fw-bold">Custom Alias Name</label>
                     <input type="text" name="alias_name" class="form-control" required
                        placeholder="E.g. Samping Sobek">
                  </div>
               </div>
               <div class="modal-footer bg-light py-2">
                  <button type="button" class="btn btn-outline-secondary btn-sm"
                     data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary btn-sm">Save Alias</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Image Preview Modal -->
   <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Image Preview</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
               <img src="" id="previewImage" class="img-fluid rounded">
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
         // Initialize Select2 for filters
         $('#companyFilter').select2({
            width: '100%'
         }).on('change', function() {
            const companyId = $(this).val();

            $('.failure-name-cell').each(function() {
               const masterName = $(this).data('master-name');
               const aliases = $(this).data('aliases') || {};
               const mainSpan = $(this).find('.main-name');

               if (companyId && aliases[companyId]) {
                  mainSpan.html(
                     `<span class="text-primary fw-bold"><i class="ri-price-tag-3-fill me-1 small"></i>${aliases[companyId]}</span>`
                  );
               } else {
                  mainSpan.text(masterName);
               }
            });
         });

         $('.datatables-failures').DataTable({
            order: [
               [0, 'desc']
            ],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
         });

         // Initialize Select2 for the modal
         $('.select2-modal').select2({
            dropdownParent: $('#manageAliasModal'),
            width: '100%'
         });

         $(document).on('click', '.manage-alias', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');
            const name = $(this).data('name');

            $('#alias_fc_id').val(id);
            $('#alias_fc_display').text(`${code} - ${name}`);
            $('#manageAliasModal').modal('show');
         });

         $(document).on('click', '.delete-failure', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `Kode Kerusakan "${code}" akan dihapus permanen!`,
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
                  form.action = `{{ url('master_failure_code') }}/${id}`;
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

         @if (session('error'))
            Swal.fire({
               icon: 'error',
               title: 'Oops...',
               text: '{{ session('error') }}',
            });
         @endif
      });

      function showImagePreview(src) {
         $('#previewImage').attr('src', src);
         $('#imagePreviewModal').modal('show');
      }
   </script>
@endsection
