@extends('layouts.admin')

@section('title', 'Role Permissions')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="mb-4">Role Permissions Management</h4>

      <div class="card mb-4">
         <div class="card-body">
            <div class="row align-items-end">
               <div class="col-md-6 mb-3 mb-md-0">
                  <label class="form-label">Select Role</label>
                  <select id="roleSelector" class="select2 form-select">
                     <option value="">-- Select Role --</option>
                     @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                     @endforeach
                  </select>
               </div>
               <div class="col-md-6 text-md-end">
                  <p class="mb-0 text-muted">Manage application access and menu permissions for the selected role.</p>
               </div>
            </div>
         </div>
      </div>

      <form id="permissionForm" action="{{ route('permissions.store') }}" method="POST" style="display: none;">
         @csrf
         <input type="hidden" name="role_id" id="hiddenRoleId">

         <!-- Applications Access -->
         <div class="card mb-4">
            <div class="card-header border-bottom">
               <h5 class="card-title mb-0">Application Access</h5>
               <small class="text-muted">Select which applications this role can access.</small>
            </div>
            <div class="card-body pt-4">
               <div class="row g-3">
                  @foreach ($aplikasi as $app)
                     <div class="col-md-3">
                        <div class="form-check custom-option custom-option-basic">
                           <label class="form-check-label custom-option-content" for="app{{ $app->id }}">
                              <input class="form-check-input app-checkbox" type="checkbox" name="aplikasi_ids[]"
                                 value="{{ $app->id }}" id="app{{ $app->id }}">
                              <span class="custom-option-header">
                                 <span class="h6 mb-0">{{ $app->name }}</span>
                              </span>
                           </label>
                        </div>
                     </div>
                  @endforeach
               </div>
            </div>
         </div>

         <!-- Menus Permissions -->
         <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
               <h5 class="card-title mb-0">Menu Permissions</h5>
               <div>
                  <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllPermissions">Select
                     All</button>
               </div>
            </div>
            <div class="card-body p-0">
               <div class="table-responsive">
                  <table class="table table-hover table-sm">
                     <thead class="table-light">
                        <tr>
                           <th style="width: 70%">Menu Name</th>
                           <th class="text-center">Has Access</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach ($aplikasi as $app)
                           <tr class="table-dark app-header" data-app-id="{{ $app->id }}">
                              <td colspan="2" class="fw-bold px-4 py-2">{{ strtoupper($app->name) }}</td>
                           </tr>
                           @foreach ($app->menus()->orderBy('name')->get() as $menu)
                              @include('user-management.permissions._menu_row', [
                                  'menu' => $menu,
                              ])
                           @endforeach
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="card-footer border-top text-end">
               <button type="submit" class="btn btn-primary px-5">Save Changes</button>
            </div>
         </div>
      </form>

      <div id="noRoleSelected" class="text-center py-5">
         <i class="ri-shield-user-line ri-5x text-muted mb-3 d-block"></i>
         <h5 class="text-muted">Please select a role to continue</h5>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         const $roleSelector = $('#roleSelector');
         const $permissionForm = $('#permissionForm');
         const $noRoleSelected = $('#noRoleSelected');
         const $hiddenRoleId = $('#hiddenRoleId');

         if ($.fn.select2) {
            $('.select2').select2();
         }

         $roleSelector.on('change', function() {
            const roleId = $(this).val();
            if (!roleId) {
               $permissionForm.hide();
               $noRoleSelected.show();
               return;
            }

            $hiddenRoleId.val(roleId);

            $.get('{{ route('permissions.get') }}', {
               role_id: roleId
            }, function(data) {
               $('input[type="checkbox"]').prop('checked', false);

               data.role_app_ids.forEach(id => {
                  $(`#app${id}`).prop('checked', true);
               });

               data.role_menu_ids.forEach(id => {
                  $(`#menuCheck_${id}`).prop('checked', true);
               });

               $noRoleSelected.hide();
               $permissionForm.fadeIn();
            });
         });

         $('#selectAllPermissions').on('click', function() {
            const anyUnchecked = $('.menu-checkbox').length > $('.menu-checkbox:checked').length;
            $('.menu-checkbox').prop('checked', anyUnchecked);
         });
      });
   </script>
@endsection
