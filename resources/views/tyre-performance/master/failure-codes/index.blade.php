@extends('layouts.admin')

@section('title', 'Master Tyre Failure Codes')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Failure Codes</h4>
         <a href="{{ route('tyre-failure-codes.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> Add Failure Code
         </a>
      </div>

      @if (session('success'))
         <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      <div class="card">
         <div class="table-responsive text-nowrap">
            <table class="table table-hover">
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
                  @forelse($failureCodes as $fc)
                     <tr>
                        <td><strong>{{ $fc->failure_code }}</strong></td>
                        <td>{{ $fc->failure_name }}</td>
                        <td>
                           @if ($fc->image_1)
                              <a href="javascript:void(0);" onclick="showImagePreview('{{ asset('storage/' . $fc->image_1) }}')">
                                 <img src="{{ asset('storage/' . $fc->image_1) }}" alt="Img 1" class="rounded" width="100"
                                    height="100" style="object-fit: cover;">
                              </a>
                           @endif
                           @if ($fc->image_2)
                              <a href="javascript:void(0);" onclick="showImagePreview('{{ asset('storage/' . $fc->image_2) }}')">
                                 <img src="{{ asset('storage/' . $fc->image_2) }}" alt="Img 2" class="rounded ms-1" width="100"
                                    height="100" style="object-fit: cover;">
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
                              <a href="{{ route('tyre-failure-codes.edit', $fc->id) }}"
                                 class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1"
                                 title="Edit">
                                 <i class="ri-pencil-line"></i>
                              </a>
                              <form action="{{ route('tyre-failure-codes.destroy', $fc->id) }}" method="POST"
                                 onsubmit="return confirm('Are you sure?')" class="d-inline">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light"
                                    title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                 </button>
                              </form>
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="6" class="text-center">No data found</td>
                     </tr>
                  @endforelse
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

@endsection

@section('page-script')
   <script>
      function showImagePreview(src) {
         $('#previewImage').attr('src', src);
         $('#imagePreviewModal').modal('show');
      }
   </script>
@endsection