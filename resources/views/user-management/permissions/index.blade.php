@extends('layouts.admin')

@section('title', 'System Permission Matrix')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <div>
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-lock-password-line me-2 text-primary"></i>Permission Matrix
            </h4>
            <p class="text-muted mb-0 small">Manage granular access control across all applications and menus.</p>
         </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
         <div class="card-body">
            <div class="row align-items-center">
               <div class="col-md-5">
                  <label class="form-label fw-bold">Select Role to Configure</label>
                  <select id="roleSelector" class="select2 form-select shadow-xs">
                     <option value="">-- Click to choose a role --</option>
                     @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                     @endforeach
                  </select>
               </div>
               <div class="col-md-7 text-md-end pt-3 pt-md-0">
                  <div class="alert alert-soft-primary mb-0 py-2 border-0 small">
                     <i class="icon-base ri ri-information-line me-1"></i> Changes will take effect immediately for players
                     after
                     saving.
                  </div>
               </div>
            </div>
         </div>
      </div>

      <form id="permissionForm" action="{{ route('permissions.store') }}" method="POST" style="display: none;">
         @csrf
         <input type="hidden" name="role_id" id="hiddenRoleId">

         <div class="card shadow-sm border-0 mb-4">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
               <h5 class="card-title mb-0"><i class="icon-base ri ri-apps-2-line me-2"></i>Navigation Access Matrix</h5>
               <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="selectAllGlobal">
                  <label class="form-check-label fw-bold text-primary" for="selectAllGlobal" style="cursor: pointer">Select
                     All</label>
               </div>
            </div>
            <div class="card-body p-0">
               <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                     <thead class="table-light">
                        <tr>
                           <th style="min-width: 250px" class="ps-4">Menu Name</th>
                           <th class="text-center">View</th>
                           <th class="text-center">Create</th>
                           <th class="text-center">Update</th>
                           <th class="text-center">Delete</th>
                           <th class="text-center">Export</th>
                           <th class="text-center">Import</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach ($aplikasi as $app)
                           <tr class="bg-label-primary app-header" data-app-id="{{ $app->id }}">
                              <td colspan="7" class="fw-bold px-4 py-2 text-uppercase small letter-spacing-1">
                                 <i class="icon-base ri ri-folder-open-line me-1"></i> {{ $app->name }}
                              </td>
                           </tr>
                           @foreach ($app->menus()->orderBy('name')->get() as $menu)
                              @include('user-management.permissions._menu_row', ['menu' => $menu])
                           @endforeach
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="card-footer border-top bg-lighter text-end py-3">
               <button type="submit" class="btn btn-primary px-5 shadow-sm btn-lg">
                  <i class="icon-base ri ri-save-line me-1"></i> Save Permission Matrix
               </button>
            </div>
         </div>
      </form>

      <div id="noRoleSelected" class="card shadow-sm border-0 text-center py-5">
         <div class="card-body">
            <div class="avatar avatar-xl bg-label-secondary mx-auto mb-4" style="width: 100px; height: 100px">
               <span class="avatar-initial rounded-circle"><i class="icon-base ri ri-shield-keyhole-line ri-4x"></i></span>
            </div>
            <h4 class="text-secondary fw-bold">No Role Selected</h4>
            <p class="text-muted mx-auto" style="max-width: 400px">Please select a user role from the dropdown above to
               manage its granular menu permissions and application access.</p>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
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

            // Show loading state if needed
            Swal.fire({
               title: 'Fetching Permissions...',
               allowOutsideClick: false,
               didOpen: () => {
                  Swal.showLoading();
               }
            });

            $.get('{{ route('permissions.get') }}', {
               role_id: roleId
            }, function(data) {
               Swal.close();

               // Reset all checkboxes first
               $permissionForm.find('input[type="checkbox"]').prop('checked', false);

               // Populate granular permissions
               if (data.role_permissions) {
                  Object.keys(data.role_permissions).forEach(menuId => {
                     const perms = data.role_permissions[menuId];
                     $(`#menuCheck_${menuId}`).prop('checked', true);

                     if (Array.isArray(perms)) {
                        perms.forEach(p => {
                           $(`#${p}_${menuId}`).prop('checked', true);
                        });
                     }
                  });
               }

               $noRoleSelected.hide();
               $permissionForm.fadeIn();
            }).fail(function() {
               Swal.fire('Error', 'Failed to fetch permissions', 'error');
            });
         });

         // Global Select All
         $('#selectAllGlobal').on('change', function() {
            const isChecked = this.checked;
            $permissionForm.find('input[type="checkbox"]').prop('checked', isChecked);
         });

         // Menu main check logic
         $(document).on('change', '.menu-main-check', function() {
            const menuId = $(this).val();
            const isChecked = this.checked;
            $(`.menu-perm-${menuId}`).prop('checked', isChecked);
         });

         // Individual permission check logic
         $(document).on('change', '.perm-check', function() {
            const row = $(this).closest('.menu-row');
            const menuCheck = row.find('.menu-main-check');
            if (row.find('.perm-check:checked').length > 0) {
               menuCheck.prop('checked', true);
            } else {
               menuCheck.prop('checked', false);
            }
         });

         @if (session('success'))
            Swal.fire({
               icon: 'success',
               title: 'Updated!',
               text: '{{ session('success') }}',
               timer: 2000,
               showConfirmButton: false
            });
         @endif
      });
   </script>
@endsection
