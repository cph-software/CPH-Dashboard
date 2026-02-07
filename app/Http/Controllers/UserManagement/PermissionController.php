<?php

namespace App\Http\Controllers\UserManagement;

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Aplikasi;
use App\Models\Menu;
use App\Models\RoleMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $aplikasi = Aplikasi::orderBy('order_no')->get();
        return view('user-management.permissions.index', compact('roles', 'aplikasi'));
    }

    public function getPermissions(Request $request)
    {
        $roleId = $request->role_id;
        $role = Role::with(['menus', 'aplikasi'])->findOrFail($roleId);
        
        $roleAppIds = $role->aplikasi->pluck('id')->toArray();
        $roleMenuIds = $role->menus->pluck('id')->toArray();

        return response()->json([
            'role_app_ids' => $roleAppIds,
            'role_menu_ids' => $roleMenuIds
        ]);
    }

    public function store(Request $request)
    {
        $roleId = $request->role_id;
        $appIds = $request->input('aplikasi_ids', []);
        $menuIds = $request->input('menu_ids', []);

        DB::transaction(function () use ($roleId, $appIds, $menuIds) {
            $role = Role::findOrFail($roleId);
            
            // Sync Applications
            $role->aplikasi()->sync($appIds);

            // Sync Menus
            $role->menus()->sync($menuIds);
        });

        return redirect()->back()->with('success', 'Permissions updated successfully');
    }
}
