<?php

namespace App\Helpers;

use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class MenuHelper
{
    public static function getSidebarMenus()
    {
        $user = Auth::user();
        if (!$user || !$user->role) {
            return collect();
        }

        $roleId = $user->role_id;

        $menus = Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->whereHas('roles', function ($q) use ($roleId) {
                $q->where('role.id', $roleId);
            })
            ->with([
                'children' => function ($query) use ($roleId) {
                    $query->whereHas('roles', function ($q) use ($roleId) {
                        $q->where('role.id', $roleId);
                    })->where('is_active', true)->orderBy('order_no');
                }
            ])
            ->orderBy('order_no')
            ->get();

        return $menus;
    }
}
