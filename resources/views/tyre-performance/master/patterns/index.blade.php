@extends('layouts.admin')

@section('title', 'Master Tyre Patterns')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Patterns</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatternModal">
            <i class="ri-add-line me-1"></i> Add Pattern
         </button>
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
                     <th>Pattern Name</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @forelse($patterns as $pattern)
                     <tr>
                        <td><strong>{{ $pattern->name }}</strong></td>
                        <td>
                           <div class="d-flex align-items-center">
                              <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-pattern" 
                                 href="javascript:void(0);" data-bs-toggle="modal"
                                 data-bs-target="#editPatternModal" data-id="{{ $pattern->id }}"
                                 data-name="{{ $pattern->name }}" title="Edit">
                                 <i class="ri-pencil-line"></i>
                              </a>
                              <form action="{{ route('tyre-patterns.destroy', $pattern->id) }}" method="POST"
                                 onsubmit="return confirm('Are you sure?')" class="d-inline">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light" title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                 </button>
                              </form>
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="2" class="text-center">No data found</td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- Add Pattern Modal -->
   <div class="modal fade" id="addPatternModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add New Pattern</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-patterns.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="name" class="form-label">Pattern Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                           placeholder="e.g. Rough Terrain" required>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save changes</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Edit Pattern Modal -->
   <div class="modal fade" id="editPatternModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Pattern</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPatternForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_name" class="form-label">Pattern Name</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Update changes</button>
               </div>
            </form>
         </div>
      </div>
   </div>

@endsection

@section('page-script')
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const editButtons = document.querySelectorAll('.edit-pattern');
         const editForm = document.querySelector('#editPatternForm');

         editButtons.forEach(button => {
            button.addEventListener('click', function() {
               const id = this.getAttribute('data-id');
               const name = this.getAttribute('data-name');

               editForm.action = `/tyre_performance/master/patterns/${id}`;
               document.querySelector('#edit_name').value = name;
            });
         });
      });
   </script>
@endsection
