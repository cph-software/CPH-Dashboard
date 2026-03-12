@php
   $user = auth()->user();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
   <div class="app-brand demo" style="height: 75px">
      <a href="{{ url('/') }}" class="app-brand-link">
         <span class="app-brand-logo demo">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 40px">
         </span>
         <span class="app-brand-text demo menu-text fw-bold ms-2">CPH TYRE</span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
         <i class="icon-base ri ri-arrow-left-s-line align-middle"></i>
      </a>
   </div>

   <div class="menu-divider mt-0"></div>

   <ul class="menu-inner py-1">
      @if ($user && $user->role)
         @php
            // Helper function to build URL and check active state
            if (!function_exists('getMenuState')) {
                function getMenuState($url, $appPrefix, $allRoleMenus, $menuId = null)
                {
                    $cleanUrl = ltrim($url, '/');
                    $fullUrl = $appPrefix ? $appPrefix . '/' . $cleanUrl : $cleanUrl;

                    $isActive = request()->is($fullUrl . '*');
                    $isOpen = $isActive;

                    // If not active, check if any children are active
                    if (!$isActive && $menuId) {
                        $children = $allRoleMenus->filter(function ($rm) use ($menuId) {
                            return $rm->menu->parent_id == $menuId;
                        });

                        foreach ($children as $child) {
                            $childState = getMenuState($child->menu->url, $appPrefix, $allRoleMenus, $child->menu->id);
                            if ($childState['isActive'] || $childState['isOpen']) {
                                $isActive = true;
                                $isOpen = true;
                                break;
                            }
                        }
                    }

                    return [
                        'url' => $fullUrl,
                        'isActive' => $isActive,
                        'isOpen' => $isOpen,
                    ];
                }
            }

            // Get all menus for this project (identified by ri-* icon pattern)
            $allRoleMenus = getAllRoleMenusForProject($user->role_id);
            $appPrefix = ''; // Routes are flat now

            // Check if user has access to Dashboard menu
            $hasDashboard = $allRoleMenus->first(function ($rm) {
                return $rm->menu->url === 'dashboard';
            });

            // Get top-level menus (no parent)
            $topLevelMenus = $allRoleMenus
                ->filter(function ($item) {
                    return is_null($item->menu->parent_id);
                })
                ->sortBy(function ($item) {
                    return $item->menu->order_no;
                });
         @endphp

         {{-- STATIC DASHBOARD --}}
         @if ($hasDashboard)
            <li class="menu-header small text-uppercase">
               <span class="menu-header-text">Monitoring</span>
            </li>
            <li
               class="menu-item {{ request()->routeIs('master_data.dashboard') || request()->is('tyre-dashboard*') ? 'active' : '' }}">
               <a href="{{ route('master_data.dashboard') }}" class="menu-link">
                  <i class="menu-icon icon-base ri ri-dashboard-3-line"></i>
                  <div data-i18n="Tyre Dashboard">Tyre Dashboard</div>
               </a>
            </li>
            <li class="menu-item {{ request()->is('monitoring*') ? 'active' : '' }}">
               <a href="{{ route('monitoring.index') }}" class="menu-link">
                  <i class="menu-icon icon-base ri ri-line-chart-line"></i>
                  <div data-i18n="Tyre Monitoring">Tyre Monitoring</div>
               </a>
            </li>
         @endif

         {{-- DYNAMIC MENU TREE --}}
         <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Menu</span>
         </li>

         @foreach ($topLevelMenus as $roleMenu)
            @php
               $menu = $roleMenu->menu;
               if ($menu->url === 'dashboard') {
                   continue;
               }

               $state = getMenuState($menu->url, $appPrefix, $allRoleMenus, $menu->id);

               $children = $allRoleMenus
                   ->filter(function ($item) use ($menu) {
                       return $item->menu->parent_id == $menu->id;
                   })
                   ->sortBy('menu.order_no');

               $hasChildren = $children->count() > 0;
            @endphp

            @if ($hasChildren)
               <li class="menu-item {{ $state['isOpen'] ? 'active open' : '' }}">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon icon-base ri {{ $menu->icon }}"></i>
                     <div data-i18n="{{ $menu->name }}">{{ $menu->name }}</div>
                  </a>
                  <ul class="menu-sub">
                     @foreach ($children as $childRoleMenu)
                        @php
                           $childMenu = $childRoleMenu->menu;
                           $childState = getMenuState($childMenu->url, $appPrefix, $allRoleMenus, $childMenu->id);

                           $grandChildren = $allRoleMenus
                               ->filter(function ($item) use ($childMenu) {
                                   return $item->menu->parent_id == $childMenu->id;
                               })
                               ->sortBy('menu.order_no');

                           $hasGrandChildren = $grandChildren->count() > 0;
                        @endphp

                        @if ($hasGrandChildren)
                           <li class="menu-item {{ $childState['isOpen'] ? 'active open' : '' }}">
                              <a href="javascript:void(0);" class="menu-link menu-toggle">
                                 <div data-i18n="{{ $childMenu->name }}">{{ $childMenu->name }}</div>
                              </a>
                              <ul class="menu-sub">
                                 @foreach ($grandChildren as $gcRoleMenu)
                                    @php
                                       $gcMenu = $gcRoleMenu->menu;
                                       $gcState = getMenuState($gcMenu->url, $appPrefix, $allRoleMenus);
                                    @endphp
                                    <li class="menu-item {{ $gcState['isActive'] ? 'active' : '' }}">
                                       <a href="{{ url($gcState['url']) }}" class="menu-link">
                                          <div data-i18n="{{ $gcMenu->name }}">{{ $gcMenu->name }}</div>
                                       </a>
                                    </li>
                                 @endforeach
                              </ul>
                           </li>
                        @else
                           <li class="menu-item {{ $childState['isActive'] ? 'active' : '' }}">
                              <a href="{{ url($childState['url']) }}" class="menu-link">
                                 <div data-i18n="{{ $childMenu->name }}">{{ $childMenu->name }}</div>
                              </a>
                           </li>
                        @endif
                     @endforeach
                  </ul>
               </li>
            @else
               <li class="menu-item {{ $state['isActive'] ? 'active' : '' }}">
                  <a href="{{ url($state['url']) }}" class="menu-link">
                     <i class="menu-icon icon-base ri {{ $menu->icon }}"></i>
                     <div data-i18n="{{ $menu->name }}">{{ $menu->name }}</div>
                  </a>
               </li>
            @endif
         @endforeach
      @endif
   </ul>
</aside>
