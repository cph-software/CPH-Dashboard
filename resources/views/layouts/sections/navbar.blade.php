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

      @if (\App\Helpers\SessionCompanyHelper::isSuperAdmin() || \App\Helpers\SessionCompanyHelper::isWorkshopAdmin())
         @php
            $isSuperAdmin = \App\Helpers\SessionCompanyHelper::isSuperAdmin();
            $currentActiveCompany = session('active_company_id');
            $isGlobalClient = is_array($currentActiveCompany);
            if ($isSuperAdmin) {
                $activeCompanies = \App\Models\TyreCompany::orderBy('company_name', 'asc')->get();
                $globalLabel = "All Companies (Sistem)";
                $globalValue = '0';
                $isGlobalSelected = !$currentActiveCompany;
            } else {
                $userCompany = Auth::user()->tyreCompany;
                $activeCompanies = $userCompany ? $userCompany->children()->orderBy('company_name', 'asc')->get() : collect();
                if ($userCompany) {
                    $activeCompanies->prepend($userCompany);
                }
                $globalLabel = "Global Klien (Agregat)";
                $globalValue = 'ALL_CLIENTS';
                $isGlobalSelected = $isGlobalClient || !$currentActiveCompany;
            }

            // Determine active display name
            $activeDisplayLabel = $globalLabel;
            if (!$isGlobalSelected && $currentActiveCompany) {
                $matchedComp = $activeCompanies->firstWhere('id', $currentActiveCompany);
                if ($matchedComp) {
                    $activeDisplayLabel = $matchedComp->company_name;
                    if (!$isSuperAdmin && $matchedComp->id == Auth::user()->tyre_company_id) {
                        $activeDisplayLabel .= ' (Bengkel Anda)';
                    }
                }
            }
         @endphp
         <style>
            .custom-company-switcher-btn {
               background: #f8fafc;
               border: 1px solid #e2e8f0;
               border-radius: 8px;
               transition: all 0.2s ease;
               cursor: pointer;
            }
            .custom-company-switcher-btn:hover,
            .custom-company-switcher-btn[aria-expanded="true"] {
               background: #ffffff;
               border-color: #696cff;
               box-shadow: 0 2px 10px rgba(105, 108, 255, 0.15) !important;
            }
            .company-search-wrapper {
               position: relative;
            }
            .company-search-wrapper .search-icon {
               position: absolute;
               left: 12px;
               top: 50%;
               transform: translateY(-50%);
               color: #94a3b8;
               font-size: 1rem;
               pointer-events: none;
            }
            .custom-company-search-input {
               background-color: #f1f5f9 !important;
               border: 1px solid #e2e8f0 !important;
               border-radius: 8px !important;
               padding: 7px 12px 7px 36px !important;
               font-size: 0.83rem !important;
               color: #334155 !important;
               transition: all 0.2s ease;
            }
            .custom-company-search-input:focus {
               background-color: #ffffff !important;
               border-color: #696cff !important;
               box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.15) !important;
               outline: none !important;
            }
            .company-item-icon-wrapper {
               width: 28px;
               height: 28px;
               display: inline-flex;
               align-items: center;
               justify-content: center;
               border-radius: 6px;
               font-size: 0.9rem;
               flex-shrink: 0;
            }
            .company-switcher-item {
               border-radius: 8px !important;
               padding: 8px 12px !important;
               margin: 2px 0 !important;
               font-size: 0.85rem !important;
               color: #475569 !important;
               transition: all 0.15s ease-in-out !important;
               cursor: pointer;
               display: flex !important;
               align-items: center !important;
               justify-content: space-between !important;
            }
            .company-switcher-item:hover {
               background-color: rgba(105, 108, 255, 0.08) !important;
               color: #696cff !important;
            }
            .company-switcher-item.active {
               background-color: #696cff !important;
               color: #ffffff !important;
               font-weight: 600 !important;
            }
            .company-switcher-item.active .company-item-title {
               color: #ffffff !important;
            }
            .company-switcher-item.active .company-item-icon-wrapper {
               background-color: rgba(255, 255, 255, 0.2) !important;
               color: #ffffff !important;
            }
            .navbar-company-list::-webkit-scrollbar {
               width: 5px;
            }
            .navbar-company-list::-webkit-scrollbar-track {
               background: transparent;
            }
            .navbar-company-list::-webkit-scrollbar-thumb {
               background: #cbd5e1;
               border-radius: 10px;
            }
            .navbar-company-list::-webkit-scrollbar-thumb:hover {
               background: #94a3b8;
            }
         </style>

         <div class="navbar-nav align-items-center ms-3">
            <div class="nav-item dropdown" id="navbarCompanyDropdownContainer">
               <button class="btn btn-sm d-flex align-items-center gap-2 px-3 py-2 fw-semibold custom-company-switcher-btn"
                  type="button" id="navbarCompanySwitcherBtn" data-bs-toggle="dropdown" aria-expanded="false" style="max-width: 290px;">
                  <div class="company-item-icon-wrapper bg-label-primary text-primary" style="width: 24px; height: 24px; font-size: 0.8rem;">
                     <i class="{{ $isGlobalSelected ? 'ri-global-line' : 'ri-building-line' }}"></i>
                  </div>
                  <span class="text-truncate text-dark fw-bold" style="max-width: 210px; font-size: 0.85rem;">{{ $activeDisplayLabel }}</span>
                  <i class="ri-arrow-down-s-line text-muted ms-auto fs-6"></i>
               </button>
               <div class="dropdown-menu dropdown-menu-start border-0 shadow-lg p-2 mt-2" aria-labelledby="navbarCompanySwitcherBtn" style="min-width: 330px; max-width: 380px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.12); border: 1px solid #edf2f7;">
                  <div class="company-search-wrapper px-1 pt-1 pb-2">
                     <i class="ri-search-line search-icon"></i>
                     <input type="text" class="form-control custom-company-search-input" id="navbar_company_search_input" placeholder="Cari nama perusahaan / customer..." autocomplete="off">
                  </div>
                  <div class="dropdown-divider my-1 opacity-50"></div>
                  <div class="navbar-company-list py-1 px-1" id="navbar_company_items_list" style="max-height: 270px; overflow-y: auto;">
                     <!-- Global Option -->
                     <a class="dropdown-item company-switcher-item {{ $isGlobalSelected ? 'active' : '' }}"
                        href="javascript:void(0);" data-company-id="{{ $globalValue }}" data-company-name="{{ strtolower($globalLabel) }}">
                        <div class="d-flex align-items-center text-truncate me-2">
                           <div class="company-item-icon-wrapper me-2 {{ $isGlobalSelected ? 'bg-white text-primary' : 'bg-label-primary text-primary' }}">
                              <i class="ri-global-line"></i>
                           </div>
                           <span class="company-item-title text-truncate">{{ $globalLabel }}</span>
                        </div>
                        @if ($isGlobalSelected)
                           <i class="ri-check-line text-white fw-bold fs-6"></i>
                        @endif
                     </a>
                     <div class="dropdown-divider my-1 opacity-50"></div>
                     <!-- Companies List -->
                     @foreach ($activeCompanies as $comp)
                        @php
                           $isSelected = !$isGlobalClient && $currentActiveCompany == $comp->id;
                           $isOwn = (!$isSuperAdmin && $comp->id == Auth::user()->tyre_company_id);
                        @endphp
                        <a class="dropdown-item company-switcher-item {{ $isSelected ? 'active' : '' }}"
                           href="javascript:void(0);" data-company-id="{{ $comp->id }}" data-company-name="{{ strtolower($comp->company_name) }}">
                           <div class="d-flex align-items-center text-truncate me-2">
                              <div class="company-item-icon-wrapper me-2 {{ $isSelected ? 'bg-white text-primary' : ($isOwn ? 'bg-label-warning text-warning' : 'bg-label-secondary text-secondary') }}">
                                 <i class="{{ $isOwn ? 'ri-store-2-line' : 'ri-building-4-line' }}"></i>
                              </div>
                              <span class="company-item-title text-truncate">{{ $comp->company_name }}</span>
                              @if ($isOwn)
                                 <span class="badge {{ $isSelected ? 'bg-white text-warning' : 'bg-label-warning' }} ms-2 small py-1 px-2" style="font-size: 0.65rem;">Bengkel Anda</span>
                              @endif
                           </div>
                           @if ($isSelected)
                              <i class="ri-check-line text-white fw-bold fs-6"></i>
                           @endif
                        </a>
                     @endforeach
                     <div class="no-companies-found text-center text-muted py-3 small d-none" id="no_companies_found_msg">
                        <i class="ri-search-2-line me-1"></i> Perusahaan tidak ditemukan
                     </div>
                  </div>
               </div>
            </div>
         </div>
         
         @if(!$isSuperAdmin && !$isGlobalClient && $currentActiveCompany && $currentActiveCompany != Auth::user()->tyre_company_id)
            <span class="badge bg-label-warning fw-bold ms-2 px-3 py-2" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                <i class="ri-eye-line me-1"></i> Melihat Data: {{ \App\Models\TyreCompany::find($currentActiveCompany)->company_name ?? 'Klien' }}
            </span>
         @endif

         <script>
            document.addEventListener('DOMContentLoaded', function() {
               const searchInput = document.getElementById('navbar_company_search_input');
               const itemsList = document.getElementById('navbar_company_items_list');
               const items = itemsList ? itemsList.querySelectorAll('.company-switcher-item') : [];
               const noFoundMsg = document.getElementById('no_companies_found_msg');
               const dropdownContainer = document.getElementById('navbarCompanyDropdownContainer');

               // Focus search on open
               if (dropdownContainer && searchInput) {
                  dropdownContainer.addEventListener('shown.bs.dropdown', function () {
                     searchInput.value = '';
                     filterCompanyItems('');
                     setTimeout(() => searchInput.focus(), 100);
                  });
               }

               // Real-time filter
               function filterCompanyItems(term) {
                  const query = term.toLowerCase().trim();
                  let visibleCount = 0;
                  items.forEach(item => {
                     const name = item.getAttribute('data-company-name') || '';
                     if (!query || name.includes(query)) {
                        item.classList.remove('d-none');
                        visibleCount++;
                     } else {
                        item.classList.add('d-none');
                     }
                  });
                  if (noFoundMsg) {
                     noFoundMsg.classList.toggle('d-none', visibleCount > 0);
                  }
               }

               if (searchInput) {
                  searchInput.addEventListener('click', function(e) {
                     e.stopPropagation();
                  });
                  if (searchInput.parentElement) {
                     searchInput.parentElement.addEventListener('click', function(e) {
                        e.stopPropagation();
                     });
                  }
                  searchInput.addEventListener('input', function() {
                     filterCompanyItems(this.value);
                  });
               }

               // Switch company on click
               items.forEach(item => {
                  item.addEventListener('click', function() {
                     const companyId = this.getAttribute('data-company-id');
                     if (!companyId) return;

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
               });
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
               <li class="dropdown-menu-footer border-top">
                  <a href="{{ route('notifications.index') }}" class="dropdown-item d-flex justify-content-center p-3 text-primary">
                     <span class="fw-medium">Lihat Semua Notifikasi</span>
                  </a>
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
                     // Define styling based on status
                     let textColor = 'text-primary';
                     let iconClass = 'ri-notification-3-line';
                     
                     switch(notif.status) {
                        case 'pending':
                           textColor = 'text-warning';
                           iconClass = 'ri-time-line';
                           break;
                        case 'approved':
                           textColor = 'text-success';
                           iconClass = 'ri-checkbox-circle-line';
                           break;
                        case 'rejected':
                           textColor = 'text-danger';
                           iconClass = 'ri-close-circle-line';
                           break;
                        case 'info':
                           textColor = 'text-info';
                           iconClass = 'ri-information-line';
                           break;
                        default: // for old error notifications
                           if (notif.module && notif.module.toLowerCase().includes('error')) {
                              textColor = 'text-danger';
                              iconClass = 'ri-error-warning-line';
                           }
                     }

                     // Extract warning list
                     let errorsHtml = '';
                     if (notif.details && notif.details['Pesan Error']) {
                        let errs = Array.isArray(notif.details['Pesan Error']) ? notif.details['Pesan Error'] : [notif.details['Pesan Error']];
                        errorsHtml = `<ul class="ps-3 mb-0 mt-1 small text-danger" style="font-size: 0.75rem;">` + errs.map(e => `<li>${e}</li>`).join('') + `</ul>`;
                     }
                     if (notif.details && notif.details['reject_reason']) {
                        errorsHtml += `<div class="mt-1 small text-danger fw-bold" style="font-size: 0.75rem;">Alasan Penolakan: <span class="fw-normal">${notif.details['reject_reason']}</span></div>`;
                     }

                     list.innerHTML += `
                        <li class="list-group-item border-bottom notification-item p-3" data-id="${notif.id}" data-url="${notif.action_url}">
                           <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                              <h6 class="mb-0 ${textColor} fw-bold" style="font-size: 0.85rem;">
                                 <i class="${iconClass} me-1 align-middle"></i>${notif.module}
                              </h6>
                              <small class="text-muted" style="font-size: 0.7rem;">${notif.created_at}</small>
                           </div>
                           <p class="mb-0 small fw-medium" style="font-size: 0.8rem;">${notif.message}</p>
                           ${errorsHtml}
                           <small class="text-muted d-block mt-1" style="font-size: 0.7rem;"><i class="ri-user-line me-1"></i>${notif.user_name}</small>
                        </li>
                     `;
                  });
                  
                  // Add click listeners to mark as read and redirect
                  document.querySelectorAll('.notification-item').forEach(item => {
                     item.addEventListener('click', function(e) {
                         const id = this.getAttribute('data-id');
                         const url = this.getAttribute('data-url');
                         
                         fetch(`/notifications/${id}/read`, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json'
                             }
                         }).then(() => {
                             if (url && url !== '#') {
                                 window.location.href = url;
                             } else {
                                 fetchNotifications();
                             }
                         });
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
