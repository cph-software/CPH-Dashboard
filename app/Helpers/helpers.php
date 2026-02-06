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

        return $role->aplikasi()->get();
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
                $query->where('aplikasi_id', $aplikasiId)
                    ->where('is_active', true)
                    ->where('is_header', false) // Exclude headers
                    ->whereNull('parent_id') // Only get parent menus
                    ->orderBy('order_no');
            })
            ->with([
                'menu.children' => function ($query) {
                    $query->where('is_active', true)->orderBy('order_no');
                }
            ])
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
                $query->where('is_active', true)
                    ->whereNull('parent_id')
                    ->where('aplikasi_id', 1); // Assuming 1 is general/main app
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
        return auth()->user()?->hasPermission($menuName, $action) ?? false;
    }
}
