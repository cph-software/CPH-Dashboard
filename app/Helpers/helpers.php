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
    function setLogActivity($userId, $message)
    {
        // For now, we can just log to laravel.log or ignore if table doesn't exist
        \Log::info("User ID {$userId}: {$message}");

        // If you have an ActivityLog model, you can do:
        // \App\Models\ActivityLog::create(['user_id' => $userId, 'activity' => $message]);
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

        // Check if user has access to 'Tyre Performance' (ID 20)
        $roleId = $user->role_id;
        $hasTyreAccess = \App\Models\Aplikasi::where('id', 20)
            ->whereHas('roles', function ($q) use ($roleId) {
                $q->where('role.id', $roleId);
            })->exists();

        // Secondary check via menus if explicit link is missing
        if (!$hasTyreAccess) {
            $hasTyreAccess = \App\Models\Menu::where('aplikasi_id', 20)
                ->whereHas('roles', function ($q) use ($roleId) {
                    $q->where('role.id', $roleId);
                })->exists();
        }

        if ($hasTyreAccess) {
            return '/master_data_tyre/dashboard';
        }

        return '/dashboard';
    }
}
