<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
   id="layout-navbar">
   <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
      <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
         <i class="icon-base ri ri-menu-line icon-24px"></i>
      </a>
   </div>

   <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
      <!-- Search -->
      <div class="navbar-nav align-items-center">
         <div class="nav-item d-flex align-items-center">
            <i class="icon-base ri ri-search-line icon-20px"></i>
            <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2" placeholder="Search..."
               aria-label="Search..." />
         </div>
      </div>
      <!-- /Search -->

      @if (Auth::user() && Auth::user()->role_id == 1)
         @php
            $activeCompanies = \App\Models\TyreCompany::orderBy('company_name', 'asc')->get();
            $currentActiveCompany = session('active_company_id');
         @endphp
         <div class="navbar-nav align-items-center ms-4">
            <div class="nav-item">
               <select class="form-select border-0 shadow-none bg-transparent fw-bold text-primary"
                  id="admin_company_filter" style="cursor: pointer;">
                  <option value="0" {{ !$currentActiveCompany ? 'selected' : '' }}>🏢 All Companies (Global View)
                  </option>
                  @foreach ($activeCompanies as $comp)
                     <option value="{{ $comp->id }}" {{ $currentActiveCompany == $comp->id ? 'selected' : '' }}>
                        🏢 {{ $comp->company_name }}
                     </option>
                  @endforeach
               </select>
            </div>
         </div>

         <script>
            document.addEventListener('DOMContentLoaded', function() {
               const filter = document.getElementById('admin_company_filter');
               if (filter) {
                  filter.addEventListener('change', function() {
                     const companyId = this.value;
                     fetch("{{ route('tyre-movement.set-active-company') }}", {
                           method: 'POST',
                           headers: {
                              'Content-Type': 'application/json',
                              'X-CSRF-TOKEN': '{{ csrf_token() }}'
                           },
                           body: JSON.stringify({
                              tyre_company_id: companyId
                           })
                        })
                        .then(response => response.json())
                        .then(data => {
                           if (data.success) {
                              window.location.reload();
                           }
                        })
                        .catch(error => console.error('Error:', error));
                  });
               }
            });
         </script>
      @endif

      <ul class="navbar-nav flex-row align-items-center ms-auto">
         <!-- Notification -->
         @if(Auth::check() && hasPermission('Error Notification'))
         <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-4 me-xl-1">
            <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
               <i class="icon-base ri ri-notification-3-line icon-22px"></i>
               <span class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border" id="notification-badge" style="display:none;"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end py-0 shadow-sm border-0" style="min-width: 320px;">
               <li class="dropdown-menu-header border-bottom">
                  <div class="dropdown-header d-flex align-items-center py-3">
                     <h6 class="mb-0 me-auto fw-bold">Notifications</h6>
                     <a href="javascript:void(0)" class="text-body" id="mark-all-read" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read">
                        <i class="icon-base ri ri-mail-check-line icon-20px"></i>
                     </a>
                  </div>
               </li>
               <li class="dropdown-notifications-list scrollable-container" style="max-height: 350px; overflow-y: auto;">
                  <ul class="list-group list-group-flush" id="notification-list">
                     <li class="list-group-item list-group-item-action dropdown-notifications-item d-flex align-items-center justify-content-center py-4 border-0">
                        <small class="text-muted"><i class="ri-loader-4-line ri-spin me-2"></i>Loading notifications...</small>
                     </li>
                  </ul>
               </li>
            </ul>
         </li>
         @endif
         <!--/ Notification -->

         <!-- User -->
         <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
               <div class="avatar avatar-online">
                  <img src="{{ asset('template/full-version/assets/img/avatars/1.png') }}" alt
                     class="w-px-40 h-auto rounded-circle" />
               </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
               <li>
                  <a class="dropdown-item" href="#">
                     <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                           <div class="avatar avatar-online">
                              <img src="{{ asset('template/full-version/assets/img/avatars/1.png') }}" alt
                                 class="w-px-40 h-auto rounded-circle" />
                           </div>
                        </div>
                        <div class="flex-grow-1">
                           <h6 class="mb-0">
                              {{ Auth::user() ? Auth::user()->karyawan->full_name ?? Auth::user()->name : 'Guest' }}
                           </h6>
                           <small class="text-muted">
                              {{ Auth::user() && Auth::user()->tyreCompany ? Auth::user()->tyreCompany->company_name : (Auth::user() && Auth::user()->role ? Auth::user()->role->name : 'Visitor') }}
                           </small>
                        </div>
                     </div>
                  </a>
               </li>
               <li>
                  <div class="dropdown-divider"></div>
               </li>
               <li>
                  <a class="dropdown-item" href="#">
                     <i class="icon-base ri ri-user-line me-3 icon-20px"></i><span class="align-middle">My
                        Profile</span>
                  </a>
               </li>
               <li>
                  <div class="dropdown-divider"></div>
               </li>
               <li>
                  <form action="{{ route('logout') }}" method="POST">
                     @csrf
                     <button type="submit" class="dropdown-item">
                        <i class="icon-base ri ri-logout-box-r-line me-3 icon-20px"></i><span class="align-middle">Log
                           Out</span>
                     </button>
                  </form>
               </li>
            </ul>
         </li>
         <!--/ User -->
      </ul>
   </div>
</nav>

@if(Auth::check() && hasPermission('Error Notification'))
<style>
.notification-item { border-left: 3px solid transparent; transition: all 0.2s ease; cursor: pointer; }
.notification-item:hover { background-color: #f8f9fa; border-left: 3px solid var(--bs-primary); }
</style>
<script>
   document.addEventListener('DOMContentLoaded', function() {
      const fetchNotifications = () => {
         fetch('{{ route("notifications.unread") }}', {
            headers: {
               'X-Requested-With': 'XMLHttpRequest',
               'Accept': 'application/json'
            }
         })
         .then(res => res.json())
         .then(data => {
            if (data.success) {
               const badge = document.getElementById('notification-badge');
               const list = document.getElementById('notification-list');
               
               if (data.count > 0) {
                  badge.style.display = 'block';
                  list.innerHTML = '';
                  data.data.forEach(notif => {
                     // Extract warning list
                     let errorsHtml = '';
                     if (notif.details && notif.details['Pesan Error']) {
                        let errs = Array.isArray(notif.details['Pesan Error']) ? notif.details['Pesan Error'] : [notif.details['Pesan Error']];
                        errorsHtml = `<ul class="ps-3 mb-0 mt-1 small text-danger" style="font-size: 0.75rem;">` + errs.map(e => `<li>${e}</li>`).join('') + `</ul>`;
                     }

                     list.innerHTML += `
                        <li class="list-group-item border-bottom notification-item p-3" data-id="${notif.id}">
                           <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                              <h6 class="mb-0 text-danger fw-bold" style="font-size: 0.85rem;">
                                 <i class="ri-error-warning-line me-1 align-middle"></i>${notif.module}
                              </h6>
                              <small class="text-muted" style="font-size: 0.7rem;">${notif.created_at}</small>
                           </div>
                           <p class="mb-0 small fw-medium" style="font-size: 0.8rem;">${notif.message}</p>
                           ${errorsHtml}
                           <small class="text-muted d-block mt-1" style="font-size: 0.7rem;"><i class="ri-user-line me-1"></i>${notif.user_name}</small>
                        </li>
                     `;
                  });
                  
                  // Add click listeners to mark as read
                  document.querySelectorAll('.notification-item').forEach(item => {
                     item.addEventListener('click', function(e) {
                         // Prevent default if acting on children, but we want the whole surface clickable
                         const id = this.getAttribute('data-id');
                         fetch(`/notifications/${id}/read`, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json'
                             }
                         }).then(() => fetchNotifications());
                     });
                  });
               } else {
                  badge.style.display = 'none';
                  list.innerHTML = `
                     <li class="list-group-item border-0 d-flex flex-column align-items-center justify-content-center py-5">
                        <i class="ri-notification-badge-line text-muted mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                        <small class="text-muted">No new notifications</small>
                     </li>
                  `;
               }
            }
         });
      };

      // Fetch on load
      fetchNotifications();

      // Poll every 15 seconds
      setInterval(fetchNotifications, 15000);

      // Mark all read
      const markAllBtn = document.getElementById('mark-all-read');
      if (markAllBtn) {
         markAllBtn.addEventListener('click', function() {
             fetch('{{ route("notifications.read-all") }}', {
                 method: 'POST',
                 headers: {
                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                     'Accept': 'application/json'
                 }
             }).then(() => fetchNotifications());
         });
      }
   });
</script>
@endif
