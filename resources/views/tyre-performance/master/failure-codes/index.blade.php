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
         @if (hasPermission('Failure Codes', 'create'))
            <a href="{{ route('tyre-failure-codes.create') }}" class="btn btn-primary">
               <i class="icon-base ri ri-add-line me-1"></i> Add Failure Code
            </a>
         @endif
      </div>

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-failures table border-top table-hover">
               <thead>
                  <tr>
                     <th>Code</th>
                     <th>Name</th>
                     <th>Image</th>
                     <th>Category</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @foreach ($failureCodes as $fc)
                     <tr>
                        <td><strong>{{ $fc->failure_code }}</strong></td>
                        <td>
                           @if ($fc->display_name)
                              <span class="fw-bold text-primary">{{ $fc->display_name }}</span><br>
                              <small class="text-muted">({{ $fc->failure_name }})</small>
                           @else
                              {{ $fc->failure_name }}
                           @endif
                        </td>
                        <td>
                           @if ($fc->image_1)
                              <a href="javascript:void(0);"
                                 onclick="showImagePreview('{{ asset('storage/' . $fc->image_1) }}')">
                                 <img src="{{ asset('storage/' . $fc->image_1) }}" alt="Img 1" class="rounded"
                                    width="100" height="100" style="object-fit: cover;">
                              </a>
                           @endif
                           @if ($fc->image_2)
                              <a href="javascript:void(0);"
                                 onclick="showImagePreview('{{ asset('storage/' . $fc->image_2) }}')">
                                 <img src="{{ asset('storage/' . $fc->image_2) }}" alt="Img 2" class="rounded ms-1"
                                    width="100" height="100" style="object-fit: cover;">
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
                           <span class="badge bg-label-{{ $fc->status == 'Active' ? 'success' : 'secondary' }}">
                              {{ $fc->status }}
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
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('.datatables-failures').DataTable({
            order: [
               [0, 'desc']
            ],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
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
                  form.action = `{{ url('master_data_tyre/master_failure_code') }}/${id}`;
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
