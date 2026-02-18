<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
   <div class="app-brand demo">
      <a href="{{ route('dashboard') }}" class="app-brand-link">
         <span class="app-brand-logo demo">
            <span class="text-primary">
               <svg width="32" height="18" viewBox="0 0 38 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                     d="M30.0944 2.22569C29.0511 0.444187 26.7508 -0.172113 24.9566 0.849138C23.1623 1.87039 22.5536 4.14247 23.5969 5.92397L30.5368 17.7743C31.5801 19.5558 33.8804 20.1721 35.6746 19.1509C37.4689 18.1296 38.0776 15.8575 37.0343 14.076L30.0944 2.22569Z"
                     fill="currentColor" />
                  <path
                     d="M30.171 2.22569C29.1277 0.444187 26.8274 -0.172113 25.0332 0.849138C23.2389 1.87039 22.6302 4.14247 23.6735 5.92397L30.6134 17.7743C31.6567 19.5558 33.957 20.1721 35.7512 19.1509C37.5455 18.1296 38.1542 15.8575 37.1109 14.076L30.171 2.22569Z"
                     fill="url(#paint0_linear_2989_100980)" fill-opacity="0.4" />
                  <path
                     d="M22.9676 2.22569C24.0109 0.444187 26.3112 -0.172113 28.1054 0.849138C29.8996 1.87039 30.5084 4.14247 29.4651 5.92397L22.5251 17.7743C21.4818 19.5558 19.1816 20.1721 17.3873 19.1509C15.5931 18.1296 14.9843 15.8575 16.0276 14.076L22.9676 2.22569Z"
                     fill="currentColor" />
                  <path
                     d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z"
                     fill="currentColor" />
                  <path
                     d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z"
                     fill="url(#paint1_linear_2989_100980)" fill-opacity="0.4" />
                  <path
                     d="M7.82901 2.22569C8.87231 0.444187 11.1726 -0.172113 12.9668 0.849138C14.7611 1.87039 15.3698 4.14247 14.3265 5.92397L7.38656 17.7743C6.34325 19.5558 4.04298 20.1721 2.24875 19.1509C0.454514 18.1296 -0.154233 15.8575 0.88907 14.076L7.82901 2.22569Z"
                     fill="currentColor" />
                  <defs>
                     <linearGradient id="paint0_linear_2989_100980" x1="5.36642" y1="0.849138" x2="10.532" y2="24.104"
                        gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-opacity="1" />
                        <stop offset="1" stop-opacity="0" />
                     </linearGradient>
                     <linearGradient id="paint1_linear_2989_100980" x1="5.19475" y1="0.849139" x2="10.3357" y2="24.1155"
                        gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-opacity="1" />
                        <stop offset="1" stop-opacity="0" />
                     </linearGradient>
                  </defs>
               </svg>
            </span>
         </span>
         <span class="app-brand-text demo menu-text fw-semibold ms-2">CPH TYRE</span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
         <i class="icon-base ri ri-arrow-left-s-line align-middle"></i>
      </a>
   </div>

   <div class="menu-inner-shadow"></div>

   <ul class="menu-inner py-1">
      {{-- STATIC MENUS - Available for all users --}}
      {{-- <li class="menu-header small">
         <span class="menu-header-text" data-i18n="MAIN MENU">MAIN MENU</span>
      </li> --}}

      {{-- <li class="menu-item {{ request()->is('dashboard*') ? 'active' : '' }}">
         <a href="{{ url('dashboard') }}" class="menu-link">
            <i class="menu-icon icon-base ri ri-home-smile-line"></i>
            <div data-i18n="Dashboard">Dashboard</div>
         </a>
      </li> --}}

      <li class="menu-header small">
         <span class="menu-header-text" data-i18n="APPLICATIONS">APPLICATIONS</span>
      </li>

      {{-- DYNAMIC MENUS - Based on Aplikasi and Role --}}
      @php
         $aplikasiList = getAplikasiPerRole(auth()->user()->role_id);
      @endphp

      @foreach ($aplikasiList as $aplikasi)
         @php
            $appPrefix = spaceToUL($aplikasi->name);
            // Fix: Override prefix for Master Data Tyre app to match web.php route
            if ($aplikasi->id == 20) {
               $appPrefix = 'master_data_tyre';
            }
            // Check if any child menu is active to open the app dropdown
            $isAppActive = request()->is($appPrefix . '*');
            $roleMenus = getRoleMenu(auth()->user()->role_id, $aplikasi->id);
         @endphp

         @if ($roleMenus->count() > 0)
            {{-- APLIKASI SEBAGAI PARENT DROPDOWN (Standard for all apps) --}}
            <li class="menu-item {{ $isAppActive ? 'active open' : '' }}">
               <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon icon-base ri ri-apps-2-line"></i>
                  <div data-i18n="{{ $aplikasi->name }}">{{ $aplikasi->name }}</div>
               </a>
               <ul class="menu-sub">
                  @foreach ($roleMenus as $roleMenu)
                     @php
                        $menu = $roleMenu->menu;
                        $fullUrl = $appPrefix . '/' . $menu->url;
                        $isMenuActive = request()->is($fullUrl . '*');
                     @endphp

                     {{-- Single Menu Item (Level 2) --}}
                     <li class="menu-item {{ $isMenuActive ? 'active' : '' }}">
                        <a href="{{ url($fullUrl) }}" class="menu-link">
                           <div data-i18n="{{ $menu->name }}">{{ $menu->name }}</div>
                        </a>
                     </li>
                  @endforeach
               </ul>
            </li>
         @endif
      @endforeach
   </ul>
</aside>