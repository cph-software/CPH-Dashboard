@extends('layouts.admin')

@section('title', 'Edit Role: ' . $role->name)

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <div class="d-flex align-items-center">
            <i class="ri-shield-keyhole-line ri-2x text-primary me-2"></i>
            <h4 class="mb-0">Edit Role: <span class="text-primary fw-bold">{{ $role->name }}</span></h4>
         </div>
         <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-go-back-line me-1"></i> Back to List
         </a>
      </div>

      <div class="card shadow-sm">
         <div class="card-body">
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
               @csrf
               @method('PUT')
               <div class="row g-4">
                  <div class="col-12">
                     <label class="form-label fw-bold text-heading fs-5" for="roleName">Role Name</label>
                     <input type="text" id="roleName" name="name"
                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                        placeholder="Example: Marketing Manager" value="{{ old('name', $role->name) }}" required autofocus>
                     @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                     <div class="form-text mt-2 text-muted">A unique and descriptive name for this role.</div>
                  </div>

                  <div class="col-12">
                     <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-heading">
                           <i class="ri-menu-2-line me-2 text-primary"></i>
                           Menu Permissions
                        </h5>
                        <div class="form-check">
                           <input class="form-check-input" type="checkbox" id="selectAll">
                           <label class="form-check-label fw-medium text-primary" for="selectAll">Select All Menus</label>
                        </div>
                     </div>

                     <div class="table-responsive rounded border">
                        <table class="table table-hover mb-0">
                           <thead class="table-light">
                              <tr>
                                 <th style="width: 80%">Aplikasi & Menu Name</th>
                                 <th class="text-center">Has Access</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($aplikasi as $app)
                                 <tr class="table-dark">
                                    <td colspan="2" class="px-4 py-2">
                                       <div class="d-flex justify-content-between align-items-center">
                                          <span class="fw-bold">{{ strtoupper($app->name) }}</span>
                                          <div class="form-check">
                                             @php
                                                $appMenuIds = $app->menus->pluck('id')->toArray();
                                                $allMenusChecked =
                                                    !empty($appMenuIds) && empty(array_diff($appMenuIds, $roleMenuIds));
                                             @endphp
                                             <input class="form-check-input select-app" type="checkbox"
                                                data-app-id="{{ $app->id }}" id="app_{{ $app->id }}"
                                                {{ $allMenusChecked ? 'checked' : '' }}>
                                             <label class="form-check-label text-white small"
                                                for="app_{{ $app->id }}">Select All in App</label>
                                          </div>
                                       </div>
                                    </td>
                                 </tr>
                                 @forelse ($app->menus as $menu)
                                    <tr class="border-bottom">
                                       <td class="ps-4">
                                          <i class="ri-arrow-right-s-line me-2 text-primary"></i>
                                          <span class="text-heading">{{ $menu->name }}</span>
                                       </td>
                                       <td class="text-center">
                                          <div class="form-check d-flex justify-content-center">
                                             <input class="form-check-input menu-checkbox app-{{ $app->id }}"
                                                type="checkbox" name="menu_ids[]" value="{{ $menu->id }}"
                                                id="menuCheck{{ $menu->id }}"
                                                {{ in_array($menu->id, $roleMenuIds) ? 'checked' : '' }} />
                                          </div>
                                       </td>
                                    </tr>
                                 @empty
                                    <tr>
                                       <td colspan="2" class="text-center py-3 text-muted italic">No menus available for
                                          this application</td>
                                    </tr>
                                 @endforelse
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  </div>

                  <div class="col-12 pt-3 border-top mt-5">
                     <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-lg px-5">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">Update Role</button>
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
         const appSelectors = document.querySelectorAll('.select-app');

         // Select All Overall
         if (selectAll) {
            selectAll.addEventListener('change', function() {
               checkboxes.forEach(cb => cb.checked = selectAll.checked);
               // Also update app selectors
               appSelectors.forEach(as => as.checked = selectAll.checked);
            });

            // Set initial state of "Select All"
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            if (checkboxes.length > 0) selectAll.checked = allChecked;
         }

         // Select All per App
         appSelectors.forEach(as => {
            as.addEventListener('change', function() {
               const appId = this.getAttribute('data-app-id');
               const appCheckboxes = document.querySelectorAll(`.app-${appId}`);
               appCheckboxes.forEach(cb => cb.checked = this.checked);

               // Update overall select all
               updateOverallSelectAll();
            });
         });

         // Update App selector if all menu checkboxes are manually checked
         checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
               const classList = Array.from(this.classList);
               const appClass = classList.find(c => c.startsWith('app-'));
               if (appClass) {
                  const appId = appClass.split('-')[1];
                  const appSelector = document.querySelector(`#app_${appId}`);
                  const appCheckboxes = document.querySelectorAll(`.${appClass}`);
                  const allChecked = Array.from(appCheckboxes).every(acb => acb.checked);
                  if (appSelector) appSelector.checked = allChecked;
               }
               updateOverallSelectAll();
            });
         });

         function updateOverallSelectAll() {
            if (selectAll) {
               const allChecked = Array.from(checkboxes).every(cb => cb.checked);
               selectAll.checked = allChecked;
            }
         }
      });
   </script>
@endsection
