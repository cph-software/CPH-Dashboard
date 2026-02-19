@extends('layouts.admin')

@section('title', 'Edit Role: ' . $role->name)

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <div class="d-flex align-items-center">
            <div class="avatar avatar-lg me-3">
               <span class="avatar-initial rounded bg-label-primary shadow-sm">
                  <i class="icon-base ri ri-shield-keyhole-line ri-24px"></i>
               </span>
            </div>
            <div>
               <h4 class="mb-0 fw-bold">Edit Role: <span class="text-primary">{{ $role->name }}</span></h4>
               <p class="mb-0 text-muted small">Configure role name and granular menu permissions</p>
            </div>
         </div>
         <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="icon-base ri ri-arrow-go-back-line me-1"></i> Back to List
         </a>
      </div>

      <form action="{{ route('roles.update', $role->id) }}" method="POST">
         @csrf
         @method('PUT')
         <div class="row">
            <!-- Left Side: Basic Info -->
            <div class="col-xl-4 col-lg-5">
               <div class="card shadow-sm border-0 mb-4">
                  <div class="card-header bg-lighter border-bottom">
                     <h5 class="card-title mb-0"><i class="icon-base ri ri-information-line me-2"></i>Role Information</h5>
                  </div>
                  <div class="card-body pt-4">
                     <div class="mb-3">
                        <label class="form-label fw-bold" for="roleName">Role Name</label>
                        <input type="text" id="roleName" name="name"
                           class="form-control @error('name') is-invalid @enderror" placeholder="Example: Manager Tyre"
                           value="{{ old('name', $role->name) }}" required>
                        @error('name')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                     <div class="alert alert-info py-2 px-3 small border-0 mb-0">
                        <i class="icon-base ri ri-lightbulb-line me-1"></i> Role names should be descriptive of the user's
                        function
                        in
                        the system.
                     </div>
                  </div>
               </div>

               <div class="card shadow-sm border-0">
                  <div class="card-body">
                     <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                           <i class="icon-base ri ri-save-line me-1"></i> Update Role & Permissions
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-label-secondary">Cancel Changes</a>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Right Side: Permissions Matrix -->
            <div class="col-xl-8 col-lg-7">
               <div class="card shadow-sm border-0">
                  <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                     <h5 class="card-title mb-0"><i class="icon-base ri ri-lock-password-line me-2 text-primary"></i>Menu
                        Permissions
                        Matrix</h5>
                     <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label fw-bold text-primary" for="selectAll" style="cursor: pointer">Select
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
                                 <tr class="bg-label-primary">
                                    <td colspan="7" class="ps-4 py-2 border-top-0">
                                       <div class="d-flex justify-content-between align-items-center">
                                          <span
                                             class="fw-bold text-uppercase small letter-spacing-1">{{ $app->name }}</span>
                                          <div class="form-check mb-0">
                                             @php
                                                $appMenuIds = $app->menus->pluck('id')->toArray();
                                                $allMenusChecked =
                                                    !empty($appMenuIds) && empty(array_diff($appMenuIds, $roleMenuIds));
                                             @endphp
                                             <input class="form-check-input select-app" type="checkbox"
                                                data-app-id="{{ $app->id }}" id="app_{{ $app->id }}"
                                                {{ $allMenusChecked ? 'checked' : '' }}>
                                             <label class="form-check-label small fw-bold" for="app_{{ $app->id }}"
                                                style="cursor: pointer">All {{ $app->name }}</label>
                                          </div>
                                       </div>
                                    </td>
                                 </tr>
                                 @forelse ($app->menus as $menu)
                                    @php
                                       // Find existing permissions for this menu in this role
                                       $roleMenu = $role->menus->where('id', $menu->id)->first();
                                       $existingPerms = [];
                                       if ($roleMenu && $roleMenu->pivot->permissions) {
                                           $existingPerms = json_decode($roleMenu->pivot->permissions, true) ?: [];
                                       }
                                       $hasAccess = in_array($menu->id, $roleMenuIds);
                                    @endphp
                                    <tr class="menu-row" data-menu-id="{{ $menu->id }}">
                                       <td class="ps-4">
                                          <div class="d-flex align-items-center">
                                             <input class="form-check-input menu-main-check app-{{ $app->id }} me-2"
                                                type="checkbox" name="menu_ids[]" value="{{ $menu->id }}"
                                                id="menu_{{ $menu->id }}" {{ $hasAccess ? 'checked' : '' }}>
                                             <label for="menu_{{ $menu->id }}" class="fw-medium text-heading mb-0"
                                                style="cursor: pointer">{{ $menu->name }}</label>
                                          </div>
                                       </td>
                                       @foreach (['view', 'create', 'update', 'delete', 'export', 'import'] as $perm)
                                          <td class="text-center">
                                             <div class="form-check d-inline-block">
                                                <input
                                                   class="form-check-input perm-check perm-{{ $perm }} menu-perm-{{ $menu->id }}"
                                                   type="checkbox" name="menu_permissions[{{ $menu->id }}][]"
                                                   value="{{ $perm }}"
                                                   {{ in_array($perm, $existingPerms) ? 'checked' : '' }}>
                                             </div>
                                          </td>
                                       @endforeach
                                    </tr>
                                 @empty
                                    <tr>
                                       <td colspan="7" class="text-center py-4 text-muted fst-italic">No menus listed in
                                          this application</td>
                                    </tr>
                                 @endforelse
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </form>
   </div>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         const selectAll = $('#selectAll');
         const menuChecks = $('.menu-main-check');
         const permChecks = $('.perm-check');
         const appSelectors = $('.select-app');

         // Handle Global Select All
         selectAll.on('change', function() {
            const isChecked = this.checked;
            menuChecks.prop('checked', isChecked);
            permChecks.prop('checked', isChecked);
            appSelectors.prop('checked', isChecked);
         });

         // Handle App Level Select All
         appSelectors.on('change', function() {
            const appId = $(this).data('app-id');
            const isChecked = this.checked;
            $(`.app-${appId}`).prop('checked', isChecked);

            // Also check all permissions for those menus
            $(`.app-${appId}`).each(function() {
               const menuId = $(this).val();
               $(`.menu-perm-${menuId}`).prop('checked', isChecked);
            });
            updateGlobalSelectAll();
         });

         // Handle Menu Checkbox changes
         menuChecks.on('change', function() {
            const menuId = $(this).val();
            const isChecked = this.checked;

            // Auto check 'view' if menu is checked, or uncheck all perms if unchecked
            if (isChecked) {
               $(`.menu-perm-${menuId}.perm-view`).prop('checked', true);
            } else {
               $(`.menu-perm-${menuId}`).prop('checked', false);
            }

            updateAppSelector($(this));
            updateGlobalSelectAll();
         });

         // Handle Individual Permission changes
         permChecks.on('change', function() {
            const row = $(this).closest('.menu-row');
            const menuCheck = row.find('.menu-main-check');

            // If any permission is checked, menu must be checked
            if (row.find('.perm-check:checked').length > 0) {
               menuCheck.prop('checked', true);
            } else {
               menuCheck.prop('checked', false);
            }

            updateAppSelector(menuCheck);
            updateGlobalSelectAll();
         });

         function updateAppSelector(menuCheck) {
            const classList = menuCheck.attr('class').split(/\s+/);
            const appClass = classList.find(c => c.startsWith('app-'));
            if (appClass) {
               const appId = appClass.split('-')[1];
               const appSelector = $(`#app_${appId}`);
               const allAppMenusData = $(`.${appClass}`);
               const allChecked = allAppMenusData.length === allAppMenusData.filter(':checked').length;
               appSelector.prop('checked', allChecked);
            }
         }

         function updateGlobalSelectAll() {
            const allChecked = menuChecks.length === menuChecks.filter(':checked').length;
            selectAll.prop('checked', allChecked);
         }
      });
   </script>
@endsection
