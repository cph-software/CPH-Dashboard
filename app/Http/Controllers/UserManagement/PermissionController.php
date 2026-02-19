<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Aplikasi;
use App\Models\Menu;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = Role::withCount('users')->get();
        $aplikasi = Aplikasi::orderBy('name')->get();
        return view('user-management.permissions.index', compact('roles', 'aplikasi'));
    }

    public function getPermissions(Request $request)
    {
        $roleId = $request->role_id;
        $role = Role::with(['menus', 'aplikasi'])->findOrFail($roleId);
        
        $roleAppIds = $role->aplikasi->pluck('id')->toArray();
        $roleMenuIds = $role->menus->pluck('id')->toArray();
        
        // Map menu permissions for easier JS lookup
        $rolePermissions = [];
        foreach ($role->menus as $menu) {
            $rolePermissions[$menu->id] = json_decode($menu->pivot->permissions, true) ?: [];
        }

        return response()->json([
            'role_app_ids' => $roleAppIds,
            'role_menu_ids' => $roleMenuIds,
            'role_permissions' => $rolePermissions
        ]);
    }

    public function store(Request $request)
    {
        $roleId = $request->role_id;
        $menuIds = $request->input('menu_ids', []);
        $menuPermissions = $request->input('menu_permissions', []);

        $this->roleService->updateWithPermissions($roleId, [], $menuIds, $menuPermissions);

        return redirect()->back()->with('success', 'Permission matrix updated successfully');
    }
}
