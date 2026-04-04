<?php

if (!function_exists('format_rupiah')) {
    function format_rupiah($number)
    {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd M Y')
    {
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('setLogActivity')) {
    /**
     * Log aktivitas user ke database.
     * 
     * Backward compatible dengan pemanggilan lama: setLogActivity($userId, $message)
     * Pemanggilan baru: setLogActivity($userId, $message, [
     *     'action_type' => 'create',    // login, create, update, delete, import, export, view
     *     'module'      => 'BA',        // BA, Invoice, Tyre, User, etc.
     *     'data_before' => [...],       // array/object sebelum perubahan
     *     'data_after'  => [...],       // array/object setelah perubahan
     *     'ip_address'  => '...',       // opsional, default dari request
     * ])
     * 
     * @param int $userId
     * @param string $message
     * @param array $options
     */
    function setLogActivity($userId, $message, $options = [])
    {
        try {
            \App\Models\ActivityLog::create([
                'user_id'         => $userId,
                'tyre_company_id' => $options['tyre_company_id'] ?? (auth()->user()->tyre_company_id ?? null),
                'project'         => $options['project'] ?? 'CPH Dashboard',
                'activity'        => $message,
                'action_type'     => $options['action_type'] ?? null,
                'module'          => $options['module'] ?? null,
                'data_before'     => $options['data_before'] ?? null,
                'data_after'      => $options['data_after'] ?? null,
                'ip_address'      => $options['ip_address'] ?? (request() ? request()->ip() : null),
            ]);
        } catch (\Exception $e) {
            // Fallback ke file log jika DB gagal (backward compat)
            \Log::info("User ID {$userId}: {$message}");
            \Log::warning("ActivityLog DB write failed: " . $e->getMessage());
        }
    }
}

if (!function_exists('getAplikasiPerRole')) {
    /**
     * Get all aplikasi that are assigned to a specific role
     * 
     * @param int $roleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getAplikasiPerRole($roleId)
    {
        $role = \App\Models\Role::find($roleId);
        if (!$role) {
            return collect();
        }

        // Get application IDs linked via assigned menus
        $menuAppIds = \App\Models\Menu::whereHas('roles', function ($q) use ($roleId) {
            $q->where('role.id', $roleId);
        })->pluck('aplikasi_id')->unique()->toArray();

        // Get application IDs explicitly linked to the role
        $explicitAppIds = $role->aplikasi()->pluck('aplikasi.id')->toArray();

        $allAppIds = array_unique(array_merge($menuAppIds, $explicitAppIds));

        if (empty($allAppIds)) {
            return collect();
        }

        return \App\Models\Aplikasi::whereIn('id', $allAppIds)->orderBy('name', 'asc')->get();
    }
}

if (!function_exists('getRoleMenu')) {
    /**
     * Get all menus for a specific role and aplikasi
     * 
     * @param int $roleId
     * @param int $aplikasiId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getRoleMenu($roleId, $aplikasiId = 0)
    {
        return \App\Models\RoleMenu::where('role_id', $roleId)
            ->whereHas('menu', function ($query) use ($aplikasiId) {
                $query->where('aplikasi_id', $aplikasiId);
            })
            ->with('menu')
            ->get();
    }
}

if (!function_exists('getAllRoleMenusForProject')) {
    /**
     * Get all menus for a role that belong to THIS project.
     * Identifies project menus by icon pattern: ri-* (RemixIcon).
     * The other project uses FontAwesome (<i class="fas fa-...">).
     *
     * @param int $roleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getAllRoleMenusForProject($roleId)
    {
        return \App\Models\RoleMenu::where('role_id', $roleId)
            ->whereHas('menu', function ($query) {
                // Allows RemixIcon (ri-), BoxIcons (bx-), and also generic dashboard or menu without icon
                $query->where('icon', 'like', 'ri-%')
                      ->orWhere('icon', 'like', 'bx-%')
                      ->orWhereNull('icon')
                      ->orWhere('url', 'tyre-dashboard')
                      ->orWhere('url', 'dashboard');
            })
            ->with('menu')
            ->get();
    }
}

if (!function_exists('getGeneralMenu')) {
    /**
     * Get general menus (menus without specific aplikasi or global menus)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getGeneralMenu()
    {
        $roleId = auth()->user()->role_id ?? null;
        if (!$roleId) {
            return collect();
        }

        return \App\Models\RoleMenu::where('role_id', $roleId)
            ->whereHas('menu', function ($query) {
                $query->where('aplikasi_id', 1); // Assuming 1 is general/main app
            })
            ->with('menu')
            ->get();
    }
}

if (!function_exists('spaceToUL')) {
    /**
     * Convert space to underscore and lowercase
     * 
     * @param string $string
     * @return string
     */
    function spaceToUL($string)
    {
        return strtolower(str_replace(" ", "_", $string));
    }
}

if (!function_exists('hasPermission')) {
    /**
     * Check if user has permission for a specific menu and action
     * 
     * @param string $menuName
     * @param string $action (view, create, update, delete)
     * @return bool
     */
    function hasPermission($menuName, $action = 'view')
    {
        return optional(auth()->user())->hasPermission($menuName, $action) ?? false;
    }
}
if (!function_exists('getDashboardRedirectUrl')) {
    /**
     * Determine the correct dashboard URL based on user role and application access.
     * 
     * @return string
     */
    function getDashboardRedirectUrl()
    {
        $user = auth()->user();
        if (!$user)
            return '/login';

        // Check if user has access to Tyre app by name OR IDs
        $roleId = $user->role_id;
        
        $tyreApp = \App\Models\Aplikasi::where('name', 'Master Data Tyre')
                    ->orWhere('name', 'Tyre Performance')
                    ->orWhere('id', 2)
                    ->orWhere('id', 3)
                    ->first();
                    
        $tyreAppId = $tyreApp ? $tyreApp->id : null;

        $hasTyreAccess = false;
        if ($tyreAppId) {
            $hasTyreAccess = \App\Models\Aplikasi::where('id', $tyreAppId)
                ->whereHas('roles', function ($q) use ($roleId) {
                    $q->where('role.id', $roleId);
                })->exists();

            // Secondary check via menus if explicit link is missing
            if (!$hasTyreAccess) {
                $hasTyreAccess = \App\Models\Menu::where('aplikasi_id', $tyreAppId)
                    ->whereHas('roles', function ($q) use ($roleId) {
                        $q->where('role.id', $roleId);
                    })->exists();
            }
        }

        if ($hasTyreAccess) {
            return '/tyre-dashboard';
        }

        return '/dashboard';
    }
}
