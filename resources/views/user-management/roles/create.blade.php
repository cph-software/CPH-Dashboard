@extends('layouts.admin')

@section('title', 'Add New Role')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="mb-0">Add New Role</h4>
         <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-go-back-line me-1"></i> Back to List
         </a>
      </div>

      <div class="card">
         <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
               @csrf
               <div class="row g-4">
                  <div class="col-12">
                     <label class="form-label fw-bold text-heading fs-5" for="roleName">Role Name</label>
                     <input type="text" id="roleName" name="name"
                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                        placeholder="Example: Marketing Manager" value="{{ old('name') }}" required autofocus>
                     @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                     <div class="form-text mt-2 text-muted">A unique and descriptive name for this role.</div>
                  </div>

                  <div class="col-12">
                     <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="ri-shield-keyhole-line me-2"></i> Role Permissions</h5>
                        <div class="form-check">
                           <input class="form-check-input" type="checkbox" id="selectAll">
                           <label class="form-check-label fw-medium" for="selectAll">Select All Permissions</label>
                        </div>
                     </div>

                     <div class="table-responsive rounded border">
                        <table class="table table-hover mb-0">
                           <thead class="table-light">
                              <tr>
                                 <th style="width: 80%">Menu Name</th>
                                 <th class="text-center">Has Access</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($menus as $menu)
                                 <tr class="table-light border-bottom">
                                    <td class="text-nowrap fw-bold text-heading">
                                       <i class="ri-folder-shared-line me-2 text-primary"></i>
                                       {{ $menu->name }}
                                    </td>
                                    <td class="text-center">
                                       <div class="form-check d-flex justify-content-center">
                                          <input class="form-check-input menu-checkbox" type="checkbox" name="menu_ids[]"
                                             value="{{ $menu->id }}" id="menuCheck{{ $menu->id }}" />
                                       </div>
                                    </td>
                                 </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  </div>

                  <div class="col-12 pt-3">
                     <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-outline-secondary btn-lg px-5">Reset</button>
                        <button type="submit" class="btn btn-primary btn-lg px-5">Create Role</button>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
@endsection

@section('page-script')
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const selectAll = document.querySelector('#selectAll');
         const checkboxes = document.querySelectorAll('.menu-checkbox');

         if (selectAll) {
            selectAll.addEventListener('change', function() {
               checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });
         }
      });
   </script>
@endsection
